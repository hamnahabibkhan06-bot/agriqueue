@extends('layouts.app')
@section('title','Book a slot')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Book a procurement slot</h1>
  <p class="text-stone-600 mt-1">Capacity is checked live, so a slot you can pick is a slot you can keep.</p>
  <form method="POST" action="{{ route('farmer.bookings.store') }}" id="f" class="mt-6 grid lg:grid-cols-5 gap-6">@csrf
    <div class="lg:col-span-2 card p-6 space-y-4 self-start">
      <label class="block"><span class="font-semibold text-sm">Crop</span>
        <select name="crop_id" class="field-in mt-1" required>@foreach($crops as $c)<option value="{{ $c->id }}" @selected(old('crop_id')==$c->id)>{{ $c->name }} · Rs {{ number_format($c->price_per_ton) }}/ton</option>@endforeach</select></label>
      <label class="block"><span class="font-semibold text-sm">Estimated quantity (tons)</span>
        <input id="qty" name="estimated_quantity" type="number" step="0.5" min="0.5" max="60" value="{{ old('estimated_quantity',3) }}" class="field-in mt-1" required>
        @error('estimated_quantity')<span role="alert" class="text-red-700 text-sm">{{ $message }}</span>@enderror</label>
      <label class="block"><span class="font-semibold text-sm">Procurement center</span>
        <select id="center" name="center_id" class="field-in mt-1" required>@foreach($centers as $c)<option value="{{ $c->id }}" @selected(old('center_id')==$c->id)>{{ $c->name }} ({{ $c->location }})</option>@endforeach</select></label>
      <label class="block"><span class="font-semibold text-sm">Delivery date</span>
        <input id="date" name="booking_date" type="date" min="{{ today()->toDateString() }}" max="{{ today()->addDays(30)->toDateString() }}" value="{{ old('booking_date',today()->toDateString()) }}" class="field-in mt-1" required>
        @error('booking_date')<span role="alert" class="text-red-700 text-sm">{{ $message }}</span>@enderror</label>
      <label class="block"><span class="font-semibold text-sm">Vehicle number (optional)</span><input name="vehicle_number" value="{{ old('vehicle_number') }}" class="field-in mt-1" placeholder="e.g. SND-4821"></label>
      <input type="hidden" name="time_slot" id="slot" value="{{ old('time_slot') }}">
      @error('time_slot')<p role="alert" class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-3 py-2 text-sm">{{ $message }}</p>@enderror
      <button id="go" class="btn btn-primary w-full" disabled>Choose a slot first</button>
    </div>
    <div class="lg:col-span-3 card p-6">
      <div class="flex flex-wrap justify-between gap-2 mb-1"><h2 class="font-display text-2xl font-extrabold text-field">Available slots</h2><p id="daily" class="text-sm font-semibold text-stone-600"></p></div>
      <div id="rec" class="hidden mb-3 bg-wheat/25 rounded-lg px-3 py-2 font-semibold"></div>
      <div id="slots" class="grid sm:grid-cols-2 gap-3" aria-live="polite"><p class="text-stone-500">Loading slots…</p></div>
    </div>
  </form>
</div>
@endsection
@push('scripts')
<script>
const $ = id => document.getElementById(id);
const colors = {'Available':'bg-green-100 text-green-800','Almost full':'bg-amber-100 text-amber-800','Full':'bg-red-100 text-red-800','Closed':'bg-stone-200 text-stone-600'};
async function load(){
  const url = `{{ route('farmer.slots') }}?center_id=${$('center').value}&date=${$('date').value}&qty=${$('qty').value||0}`;
  try {
    const d = await (await fetch(url,{headers:{'Accept':'application/json'}})).json();
    $('daily').textContent = `Today's center limit: ${d.daily_booked} of ${d.daily_capacity} tons booked`;
    const best = d.slots.find(s=>s.recommended);
    $('rec').classList.toggle('hidden', !best);
    if(best) $('rec').textContent = `Best available slot for ${$('qty').value} tons: ${best.slot}`;
    $('slots').innerHTML = d.slots.map(s => {
      const ok = s.fits, sel = $('slot').value === s.slot && ok;
      return `<button type="button" data-slot="${s.slot}" ${ok?'':'disabled'} class="text-left rounded-xl border-2 p-3 transition ${sel?'border-field bg-mist':'border-mist'} ${ok?'hover:border-leaf':'opacity-60 cursor-not-allowed'}">
        <div class="flex justify-between items-center"><b class="text-lg">${s.slot}</b><span class="text-xs font-bold rounded-full px-2 py-0.5 ${colors[s.status]}">${s.recommended?'Recommended':s.status}</span></div>
        <div class="mt-2 h-2 bg-mist rounded-full overflow-hidden"><div class="h-2 ${s.remaining<=0?'bg-red-500':'bg-leaf'}" style="width:${Math.min(100,s.booked/s.capacity*100)}%"></div></div>
        <p class="text-sm text-stone-600 mt-1">${s.booked} of ${s.capacity} tons booked · ${s.remaining} t free</p></button>`;
    }).join('');
    if(!d.slots.some(s=>s.slot===$('slot').value && s.fits)) { $('slot').value=''; }
    sync();
  } catch(e){ $('slots').innerHTML = '<p class="text-red-700">Could not load slots. Check your connection and try again.</p>'; }
}
function sync(){ const has = !!$('slot').value; $('go').disabled = !has; $('go').textContent = has ? `Confirm booking for ${$('slot').value}` : 'Choose a slot first'; }
$('slots').addEventListener('click', e => { const b = e.target.closest('button[data-slot]'); if(!b) return; $('slot').value = b.dataset.slot; load(); });
['center','date','qty'].forEach(i => $(i).addEventListener('change', () => { $('slot').value=''; load(); }));
$('qty').addEventListener('input', () => { clearTimeout(window.t); window.t = setTimeout(()=>{ $('slot').value=''; load(); }, 400); });
load();
</script>
@endpush
