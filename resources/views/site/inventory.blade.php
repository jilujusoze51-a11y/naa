@extends('layout')
@section('title','Inventory — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-[1400px] mx-auto px-5 py-10">
  <h1 class="dsp text-5xl font-extrabold mb-3">Find your next <span class="gold">machine</span></h1>
  <p class="text-sm mb-8" style="color:var(--ash)">{{ $vehicles->total() }} lots available.</p>

  <form method="GET" class="card rounded-lg p-4 mb-6 grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input name="q" value="{{ request('q') }}" placeholder="Search make, model, VIN, lot…"
           class="px-3 py-2.5 rounded text-[13px]">
    <select name="make" class="px-3 py-2.5 rounded text-[13px]">
      <option value="">All makes</option>
      @foreach($makes as $m)<option @selected(request('make')===$m)>{{ $m }}</option>@endforeach
    </select>
    <select name="title" class="px-3 py-2.5 rounded text-[13px]">
      <option value="">Any title</option>
      @foreach(['Clean','Rebuilt','Salvage'] as $t)<option @selected(request('title')===$t)>{{ $t }}</option>@endforeach
    </select>
    <input name="max" type="number" value="{{ request('max') }}" placeholder="Max opening bid"
           class="px-3 py-2.5 rounded text-[13px] mn">
    <button class="px-5 py-2.5 rounded btn-g">Filter</button>
  </form>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($vehicles as $v) @include('site.card',['v'=>$v])
    @empty
      <div class="col-span-full card rounded-lg p-14 text-center">
        <div class="dsp text-2xl font-extrabold mb-1">Nothing matches</div>
        <a href="/inventory" class="text-[13px] gold font-bold">Clear filters</a>
      </div>
    @endforelse
  </div>

  <div class="mt-8">{{ $vehicles->links() }}</div>
</div>
@include('partials.footer')
@endsection
