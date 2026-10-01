@extends('layouts.app')
@section('title','Centers')
@section('content')
@php $fields = [['daily_capacity','Daily capacity (t)'],['slot_capacity','Slot capacity (t)'],['weighing_stations','Weighing stations'],['unloading_points','Unloading points'],['inspection_counters','Inspection counters'],['storage_capacity','Storage (t)'],['staff_available','Staff available'],['avg_minutes_per_vehicle','Min per vehicle']]; @endphp
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Procurement centers and capacity</h1>
  <p class="text-stone-600">Daily and per-slot limits here control what farmers can book.</p>
  <div class="mt-6 space-y-4">
  @foreach($centers as $c)
    <form method="POST" action="{{ route('admin.centers.update',$c) }}" class="card p-5">@csrf @method('PUT')
      <div class="grid md:grid-cols-3 gap-3"><label class="text-sm font-semibold">Name<input name="name" value="{{ $c->name }}" class="field-in mt-1" required></label><label class="text-sm font-semibold">Location<input name="location" value="{{ $c->location }}" class="field-in mt-1" required></label>
        <label class="flex items-center gap-2 mt-6 font-semibold text-sm"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Open for booking <span class="text-stone-500 font-normal">({{ $c->bookings_count }} bookings)</span></label></div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">@foreach($fields as [$n,$l])<label class="text-xs font-semibold">{{ $l }}<input type="number" step="any" name="{{ $n }}" value="{{ $c->$n+0 }}" class="field-in !py-1.5 mt-1" required></label>@endforeach</div>
      <button class="btn btn-primary mt-4">Save {{ $c->name }}</button>
    </form>
  @endforeach
  </div>
  <h2 class="font-display text-2xl font-extrabold text-field mt-10 mb-3">Add a center</h2>
  <form method="POST" action="{{ route('admin.centers.store') }}" class="card p-5">@csrf
    <div class="grid md:grid-cols-2 gap-3"><label class="text-sm font-semibold">Name<input name="name" class="field-in mt-1" required></label><label class="text-sm font-semibold">Location<input name="location" class="field-in mt-1" required></label></div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">@foreach($fields as [$n,$l])<label class="text-xs font-semibold">{{ $l }}<input type="number" step="any" name="{{ $n }}" value="{{ ['daily_capacity'=>200,'slot_capacity'=>40,'weighing_stations'=>2,'unloading_points'=>3,'inspection_counters'=>2,'storage_capacity'=>2000,'staff_available'=>6,'avg_minutes_per_vehicle'=>18][$n] }}" class="field-in !py-1.5 mt-1" required></label>@endforeach</div>
    <button class="btn btn-gold mt-4">Add center</button>
  </form>
</div>
@endsection
