@extends('layouts.app')
@section('title','Receipt '.$b->procurement->receipt_number)
@section('content')
@php $p = $b->procurement; @endphp
<div class="max-w-2xl mx-auto px-4 py-10">
  <div class="card p-8" id="receipt">
    <div class="flex justify-between items-start border-b border-mist pb-4">
      <div><h1 class="font-display text-3xl font-extrabold text-field">Procurement receipt</h1><p class="text-stone-600">{{ $b->center->name }} · {{ $b->center->location }}</p></div>
      <div class="text-right"><p class="font-bold">{{ $p->receipt_number }}</p><p class="text-sm text-stone-600">{{ $p->completion_time?->format('d M Y, H:i') }}</p></div>
    </div>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 mt-5 text-sm">
      <div><dt class="text-stone-500">Farmer</dt><dd class="font-bold">{{ $b->farmer->name }}</dd></div>
      <div><dt class="text-stone-500">Token</dt><dd class="font-bold">{{ $b->token_number }}</dd></div>
      <div><dt class="text-stone-500">Crop</dt><dd class="font-bold">{{ $b->crop->name }}</dd></div>
      <div><dt class="text-stone-500">Quality grade</dt><dd class="font-bold">{{ $p->quality_grade }} · moisture {{ $p->moisture }}%</dd></div>
      <div><dt class="text-stone-500">Gross / empty weight</dt><dd class="font-bold">{{ number_format($p->gross_weight) }} / {{ number_format($p->empty_weight) }} kg</dd></div>
      <div><dt class="text-stone-500">Net crop weight</dt><dd class="font-bold">{{ number_format($p->actual_weight) }} kg</dd></div>
      <div><dt class="text-stone-500">Rate per ton (after grade)</dt><dd class="font-bold">Rs {{ number_format($p->price_per_unit) }}</dd></div>
      <div><dt class="text-stone-500">Payment</dt><dd>@include('partials.badge',['s'=>$p->payment_status])</dd></div>
    </dl>
    <div class="mt-6 bg-field text-white rounded-xl p-5 flex justify-between items-center"><span class="font-semibold">Total amount</span><span class="font-display text-3xl font-extrabold">Rs {{ number_format($p->total_amount) }}</span></div>
  </div>
  <button onclick="window.print()" class="btn btn-ghost mt-5">Print receipt</button>
</div>
<style>@media print{header,footer,button{display:none!important}body{background:#fff}}</style>
@endsection
