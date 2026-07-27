@extends('admin-base')
@section('title','Sale Approvals — Admin')
@section('heading','Sale Approvals')
@section('admin')
<div class="flex flex-wrap gap-1 mb-4 border-b hair">
  @foreach(['pending'=>'PENDING APPROVAL','sold'=>'SOLD','unsold'=>'UNSOLD','rejected'=>'REJECTED'] as $k=>$lbl)
    <a href="?f={{ $k }}" class="px-4 py-2.5 text-[12px] font-bold border-b-2"
       style="{{ $f===$k ? 'border-color:var(--gold);color:var(--bone)' : 'border-color:transparent;color:var(--dim)' }}">
      {{ $lbl }} <span class="mn ml-1">{{ $counts[$k] }}</span></a>
  @endforeach
</div>

<div class="space-y-3">
@forelse($sales as $s)
  <div class="card rounded-lg p-5 flex flex-wrap gap-5">
    <div class="thumb w-32 h-24 rounded flex items-center justify-center text-3xl shrink-0">🚗</div>
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2 mb-2">
        <span class="px-2 py-0.5 rounded text-[10px] font-bold"
              style="background:{{ $s->kind==='buynow'?'rgba(16,185,129,.14)':'rgba(245,158,11,.14)' }};
                     color:{{ $s->kind==='buynow'?'var(--live)':'var(--note)' }}">
          {{ $s->kind==='buynow'?'BUY NOW':'HIGH BID' }}</span>
        <span class="mn text-[11px]" style="color:var(--dim)">Lot #{{ $s->vehicle?->lot }}</span>
      </div>
      <div class="dsp text-xl font-extrabold">{{ $s->vehicle?->title() }}</div>
      <div class="text-[12px] mt-2" style="color:var(--ash)">
        {{ $s->user?->name }} · {{ $s->user?->email }} · {{ $s->user?->phone }}
      </div>
    </div>
    <div class="text-right shrink-0">
      <div class="text-[10px] tracking-[.1em]" style="color:var(--dim)">
        {{ $s->kind==='buynow'?'BUY NOW PRICE':'WINNING BID' }}</div>
      <div class="dsp text-3xl font-extrabold mn gold">${{ number_format($s->amount,2) }}</div>
      <div class="text-[11px] mt-1" style="color:var(--dim)">{{ $s->created_at->diffForHumans() }}</div>
      @if($s->status==='pending')
      <form method="POST" action="/admin/sales/{{ $s->id }}" class="flex gap-2 justify-end mt-3">@csrf
        <button name="action" value="approve" class="px-4 py-2 rounded text-[12px] btn-ok">✓ Approve</button>
        <button name="action" value="reject" class="px-4 py-2 rounded text-[12px] btn-no">✕ Reject</button>
      </form>
      @else
        <div class="text-[11px] mt-3 font-bold" style="color:var(--dim)">{{ $s->decision }}</div>
      @endif
    </div>
  </div>
@empty
  <div class="card rounded-lg px-5 py-14 text-center">
    <div class="text-[13px] font-bold mb-1">Nothing in this ledger</div>
    <div class="text-[12px]" style="color:var(--dim)">Won lots land here after a live round closes.</div>
  </div>
@endforelse
</div>
<div class="mt-6">{{ $sales->links() }}</div>
@endsection
