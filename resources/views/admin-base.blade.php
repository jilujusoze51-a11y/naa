@extends('layout')
@section('body')
<div class="flex min-h-screen">
  @include('partials.admin-nav')
  <div class="flex-1 min-w-0">
    <header class="sticky top-0 z-30 h-16 border-b hair flex items-center justify-between px-5"
            style="background:rgba(7,10,15,.95);backdrop-filter:blur(12px)">
      <div class="dsp text-lg font-extrabold">@yield('heading')</div>
      <div class="flex items-center gap-3 text-[12px]">
        <a href="/" target="_blank" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded font-bold btn-o">
          🌐 View Site ↗</a>
        <span style="color:var(--dim)">{{ auth()->user()->name }}</span>
        <form method="POST" action="/logout">@csrf
          <button class="text-[12px]" style="color:var(--dim)">Logout</button></form>
      </div>
    </header>
    <main class="p-5">@yield('admin')</main>
  </div>
</div>
@endsection
