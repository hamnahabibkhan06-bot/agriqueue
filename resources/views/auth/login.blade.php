@extends('layouts.app')
@section('title','Log in')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-12 grid md:grid-cols-2 gap-10 items-center">
  <div class="hidden md:block rounded-2xl overflow-hidden relative h-[460px] bg-field">
    <img src="https://loremflickr.com/900/900/farmer,harvest?lock=41" alt="Farmer harvesting a crop" class="absolute inset-0 w-full h-full object-cover opacity-80" onerror="this.remove()">
    <div class="absolute inset-0 bg-gradient-to-t from-field-dark/90 to-transparent"></div>
    <p class="absolute bottom-6 left-6 right-6 text-white font-display text-3xl font-extrabold">Your turn is already booked. Just arrive on time.</p>
  </div>
  <div>
    <h1 class="font-display text-4xl font-extrabold text-field mb-6">Log in</h1>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">@csrf
      <label class="block"><span class="font-semibold text-sm">Email</span><input class="field-in mt-1" type="email" name="email" value="{{ old('email') }}" required autofocus></label>
      <label class="block"><span class="font-semibold text-sm">Password</span><input class="field-in mt-1" type="password" name="password" required></label>
      @error('email')<p role="alert" class="text-red-700 text-sm">{{ $message }}</p>@enderror
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Keep me logged in</label>
      <button class="btn btn-primary w-full">Log in</button>
    </form>
    <p class="mt-4 text-sm">New farmer? <a class="font-bold text-field underline" href="{{ route('register') }}">Create an account</a></p>
    <div class="mt-8 card p-4 text-sm">
      <p class="font-bold mb-2">Demo accounts <span class="font-normal text-stone-500">(password: password)</span></p>
      <ul class="space-y-1 text-stone-700">
        <li>Farmer: farmer@agriqueue.test</li><li>Staff: staff@agriqueue.test</li>
        <li>Inspector: inspector@agriqueue.test</li><li>Admin: admin@agriqueue.test</li>
      </ul>
    </div>
  </div>
</div>
@endsection
