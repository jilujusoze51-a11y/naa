@extends('admin-base')
@section('title','Vehicles — Admin')
@section('heading','Vehicles')
@section('admin')
<div class="flex flex-wrap items-center gap-2 mb-4">
  <form method="GET" class="flex gap-2 flex-1">
    <input name="q" value="{{ request('q') }}" placeholder="Search make, model, VIN, lot…"
           class="px-3 py-2 rounded text-[13px] flex-1 max-w-md">
    <select name="status" class="px-3 py-2 rounded text-[13px]">
      <option value="">Any status</option>
      @foreach(['draft','upcoming','live','sold','unsold'] as $s)
        <option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="px-4 py-2 rounded text-[12px] btn-o font-bold">Filter</button>
  </form>
  <a href="/admin/vehicles/new" class="px-4 py-2 rounded text-[12px] btn-g">+ Add Vehicle</a>
</div>

<div class="card rounded-lg overflow-hidden">
  <table class="w-full text-[13px]">
    <thead><tr class="border-b hair text-[10px] tracking-[.1em]" style="background:var(--card2);color:var(--dim)">
      <th class="text-left px-5 py-3">VEHICLE</th><th class="text-left px-4 py-3">VIN</th>
      <th class="text-right px-4 py-3">OPENING</th><th class="text-right px-4 py-3">CURRENT</th>
      <th class="text-right px-4 py-3">BUY NOW</th><th class="text-left px-4 py-3">STATUS</th>
      <th class="text-right px-5 py-3">ACTIONS</th></tr></thead>
    <tbody>
    @forelse($vehicles as $v)
      <tr class="border-b hair">
        <td class="px-5 py-3">
          <div class="font-bold">{{ $v->title() }}</div>
          <div class="mn text-[11px]" style="color:var(--dim)">Lot #{{ $v->lot }}
            @if($v->hot)<span class="ml-1 px-1 rounded text-[9px] font-bold" style="background:var(--heat);color:#fff">HOT</span>@endif
          </div>
        </td>
        <td class="px-4 py-3 mn text-[11px]" style="color:var(--dim)">{{ $v->vin }}</td>
        <td class="px-4 py-3 text-right mn">${{ number_format($v->start_bid) }}</td>
        <td class="px-4 py-3 text-right mn font-bold gold">${{ number_format($v->current_bid) }}</td>
        <td class="px-4 py-3 text-right mn">{{ $v->buy_now>0?'$'.number_format($v->buy_now):'—' }}</td>
        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:{{ $v->status==='live'?'var(--live)':'var(--card2)' }};
                   color:{{ $v->status==='live'?'#04231A':'var(--ash)' }}">{{ strtoupper($v->status) }}</span></td>
        <td class="px-5 py-3 text-right">
          <div class="flex gap-2 justify-end text-[12px] font-bold">
            <a href="/lot/{{ $v->id }}" target="_blank" style="color:var(--sig)">View</a>
            <a href="/admin/vehicles/{{ $v->id }}" class="gold">Edit</a>
            @if($v->status!=='live')
              <form method="POST" action="/admin/vehicles/{{ $v->id }}/live">@csrf
                <button style="color:var(--live)">Go Live</button></form>
            @endif
            <form method="POST" action="/admin/vehicles/{{ $v->id }}/delete"
                  onsubmit="return confirm('Delete this vehicle?')">@csrf
              <button style="color:var(--heat)">Delete</button></form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="px-5 py-12 text-center text-[13px]" style="color:var(--dim)">No vehicles yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $vehicles->links() }}</div>
@endsection
