@extends('admin-base')
@section('title','Users — Admin')
@section('heading','Users')
@section('admin')
<form method="GET" class="flex gap-2 mb-4">
  <input name="q" value="{{ request('q') }}" placeholder="Search name or email…" class="px-3 py-2 rounded text-[13px] w-64">
  <select name="status" class="px-3 py-2 rounded text-[13px]">
    <option value="">All statuses</option>
    @foreach(['pending','active','deactivated','rejected'] as $s)
      <option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
  </select>
  <button class="px-4 py-2 rounded text-[12px] btn-o font-bold">Filter</button>
</form>

<div class="card rounded-lg overflow-hidden">
  <table class="w-full text-[13px]">
    <thead><tr class="border-b hair text-[10px] tracking-[.1em]" style="background:var(--card2);color:var(--dim)">
      <th class="text-left px-5 py-3">USER</th><th class="text-left px-4 py-3">CONTACT</th>
      <th class="text-left px-4 py-3">ROLE</th><th class="text-left px-4 py-3">VERIFIED</th>
      <th class="text-left px-4 py-3">JOINED</th><th class="text-left px-4 py-3">STATUS</th>
      <th class="text-right px-5 py-3">ACTIONS</th></tr></thead>
    <tbody>
    @foreach($users as $u)
      <tr class="border-b hair">
        <td class="px-5 py-3.5 font-bold">{{ $u->name }}</td>
        <td class="px-4 py-3.5"><div class="text-[12px]">{{ $u->email }}</div>
            <div class="mn text-[11px]" style="color:var(--dim)">{{ $u->phone }}</div></td>
        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:var(--card2);color:var(--ash)">{{ strtoupper($u->role) }}</span></td>
        <td class="px-4 py-3.5 text-[12px] font-bold"
            style="color:{{ $u->verified?'var(--live)':'var(--dim)' }}">
            {{ $u->verified?'✓ Verified':'○ Not verified' }}</td>
        <td class="px-4 py-3.5 mn text-[12px]" style="color:var(--ash)">{{ $u->created_at->format('d M Y') }}</td>
        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold"
            style="background:var(--card2);color:var(--ash)">{{ strtoupper($u->status) }}</span></td>
        <td class="px-5 py-3.5 text-right">
          <div class="flex gap-2 justify-end">
            @if($u->status==='pending')
              <a href="/admin/kyc/{{ $u->id }}" class="px-3 py-1.5 rounded text-[11px] btn-g">Review</a>
            @endif
            @if(!$u->isAdmin())
            <form method="POST" action="/admin/users/{{ $u->id }}/toggle">@csrf
              <button class="px-3 py-1.5 rounded text-[11px] {{ $u->status==='deactivated'?'btn-ok':'btn-no' }}">
                {{ $u->status==='deactivated'?'Reactivate':'Deactivate' }}</button></form>
            @endif
          </div></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
