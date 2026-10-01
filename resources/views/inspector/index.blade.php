@extends('layouts.app')
@section('title','Inspections')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Crops waiting for inspection</h1>
  <p class="text-stone-600">Record the grade and moisture, then accept or reject the produce.</p>
  <div class="mt-6 space-y-5">
  @forelse($pending as $b)
    <form method="POST" action="{{ route('inspector.store',$b) }}" class="card p-5">@csrf
      <div class="flex flex-wrap justify-between gap-2">
        <div><p class="font-display text-2xl font-extrabold">{{ $b->token_number }} · {{ $b->crop->name }}</p><p class="text-sm text-stone-600">{{ $b->farmer->name }} · {{ $b->center->name }} · net {{ number_format($b->procurement->actual_weight) }} kg</p></div>
      </div>
      <div class="grid sm:grid-cols-4 gap-3 mt-4">
        <label class="text-sm font-semibold">Grade<select name="quality_grade" class="field-in mt-1" required><option>A</option><option>B</option><option>C</option></select></label>
        <label class="text-sm font-semibold">Moisture %<input name="moisture" type="number" step="0.1" min="0" max="60" required class="field-in mt-1"></label>
        <label class="text-sm font-semibold">Damaged %<input name="damaged_percent" type="number" step="0.1" min="0" max="100" class="field-in mt-1"></label>
        <label class="text-sm font-semibold">Foreign material %<input name="foreign_material" type="number" step="0.1" min="0" max="100" class="field-in mt-1"></label>
        <label class="text-sm font-semibold sm:col-span-4">Remarks<input name="inspector_remarks" maxlength="500" class="field-in mt-1" placeholder="Optional notes for the farmer and staff"></label>
      </div>
      <div class="mt-4 flex gap-3">
        <button name="decision" value="accepted" class="btn btn-primary">Accept produce</button>
        <button name="decision" value="rejected" class="btn btn-danger" onclick="return confirm('Reject this produce? The farmer will be notified.')">Reject produce</button>
      </div>
    </form>
  @empty
    <div class="card p-8 text-center text-stone-600">No crops are waiting. New ones appear here once staff record the weight.</div>
  @endforelse
  </div>
  @if($recent->count())
  <h2 class="font-display text-2xl font-extrabold text-field mt-10 mb-3">Your recent inspections</h2>
  <div class="card divide-y divide-mist">@foreach($recent as $p)<div class="p-3 flex justify-between text-sm"><span><b>{{ $p->booking->token_number }}</b> · {{ $p->booking->crop->name }} · Grade {{ $p->quality_grade }} · moisture {{ $p->moisture }}%</span>@include('partials.badge',['s'=>$p->quality_status])</div>@endforeach</div>
  @endif
</div>
@endsection
