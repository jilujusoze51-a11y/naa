@extends('admin-base')
@section('title',($v->exists?'Edit':'Add').' vehicle — Admin')
@section('heading',$v->exists ? 'Edit Lot #'.$v->lot : 'Add vehicle')
@section('admin')
<a href="/admin/vehicles" class="text-[12px] mb-4 inline-block" style="color:var(--dim)">← Vehicles</a>

<form method="POST" action="{{ $v->exists ? '/admin/vehicles/'.$v->id : '/admin/vehicles' }}"
      enctype="multipart/form-data" class="card rounded-lg p-6 space-y-4 max-w-4xl">@csrf
  <div class="grid sm:grid-cols-4 gap-3">
    @foreach(['lot'=>'LOT #','year'=>'YEAR','make'=>'MAKE','model'=>'MODEL'] as $f=>$lbl)
      <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">{{ $lbl }}</label>
        <input name="{{ $f }}" value="{{ old($f,$v->$f) }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
    @endforeach
  </div>
  <div class="grid sm:grid-cols-3 gap-3">
    @foreach(['vin'=>'VIN','trim'=>'TRIM','miles'=>'ODOMETER'] as $f=>$lbl)
      <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">{{ $lbl }}</label>
        <input name="{{ $f }}" value="{{ old($f,$v->$f) }}" class="w-full px-3 py-2.5 rounded text-[13px] mn"></div>
    @endforeach
  </div>
  <div class="grid sm:grid-cols-4 gap-3">
    <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">TITLE</label>
      <select name="title_status" class="w-full px-3 py-2.5 rounded text-[13px]">
        @foreach(['Clean','Rebuilt','Salvage'] as $t)
          <option @selected(old('title_status',$v->title_status)===$t)>{{ $t }}</option>@endforeach
      </select></div>
    @foreach(['location'=>'LOCATION','engine'=>'ENGINE','transmission'=>'TRANSMISSION'] as $f=>$lbl)
      <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">{{ $lbl }}</label>
        <input name="{{ $f }}" value="{{ old($f,$v->$f) }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
    @endforeach
  </div>

  <div class="pt-4 border-t hair grid sm:grid-cols-3 gap-3">
    @foreach(['start_bid'=>'OPENING BID','reserve'=>'RESERVE','buy_now'=>'BUY NOW (0 = off)'] as $f=>$lbl)
      <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">{{ $lbl }}</label>
        <input name="{{ $f }}" type="number" step="100" value="{{ old($f,$v->$f ?: 0) }}"
               class="w-full px-3 py-2.5 rounded text-[13px] mn"></div>
    @endforeach
  </div>

  {{-- PHOTOS --}}
  <div class="pt-4 border-t hair">
    <div class="dsp font-extrabold mb-1">Photos</div>
    <p class="text-[11px] mb-3" style="color:var(--dim)">The first photo is the primary image on the lot card.</p>
    @if($v->exists && $v->photos->count())
      <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 mb-3">
        @foreach($v->photos as $i=>$p)
          <div class="relative rounded overflow-hidden"
               style="border:2px solid {{ $i===0?'var(--live)':'var(--edge)' }}">
            <img src="{{ Storage::url($p->path) }}" class="h-20 w-full object-cover" alt="">
            @if($i===0)<span class="absolute top-1 left-1 px-1 rounded text-[8px] font-bold"
               style="background:var(--live);color:#04231A">PRIMARY</span>@endif
          </div>
        @endforeach
      </div>
      <div class="flex flex-wrap gap-2 mb-3">
        @foreach($v->photos as $p)
          <button type="button" onclick="if(confirm('Delete this photo?'))document.getElementById('del{{ $p->id }}').submit()"
                  class="px-2 py-1 rounded text-[11px] btn-no">✕ Photo {{ $loop->iteration }}</button>
        @endforeach
      </div>
    @endif
    <label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">ADD PHOTOS</label>
    <input type="file" name="photos[]" multiple accept="image/*" class="w-full px-3 py-2.5 rounded text-[12px]">
  </div>

  <div class="pt-4 border-t hair grid sm:grid-cols-3 gap-3">
    <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">STATUS</label>
      <select name="status" class="w-full px-3 py-2.5 rounded text-[13px]">
        @foreach(['draft','upcoming','live','sold','unsold'] as $s)
          <option @selected(old('status',$v->status)===$s)>{{ $s }}</option>@endforeach
      </select></div>
    <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">AGENT</label>
      <input name="agent" value="{{ old('agent',$v->agent) }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
    <label class="flex items-end gap-2 pb-2.5 cursor-pointer">
      <input type="checkbox" name="hot" value="1" @checked(old('hot',$v->hot)) class="accent-yellow-600" style="width:16px;height:16px">
      <span class="text-[13px] font-semibold">🔥 Hot lot</span></label>
  </div>

  <div class="flex gap-3 pt-4 border-t hair">
    <a href="/admin/vehicles" class="flex-1 py-3 rounded btn-o font-bold text-center">Cancel</a>
    <button class="flex-1 py-3 rounded btn-g">{{ $v->exists ? 'Save changes' : 'Add vehicle' }}</button>
  </div>
</form>

@if($v->exists)
  @foreach($v->photos as $p)
    <form id="del{{ $p->id }}" method="POST" action="/admin/photos/{{ $p->id }}/delete" class="hidden">@csrf</form>
  @endforeach
@endif
@endsection
