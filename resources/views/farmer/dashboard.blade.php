@extends('layouts.app')
@section('title','My dashboard')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-wrap justify-between items-end gap-3">
    <div><h1 class="font-display text-4xl font-extrabold text-field">Hello, {{ explode(' ', auth()->user()->name)[0] }}</h1><p class="text-stone-600">{{ now()->format('l, d F Y') }}</p></div>
    <a href="{{ route('farmer.bookings.create') }}" class="btn btn-gold !px-6 !py-3 text-lg">Book a slot</a>
  </div>

  <div class="mt-6 grid lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2 card p-6">
      <h2 class="font-display text-2xl font-extrabold text-field mb-4">Your next delivery</h2>
      @if($current)
        <div class="flex flex-wrap gap-6 items-center">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $current->token_number }}" width="110" height="110" alt="QR code for token {{ $current->token_number }}">
          <div class="flex-1 min-w-[220px]">
            <p class="font-display text-3xl font-extrabold">{{ $current->token_number }}</p>
            <p class="text-stone-700">{{ $current->crop->name }} · {{ $current->estimated_quantity }} t · {{ $current->center->name }}</p>
            <p class="text-stone-700">{{ $current->booking_date->format('D d M') }} · {{ $current->time_slot }}</p>
            <div class="mt-2">@include('partials.badge',['s'=>$current->queue_status])</div>
            @if($current->queuePosition())<p class="mt-2 font-semibold text-field">Position {{ $current->queuePosition() }} in line · about {{ $current->etaMinutes() }} min</p>@endif
          </div>
          <a class="btn btn-primary" href="{{ route('farmer.bookings.show',$current) }}">Open token</a>
        </div>
      @else
        <p class="text-stone-600">You have no upcoming delivery. Book a slot to receive your digital token.</p>
      @endif
    </section>
    <section class="card p-6">
      <h2 class="font-display text-2xl font-extrabold text-field mb-3">Your totals</h2>
      <dl class="space-y-3">
        <div class="flex justify-between"><dt>Bookings made</dt><dd class="font-bold">{{ $totals['bookings'] }}</dd></div>
        <div class="flex justify-between"><dt>Completed deliveries</dt><dd class="font-bold">{{ $totals['completed'] }}</dd></div>
        <div class="flex justify-between"><dt>Crop delivered</dt><dd class="font-bold">{{ $totals['tons'] }} t</dd></div>
        <div class="flex justify-between"><dt>Payments pending</dt><dd class="font-bold text-wheat-dark">Rs {{ number_format($totals['pending']) }}</dd></div>
      </dl>
    </section>
  </div>

  <div class="mt-6 grid lg:grid-cols-2 gap-6">
    <section class="card p-6">
      <h2 class="font-display text-2xl font-extrabold text-field mb-3">Upcoming bookings</h2>
      @forelse($upcoming as $b)
        <a href="{{ route('farmer.bookings.show',$b) }}" class="flex justify-between py-2 border-b border-mist last:border-0 hover:bg-paper"><span><b>{{ $b->booking_date->format('d M') }}</b> · {{ $b->time_slot }} · {{ $b->crop->name }}</span>@include('partials.badge',['s'=>$b->queue_status])</a>
      @empty<p class="text-stone-600">Nothing booked yet.</p>@endforelse
    </section>
    <section class="card p-6">
      <div class="flex justify-between"><h2 class="font-display text-2xl font-extrabold text-field mb-3">Latest notifications</h2><a class="text-sm font-bold underline text-field" href="{{ route('farmer.notices') }}">See all</a></div>
      @forelse($notices as $n)<p class="py-2 border-b border-mist last:border-0 text-sm {{ $n->is_read ? 'text-stone-600' : 'font-semibold' }}">{{ $n->message }} <span class="text-stone-400">· {{ $n->created_at->diffForHumans() }}</span></p>@empty<p class="text-stone-600">No notifications yet.</p>@endforelse
    </section>
  </div>
</div>
@endsection
