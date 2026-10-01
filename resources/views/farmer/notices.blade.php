@extends('layouts.app')
@section('title','Notifications')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Notifications</h1>
  <div class="card mt-5 divide-y divide-mist">
    @forelse($notices as $n)<div class="p-4"><p class="{{ $n->is_read ? '' : 'font-bold' }}">{{ $n->message }}</p><p class="text-xs text-stone-500">{{ $n->created_at->format('d M Y, H:i') }}</p></div>
    @empty<p class="p-6 text-stone-600">Nothing yet. You will get a message when your booking changes.</p>@endforelse
  </div>
  <div class="mt-4">{{ $notices->links() }}</div>
</div>
@endsection
