@extends('layouts.app')
@section('title','All bookings')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">All bookings</h1>
  <form method="GET" class="mt-5 flex flex-wrap gap-3"><select name="center_id" class="field-in !w-56"><option value="">All centers</option>@foreach($centers as $c)<option value="{{ $c->id }}" @selected(request('center_id')==$c->id)>{{ $c->name }}</option>@endforeach</select>
    <select name="status" class="field-in !w-48"><option value="">All statuses</option>@foreach(['booked','checked_in','waiting','weighing','quality_check','unloading','payment_pending','completed','cancelled','missed','rejected','delayed'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
    <input type="date" name="date" value="{{ request('date') }}" class="field-in !w-44"><button class="btn btn-primary">Filter</button></form>
  <div class="card mt-4 overflow-x-auto"><table class="w-full text-sm"><thead class="bg-mist text-left"><tr><th class="p-3">Token</th><th>Farmer</th><th>Center</th><th>Crop</th><th>Date and slot</th><th>Status</th><th>Amount</th></tr></thead><tbody>
  @foreach($bookings as $b)<tr class="border-t border-mist"><td class="p-3 font-bold">{{ $b->token_number }}</td><td>{{ $b->farmer->name }}</td><td>{{ $b->center->name }}</td><td>{{ $b->crop->name }} · {{ $b->estimated_quantity }} t</td><td>{{ $b->booking_date->format('d M') }} · {{ $b->time_slot }}</td><td>@include('partials.badge',['s'=>$b->queue_status])</td><td>{{ $b->procurement?->total_amount ? 'Rs '.number_format($b->procurement->total_amount) : '—' }}</td></tr>@endforeach
  </tbody></table></div>
  <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
