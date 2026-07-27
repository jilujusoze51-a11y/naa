@extends('layout')
@section('title','About — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-3xl mx-auto px-5 py-12">
  <h1 class="dsp text-5xl font-extrabold mb-6">We built the auction room the internet deserved.</h1>
  <div class="space-y-4 text-[15px] leading-relaxed" style="color:var(--ash)">
    <p>National Auto Auction sells repossessed vehicles on behalf of banks, credit unions and lenders,
       from a lane in Beecher, Illinois.</p>
    <p>Every reserve is published. Every title status is disclosed — including when it is not clean.
       Every lot has a named agent with a phone number that reaches a human.</p>
  </div>
</div>
@include('partials.footer')
@endsection
