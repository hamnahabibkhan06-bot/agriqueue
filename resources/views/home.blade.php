@extends('layouts.app')
@section('title','Book your procurement slot')
@section('content')
{{-- Hero: the product's most characteristic object is the digital token --}}
<section class="relative bg-field-dark overflow-hidden">
  <img src="https://loremflickr.com/1800/1000/wheat,field,sunset?lock=3" alt="" class="absolute inset-0 w-full h-full object-cover opacity-55" onerror="this.remove()">
  <div class="absolute inset-0 bg-gradient-to-r from-field-dark via-field-dark/70 to-transparent"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-20 lg:py-28 grid lg:grid-cols-5 gap-12 items-center">
    <div class="lg:col-span-3 text-white">
      <h1 class="font-display font-extrabold text-5xl sm:text-6xl leading-[1.02] tracking-tight">Book your slot.<br>Leave the queue at home.</h1>
      <p class="mt-6 text-lg text-mist max-w-xl">Farmers pick a procurement center and a time, get a digital token, and follow their place in line from the field. Centers weigh, inspect and pay without the crowd.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        @guest<a href="{{ route('register') }}" class="btn btn-gold text-lg !px-6 !py-3">Register as a farmer</a><a href="{{ route('login') }}" class="btn border-2 border-white/70 text-white hover:bg-white/10 !px-6 !py-3">Log in</a>
        @else<a href="{{ route('dashboard') }}" class="btn btn-gold text-lg !px-6 !py-3">Go to my dashboard</a>@endguest
      </div>
    </div>
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl shadow-2xl p-6 rotate-1 max-w-sm ml-auto">
        <div class="flex justify-between items-start">
          <div><p class="text-xs font-semibold text-stone-500">Digital token</p><p class="font-display text-3xl font-extrabold text-field">AP-1-1003-007</p></div>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=AP-1-1003-007" width="72" height="72" alt="Sample QR token">
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div><dt class="text-stone-500">Crop</dt><dd class="font-bold">Wheat · 3 tons</dd></div>
          <div><dt class="text-stone-500">Slot</dt><dd class="font-bold">Friday 11:00–12:00</dd></div>
          <div class="col-span-2"><dt class="text-stone-500">Center</dt><dd class="font-bold">Hyderabad Grain Market</dd></div>
        </dl>
        <div class="mt-4 rounded-lg bg-wheat/20 px-3 py-2 text-sm font-semibold text-soil">You are 3rd in line. About 18 minutes to weighing.</div>
      </div>
    </div>
  </div>
</section>

{{-- Live numbers --}}
<section class="max-w-7xl mx-auto px-4 -mt-8 relative">
  <div class="card shadow-lg grid grid-cols-3 divide-x divide-mist text-center py-5">
    <div><p class="font-display text-3xl font-extrabold text-field">{{ $centers }}</p><p class="text-sm text-stone-600">procurement centers</p></div>
    <div><p class="font-display text-3xl font-extrabold text-field">{{ number_format($tons) }} t</p><p class="text-sm text-stone-600">crop received</p></div>
    <div><p class="font-display text-3xl font-extrabold text-field">{{ $farmers }}</p><p class="text-sm text-stone-600">registered farmers</p></div>
  </div>
</section>

{{-- Process: a real sequence --}}
<section class="max-w-7xl mx-auto px-4 mt-20">
  <h2 class="font-display text-4xl font-extrabold text-field max-w-2xl">From booking to payment in six steps</h2>
  <ol class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach([['Choose crop and center','Enter your crop and expected tons. The system checks the center’s real capacity.'],['Pick a slot','See which hours are open, almost full or closed. The best slot is highlighted.'],['Get a digital token','A QR token is created and sent to your notifications.'],['Check in and wait','Staff verify the token. Track your queue position and wait time live.'],['Weigh and inspect','Gross and empty weights give the net crop. An inspector records grade and moisture.'],['Unload and get paid','Payment is recorded against your receipt. History stays in your account.']] as $i => [$t,$d])
      <li class="card p-5 flex gap-4"><span class="font-display text-3xl font-extrabold text-wheat-dark leading-none">{{ $i+1 }}</span><div><h3 class="font-bold text-lg text-field">{{ $t }}</h3><p class="text-stone-600 mt-1">{{ $d }}</p></div></li>
    @endforeach
  </ol>
</section>

{{-- Crops --}}
<section class="max-w-7xl mx-auto px-4 mt-20">
  <h2 class="font-display text-4xl font-extrabold text-field">Crops we receive</h2>
  <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach($crops as $c)
      <article class="rounded-2xl overflow-hidden relative h-56 bg-field">
        <img src="{{ $c->image }}" alt="{{ $c->name }} crop" loading="lazy" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()">
        <div class="absolute inset-0 bg-gradient-to-t from-field-dark/90 via-transparent"></div>
        <div class="absolute bottom-3 left-4 text-white"><h3 class="font-display text-2xl font-extrabold">{{ $c->name }}</h3><p class="text-sm text-mist">Rs {{ number_format($c->price_per_ton) }} per ton</p></div>
      </article>
    @endforeach
  </div>
</section>

{{-- Roles --}}
<section class="max-w-7xl mx-auto px-4 mt-20">
  <h2 class="font-display text-4xl font-extrabold text-field">One system, four jobs</h2>
  <div class="mt-8 grid md:grid-cols-2 gap-5">
    @foreach([['Farmers','Book a slot, follow the queue, see the final weight and payment.','farmer,tractor',51],['Center staff','Check in vehicles, call the next farmer, record weights and unloading.','warehouse,scale',52],['Quality inspectors','Grade crops, record moisture and damage, accept or reject with remarks.','grain,inspection',53],['Management','Set capacity, manage centers and crops, and read daily analytics.','agriculture,field',54]] as [$t,$d,$k,$n])
      <article class="card overflow-hidden flex">
        <img src="https://loremflickr.com/400/400/{{ $k }}?lock={{ $n }}" alt="" loading="lazy" class="w-40 object-cover bg-mist" onerror="this.style.display='none'">
        <div class="p-5"><h3 class="font-display text-2xl font-extrabold text-field">{{ $t }}</h3><p class="text-stone-600 mt-2">{{ $d }}</p></div>
      </article>
    @endforeach
  </div>
</section>
@endsection
