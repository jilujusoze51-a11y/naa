@extends('layout')
@section('title','Repossessed Vehicle Auctions — NAA')
@section('body')
@include('partials.nav')

<div class="border-b hair">
  <div class="max-w-[1400px] mx-auto px-5 py-14 grid lg:grid-cols-[1fr_560px] gap-10 items-center">
    <div>
      <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded text-[10px] font-bold tracking-[.14em] mb-6"
           style="background:{{ $live ? 'rgba(16,185,129,.1)' : 'var(--card)' }};color:{{ $live ? 'var(--live)' : 'var(--ash)' }};border:1px solid var(--edge)">
        <span class="w-1.5 h-1.5 rounded-full {{ $live ? 'lv' : '' }}"
              style="background:{{ $live ? 'var(--live)' : 'var(--ash)' }}"></span>
        {{ $live ? 'AUCTION IN PROGRESS' : 'ONLINE REPOSSESSION AUCTIONS' }}
      </div>
      <h1 class="dsp text-[46px] sm:text-[60px] font-extrabold leading-[.95] mb-6">
        Repossessed vehicles.<br><span class="gold">Lender prices.</span><br>Live.
      </h1>
      <p class="text-[15px] leading-relaxed mb-8 max-w-lg" style="color:var(--ash)">
        Bid live on repossessed cars and trucks straight from regulated lenders —
        reserves shown up front, proxy bidding built in. No dealership markup.
      </p>
      <div class="flex flex-wrap gap-3 mb-9">
        <a href="/inventory" class="px-6 py-3.5 rounded btn-g">Browse inventory</a>
        @guest<a href="/register" class="px-6 py-3.5 rounded btn-o font-semibold">Register free to bid</a>@endguest
      </div>
      <div class="grid grid-cols-3 gap-5 pt-7 border-t hair max-w-md">
        <div><div class="dsp text-[22px] font-extrabold gold mn">{{ $counts['lots'] }}</div>
             <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">LOTS ON FLOOR</div></div>
        <div><div class="dsp text-[22px] font-extrabold gold mn">{{ $counts['sold'] }}</div>
             <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">LOTS SOLD</div></div>
        <div><div class="dsp text-[22px] font-extrabold gold mn">20–40%</div>
             <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">BELOW RETAIL</div></div>
      </div>
    </div>

    @php $feature = $live ?? $next; @endphp
    @if($feature)
    <div class="card rounded-lg overflow-hidden">
      <div class="px-4 py-3 border-b hair flex items-center justify-between" style="background:var(--card2)">
        <span class="text-[10px] font-bold tracking-[.14em]"
              style="color:{{ $live ? 'var(--live)' : 'var(--gold-lt)' }}">
          {{ $live ? '● LIVE NOW · ON THE BLOCK' : 'NEXT LOT' }}
        </span>
        <span class="mn text-[11px]" style="color:var(--ash)">LOT #{{ $feature->lot }}</span>
      </div>
      <div class="thumb h-56 flex items-center justify-center text-7xl">🚗</div>
      <div class="p-5">
        <div class="dsp text-2xl font-extrabold mb-4">{{ $feature->title() }}</div>
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="rounded p-3" style="background:var(--void);border:1px solid var(--edge)">
            <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">
              {{ $live ? 'CURRENT BID' : 'OPENING BID' }}</div>
            <div class="dsp text-2xl font-extrabold gold mn">
              ${{ number_format($live ? $feature->current_bid : $feature->start_bid) }}</div>
          </div>
          <div class="rounded p-3" style="background:var(--void);border:1px solid var(--edge)">
            <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">BUY NOW</div>
            <div class="dsp text-2xl font-extrabold mn">
              {{ $feature->buy_now > 0 ? '$'.number_format($feature->buy_now) : '—' }}</div>
          </div>
        </div>
        <a href="/lot/{{ $feature->id }}" class="block w-full py-3 rounded btn-g text-center">
          {{ $live ? 'Bid Now' : 'View lot' }}</a>
      </div>
    </div>
    @endif
  </div>
</div>

@if($hot->count())
<div class="max-w-[1400px] mx-auto px-5 py-14">
  <div class="flex items-end justify-between mb-6">
    <h2 class="dsp text-3xl font-extrabold">Hot Lots</h2>
    <a href="/inventory" class="text-[13px] font-bold gold">All lots →</a>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($hot as $v) @include('site.card',['v'=>$v]) @endforeach
  </div>
</div>
@endif

@include('partials.footer')
@endsection
