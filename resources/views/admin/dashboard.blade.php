@extends('layouts.app')
@section('title','Management dashboard')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="font-display text-4xl font-extrabold text-field">Management dashboard</h1>
  <p class="text-stone-600">Live position across all centers · {{ now()->format('d F Y') }}</p>

  <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4">
    @foreach([['Farmers expected today',$stats['expected'],''],['Farmers waiting now',$stats['waiting'],''],['Completed today',$stats['completed'],''],['Total crop received',number_format($stats['tons']),' t'],['Average waiting time',$stats['avg_wait'],' min'],['Daily capacity used',$stats['usage'],'%'],['Rejected produce',$stats['rejected'],''],['Payment pending',$stats['pay_pending'],''],['Average processing',$stats['avg_process'],' min'],['Most received crop',$stats['top_crop'],'']] as [$l,$v,$s])
      <div class="card p-4"><p class="text-sm text-stone-600">{{ $l }}</p><p class="font-display text-3xl font-extrabold text-field mt-1">{{ $v }}<span class="text-lg">{{ $s }}</span></p></div>
    @endforeach
  </div>

  <h2 class="font-display text-2xl font-extrabold text-field mt-10 mb-3">Center load today</h2>
  <div class="grid md:grid-cols-3 gap-4">
    @foreach($centers as $row)
      <div class="card p-4"><div class="flex justify-between"><b>{{ $row['c']->name }}</b><span class="text-sm text-stone-600">{{ $row['queue'] }} waiting</span></div>
        <div class="h-3 bg-mist rounded-full mt-3 overflow-hidden"><div class="h-3 {{ $row['pct']>=90?'bg-red-500':($row['pct']>=70?'bg-wheat':'bg-leaf') }}" style="width:{{ $row['pct'] }}%"></div></div>
        <p class="text-sm mt-1 text-stone-600">{{ $row['booked'] }} of {{ $row['c']->daily_capacity }} tons booked ({{ $row['pct'] }}%) @if($row['pct']>=90)<b class="text-red-700">· overload risk</b>@endif</p></div>
    @endforeach
  </div>

  <h2 class="font-display text-2xl font-extrabold text-field mt-10 mb-3">Analytics</h2>
  <div class="grid lg:grid-cols-2 gap-5">
    <div class="card p-4"><h3 class="font-bold mb-2">Crop received by day (tons, last 14 days)</h3><canvas id="c1" height="150"></canvas></div>
    <div class="card p-4"><h3 class="font-bold mb-2">Peak arrival hours (bookings per slot)</h3><canvas id="c2" height="150"></canvas></div>
    <div class="card p-4"><h3 class="font-bold mb-2">Crop received by center (tons)</h3><canvas id="c3" height="150"></canvas></div>
    <div class="card p-4"><h3 class="font-bold mb-2">Quality grade distribution</h3><canvas id="c4" height="150"></canvas></div>
  </div>
  <p class="text-sm text-stone-600 mt-3">Average queue length across centers right now: <b>{{ $stats['avg_queue'] }}</b> vehicles.</p>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const C = @json($charts), g='#1E4D2B', l='#5B9A3C', w='#E0A93B';
const obj = o => ({labels:Object.keys(o), data:Object.values(o)});
new Chart(c1,{type:'line',data:{labels:C.days,datasets:[{data:C.trend,borderColor:g,backgroundColor:'rgba(91,154,60,.2)',fill:true,tension:.3}]},options:{plugins:{legend:{display:false}}}});
const pk=obj(C.peak); new Chart(c2,{type:'bar',data:{labels:pk.labels,datasets:[{data:pk.data,backgroundColor:w}]},options:{plugins:{legend:{display:false}}}});
const ce=obj(C.centers); new Chart(c3,{type:'bar',data:{labels:ce.labels,datasets:[{data:ce.data,backgroundColor:l}]},options:{indexAxis:'y',plugins:{legend:{display:false}}}});
const gr=obj(C.grades); new Chart(c4,{type:'doughnut',data:{labels:gr.labels.map(x=>'Grade '+x),datasets:[{data:gr.data,backgroundColor:[g,w,'#B3261E']}]}});
</script>
@endpush
