@extends('admin-base')
@section('title','Lead — '.$l->name)
@section('heading','Lead — '.$l->name)
@section('admin')
<a href="/admin/leads" class="text-[12px] mb-4 inline-block" style="color:var(--dim)">← Leads</a>
<div class="grid lg:grid-cols-[1fr_320px] gap-4">
  <div class="space-y-4">
    <div class="card rounded-lg p-6">
      <div class="dsp text-2xl font-extrabold">{{ $l->name }}</div>
      <div class="text-[13px]" style="color:var(--sig)">{{ $l->email }}</div>
      <div class="text-[12px] mt-1" style="color:var(--dim)">{{ $l->phone }}</div>
      <div class="flex gap-2 mt-5 pt-5 border-t hair">
        <a href="mailto:{{ $l->email }}" class="px-4 py-2 rounded text-[12px] btn-g">✉ Reply via email</a>
        @if($l->phone)<a href="tel:{{ $l->phone }}" class="px-4 py-2 rounded text-[12px] btn-o font-bold">☎ Call</a>@endif
      </div>
    </div>
    <div class="card rounded-lg p-6">
      <div class="dsp font-extrabold mb-3">Inquiry</div>
      <div class="rounded p-4 text-[13px]" style="background:var(--void);border:1px solid var(--edge)">
        @if($l->offer)<div class="mn font-bold gold text-lg">Offer: ${{ number_format($l->offer,2) }}</div>@endif
        @if($l->vehicle)<div class="text-[12px] mt-1" style="color:var(--ash)">
          Lot #{{ $l->vehicle->lot }} — {{ $l->vehicle->title() }}</div>@endif
        @if($l->message)<div class="mt-2" style="color:var(--ash)">{{ $l->message }}</div>@endif
      </div>
    </div>
    <form method="POST" action="/admin/leads/{{ $l->id }}" class="card rounded-lg p-6">@csrf
      <div class="dsp font-extrabold mb-3">Notes</div>
      <textarea name="notes" rows="4" class="w-full px-3 py-2.5 rounded text-[13px] mb-3">{{ $l->notes }}</textarea>
      <div class="grid sm:grid-cols-3 gap-3 mb-3">
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">STAGE</label>
          <select name="stage" class="w-full px-3 py-2.5 rounded text-[13px]">
            @foreach(['new'=>'New Leads','na'=>'N/A','call'=>'First Call','int'=>'Interested',
                      'paper'=>'Paper Work','inv'=>'Invoice Sent','paid'=>'Paid'] as $k=>$lbl)
              <option value="{{ $k }}" @selected($l->stage===$k)>{{ $lbl }}</option>@endforeach
          </select></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">STATUS</label>
          <select name="lead_status" class="w-full px-3 py-2.5 rounded text-[13px]">
            <option value="">Open</option>
            @foreach(['won'=>'Won','lost'=>'Lost','aband'=>'Abandoned'] as $k=>$lbl)
              <option value="{{ $k }}" @selected($l->lead_status===$k)>{{ $lbl }}</option>@endforeach
          </select></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">AGENT</label>
          <input name="agent" value="{{ $l->agent }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
      </div>
      <button class="px-6 py-3 rounded btn-g">Save</button>
    </form>
  </div>
  <div class="card rounded-lg p-5">
    <div class="dsp font-extrabold mb-3">Details</div>
    @foreach(['Subject'=>$l->subject,'Received'=>$l->created_at->format('M j, Y g:i A'),
              'Stage'=>$l->stage,'Agent'=>$l->agent ?: '—'] as $k=>$v)
      <div class="flex justify-between py-2 border-b hair text-[12px]">
        <span style="color:var(--dim)">{{ $k }}</span><span class="font-bold">{{ $v }}</span></div>
    @endforeach
  </div>
</div>
@endsection
