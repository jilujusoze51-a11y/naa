<aside class="w-56 shrink-0 border-r hair hidden lg:flex flex-col" style="background:#05070B">
  <div class="h-16 flex items-center gap-2.5 px-5 border-b hair">
    <div class="w-8 h-8 rounded flex items-center justify-center dsp font-extrabold text-[12px]"
         style="background:var(--gold);color:#0A0A0A">NAA</div>
    <div class="dsp text-[13px] font-extrabold">National Auto <span style="color:var(--dim)">Admin</span></div>
  </div>
  <nav class="flex-1 p-2.5 space-y-0.5 overflow-y-auto">
    @foreach([
      ['/admin','▣','Dashboard',null],
      ['/admin/kyc','🪪','KYC Approvals',$pendingKyc ?? null],
      ['/admin/vehicles','🚗','Vehicles',null],
      ['/admin/bids','💲','Bids',null],
      ['/admin/sales','🛡','Sale Approvals',$pendingSales ?? null],
      ['/admin/pipeline','▤','Pipeline',null],
      ['/admin/leads','📨','Leads',null],
      ['/admin/users','👤','Users',null],
    ] as [$url,$icon,$label,$badge])
      <a href="{{ $url }}" class="flex items-center justify-between px-3 py-2 rounded text-[13px] font-semibold"
         style="{{ request()->is(ltrim($url,'/')) ? 'background:rgba(201,162,39,.1);color:var(--gold-lt)' : 'color:var(--ash)' }}">
        <span class="flex items-center gap-2.5"><span class="w-4 text-center">{{ $icon }}</span>{{ $label }}</span>
        @if($badge)<span class="mn text-[10px] px-1.5 py-0.5 rounded font-bold"
           style="background:var(--heat);color:#fff">{{ $badge }}</span>@endif
      </a>
    @endforeach
  </nav>
  <div class="p-2.5 border-t hair">
    <a href="/" target="_blank" class="w-full py-2 rounded text-[12px] font-bold flex items-center justify-center gap-2 btn-o">
      ← Back to Website ↗
    </a>
  </div>
</aside>
