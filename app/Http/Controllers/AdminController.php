<?php
namespace App\Http\Controllers;

use App\Models\{User, Vehicle, Bid, Sale, Lead, Photo};
use App\Services\AuctionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct(private AuctionService $auction) {}

    // ── DASHBOARD ───────────────────────────────────────────────
    public function dashboard()
    {
        return view('admin.dashboard', [
            'pendingKyc'   => User::where('status','pending')->count(),
            'pendingSales' => Sale::where('status','pending')->count(),
            'salesValue'   => Sale::where('status','pending')->sum('amount'),
            'newLeads'     => Lead::where('stage','new')->count(),
            'bids24'       => Bid::where('created_at','>=',now()->subDay())->count(),
            'vol24'        => Bid::where('created_at','>=',now()->subDay())->sum('amount'),
            'recentKyc'    => User::where('status','pending')->latest()->take(6)->get(),
            'recentLeads'  => Lead::latest()->take(6)->get(),
            'recentBids'   => Bid::with('vehicle')->latest()->take(8)->get(),
            'totals'       => [
                'users'    => User::count(),
                'vehicles' => Vehicle::count(),
                'bids'     => Bid::count(),
                'sold'     => Vehicle::where('status','sold')->count(),
                'revenue'  => Sale::where('status','sold')->sum('amount'),
            ],
        ]);
    }

    // ── KYC ─────────────────────────────────────────────────────
    public function kyc(Request $r)
    {
        $q = User::where('role','bidder');
        $f = $r->get('f','pending');
        if ($f !== 'all') $q->where('status',$f);
        if ($s = $r->get('q')) {
            $q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        }
        return view('admin.kyc', ['users'=>$q->latest()->paginate(20)->withQueryString(), 'f'=>$f]);
    }

    public function kycReview(User $user)
    {
        return view('admin.kyc-review', ['u'=>$user]);
    }

    /**
     * FIX: serve identity documents from the PRIVATE disk, behind auth+admin.
     * Previously these were written to the public disk and linked with
     * Storage::url(), which exposed every driver's licence at an
     * unauthenticated URL. This route is the only way to read them.
     */
    public function kycDocument(User $user, string $side)
    {
        abort_unless(in_array($side, ['front','back'], true), 404);

        $path = $side === 'front' ? $user->doc_front : $user->doc_back;

        abort_unless($path, 404);
        abort_unless(Storage::disk(config('filesystems.private_disk'))->exists($path), 404);

        return response()->file(Storage::disk(config('filesystems.private_disk'))->path($path), [
            'Cache-Control'      => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function kycDecide(Request $r, User $user)
    {
        if ($r->action === 'approve') {
            $user->update(['verified'=>true,'status'=>'active','decision'=>'Approved by admin']);
            $msg = "$user->name approved — they can now bid.";
        } else {
            $reason = trim((string) $r->reason);
            $user->update(['verified'=>false,'status'=>'rejected',
                'decision'=>$reason !== '' ? "Rejected — $reason" : 'Rejected by admin']);
            $msg = "$user->name rejected.";
        }
        if ($r->filled('admin_notes')) $user->update(['admin_notes'=>$r->admin_notes]);
        return redirect('/admin/kyc')->with('ok',$msg);
    }

    // ── VEHICLES ────────────────────────────────────────────────
    public function vehicles(Request $r)
    {
        $q = Vehicle::query();
        if ($s = $r->get('q')) {
            $q->where(fn($x)=>$x->where('make','like',"%$s%")->orWhere('model','like',"%$s%")
                ->orWhere('vin','like',"%$s%")->orWhere('lot','like',"%$s%"));
        }
        if ($st = $r->get('status')) $q->where('status',$st);
        return view('admin.vehicles', ['vehicles'=>$q->latest('id')->paginate(20)->withQueryString()]);
    }

    public function vehicleForm(?Vehicle $vehicle = null)
    {
        return view('admin.vehicle-form', ['v'=>$vehicle ?? new Vehicle(['status'=>'draft'])]);
    }

    public function vehicleSave(Request $r, ?Vehicle $vehicle = null)
    {
        $d = $r->validate([
            'lot'=>['required','string','max:40'],
            'year'=>['nullable','integer'],
            'make'=>['required','string','max:60'],
            'model'=>['required','string','max:60'],
            'trim'=>['nullable','string','max:80'],
            'vin'=>['nullable','string','max:40'],
            'miles'=>['nullable','integer'],
            'title_status'=>['nullable','string','max:30'],
            'location'=>['nullable','string','max:120'],
            'engine'=>['nullable','string','max:80'],
            'transmission'=>['nullable','string','max:60'],
            'color'=>['nullable','string','max:60'],
            'start_bid'=>['nullable','numeric'],
            'reserve'=>['nullable','numeric'],
            'buy_now'=>['nullable','numeric'],
            'status'=>['required','string'],
            'agent'=>['nullable','string','max:80'],
        ]);
        $d['hot'] = $r->boolean('hot');

        $vehicle = $vehicle && $vehicle->exists
            ? tap($vehicle)->update($d)
            : Vehicle::create($d);

        // new photos
        foreach ((array) $r->file('photos', []) as $file) {
            Photo::create([
                'vehicle_id'=>$vehicle->id,
                'path'=>$file->store('vehicles','public'),
                'sort'=>($vehicle->photos()->max('sort') ?? -1) + 1,
            ]);
        }

        return redirect('/admin/vehicles')->with('ok',"Lot #{$vehicle->lot} saved.");
    }

    public function vehicleDelete(Vehicle $vehicle)
    {
        $vehicle->delete();
        return back()->with('ok','Vehicle deleted.');
    }

    public function goLive(Request $r, Vehicle $vehicle)
    {
        $this->auction->goLive($vehicle, (int) $r->get('minutes',10));
        return back()->with('ok',"Lot #{$vehicle->lot} is live.");
    }

    // photo actions
    public function photoDelete(Photo $photo)
    {
        Storage::disk('public')->delete($photo->path);
        $photo->delete();
        return back()->with('ok','Photo removed.');
    }

    public function photoSort(Request $r, Vehicle $vehicle)
    {
        foreach ((array) $r->order as $i => $id) {
            Photo::where('id',$id)->where('vehicle_id',$vehicle->id)->update(['sort'=>$i]);
        }
        return response()->json(['ok'=>true]);
    }

    // ── BIDS ────────────────────────────────────────────────────
    public function bids(Request $r)
    {
        $q = Bid::with(['vehicle','user']);
        if ($s = $r->get('q')) $q->where('bidder_name','like',"%$s%");
        return view('admin.bids', ['bids'=>$q->latest()->paginate(30)->withQueryString()]);
    }

    public function bidDelete(Bid $bid)
    {
        $v = $bid->vehicle;
        $bid->delete();
        $top = $v->topBid();
        $v->update(['current_bid'=>$top->amount ?? $v->start_bid, 'winner_id'=>$top->user_id ?? null]);
        return back()->with('ok','Bid removed.');
    }

    // ── SALE APPROVALS ──────────────────────────────────────────
    public function sales(Request $r)
    {
        $f = $r->get('f','pending');
        return view('admin.sales', [
            'sales'=>Sale::with(['vehicle','user'])->where('status',$f)->latest()->paginate(20),
            'f'=>$f,
            'counts'=>[
                'pending'=>Sale::where('status','pending')->count(),
                'sold'=>Sale::where('status','sold')->count(),
                'unsold'=>Sale::where('status','unsold')->count(),
                'rejected'=>Sale::where('status','rejected')->count(),
            ],
        ]);
    }

    public function saleDecide(Request $r, Sale $sale)
    {
        if ($r->action === 'approve') {
            $sale->update(['status'=>'sold','decision'=>'Invoice sent']);
            $sale->vehicle->update(['status'=>'sold']);
            $msg = 'Approved — invoice sent.';
        } else {
            $sale->update(['status'=>'rejected','decision'=>'Rejected — rolled to next round']);
            $sale->vehicle->update(['status'=>'unsold']);
            $msg = 'Rejected — lot rolled to the next round.';
        }
        return back()->with('ok',$msg);
    }

    // ── LEADS ───────────────────────────────────────────────────
    public function leads(Request $r)
    {
        $q = Lead::with('vehicle');
        if ($f = $r->get('f')) {
            if ($f==='unread') $q->where('read',false);
            if ($f==='offer')  $q->where('subject','Make Offer');
            if ($f==='contact')$q->where('subject','Contact');
        }
        return view('admin.leads', [
            'leads'=>$q->latest()->paginate(25)->withQueryString(),
            'unread'=>Lead::where('read',false)->count(),
        ]);
    }

    public function leadShow(Lead $lead)
    {
        $lead->update(['read'=>true]);
        return view('admin.lead-show', ['l'=>$lead]);
    }

    public function leadUpdate(Request $r, Lead $lead)
    {
        $lead->update($r->only(['stage','lead_status','agent','notes']));
        return back()->with('ok','Lead updated.');
    }

    // ── PIPELINE ────────────────────────────────────────────────
    public function pipeline()
    {
        return view('admin.pipeline', [
            'leads'=>Lead::with('vehicle')->latest()->get(),
            'stages'=>[
                'new'=>'New Leads','na'=>'N/A','call'=>'First Call',
                'int'=>'Interested','paper'=>'Paper Work','inv'=>'Invoice Sent','paid'=>'Paid',
            ],
        ]);
    }

    public function pipelineMove(Request $r, Lead $lead)
    {
        $lead->update($r->only(['stage','lead_status']));
        return response()->json(['ok'=>true]);
    }

    // ── USERS ───────────────────────────────────────────────────
    public function users(Request $r)
    {
        $q = User::query();
        if ($s = $r->get('q')) {
            $q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        }
        if ($st = $r->get('status')) $q->where('status',$st);
        return view('admin.users', ['users'=>$q->latest()->paginate(25)->withQueryString()]);
    }

    public function userToggle(User $user)
    {
        $user->update(['status'=>$user->status==='deactivated' ? 'active' : 'deactivated']);
        return back()->with('ok',"$user->name updated.");
    }
}
