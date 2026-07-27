<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','National Auto Auction')</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Hanken+Grotesk:wght@300..800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
  :root{--void:#070A0F;--floor:#0C1119;--card:#121924;--card2:#18202D;--edge:#232E3D;
        --bone:#E9EDF2;--ash:#8896A8;--dim:#5A6878;--gold:#C9A227;--gold-lt:#E8C34A;
        --sig:#3B82F6;--live:#10B981;--heat:#EF4444;--note:#F59E0B;--vio:#8B5CF6}
  body{background:var(--void);color:var(--bone);font-family:'Hanken Grotesk',system-ui,sans-serif}
  .dsp{font-family:'Bricolage Grotesque',sans-serif;letter-spacing:-.02em}
  .mn{font-family:'JetBrains Mono',monospace;font-variant-numeric:tabular-nums}
  .card{background:var(--card);border:1px solid var(--edge)}
  .hair{border-color:var(--edge)}
  .gold{color:var(--gold-lt)}
  .btn-g{background:var(--gold);color:#0A0A0A;font-weight:700}
  .btn-g:hover{background:var(--gold-lt)}
  .btn-ok{background:var(--live);color:#04231A;font-weight:700}
  .btn-no{background:var(--heat);color:#fff;font-weight:700}
  .btn-o{border:1px solid var(--edge);color:var(--bone)}
  .btn-o:hover{border-color:var(--gold);color:var(--gold-lt)}
  .btn-s{background:var(--sig);color:#fff;font-weight:700}
  input,select,textarea{background:var(--void);border:1px solid var(--edge);color:var(--bone)}
  input:focus,select:focus,textarea:focus{outline:none;border-color:var(--gold)}
  .thumb{background:linear-gradient(140deg,#1E2733,#0F151D)}
  .lv{animation:lv 1.5s ease-in-out infinite}@keyframes lv{0%,100%{opacity:1}50%{opacity:.35}}
  table tbody tr:hover{background:rgba(255,255,255,.025)}
  [x-cloak]{display:none!important}
</style>
@stack('head')
</head>
<body class="min-h-screen">

@include('partials.flash')
@yield('body')

<script>
  window.CSRF = document.querySelector('meta[name=csrf-token]').content;
</script>
@stack('scripts')
</body>
</html>
