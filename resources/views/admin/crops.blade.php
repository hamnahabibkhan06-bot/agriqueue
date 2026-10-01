@extends('layouts.app')
@section('title','Crops')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Crop categories and prices</h1>
  <div class="card mt-5 divide-y divide-mist">
    @foreach($crops as $c)
      <form method="POST" action="{{ route('admin.crops.update',$c) }}" class="p-4 flex flex-wrap items-end gap-3">@csrf @method('PUT')
        <img src="{{ $c->image }}" alt="" class="w-14 h-14 rounded-lg object-cover bg-mist" onerror="this.style.visibility='hidden'">
        <label class="text-sm font-semibold flex-1 min-w-[140px]">Name<input name="name" value="{{ $c->name }}" class="field-in mt-1" required></label>
        <label class="text-sm font-semibold">Rs per ton<input name="price_per_ton" type="number" step="any" value="{{ $c->price_per_ton+0 }}" class="field-in mt-1" required></label>
        <label class="text-sm font-semibold flex items-center gap-2 pb-2"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Active</label>
        <button class="btn btn-primary">Save</button>
      </form>
    @endforeach
  </div>
  <h2 class="font-display text-2xl font-extrabold text-field mt-8 mb-3">Add a crop</h2>
  <form method="POST" action="{{ route('admin.crops.store') }}" class="card p-4 flex flex-wrap items-end gap-3">@csrf
    <label class="text-sm font-semibold flex-1">Name<input name="name" class="field-in mt-1" required>@error('name')<span class="text-red-700">{{ $message }}</span>@enderror</label>
    <label class="text-sm font-semibold">Rs per ton<input name="price_per_ton" type="number" step="any" class="field-in mt-1" required></label>
    <button class="btn btn-gold">Add crop</button>
  </form>
</div>
@endsection
