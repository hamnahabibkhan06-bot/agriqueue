<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Queue screen · {{ $center->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,800&family=Figtree:wght@500;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#143520;font-family:Figtree,sans-serif" class="text-white min-h-screen p-8">
<div class="flex justify-between items-end mb-8">
  <div><h1 style="font-family:'Bricolage Grotesque'" class="text-5xl font-extrabold">{{ $center->name }}</h1><p class="text-[#DDE7CF] text-xl">Live queue · tokens only</p></div>
  <p class="text-2xl text-[#E0A93B] font-bold" id="clock">--:--:--</p>
</div>
<div class="grid grid-cols-2 xl:grid-cols-4 gap-6" id="cols"></div>
<script>
const titles = {weighing:['Now weighing','#E0A93B'], waiting:['Waiting in line','#5B9A3C'], quality:['Quality check','#9aa8ff'], unloading:['Unloading','#f4a261']};
async function tick(){
  try{
    const r = await fetch('{{ route('board.data',$center) }}'); const d = await r.json();
    document.getElementById('cols').innerHTML = Object.keys(titles).map(k =>
      `<section class="rounded-2xl p-6" style="background:rgba(255,255,255,.07);border-top:6px solid ${titles[k][1]}">
        <h2 class="text-2xl font-bold mb-4">${titles[k][0]}</h2>
        ${(d[k]||[]).length ? d[k].map(t=>`<p class="text-3xl font-extrabold py-1 tracking-wide" style="font-family:'Bricolage Grotesque'">${t}</p>`).join('') : '<p class="text-[#DDE7CF]">Nobody right now</p>'}
      </section>`).join('');
    document.getElementById('clock').textContent = 'Updated ' + d.updated;
  }catch(e){ document.getElementById('clock').textContent = 'Reconnecting…'; }
}
tick(); setInterval(tick, 5000);
</script>
</body></html>
