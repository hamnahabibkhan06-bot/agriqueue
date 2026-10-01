@extends('layouts.app')
@section('title','Token '.$b->token_number)
@section('content')
@php $flow = \App\Models\Booking::FLOW; $p = $b->procurement; @endphp
<div class="max-w-4xl mx-auto px-4 py-8">
  <a href="{{ route('farmer.bookings') }}" class="text-sm font-bold underline text-field">All bookings</a>
  <div class="mt-3 grid md:grid-cols-5 gap-6">
    <section class="md:col-span-3 card p-6">
      <div class="flex justify-between gap-4">
        <div><p class="text-sm text-stone-500 font-semibold">Digital token</p><h1 class="font-display text-4xl font-extrabold text-field">{{ $b->token_number }}</h1><div class="mt-2" id="badge">@include('partials.badge',['s'=>$b->queue_status])</div></div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ $b->token_number }}" width="120" height="120" alt="QR code for token {{ $b->token_number }}">
      </div>
      <dl class="grid grid-cols-2 gap-3 mt-5 text-sm">
        <div><dt class="text-stone-500">Crop</dt><dd class="font-bold">{{ $b->crop->name }} · {{ $b->estimated_quantity }} tons</dd></div>
        <div><dt class="text-stone-500">Date and slot</dt><dd class="font-bold">{{ $b->booking_date->format('D d M Y') }} · {{ $b->time_slot }}</dd></div>
        <div><dt class="text-stone-500">Center</dt><dd class="font-bold">{{ $b->center->name }}</dd></div>
        <div><dt class="text-stone-500">Vehicle</dt><dd class="font-bold">{{ $b->vehicle_number ?: 'Not given' }}</dd></div>
      </dl>
      <div id="live" class="mt-5 rounded-lg bg-wheat/25 px-4 py-3 font-semibold {{ $b->queuePosition() ? '' : 'hidden' }}">Position <span id="pos">{{ $b->queuePosition() }}</span> in line · about <span id="eta">{{ $b->etaMinutes() }}</span> min to weighing</div>
      @if(in_array($b->queue_status,['booked','delayed']))
        <form method="POST" action="{{ route('farmer.bookings.cancel',$b) }}" class="mt-5" onsubmit="return confirm('Cancel this booking and release the slot?')">@csrf<button class="btn btn-danger">Cancel booking</button></form>
      @endif
      @if($b->queue_status==='completed')<a class="btn btn-gold mt-5" href="{{ route('receipt',$b) }}">View receipt</a>@endif
    </section>
    <section class="md:col-span-2 card p-6">
      <h2 class="font-display text-2xl font-extrabold text-field mb-3">Progress</h2>
      <ol class="space-y-2" id="steps">
        @foreach($flow as $i=>$s)
          @php $cur = array_search($b->queue_status,$flow); $done = $cur!==false && $i<=$cur; @endphp
          <li data-s="{{ $s }}" class="flex items-center gap-3 text-sm {{ $done?'font-bold text-field':'text-stone-400' }}"><span class="w-5 h-5 rounded-full grid place-items-center text-[11px] {{ $done?'bg-leaf text-white':'bg-mist' }}">{{ $done?'✓':'' }}</span>{{ ucwords(str_replace('_',' ',$s)) }}</li>
        @endforeach
      </ol>
      @if(in_array($b->queue_status,['cancelled','missed','rejected']))<p class="mt-3 text-sm font-semibold text-red-700">This booking ended as {{ $b->label() }}.</p>@endif
    </section>
  </div>
  @if($p && $p->actual_weight)
  <section class="card p-6 mt-6">
    <h2 class="font-display text-2xl font-extrabold text-field mb-3">Weight, quality and payment</h2>
    <dl class="grid sm:grid-cols-4 gap-4 text-sm">
      <div><dt class="text-stone-500">Gross / empty</dt><dd class="font-bold">{{ number_format($p->gross_weight) }} / {{ number_format($p->empty_weight) }} kg</dd></div>
      <div><dt class="text-stone-500">Net crop</dt><dd class="font-bold">{{ number_format($p->actual_weight) }} kg</dd></div>
      <div><dt class="text-stone-500">Quality</dt><dd class="font-bold">{{ $p->quality_grade ? 'Grade '.$p->quality_grade.' · moisture '.$p->moisture.'%' : 'Waiting for inspection' }}</dd></div>
      <div><dt class="text-stone-500">Amount</dt><dd class="font-bold">{{ $p->total_amount ? 'Rs '.number_format($p->total_amount) : 'Calculated after unloading' }} @if($p->total_amount) @include('partials.badge',['s'=>$p->payment_status]) @endif</dd></div>
    </dl>
    @if($p->inspector_remarks)<p class="mt-3 text-sm"><b>Inspector remarks:</b> {{ $p->inspector_remarks }}</p>@endif
  </section>
  @endif
</div>
@endsection
@push('scripts')
@if(!in_array($b->queue_status,['completed','cancelled','missed','rejected']))
<script>
// Poll the live status every 10 seconds, then refresh the page when the stage changes.
const current = @json($b->queue_status);
setInterval(async () => {
  try {
    const d = await (await fetch('{{ route('farmer.bookings.status',$b) }}',{headers:{'Accept':'application/json'}})).json();
    if (d.status !== current) return location.reload();
    document.getElementById('live').classList.toggle('hidden', !d.position);
    document.getElementById('pos').textContent = d.position; document.getElementById('eta').textContent = d.eta;
  } catch(e) {}
}, 10000);
</script>
@endif
@endpush
