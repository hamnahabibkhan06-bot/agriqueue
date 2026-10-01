@extends('layouts.app')
@section('title','My bookings')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex justify-between items-end flex-wrap gap-3"><h1 class="font-display text-4xl font-extrabold text-field">My bookings</h1><a href="{{ route('farmer.bookings.create') }}" class="btn btn-gold">Book a slot</a></div>
  <form method="GET" class="card p-4 mt-5 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
    <label class="text-sm font-semibold">Center<select name="center_id" class="field-in mt-1"><option value="">All</option>@foreach($centers as $c)<option value="{{ $c->id }}" @selected(request('center_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></label>
    <label class="text-sm font-semibold">Crop<select name="crop_id" class="field-in mt-1"><option value="">All</option>@foreach($crops as $c)<option value="{{ $c->id }}" @selected(request('crop_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></label>
    <label class="text-sm font-semibold">Date<input type="date" name="date" value="{{ request('date') }}" class="field-in mt-1"></label>
    <label class="text-sm font-semibold">Booking status<select name="status" class="field-in mt-1"><option value="">All</option>@foreach(['booked','checked_in','waiting','weighing','quality_check','unloading','payment_pending','completed','cancelled','missed','rejected','delayed'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select></label>
    <label class="text-sm font-semibold">Payment<select name="payment" class="field-in mt-1"><option value="">All</option><option value="pending" @selected(request('payment')=='pending')>Pending</option><option value="paid" @selected(request('payment')=='paid')>Paid</option></select></label>
    <div class="flex gap-2"><button class="btn btn-primary">Filter</button><a href="{{ route('farmer.bookings') }}" class="btn btn-ghost">Clear</a></div>
  </form>
  <div class="card mt-5 overflow-x-auto">
    <table class="w-full text-sm"><thead class="bg-mist text-left"><tr><th class="p-3">Token</th><th>Date and slot</th><th>Crop</th><th>Center</th><th>Status</th><th>Payment</th><th></th></tr></thead><tbody>
    @forelse($bookings as $b)
      <tr class="border-t border-mist"><td class="p-3 font-bold">{{ $b->token_number }}</td><td>{{ $b->booking_date->format('d M Y') }} · {{ $b->time_slot }}</td><td>{{ $b->crop->name }} · {{ $b->estimated_quantity }} t</td><td>{{ $b->center->name }}</td><td>@include('partials.badge',['s'=>$b->queue_status])</td><td>{{ $b->procurement?->total_amount ? '' : '' }}@if($b->procurement?->total_amount)@include('partials.badge',['s'=>$b->procurement->payment_status])@else—@endif</td><td><a class="font-bold underline text-field" href="{{ route('farmer.bookings.show',$b) }}">Open</a></td></tr>
    @empty<tr><td colspan="7" class="p-8 text-center text-stone-600">No bookings match. <a class="underline font-bold text-field" href="{{ route('farmer.bookings.create') }}">Book your first slot</a>.</td></tr>@endforelse
    </tbody></table>
  </div>
  <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
