<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deja Verificat - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-12 text-center">
            <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Deja Verificat</h1>
            <p class="text-blue-100">Acest email a fost deja confirmat</p>
        </div>

        <div class="px-8 py-10">
            <p class="text-center text-slate-700 mb-8">
                Subscrierea pentru <strong>{{ $subscription->email }}</strong> este deja activă și funcțională.
            </p>

            @if($subscription->active)
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-emerald-900 mb-2">✓ Subscrierea ta este activă</h3>
                <p class="text-sm text-emerald-700">
                    Primești notificări {{ $subscription->getFrequencyLabel() }} conform preferințelor tale.
                </p>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-amber-900 mb-2">Subscrierea este dezactivată</h3>
                <p class="text-sm text-amber-700 mb-4">
                    Accesează linkul de gestionare pentru a reactiva notificările.
                </p>
            </div>
            @endif

            <div class="text-center space-y-3">
                <a href="{{ $subscription->getManageUrl() }}"
                   class="block px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-medium rounded-lg hover:shadow-lg transition-all">
                    Gestionează Preferințele
                </a>
                <a href="{{ route('dashboard') }}"
                   class="block px-6 py-3 border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition-colors">
                    Înapoi la Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
