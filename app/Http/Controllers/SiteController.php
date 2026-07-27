<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Lead;
use App\Models\Sale;
use App\Services\AuctionService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(private AuctionService $auction) {}

    public function home()
    {
        return view('site.home', [
            'live'   => Vehicle::where('status','live')->orderBy('ends_at')->first(),
            'next'   => Vehicle::where('status','upcoming')->orderBy('id')->first(),
            'hot'    => Vehicle::where('hot',true)->whereIn('status',['live','upcoming'])->take(4)->get(),
            'counts' => [
                'lots' => Vehicle::whereIn('status',['live','upcoming'])->count(),
                'sold' => Vehicle::where('status','sold')->count(),
            ],
        ]);
    }

    public function inventory(Request $r)
    {
        $q = Vehicle::query()->whereIn('status',['live','upcoming','sold']);

        if ($s = $r->get('q')) {
            $q->where(fn($x)=>$x->where('make','like',"%$s%")
                ->orWhere('model','like',"%$s%")
                ->orWhere('vin','like',"%$s%")
                ->orWhere('lot','like',"%$s%"));
        }
        if ($m = $r->get('make'))   $q->where('make',$m);
        if ($t = $r->get('title'))  $q->where('title_status',$t);
        if ($mx = $r->get('max'))   $q->where('start_bid','<=',$mx);

        return view('site.inventory', [
            'vehicles' => $q->orderByDesc('hot')->orderBy('lot')->paginate(12)->withQueryString(),
            'makes'    => Vehicle::whereIn('status',['live','upcoming'])
                            ->distinct()->orderBy('make')->pluck('make')->filter(),
        ]);
    }

    public function lot(Vehicle $vehicle)
    {
        $vehicle->load('photos');
        return view('site.lot', [
            'v'       => $vehicle,
            'similar' => Vehicle::where('id','!=',$vehicle->id)
                            ->whereIn('status',['live','upcoming'])
                            ->where('make',$vehicle->make)->take(4)->get(),
        ]);
    }

    /** Live status for the countdown — polled by the lot page. */
    public function lotStatus(Vehicle $vehicle)
    {
        $vehicle->refresh();
        return response()->json([
            'current_bid' => (float) $vehicle->current_bid,
            'bids'        => $vehicle->bids()->count(),
            'seconds'     => $vehicle->secondsLeft(),
            'status'      => $vehicle->status,
            'leader'      => $vehicle->topBid()?->bidder_name,
            'you_lead'    => auth()->check() && $vehicle->winner_id === auth()->id(),
        ]);
    }

    public function bid(Request $r, Vehicle $vehicle)
    {
        $r->validate(['max'=>['required','numeric','min:1']]);
        $res = $this->auction->placeBid($vehicle, $r->user(), (float) $r->max);
        return back()->with($res['ok'] ? 'ok' : 'err', $res['msg']);
    }

    public function buyNow(Request $r, Vehicle $vehicle)
    {
        $res = $this->auction->buyNow($vehicle, $r->user());
        return back()->with($res['ok'] ? 'ok' : 'err', $res['msg']);
    }

    public function offer(Request $r, Vehicle $vehicle)
    {
        $d = $r->validate([
            'name'=>['required','string','max:120'],
            'email'=>['required','email'],
            'phone'=>['nullable','string','max:40'],
            'offer'=>['required','numeric','min:1'],
        ]);
        Lead::create($d + [
            'subject'=>'Make Offer','vehicle_id'=>$vehicle->id,'stage'=>'new',
        ]);
        return back()->with('ok','Offer submitted. An agent will relay the vendor\'s response.');
    }

    public function contact(Request $r)
    {
        $d = $r->validate([
            'name'=>['required','string','max:120'],
            'email'=>['required','email'],
            'phone'=>['nullable','string','max:40'],
            'message'=>['required','string','max:2000'],
        ]);
        Lead::create($d + ['subject'=>'Contact','stage'=>'new']);
        return back()->with('ok','Message sent. An agent will reach out shortly.');
    }

    public function wins()
    {
        return view('site.wins', [
            'sold' => Vehicle::where('status','sold')->latest('updated_at')->take(12)->get(),
            'mine' => auth()->check()
                ? Sale::with('vehicle')->where('user_id',auth()->id())->latest()->get()
                : collect(),
        ]);
    }

    public function page(string $slug)
    {
        abort_unless(in_array($slug,['how','about','contact']), 404);
        return view("site.$slug");
    }
}
