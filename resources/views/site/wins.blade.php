@extends('layout')
@section('title','Wins — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-[1400px] mx-auto px-5 py-10">
  <h1 class="dsp text-5xl font-extrabold mb-3">Wins</h1>
  <p class="text-sm mb-8" style="color:var(--ash)">Lots that left the floor.</p>

  @if($mine->count())
  <div class="card rounded-lg p-6 mb-8" style="border-color:var(--gold)">
    <div class="dsp text-xl font-extrabold gold mb-4">Your wins</div>
    @foreach($mine as $s)
      <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b hair">
        <div>
          <div class="font-bold">{{ $s->vehicle?->title() }}</div>
          <div class="text-[12px] mn" style="color:var(--dim)">
            LOT #{{ $s->vehicle?->lot }} · won at ${{ number_format($s->amount,2) }}</div>
        </div>
        <span class="px-3 py-1 rounded text-[10px] font-bold"
              style="background:rgba(201,162,39,.12);color:var(--gold-lt)">
          {{ strtoupper($s->status) }}</span>
      </div>
    @endforeach
  </div>
  @endif

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($sold as $v)
      <div class="card rounded-lg overflow-hidden">
        <div class="thumb h-36 flex items-center justify-center text-5xl relative">🚗
          <span class="absolute top-3 left-3 px-2 py-1 rounded text-[9px] font-bold"
                style="background:var(--live);color:#04231A">SOLD</span>
        </div>
        <div class="p-4">
          <div class="dsp text-lg font-extrabold">{{ $v->title() }}</div>
          <div class="text-[11px] mb-3" style="color:var(--dim)">Lot #{{ $v->lot }}</div>
          <div class="pt-3 border-t hair flex justify-between">
            <div><div class="text-[9px]" style="color:var(--dim)">SOLD FOR</div>
              <div class="mn font-bold" style="color:var(--live)">${{ number_format($v->current_bid) }}</div></div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@include('partials.footer')
@endsection
