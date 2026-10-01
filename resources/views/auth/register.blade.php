@extends('layouts.app')
@section('title','Register')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
  <h1 class="font-display text-4xl font-extrabold text-field">Register as a farmer</h1>
  <p class="text-stone-600 mt-2 mb-6">One account lets you book slots, track your queue and view payments.</p>
  <form method="POST" action="{{ route('register') }}" class="card p-6 grid sm:grid-cols-2 gap-4">@csrf
    @foreach([['name','Full name','text'],['email','Email','email'],['phone','Phone (03xx-xxxxxxx)','tel'],['location','Village / district','text'],['identification','CNIC number','text']] as [$n,$l,$t])
      <label class="block {{ $n==='identification' ? 'sm:col-span-2' : '' }}"><span class="font-semibold text-sm">{{ $l }}</span>
        <input class="field-in mt-1" type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" required>
        @error($n)<span role="alert" class="text-red-700 text-sm">{{ $message }}</span>@enderror</label>
    @endforeach
    <label class="block"><span class="font-semibold text-sm">Password (8+ characters)</span><input class="field-in mt-1" type="password" name="password" required>@error('password')<span role="alert" class="text-red-700 text-sm">{{ $message }}</span>@enderror</label>
    <label class="block"><span class="font-semibold text-sm">Confirm password</span><input class="field-in mt-1" type="password" name="password_confirmation" required></label>
    <button class="btn btn-primary sm:col-span-2">Create account</button>
  </form>
  <p class="mt-4 text-sm">Already registered? <a class="font-bold text-field underline" href="{{ route('login') }}">Log in</a></p>
</div>
@endsection
