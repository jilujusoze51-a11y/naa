@extends('admin-base')
@section('title','KYC Approvals — Admin')
@section('heading','KYC Approvals')
@section('admin')
<form method="GET" class="flex flex-wrap gap-2 mb-4">
  @foreach(['pending'=>'Pending','active'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $k=>$lbl)
    <a href="?f={{ $k }}" class="px-3.5 py-2 rounded text-[12px] font-bold"
       style="{{ $f===$k ? 'background:var(--gold);color:#0A0A0A' : 'background:var(--card);border:1px solid var(--edge);color:var(--ash)' }}">{{ $lbl }}</a>
  @endforeach
  <input name="q" value="{{ request('q') }}" placeholder="Search…" class="px-3 py-2 rounded text-[13px] ml-auto w-56">
</form>

<div class="card rounded-lg overflow-hidden">
  <table class="w-full text-[13px]">
    <thead><tr class="border-b hair text-[10px] tracking-[.1em]" style="background:var(--card2);color:var(--dim)">
      <th class="text-left px-5 py-3">APPLICANT</th><th class="text-left px-4 py-3">CONTACT</th>
      <th class="text-left px-4 py-3">LOCATION</th><th class="text-left px-4 py-3">TYPE</th>
      <th class="text-left px-4 py-3">SUBMITTED</th><th class="text-left px-4 py-3">STATUS</th>
      <th class="text-right px-5 py-3">ACTION</th></tr></thead>
    <tbody>
    @forelse($users as $u)
      <tr class="border-b hair">
        <td class="px-5 py-3.5 font-bold">{{ $u->name }}</td>
        <td class="px-4 py-3.5"><div class="text-[12px]">{{ $u->email }}</div>
            <div class="mn text-[11px]" style="color:var(--dim)">{{ $u->phone }}</div></td>
        <td class="px-4 py-3.5 text-[12px]">{{ $u->city ?: '—' }}</td>
        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:{{ $u->is_business?'rgba(59,130,246,.12)':'var(--card2)' }};color:{{ $u->is_business?'var(--sig)':'var(--ash)' }}">
            {{ $u->is_business?'BUSINESS':'BUYER' }}</span></td>
        <td class="px-4 py-3.5 mn text-[11px]" style="color:var(--dim)">{{ $u->created_at->diffForHumans() }}</td>
        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:var(--card2);color:var(--ash)">{{ strtoupper($u->status) }}</span></td>
        <td class="px-5 py-3.5 text-right">
          <a href="/admin/kyc/{{ $u->id }}" class="px-3 py-1.5 rounded text-[11px] btn-g">Review →</a></td>
      </tr>
    @empty
      <tr><td colspan="7" class="px-5 py-12 text-center text-[13px]" style="color:var(--dim)">Nothing here.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
