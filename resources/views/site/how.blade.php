@extends('layout')
@section('title','How it works — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-3xl mx-auto px-5 py-12">
  <h1 class="dsp text-5xl font-extrabold mb-3">How it works</h1>
  <p class="mb-10" style="color:var(--ash)">Three steps from registration to driving home your win.</p>
  @foreach([
    ['Register & verify','Create a free account and upload your driver\'s license. An agent reviews it — usually within a few hours.'],
    ['Bid, offer, or buy','During a live lot, enter your maximum and the system bids for you in $100 steps, only as high as needed. Outside a live lot you can still make an offer or buy outright.'],
    ['An agent closes it','If you win, an agent calls with payment instructions, documentation and delivery. Payment goes to the vendor — that is why the price stays low.'],
  ] as $i=>[$t,$d])
    <div class="card rounded-lg p-6 mb-3">
      <div class="mn text-[11px] gold mb-2">STEP 0{{ $i+1 }}</div>
      <div class="dsp text-xl font-extrabold mb-2">{{ $t }}</div>
      <p class="text-[13px] leading-relaxed" style="color:var(--ash)">{{ $d }}</p>
    </div>
  @endforeach
</div>
@include('partials.footer')
@endsection
