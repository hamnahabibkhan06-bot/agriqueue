@extends('layouts.app')
@section('title','Today\'s queue')
@section('content')
@php
$stages = ['booked'=>'Booked, not arrived','delayed'=>'Delayed','checked_in'=>'Checked in','waiting'=>'Live queue','weighing'=>'At weighing','quality_check'=>'At quality check','unloading'=>'Unloading','payment_pending'=>'Payment pending','completed'=>'Completed today','missed'=>'Missed'];
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-wrap justify-between items-end gap-4">
    <div><h1 class="font-display text-4xl font-extrabold text-field">{{ $center->name }}</h1><p class="text-stone-600">{{ now()->format('l d F') }} · {{ $count }} bookings today · {{ $center->weighing_stations }} weighing stations · {{ $center->unloading_points }} unloading points</p></div>
    <div class="flex flex-wrap gap-3 items-end">
      @if(auth()->user()->role==='admin')
        <form method="GET"><select name="center" class="field-in" onchange="this.form.submit()" aria-label="Center">@foreach($centers as $c)<option value="{{ $c->id }}" @selected($c->id==$center->id)>{{ $c->name }}</option>@endforeach</select></form>
      @endif
      <form method="GET" class="flex gap-2"><input type="hidden" name="center" value="{{ $center->id }}"><input name="token" value="{{ $search }}" placeholder="Verify token e.g. AP-1-1001-004" class="field-in !w-64"><button class="btn btn-primary">Find</button></form>
      <a href="{{ route('board',$center) }}" target="_blank" class="btn btn-ghost">Open queue screen</a>
    </div>
  </div>

  <div class="mt-6 space-y-6">
  @foreach($stages as $key=>$title)
    @php $list = $groups->get($key, collect()); if(in_array($key,['checked_in','waiting'])) $list = $list->sortBy('checked_in_at')->values(); @endphp
    @continue($list->isEmpty() && !in_array($key,['booked','waiting']))
    <section>
      <h2 class="font-display text-2xl font-extrabold text-field mb-2">{{ $title }} <span class="text-base font-sans text-stone-500">({{ $list->count() }})</span></h2>
      <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
      @forelse($list as $i => $b)
        <article class="card p-4">
          <div class="flex justify-between gap-2"><div><p class="font-display text-xl font-extrabold">{{ $b->token_number }}</p><p class="text-sm text-stone-600">{{ $b->farmer->name }} · {{ $b->farmer->phone }}</p></div>@if(in_array($key,['checked_in','waiting']))<span class="font-display text-3xl font-extrabold text-wheat-dark">#{{ $i+1 }}</span>@endif</div>
          <p class="text-sm mt-1">{{ $b->crop->name }} · {{ $b->estimated_quantity }} t · {{ $b->time_slot }} · {{ $b->vehicle_number ?: 'No plate' }}</p>

          @if(in_array($key,['booked','delayed']))
            <div class="mt-3 flex flex-wrap gap-2">
              <form method="POST" action="{{ route('staff.advance',$b) }}">@csrf<button class="btn btn-primary !py-1.5">Verify token and check in</button></form>
              @if($key==='booked')<form method="POST" action="{{ route('staff.flag',$b) }}">@csrf<input type="hidden" name="status" value="delayed"><button class="btn btn-ghost !py-1.5">Mark delayed</button></form>@endif
              <form method="POST" action="{{ route('staff.flag',$b) }}" onsubmit="return confirm('Mark this booking as missed? The slot will be released.')">@csrf<input type="hidden" name="status" value="missed"><button class="btn btn-danger !py-1.5">Missed</button></form>
            </div>
          @elseif($key==='checked_in')
            <form method="POST" action="{{ route('staff.advance',$b) }}" class="mt-3">@csrf<button class="btn btn-primary !py-1.5">Add to live queue</button></form>
          @elseif($key==='waiting')
            <p class="text-sm mt-1 text-stone-600">Waiting since {{ $b->queued_at?->format('H:i') }}</p>
            <form method="POST" action="{{ route('staff.advance',$b) }}" class="mt-3">@csrf<button class="btn btn-gold !py-1.5">Call for weighing</button></form>
          @elseif($key==='weighing')
            <form method="POST" action="{{ route('staff.weigh',$b) }}" class="mt-3 grid grid-cols-3 gap-2">@csrf
              <label class="text-xs font-semibold">Gross kg<input name="gross_weight" type="number" step="1" min="1" required class="field-in !py-1.5 mt-1"></label>
              <label class="text-xs font-semibold">Empty kg<input name="empty_weight" type="number" step="1" min="1" required class="field-in !py-1.5 mt-1"></label>
              <label class="text-xs font-semibold">Station<select name="weighing_station" class="field-in !py-1.5 mt-1">@for($s=1;$s<=$center->weighing_stations;$s++)<option>WS-{{ $s }}</option>@endfor</select></label>
              <button class="btn btn-primary col-span-3 !py-1.5">Record weight and send to inspector</button>
            </form>
          @elseif($key==='quality_check')
            <p class="text-sm mt-2 text-stone-600">Net {{ number_format($b->procurement?->actual_weight) }} kg. Waiting for the inspector.</p>
          @elseif($key==='unloading')
            <p class="text-sm mt-2">Grade <b>{{ $b->procurement->quality_grade }}</b> · {{ number_format($b->procurement->actual_weight) }} kg net</p>
            <form method="POST" action="{{ route('staff.advance',$b) }}" class="mt-3">@csrf<button class="btn btn-primary !py-1.5">Confirm unloading and calculate amount</button></form>
          @elseif($key==='payment_pending')
            <p class="text-sm mt-2">Amount due <b>Rs {{ number_format($b->procurement->total_amount) }}</b></p>
            <form method="POST" action="{{ route('staff.advance',$b) }}" class="mt-3">@csrf<button class="btn btn-gold !py-1.5">Record payment and complete</button></form>
          @elseif($key==='completed')
            <p class="text-sm mt-2">Paid Rs {{ number_format($b->procurement->total_amount) }} · {{ number_format($b->procurement->actual_weight) }} kg</p>
            <a class="underline font-bold text-field text-sm" href="{{ route('receipt',$b) }}">Receipt {{ $b->procurement->receipt_number }}</a>
          @endif
        </article>
      @empty
        <p class="text-stone-500 text-sm">@if($key==='waiting')Nobody is waiting.@else No farmers booked for today.@endif</p>
      @endforelse
      </div>
    </section>
  @endforeach
  </div>
</div>
@endsection
@push('scripts')<script>setTimeout(()=>{ if(!document.querySelector('input:focus,select:focus')) location.reload(); }, 30000);</script>@endpush
