@extends('layout')
@section('title','Register — NAA')
@section('body')
@include('partials.nav')
<div class="max-w-2xl mx-auto px-5 py-12">
  <div class="card rounded-lg p-8">
    <h1 class="dsp text-3xl font-extrabold mb-1">Register to Bid</h1>
    <p class="text-[13px] mb-7" style="color:var(--ash)">
      Free account. A verified ID is required before live bidding unlocks.
    </p>

    <form method="POST" action="/register" enctype="multipart/form-data" class="space-y-4">@csrf
      <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="checkbox" name="is_business" value="1" class="accent-yellow-600" style="width:16px;height:16px">
        <span class="text-[13px] font-semibold">Business Account (dealership or company)</span>
      </label>

      <div class="grid sm:grid-cols-2 gap-3">
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">FULL NAME *</label>
          <input name="name" value="{{ old('name') }}" required class="w-full px-3 py-2.5 rounded text-[13px]"></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">EMAIL *</label>
          <input name="email" type="email" value="{{ old('email') }}" required class="w-full px-3 py-2.5 rounded text-[13px]"></div>
      </div>
      <div class="grid sm:grid-cols-3 gap-3">
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">PHONE *</label>
          <input name="phone" value="{{ old('phone') }}" required class="w-full px-3 py-2.5 rounded text-[13px] mn"></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">CITY</label>
          <input name="city" value="{{ old('city') }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">COMPANY</label>
          <input name="company" value="{{ old('company') }}" class="w-full px-3 py-2.5 rounded text-[13px]"></div>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">PASSWORD *</label>
          <input name="password" type="password" required class="w-full px-3 py-2.5 rounded text-[13px]"></div>
        <div><label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">CONFIRM PASSWORD *</label>
          <input name="password_confirmation" type="password" required class="w-full px-3 py-2.5 rounded text-[13px]"></div>
      </div>

      <div class="pt-4 border-t hair">
        <div class="dsp font-extrabold mb-1">Identity Verification</div>
        <p class="text-[12px] mb-4" style="color:var(--dim)">
          Upload a clear photo of your driver's license. Images are stored privately and reviewed by an agent.
        </p>
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">LICENSE — FRONT *</label>
            <input type="file" name="doc_front" accept="image/*" required class="w-full px-3 py-2.5 rounded text-[12px]">
          </div>
          <div>
            <label class="text-[10px] tracking-[.1em] block mb-1.5" style="color:var(--dim)">LICENSE — BACK</label>
            <input type="file" name="doc_back" accept="image/*" class="w-full px-3 py-2.5 rounded text-[12px]">
          </div>
        </div>
      </div>

      <button class="w-full py-3.5 rounded btn-s">Create account →</button>
    </form>
  </div>
</div>
@endsection
