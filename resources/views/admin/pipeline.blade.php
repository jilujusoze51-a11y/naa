@extends('admin-base')
@section('title','Pipeline — Admin')
@section('heading','Pipeline')
@section('admin')
<div x-data="pipe()" class="space-y-4">
  <p class="text-[12px]" style="color:var(--dim)">
    Drag a card between stages. Drop it onto Won / Lost / Abandoned below to close it out.
  </p>

  <div class="overflow-x-auto pb-3">
    <div class="flex gap-3 min-w-max items-start">
      @foreach($stages as $key=>$label)
        <div class="w-[280px] shrink-0 rounded-lg" style="background:var(--floor);border:1px solid var(--edge)"
             @dragover.prevent @drop.prevent="move('{{ $key }}')">
          <div class="px-4 py-3 border-b hair flex justify-between" style="background:var(--card2)">
            <span class="dsp font-extrabold text-[14px]">{{ $label }}</span>
            <span class="mn text-[11px] font-bold">{{ $leads->where('stage',$key)->count() }}</span>
          </div>
          <div class="p-2 space-y-2 min-h-[400px]">
            @foreach($leads->where('stage',$key) as $l)
              <div draggable="true" @dragstart="drag={{ $l->id }}"
                   class="card rounded-lg p-3 cursor-grab"
                   style="{{ $l->lead_status ? 'border-left:3px solid var(--live)' : '' }}">
                <a href="/admin/leads/{{ $l->id }}" class="block">
                  <div class="flex justify-between gap-2 mb-1.5">
                    <span class="font-bold text-[13px]">{{ $l->name }}</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold"
                          style="background:{{ $l->subject==='Make Offer'?'rgba(59,130,246,.12)':'rgba(245,158,11,.14)' }};
                                 color:{{ $l->subject==='Make Offer'?'var(--sig)':'var(--note)' }}">
                      {{ $l->subject==='Make Offer'?'OFFER':'CONTACT' }}</span>
                  </div>
                  @if($l->offer)<div class="mn font-bold gold text-[14px]">${{ number_format($l->offer) }}</div>@endif
                  @if($l->vehicle)<div class="text-[11px]" style="color:var(--dim)">Lot #{{ $l->vehicle->lot }}</div>@endif
                  <div class="flex justify-between pt-2 mt-2 border-t hair text-[11px]" style="color:var(--dim)">
                    <span>{{ $l->agent ?: 'Unassigned' }}</span>
                    <span class="mn">{{ $l->created_at->diffForHumans(null,true) }}</span>
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="grid sm:grid-cols-3 gap-3">
    @foreach(['won'=>['Won','#10B981'],'lost'=>['Lost','#EF4444'],'aband'=>['Abandoned','#8896A8']] as $k=>[$lbl,$c])
      <div class="rounded-lg p-4 text-center" style="background:var(--floor);border:2px dashed {{ $c }}44"
           @dragover.prevent @drop.prevent="setStatus('{{ $k }}')">
        <div class="dsp font-extrabold text-[15px]" style="color:{{ $c }}">{{ $lbl }}</div>
        <div class="text-[11px] mt-1" style="color:var(--dim)">Drag a card here</div>
      </div>
    @endforeach
  </div>
</div>
@endsection

@push('scripts')
<script>
function pipe(){
  return {
    drag:null,
    async post(id,body){
      await fetch('/admin/pipeline/'+id,{method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.CSRF},
        body:JSON.stringify(body)});
      location.reload();
    },
    move(stage){ if(this.drag) this.post(this.drag,{stage}); },
    setStatus(s){ if(this.drag) this.post(this.drag,{lead_status:s}); }
  }
}
</script>
@endpush
