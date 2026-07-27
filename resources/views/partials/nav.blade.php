<header class="sticky top-0 z-40 border-b hair" style="background:rgba(7,10,15,.94);backdrop-filter:blur(12px)">
  <div class="max-w-[1400px] mx-auto px-5 h-16 flex items-center justify-between gap-4">
    <a href="/" class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded flex items-center justify-center dsp font-extrabold text-[13px]"
           style="background:var(--gold);color:#0A0A0A">NAA</div>
      <div class="leading-none hidden sm:block">
        <div class="dsp text-[14px] font-extrabold">National Auto Auction</div>
        <div class="text-[9px] tracking-[.16em] mt-0.5" style="color:var(--dim)">BEECHER, ILLINOIS</div>
      </div>
    </a>
    <nav class="hidden md:flex items-center gap-1 text-[13px] font-semibold">
      <a href="/inventory" class="px-3 py-2 rounded" style="color:var(--ash)">Inventory</a>
      <a href="/wins" class="px-3 py-2 rounded" style="color:var(--ash)">Wins</a>
      <a href="/p/how" class="px-3 py-2 rounded" style="color:var(--ash)">How it works</a>
      <a href="/p/contact" class="px-3 py-2 rounded" style="color:var(--ash)">Contact</a>
    </nav>
    <div class="flex items-center gap-2">
      @auth
        @if(auth()->user()->isAdmin())
          <a href="/admin" class="px-3 py-2 rounded text-[12px] btn-o font-bold">Admin</a>
        @endif
        <span class="hidden sm:flex items-center gap-2 px-2.5 py-1.5 rounded text-[12px]" style="background:var(--card)">
          <span class="w-1.5 h-1.5 rounded-full"
                style="background:{{ auth()->user()->verified ? 'var(--live)' : 'var(--note)' }}"></span>
          {{ auth()->user()->name }}
        </span>
        <form method="POST" action="/logout">@csrf
          <button class="text-[12px]" style="color:var(--dim)">Sign out</button>
        </form>
      @else
        <a href="/login" class="px-3 py-2 text-[13px] font-semibold" style="color:var(--ash)">Sign In</a>
        <a href="/register" class="px-4 py-2.5 rounded text-[13px] btn-g">Register free</a>
      @endauth
    </div>
  </div>
</header>
