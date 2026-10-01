<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'AgriQueue') · Agricultural Procurement</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: { field: {DEFAULT:'#1E4D2B',dark:'#143520',soft:'#2F6B3F'}, leaf:'#5B9A3C', wheat:{DEFAULT:'#E0A93B',dark:'#B8851F'}, paper:'#F1F5E8', mist:'#DDE7CF', soil:'#2C2218' },
  fontFamily: { display: ['"Bricolage Grotesque"','Georgia','serif'], sans: ['Figtree','system-ui','sans-serif'] }
}}}
</script>
<style>
  body{background:#F1F5E8;color:#2C2218} :focus-visible{outline:3px solid #E0A93B;outline-offset:2px}
  .field-in{width:100%;border:1.5px solid #C9D6B6;background:#fff;border-radius:.6rem;padding:.6rem .8rem;font-size:.95rem}
  .field-in:focus{border-color:#1E4D2B;outline:none;box-shadow:0 0 0 3px rgba(224,169,59,.35)}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border-radius:.6rem;padding:.6rem 1.1rem;font-weight:600;transition:.15s;cursor:pointer}
  .btn-primary{background:#1E4D2B;color:#fff}.btn-primary:hover{background:#143520}
  .btn-gold{background:#E0A93B;color:#2C2218}.btn-gold:hover{background:#cf9a2e}
  .btn-ghost{border:1.5px solid #1E4D2B;color:#1E4D2B}.btn-ghost:hover{background:#DDE7CF}
  .btn-danger{background:#fff;border:1.5px solid #B3261E;color:#B3261E}.btn-danger:hover{background:#fdecea}
  .card{background:#fff;border:1px solid #D6E1C5;border-radius:1rem}
  @media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
@stack('head')
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
@php $u = auth()->user(); @endphp
<header class="bg-field text-white sticky top-0 z-30">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-6">
    <a href="{{ route('home') }}" class="font-display text-2xl font-extrabold tracking-tight flex items-center gap-2">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#E0A93B" stroke-width="2" stroke-linecap="round"><path d="M12 22V8M12 8c0-3 2-5 4-5 0 3-1 5-4 5zM12 12c0-3-2-5-4-5 0 3 1 5 4 5zM12 16c0-3 2-5 4-5 0 3-1 5-4 5zM12 20c0-3-2-5-4-5 0 3 1 5 4 5z"/></svg>AgriQueue
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
      @auth
        @if($u->role==='farmer')
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('farmer.dashboard') }}">Dashboard</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('farmer.bookings') }}">My bookings</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('farmer.history') }}">History &amp; payments</a>
        @elseif($u->role==='staff')
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('staff.queue') }}">Today's queue</a>
          @if($u->center_id)<a class="px-3 py-2 rounded hover:bg-field-soft" target="_blank" href="{{ route('board',$u->center_id) }}">Queue screen</a>@endif
        @elseif($u->role==='inspector')
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('inspector.index') }}">Inspections</a>
        @else
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.dashboard') }}">Dashboard</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('staff.queue') }}">Queues</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.bookings') }}">Bookings</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.centers') }}">Centers</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.crops') }}">Crops</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.users') }}">Users</a>
          <a class="px-3 py-2 rounded hover:bg-field-soft" href="{{ route('admin.logs') }}">Activity</a>
        @endif
      @endauth
    </nav>
    <div class="ml-auto flex items-center gap-3 text-sm">
      @auth
        @if($u->role==='farmer')
          <a href="{{ route('farmer.notices') }}" class="relative p-2 rounded hover:bg-field-soft" aria-label="Notifications">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 8a6 6 0 1112 0c0 7 3 9 3 9H3s3-2 3-9M10 21a2 2 0 004 0"/></svg>
            @php $unread = $u->notices()->where('is_read', false)->count(); @endphp
            @if($unread)<span class="absolute -top-0.5 -right-0.5 bg-wheat text-soil text-[11px] font-bold rounded-full min-w-[18px] h-[18px] grid place-items-center">{{ $unread }}</span>@endif
          </a>
        @endif
        <span class="hidden sm:block text-right leading-tight"><span class="font-semibold">{{ $u->name }}</span><br><span class="text-mist text-xs capitalize">{{ $u->role }}{{ $u->center ? ' · '.$u->center->name : '' }}</span></span>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-gold !py-1.5">Log out</button></form>
      @else
        <a href="{{ route('login') }}" class="font-semibold hover:underline">Log in</a>
        <a href="{{ route('register') }}" class="btn btn-gold !py-1.5">Register as farmer</a>
      @endauth
    </div>
  </div>
</header>

<main class="flex-1">
  @if(session('ok'))<div class="max-w-7xl mx-auto px-4 mt-4"><div role="status" class="bg-mist border border-leaf text-field rounded-lg px-4 py-3 font-semibold">{{ session('ok') }}</div></div>@endif
  @if($errors->any() && !request()->routeIs('login','register','farmer.bookings.create'))<div class="max-w-7xl mx-auto px-4 mt-4"><div role="alert" class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>@endif
  @yield('content')
</main>

<footer class="bg-soil text-mist text-sm mt-16">
  <div class="max-w-7xl mx-auto px-4 py-6 flex flex-wrap gap-2 justify-between"><span>AgriQueue: farmers book slots, centers run the queue.</span><span>Problem-Solving Hackathon project</span></div>
</footer>
@stack('scripts')
</body>
</html>
