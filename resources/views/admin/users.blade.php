@extends('layouts.app')
@section('title','Users')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Users and access</h1>
  <form method="GET" class="mt-5 flex flex-wrap gap-3"><input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="field-in !w-64"><select name="role" class="field-in !w-44"><option value="">All roles</option>@foreach(['farmer','staff','inspector','admin'] as $r)<option @selected(request('role')==$r)>{{ $r }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form>
  <div class="card mt-4 overflow-x-auto"><table class="w-full text-sm"><thead class="bg-mist text-left"><tr><th class="p-3">Name</th><th>Email</th><th>Role</th><th>Center</th><th>Status</th><th>Change</th></tr></thead><tbody>
  @foreach($users as $u)
    <tr class="border-t border-mist"><td class="p-3 font-semibold">{{ $u->name }}</td><td>{{ $u->email }}</td><td class="capitalize">{{ $u->role }}</td><td>{{ $u->center?->name ?? '—' }}</td><td>@include('partials.badge',['s'=>$u->registration_status==='verified'?'accepted':'rejected'])</td>
      <td>@if($u->id!==auth()->id())<form method="POST" action="{{ route('admin.users.update',$u) }}" class="flex gap-2 py-2">@csrf @method('PUT')
        <select name="registration_status" class="field-in !py-1 !w-32"><option value="verified" @selected($u->registration_status==='verified')>Verified</option><option value="suspended" @selected($u->registration_status==='suspended')>Suspended</option></select>
        <select name="center_id" class="field-in !py-1 !w-44"><option value="">No center</option>@foreach($centers as $c)<option value="{{ $c->id }}" @selected($u->center_id==$c->id)>{{ $c->name }}</option>@endforeach</select><button class="btn btn-ghost !py-1">Save</button></form>@else<span class="text-stone-500">You</span>@endif</td></tr>
  @endforeach</tbody></table></div>
  <div class="mt-4">{{ $users->links() }}</div>

  <h2 class="font-display text-2xl font-extrabold text-field mt-8 mb-3">Create a staff, inspector or admin account</h2>
  <form method="POST" action="{{ route('admin.users.store') }}" class="card p-5 grid md:grid-cols-5 gap-3 items-end">@csrf
    <label class="text-sm font-semibold">Name<input name="name" class="field-in mt-1" required></label>
    <label class="text-sm font-semibold">Email<input name="email" type="email" class="field-in mt-1" required>@error('email')<span class="text-red-700">{{ $message }}</span>@enderror</label>
    <label class="text-sm font-semibold">Role<select name="role" class="field-in mt-1"><option>staff</option><option>inspector</option><option>admin</option></select></label>
    <label class="text-sm font-semibold">Center<select name="center_id" class="field-in mt-1"><option value="">None</option>@foreach($centers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
    <label class="text-sm font-semibold">Password<input name="password" type="password" minlength="8" class="field-in mt-1" required></label>
    <button class="btn btn-gold md:col-span-5 md:w-fit">Create account</button>
  </form>
</div>
@endsection
