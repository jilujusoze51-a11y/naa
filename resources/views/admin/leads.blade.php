@extends('admin-base')
@section('title','Leads — Admin')
@section('heading','Leads')
@section('admin')
<div class="flex flex-wrap gap-2 mb-4">
  @foreach(['' =>'All','unread'=>'Unread','offer'=>'Make Offer','contact'=>'Contact'] as $k=>$lbl)
    <a href="?f={{ $k }}" class="px-3.5 py-2 rounded text-[12px] font-bold"
       style="{{ request('f')===$k ? 'background:var(--gold);color:#0A0A0A' : 'background:var(--card);border:1px solid var(--edge);color:var(--ash)' }}">
      {{ $lbl }}@if($k==='unread') <span class="mn">({{ $unread }})</span>@endif</a>
  @endforeach
</div>

<div class="card rounded-lg overflow-hidden">
  <table class="w-full text-[13px]">
    <thead><tr class="border-b hair text-[10px] tracking-[.1em]" style="background:var(--card2);color:var(--dim)">
      <th class="text-left px-5 py-3">FROM</th><th class="text-left px-4 py-3">SUBJECT</th>
      <th class="text-left px-4 py-3">MESSAGE</th><th class="text-left px-4 py-3">RECEIVED</th>
      <th class="text-right px-5 py-3">ACTION</th></tr></thead>
    <tbody>
    @forelse($leads as $l)
      <tr class="border-b hair" style="{{ $l->read ? '' : 'background:rgba(59,130,246,.04)' }}">
        <td class="px-5 py-3.5">
          <div class="font-bold">{{ $l->name }}</div>
          <div class="text-[11px]" style="color:var(--sig)">{{ $l->email }}</div></td>
        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:{{ $l->subject==='Make Offer'?'rgba(59,130,246,.12)':'rgba(245,158,11,.14)' }};
                   color:{{ $l->subject==='Make Offer'?'var(--sig)':'var(--note)' }}">
            {{ strtoupper($l->subject) }}</span>
            @if($l->vehicle)<div class="text-[11px] mt-1" style="color:var(--dim)">→ Lot #{{ $l->vehicle->lot }}</div>@endif</td>
        <td class="px-4 py-3.5 text-[12px]" style="color:var(--ash)">
          @if($l->offer)<span class="mn font-bold gold">Offer: ${{ number_format($l->offer) }}</span>
          @else {{ Str::limit($l->message,60) }} @endif</td>
        <td class="px-4 py-3.5 mn text-[11px]" style="color:var(--dim)">{{ $l->created_at->diffForHumans() }}</td>
        <td class="px-5 py-3.5 text-right">
          <a href="/admin/leads/{{ $l->id }}" class="px-3 py-1.5 rounded text-[11px] btn-g">Open →</a></td>
      </tr>
    @empty
      <tr><td colspan="5" class="px-5 py-12 text-center text-[13px]" style="color:var(--dim)">No leads yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $leads->links() }}</div>
@endsection
