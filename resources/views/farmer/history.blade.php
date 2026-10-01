@extends('layouts.app')
@section('title','Procurement history')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Procurement history and payments</h1>
  <div class="card mt-5 overflow-x-auto">
    <table class="w-full text-sm"><thead class="bg-mist text-left"><tr><th class="p-3">Date</th><th>Crop</th><th>Center</th><th>Net weight</th><th>Grade</th><th>Amount</th><th>Payment</th><th></th></tr></thead><tbody>
    @forelse($rows as $b)@php $p=$b->procurement; @endphp
      <tr class="border-t border-mist"><td class="p-3">{{ $b->booking_date->format('d M Y') }}</td><td>{{ $b->crop->name }}</td><td>{{ $b->center->name }}</td><td>{{ number_format($p->actual_weight) }} kg</td><td>{{ $p->quality_grade }}</td><td class="font-bold">Rs {{ number_format($p->total_amount) }}</td><td>@include('partials.badge',['s'=>$p->payment_status])</td><td><a class="font-bold underline text-field" href="{{ route('receipt',$b) }}">Receipt</a></td></tr>
    @empty<tr><td colspan="8" class="p-8 text-center text-stone-600">Completed deliveries and their receipts will appear here.</td></tr>@endforelse
    </tbody></table>
  </div>
  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
