@extends('layout')
@section('title','Sign In — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-md mx-auto px-5 py-16">
  <div class="card rounded-lg p-8">
    <h1 class="dsp text-3xl font-extrabold mb-1">Sign In</h1>
    <p class="text-[13px] mb-6" style="color:var(--ash)">Enter the floor and bid on live lots.</p>
    <form method="POST" action="/login" class="space-y-3">@csrf
      <div>
        <label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">EMAIL</label>
        <input name="email" type="email" value="{{ old('email') }}" required
               class="w-full px-3 py-2.5 rounded text-[13px]">
      </div>
      <div>
        <label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">PASSWORD</label>
        <input name="password" type="password" required class="w-full px-3 py-2.5 rounded text-[13px]">
      </div>
      <label class="flex items-center gap-2 text-[12px]" style="color:var(--ash)">
        <input type="checkbox" name="remember" class="accent-yellow-600"> Remember me
      </label>
      <button class="w-full py-3.5 rounded btn-g">Sign In</button>
    </form>
    <div class="mt-6 pt-5 border-t hair text-center text-[13px]" style="color:var(--ash)">
      No account? <a href="/register" class="gold font-bold">Register free →</a>
    </div>
    <div class="mt-4 p-3 rounded text-[11px]" style="background:var(--void);color:var(--dim)">
      <b style="color:var(--bone)">Demo:</b> admin@naa.test / password &nbsp;·&nbsp; buyer@naa.test / password
    </div>
  </div>
</div>
@endsection
