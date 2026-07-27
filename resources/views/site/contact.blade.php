@extends('layout')
@section('title','Contact — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-3xl mx-auto px-5 py-12">
  <h1 class="dsp text-5xl font-extrabold mb-3">Get in touch</h1>
  <p class="mb-8" style="color:var(--ash)">Leave your details and an agent will reach out shortly.</p>
  <div class="card rounded-lg p-7">
    <form method="POST" action="/contact" class="space-y-3">@csrf
      <div class="grid sm:grid-cols-2 gap-3">
        <input name="name" placeholder="Full name" required class="px-3 py-2.5 rounded text-[13px]">
        <input name="email" type="email" placeholder="Email" required class="px-3 py-2.5 rounded text-[13px]">
      </div>
      <input name="phone" placeholder="Phone" class="w-full px-3 py-2.5 rounded text-[13px] mn">
      <textarea name="message" rows="5" placeholder="How can we help?" required
                class="w-full px-3 py-2.5 rounded text-[13px]"></textarea>
      <button class="px-6 py-3 rounded btn-g">Send message</button>
    </form>
  </div>
</div>
@include('partials.footer')
@endsection
