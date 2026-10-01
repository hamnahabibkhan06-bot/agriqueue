@extends('layouts.app')
@section('title','Activity log')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Activity log</h1>
  <div class="card mt-5 overflow-x-auto"><table class="w-full text-sm"><thead class="bg-mist text-left"><tr><th class="p-3">When</th><th>Who</th><th>Action</th><th>Details</th><th>IP</th></tr></thead><tbody>
  @foreach($logs as $l)<tr class="border-t border-mist"><td class="p-3 whitespace-nowrap">{{ $l->created_at->format('d M H:i:s') }}</td><td>{{ $l->user?->name ?? 'System' }}</td><td class="font-semibold">{{ $l->action }}</td><td>{{ $l->details }}</td><td>{{ $l->ip }}</td></tr>@endforeach
  </tbody></table></div>
  <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
