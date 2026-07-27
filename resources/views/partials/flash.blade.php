@if(session('ok') || session('err') || $errors->any())
<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,6000)"
     class="fixed top-4 right-4 z-[100] max-w-sm">
  @if(session('ok'))
    <div class="card rounded-lg px-4 py-3 text-[13px] font-bold mb-2" style="border-color:var(--live)">
      {{ session('ok') }}
    </div>
  @endif
  @if(session('err'))
    <div class="card rounded-lg px-4 py-3 text-[13px] font-bold mb-2" style="border-color:var(--heat)">
      {{ session('err') }}
    </div>
  @endif
  @foreach($errors->all() as $e)
    <div class="card rounded-lg px-4 py-3 text-[13px] mb-2" style="border-color:var(--heat)">{{ $e }}</div>
  @endforeach
</div>
@endif
