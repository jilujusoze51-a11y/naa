@extends('admin-base')
@section('title','Review — '.$u->name)
@section('heading','Review — '.$u->name)
@section('admin')
<a href="/admin/kyc" class="text-[12px] mb-4 inline-block" style="color:var(--dim)">← Registrations</a>

<form method="POST" action="/admin/kyc/{{ $u->id }}">@csrf
<div class="card rounded-lg p-5 mb-4">
  <div class="dsp font-extrabold mb-0.5">Admin Notes</div>
  <div class="text-[11px] mb-3" style="color:var(--heat)">Private — only admins see this.</div>
  <textarea name="admin_notes" rows="3" class="w-full px-3 py-2.5 rounded text-[13px]">{{ $u->admin_notes }}</textarea>
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-4">
  <div class="card rounded-lg p-6">
    <div class="dsp text-xl font-extrabold mb-4">Applicant Details</div>
    <div class="rounded p-4 mb-5" style="background:var(--void);border-left:3px solid var(--sig)">
      <div class="text-[10px] tracking-[.1em] mb-1" style="color:var(--sig)">
        {{ $u->is_business ? 'BUSINESS ACCOUNT' : 'INDIVIDUAL BUYER' }}</div>
      <div class="dsp text-lg font-extrabold">{{ $u->company ?: $u->name }}</div>
    </div>
    <div class="grid sm:grid-cols-2 gap-x-8">
      @foreach(['FULL NAME'=>$u->name,'EMAIL'=>$u->email,'PHONE'=>$u->phone,'CITY'=>$u->city,
                'SUBMITTED'=>$u->created_at->format('M j, Y g:i A'),'SOURCE'=>$u->source,
                'STATUS'=>strtoupper($u->status)] as $k=>$v)
        <div class="flex justify-between py-2.5 border-b hair text-[13px]">
          <span class="text-[10px] tracking-[.1em]" style="color:var(--dim)">{{ $k }}</span>
          <span class="font-bold">{{ $v ?: '—' }}</span>
        </div>
      @endforeach
    </div>

    <div class="dsp font-extrabold mt-6 mb-3">Identity Documents</div>
    <div class="grid sm:grid-cols-2 gap-4">
      @foreach(['FRONT'=>['front',$u->doc_front],'BACK'=>['back',$u->doc_back]] as $side=>[$key,$path])
        <div>
          <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">DRIVER'S LICENSE — {{ $side }}</div>
          @if($path)
            <a href="{{ route('admin.kyc.doc', [$u, $key]) }}" target="_blank">
              <img src="{{ route('admin.kyc.doc', [$u, $key]) }}" class="rounded w-full h-56 object-cover"
                   style="border:1px solid var(--edge)" alt="">
            </a>
            <div class="text-[10px] mt-1.5" style="color:var(--dim)">Click to open full size</div>
          @else
            <div class="rounded h-56 flex items-center justify-center text-[12px]"
                 style="background:var(--void);border:1px dashed var(--edge);color:var(--dim)">Not uploaded</div>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  <div class="space-y-4">
    <div class="card rounded-lg p-5">
      <div class="dsp font-extrabold mb-0.5">Approve</div>
      <div class="text-[11px] mb-3" style="color:var(--ash)">Unlocks sign-in and bidding.</div>
      <button name="action" value="approve" class="w-full py-3 rounded btn-ok">✓ Approve</button>
    </div>
    <div class="card rounded-lg p-5">
      <div class="dsp font-extrabold mb-0.5">Reject</div>
      <div class="text-[11px] mb-3" style="color:var(--ash)">Records the reason below.</div>
      <textarea name="reason" rows="3" placeholder="e.g. License photo unreadable. Please resubmit."
                class="w-full px-3 py-2.5 rounded text-[13px] mb-3"></textarea>
      <button name="action" value="reject" class="w-full py-3 rounded btn-s">✕ Reject</button>
    </div>
    @if($u->decision)
    <div class="card rounded-lg p-5">
      <div class="text-[10px] tracking-[.1em] mb-1.5" style="color:var(--dim)">DECISION</div>
      <div class="text-[12px]" style="color:var(--ash)">{{ $u->decision }}</div>
    </div>
    @endif
  </div>
</div>
</form>
@endsection
