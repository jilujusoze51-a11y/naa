@extends('admin-base')
@section('title','Dashboard — Admin')
@section('heading','Dashboard')
@section('admin')
<div class="space-y-5">
  <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
    <a href="/admin/kyc" class="card rounded-lg p-5 hover:border-yellow-600 transition">
      <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">PENDING KYC</div>
      <div class="dsp text-4xl font-extrabold mn" style="color:{{ $pendingKyc?'var(--heat)':'inherit' }}">{{ $pendingKyc }}</div>
      <div class="text-[11px] mt-1" style="color:var(--dim)">IDs awaiting verification</div>
    </a>
    <a href="/admin/sales" class="card rounded-lg p-5 hover:border-yellow-600 transition">
      <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">PENDING SALES</div>
      <div class="flex items-baseline gap-2">
        <div class="dsp text-4xl font-extrabold mn" style="color:{{ $pendingSales?'var(--heat)':'inherit' }}">{{ $pendingSales }}</div>
        <div class="dsp text-lg font-extrabold mn gold">${{ number_format($salesValue) }}</div>
      </div>
      <div class="text-[11px] mt-1" style="color:var(--dim)">awaiting approval</div>
    </a>
    <a href="/admin/pipeline" class="card rounded-lg p-5 hover:border-yellow-600 transition">
      <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">NEW LEADS</div>
      <div class="dsp text-4xl font-extrabold mn" style="color:{{ $newLeads?'var(--sig)':'inherit' }}">{{ $newLeads }}</div>
      <div class="text-[11px] mt-1" style="color:var(--dim)">unworked</div>
    </a>
    <a href="/admin/bids" class="card rounded-lg p-5 hover:border-yellow-600 transition">
      <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">BIDS — 24H</div>
      <div class="flex items-baseline gap-2">
        <div class="dsp text-4xl font-extrabold mn">{{ number_format($bids24) }}</div>
        <div class="dsp text-lg font-extrabold mn gold">${{ number_format($vol24) }}</div>
      </div>
    </a>
  </div>

  <div class="card rounded-lg p-5">
    <div class="dsp font-extrabold mb-4">Quick actions</div>
    <div class="grid sm:grid-cols-3 gap-3">
      <a href="/admin/vehicles/new" class="rounded p-4 hover:border-yellow-600 transition"
         style="background:var(--void);border:1px solid var(--edge)">
        <div class="text-xl mb-2">🚗</div><div class="font-bold text-[13px]">Add vehicle</div></a>
      <a href="/admin/kyc" class="rounded p-4 hover:border-yellow-600 transition"
         style="background:var(--void);border:1px solid var(--edge)">
        <div class="text-xl mb-2">🪪</div><div class="font-bold text-[13px]">Review KYC</div></a>
      <a href="/admin/sales" class="rounded p-4 hover:border-yellow-600 transition"
         style="background:var(--void);border:1px solid var(--edge)">
        <div class="text-xl mb-2">✓</div><div class="font-bold text-[13px]">Approve sales</div></a>
    </div>
  </div>

  <div class="card rounded-lg overflow-hidden">
    <div class="px-5 py-3 border-b hair" style="background:var(--card2)">
      <span class="dsp font-extrabold">All time</span></div>
    <div class="grid grid-cols-2 md:grid-cols-5">
      @foreach(['TOTAL USERS'=>$totals['users'],'VEHICLES'=>$totals['vehicles'],'TOTAL BIDS'=>$totals['bids'],
                'SOLD'=>$totals['sold'],'REVENUE'=>'$'.number_format($totals['revenue'])] as $k=>$v)
        <div class="p-5 border-r border-b hair">
          <div class="text-[10px] tracking-[.1em] mb-2" style="color:var(--dim)">{{ $k }}</div>
          <div class="dsp text-2xl font-extrabold mn">{{ $v }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-4">
    <div class="card rounded-lg overflow-hidden">
      <div class="px-5 py-3 border-b hair flex justify-between" style="background:var(--card2)">
        <span class="dsp font-extrabold text-[15px]">Pending verifications</span>
        <a href="/admin/kyc" class="text-[11px] font-bold gold">All →</a></div>
      @forelse($recentKyc as $u)
        <a href="/admin/kyc/{{ $u->id }}" class="px-4 py-3 border-b hair flex justify-between hover:bg-white/5">
          <div><div class="text-[13px] font-bold">{{ $u->name }}</div>
               <div class="text-[11px]" style="color:var(--dim)">{{ $u->email }}</div></div>
          <span class="text-[11px] gold font-bold">Review →</span>
        </a>
      @empty
        <div class="px-5 py-8 text-center text-[12px]" style="color:var(--dim)">✓ Nothing waiting</div>
      @endforelse
    </div>

    <div class="card rounded-lg overflow-hidden">
      <div class="px-5 py-3 border-b hair flex justify-between" style="background:var(--card2)">
        <span class="dsp font-extrabold text-[15px]">Recent bids</span>
        <a href="/admin/bids" class="text-[11px] font-bold gold">All →</a></div>
      @forelse($recentBids as $b)
        <div class="px-4 py-2.5 border-b hair flex justify-between text-[13px]">
          <span>{{ $b->bidder_name }} <span style="color:var(--dim)">· Lot #{{ $b->vehicle?->lot }}</span></span>
          <span class="mn font-bold gold">${{ number_format($b->amount) }}</span>
        </div>
      @empty
        <div class="px-5 py-8 text-center text-[12px]" style="color:var(--dim)">No bids yet</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
