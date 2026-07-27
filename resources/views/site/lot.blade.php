@extends('layout')
@section('title',$v->title().' — Lot #'.$v->lot)
@section('body')
@include('partials.nav')

<div class="max-w-[1400px] mx-auto px-5 py-8"
     x-data="lot({{ $v->id }}, {{ $v->secondsLeft() }}, {{ (float)$v->current_bid }})" x-init="start()">

  <a href="/inventory" class="text-[12px] mb-5 inline-block" style="color:var(--dim)">← Back to Inventory</a>

  <div class="grid lg:grid-cols-[1fr_380px] gap-6">
    {{-- LEFT --}}
    <div>
      <div class="card rounded-lg overflow-hidden mb-4">
        <div class="thumb h-[380px] flex items-center justify-center text-9xl relative">
          @if($v->photos->count())
            <img src="{{ Storage::url($v->photos->first()->path) }}" class="w-full h-full object-cover" alt="">
          @else 🚗 @endif
          @if($v->status==='live')
            <span class="absolute top-3 right-3 px-2 py-1 rounded text-[10px] font-bold lv"
                  style="background:var(--live);color:#04231A">● LIVE NOW</span>
          @endif
        </div>
        @if($v->photos->count() > 1)
        <div class="grid grid-cols-6 gap-1 p-1">
          @foreach($v->photos->take(6) as $p)
            <img src="{{ Storage::url($p->path) }}" class="h-16 w-full object-cover rounded" alt="">
          @endforeach
        </div>
        @endif
      </div>

      <div class="card rounded-lg p-6 mb-4">
        <h2 class="dsp text-xl font-extrabold mb-4">Vehicle Details</h2>
        <div class="grid sm:grid-cols-2 gap-x-10">
          @foreach([
            'Year'=>$v->year,'Make'=>$v->make,'Model'=>$v->model,'VIN'=>$v->vin,
            'Odometer'=>number_format($v->miles).' mi','Transmission'=>$v->transmission,
            'Engine'=>$v->engine,'Title'=>$v->title_status,'Location'=>$v->location,
          ] as $k=>$val)
            <div class="flex justify-between py-2.5 border-b hair text-[13px]">
              <span style="color:var(--dim)">{{ $k }}</span>
              <span class="font-bold mn">{{ $val ?: '—' }}</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- MAKE AN OFFER --}}
      <div class="card rounded-lg p-6">
        <h2 class="dsp text-xl font-extrabold mb-1">Make an Offer</h2>
        <p class="text-[12px] mb-4" style="color:var(--ash)">
          The vendor accepts, declines, or counters. An agent relays the response — usually within a day.
        </p>
        <form method="POST" action="/lot/{{ $v->id }}/offer" class="grid sm:grid-cols-2 gap-3">@csrf
          <input name="name" placeholder="Full name" required value="{{ auth()->user()->name ?? '' }}"
                 class="px-3 py-2.5 rounded text-[13px]">
          <input name="email" type="email" placeholder="Email" required value="{{ auth()->user()->email ?? '' }}"
                 class="px-3 py-2.5 rounded text-[13px]">
          <input name="phone" placeholder="Phone" value="{{ auth()->user()->phone ?? '' }}"
                 class="px-3 py-2.5 rounded text-[13px] mn">
          <input name="offer" type="number" step="100" placeholder="Your offer ($)" required
                 class="px-3 py-2.5 rounded text-[13px] mn">
          <button class="sm:col-span-2 py-3 rounded btn-o font-bold">Submit offer</button>
        </form>
      </div>
    </div>

    {{-- RIGHT: BID RAIL --}}
    <div class="lg:sticky lg:top-24 h-fit space-y-4">
      <div class="card rounded-lg p-6">
        <div class="flex items-start justify-between mb-2">
          <span class="mn text-[10px] px-2 py-0.5 rounded" style="background:var(--card2);color:var(--ash)">
            LOT #{{ $v->lot }}</span>
          <span class="px-2 py-0.5 rounded text-[9px] font-bold"
                style="background:{{ $v->status==='live'?'var(--live)':'var(--card2)' }};
                       color:{{ $v->status==='live'?'#04231A':'var(--gold-lt)' }}">
            {{ strtoupper($v->status) }}</span>
        </div>
        <h1 class="dsp text-3xl font-extrabold leading-tight mb-1">{{ $v->title() }}</h1>
        <p class="text-[12px] mb-5" style="color:var(--dim)">{{ $v->trim }} · {{ $v->location }}</p>

        <div class="grid grid-cols-2 gap-2 mb-4">
          <div class="rounded p-3" style="background:var(--void);border:1px solid var(--edge)">
            <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">TIME LEFT</div>
            <div class="dsp text-lg font-extrabold mn"
                 :style="secs>0?'color:var(--live)':'color:var(--dim)'" x-text="clock"></div>
          </div>
          <div class="rounded p-3" style="background:var(--void);border:1px solid var(--edge)">
            <div class="text-[9px] tracking-[.1em]" style="color:var(--dim)">BIDS</div>
            <div class="dsp text-lg font-extrabold mn" x-text="bids">{{ $v->bids()->count() }}</div>
          </div>
        </div>

        <div class="rounded p-4 mb-4" style="background:var(--void);border:1px solid var(--edge)">
          <div class="text-[10px] tracking-[.1em]" style="color:var(--dim)">CURRENT BID</div>
          <div class="dsp text-4xl font-extrabold gold mn" x-text="money(cur)"></div>
          <div class="mt-3 pt-3 border-t hair space-y-1.5 text-[12px]">
            <div class="flex justify-between"><span style="color:var(--dim)">Buy Now</span>
              <span class="mn font-bold">{{ $v->buy_now>0?'$'.number_format($v->buy_now,2):'—' }}</span></div>
            <div class="flex justify-between"><span style="color:var(--dim)">Reserve</span>
              <span class="mn font-bold">${{ number_format($v->reserve,2) }}</span></div>
            <div class="flex justify-between"><span style="color:var(--dim)">Min. increment</span>
              <span class="mn font-bold">$100</span></div>
          </div>
        </div>

        <div x-show="youLead" x-cloak class="rounded p-3 mb-3 text-center text-[12px] font-bold"
             style="background:rgba(16,185,129,.12);color:var(--live)">✓ You are currently winning</div>

        @guest
          <a href="/register" class="block w-full py-3.5 rounded btn-g text-center font-bold">Bid Now</a>
          <p class="text-[11px] text-center mt-2" style="color:var(--dim)">Register free — verified in a few hours</p>
        @else
          @if(!auth()->user()->canBid())
            <div class="rounded p-3 text-center text-[12px]"
                 style="background:rgba(245,158,11,.1);color:var(--note)">
              Your ID is under review. Bidding unlocks once an agent approves it.
            </div>
          @elseif($v->status==='live')
            <form method="POST" action="/lot/{{ $v->id }}/bid" class="space-y-2.5">@csrf
              <label class="text-[10px] tracking-[.1em] block" style="color:var(--dim)">YOUR MAXIMUM BID</label>
              <div class="flex items-center rounded" style="background:var(--void);border:1px solid var(--edge)">
                <span class="px-3 mn" style="color:var(--dim)">$</span>
                <input name="max" type="number" step="100" required
                       :value="(cur>0?cur+100:{{ (float)$v->start_bid }})"
                       class="flex-1 py-3 mn text-lg font-bold" style="background:transparent;border:none">
              </div>
              <button class="w-full py-3.5 rounded font-bold" style="background:var(--live);color:#04231A">
                Bid Now
              </button>
            </form>
            @if($v->buy_now > 0)
              <form method="POST" action="/lot/{{ $v->id }}/buynow" class="mt-2.5">@csrf
                <button class="w-full py-3.5 rounded btn-g flex items-center justify-between px-4">
                  <span>Buy Now</span><span class="mn">${{ number_format($v->buy_now,2) }}</span>
                </button>
              </form>
            @endif
          @else
            <div class="rounded p-3 text-center text-[12px]" style="background:var(--card2);color:var(--ash)">
              This lot is not live. You can still make an offer below.
            </div>
          @endif
        @endguest

        <p class="text-[11px] leading-relaxed mt-4" style="color:var(--dim)">
          <b style="color:var(--live)">Proxy bidding:</b> enter your maximum and we bid for you in $100 steps,
          only as high as needed to keep you in front — never above your max.
          A bid in the final 30 seconds extends the lot.
        </p>
      </div>

      {{-- BID HISTORY --}}
      @if($v->bids()->count())
      <div class="card rounded-lg overflow-hidden">
        <div class="px-5 py-3 border-b hair" style="background:var(--card2)">
          <span class="dsp font-extrabold">Bid History</span>
        </div>
        @foreach($v->bids()->orderByDesc('amount')->orderByDesc('id')->take(8)->get() as $i => $b)
          <div class="px-5 py-2.5 border-b hair flex items-center justify-between text-[13px]">
            <div class="flex items-center gap-2">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                    style="background:{{ $i===0?'var(--gold)':'var(--card2)' }};color:{{ $i===0?'#0A0A0A':'var(--dim)' }}">
                {{ $i===0 ? '★' : $i+1 }}</span>
              <span>{{ $b->bidder_name }}</span>
            </div>
            <span class="mn font-bold">${{ number_format($b->amount,2) }}</span>
          </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>

  @if($similar->count())
  <div class="mt-12">
    <h2 class="dsp text-2xl font-extrabold mb-5">Similar Lots</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach($similar as $s) @include('site.card',['v'=>$s]) @endforeach
    </div>
  </div>
  @endif
</div>

@include('partials.footer')
@endsection

@push('scripts')
<script>
function lot(id, secs, cur){
  return {
    id, secs, cur, bids:0, youLead:false, clock:'--:--:--',
    start(){
      this.tick();
      setInterval(()=>this.tick(),1000);      // countdown every second
      setInterval(()=>this.poll(),4000);      // fresh price every 4s (light on the server)
      this.poll();
    },
    tick(){
      if(this.secs>0) this.secs--;
      const h=String(Math.floor(this.secs/3600)).padStart(2,'0'),
            m=String(Math.floor(this.secs%3600/60)).padStart(2,'0'),
            s=String(this.secs%60).padStart(2,'0');
      this.clock = this.secs>0 ? `${h}:${m}:${s}` : 'CLOSED';
    },
    async poll(){
      try{
        const r = await fetch(`/lot/${this.id}/status`);
        const d = await r.json();
        this.cur = d.current_bid;
        this.bids = d.bids;
        this.secs = d.seconds;
        this.youLead = d.you_lead;
      }catch(e){}
    },
    money(n){ return '$'+Number(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }
  }
}
</script>
@endpush
