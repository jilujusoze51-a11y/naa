@extends('admin-base')
@section('title','Bids — Admin')
@section('heading','Bids')
@section('admin')
<form method="GET" class="mb-4">
  <input name="q" value="{{ request('q') }}" placeholder="Search bidder…" class="px-3 py-2 rounded text-[13px] w-64">
</form>
<div class="card rounded-lg overflow-hidden">
  <table class="w-full text-[13px]">
    <thead><tr class="border-b hair text-[10px] tracking-[.1em]" style="background:var(--card2);color:var(--dim)">
      <th class="text-left px-5 py-3">WHEN</th><th class="text-left px-4 py-3">BIDDER</th>
      <th class="text-left px-4 py-3">LOT</th><th class="text-right px-4 py-3">AMOUNT</th>
      <th class="text-right px-4 py-3">MAX</th><th class="text-right px-5 py-3">ACTION</th></tr></thead>
    <tbody>
    @forelse($bids as $b)
      <tr class="border-b hair">
        <td class="px-5 py-3 mn text-[11px]" style="color:var(--dim)">{{ $b->created_at->diffForHumans() }}</td>
        <td class="px-4 py-3"><div class="font-bold">{{ $b->bidder_name }}</div>
            <div class="text-[11px]" style="color:var(--dim)">{{ $b->user?->email }}</div></td>
        <td class="px-4 py-3">
          <a href="/lot/{{ $b->vehicle_id }}" target="_blank" style="color:var(--sig)">
            Lot #{{ $b->vehicle?->lot }} — {{ $b->vehicle?->title() }}</a></td>
        <td class="px-4 py-3 text-right mn font-bold gold">${{ number_format($b->amount,2) }}</td>
        <td class="px-4 py-3 text-right mn text-[12px]" style="color:var(--dim)">
          {{ $b->max_amount ? '$'.number_format($b->max_amount,2) : '—' }}</td>
        <td class="px-5 py-3 text-right">
          <form method="POST" action="/admin/bids/{{ $b->id }}/delete"
                onsubmit="return confirm('Remove this bid?')">@csrf
            <button class="px-3 py-1.5 rounded text-[11px] btn-no">Delete</button></form></td>
      </tr>
    @empty
      <tr><td colspan="6" class="px-5 py-12 text-center text-[13px]" style="color:var(--dim)">No bids yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $bids->links() }}</div>
@endsection
