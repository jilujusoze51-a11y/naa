<a href="/lot/{{ $v->id }}" class="card rounded-lg overflow-hidden block hover:border-yellow-600 transition">
  <div class="thumb h-40 flex items-center justify-center text-5xl relative">
    🚗
    @if($v->hot)<span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[9px] font-bold"
       style="background:var(--heat);color:#fff">🔥 HOT</span>@endif
    @if($v->status==='live')<span class="absolute top-2 right-2 px-2 py-0.5 rounded text-[9px] font-bold lv"
       style="background:var(--live);color:#04231A">● LIVE</span>@endif
  </div>
  <div class="p-4">
    <div class="text-[10px] tracking-[.08em]" style="color:var(--dim)">{{ $v->year }} · {{ strtoupper($v->make) }}</div>
    <div class="dsp text-[17px] font-extrabold leading-tight mb-3">{{ $v->title() }}</div>
    <div class="flex justify-between pt-3 border-t hair">
      <div>
        <div class="text-[9px]" style="color:var(--dim)">{{ $v->status==='live' ? 'CURRENT BID' : 'OPENING BID' }}</div>
        <div class="mn font-bold gold">${{ number_format($v->status==='live' ? $v->current_bid : $v->start_bid) }}</div>
      </div>
      <div class="text-right">
        <div class="text-[9px]" style="color:var(--dim)">BUY NOW</div>
        <div class="mn font-bold text-[13px]">{{ $v->buy_now>0 ? '$'.number_format($v->buy_now) : '—' }}</div>
      </div>
    </div>
  </div>
</a>
