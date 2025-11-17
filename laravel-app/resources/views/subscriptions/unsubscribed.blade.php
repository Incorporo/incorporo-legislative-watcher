<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dezabonat - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-slate-600 to-slate-700 px-8 py-12 text-center">
            <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Subscriere Dezactivată</h1>
            <p class="text-slate-200">Ne pare rău să te vedem plecând</p>
        </div>

        <div class="px-8 py-10">
            <p class="text-center text-slate-700 mb-8">
                Nu vei mai primi notificări despre proiecte legislative la adresa <strong>{{ $subscription->email }}</strong>.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-slate-900 mb-2">Vrei să revii?</h3>
                <p class="text-sm text-slate-600 mb-4">
                    Poți reactiva oricând notificările accesând linkul de gestionare din orice email anterior.
                </p>
                <form action="{{ route('subscriptions.reactivate', $subscription->unsubscribe_token) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Reactivează Notificările
                    </button>
                </form>
            </div>

            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="text-sm text-slate-600 hover:text-blue-600">
                    Înapoi la Dashboard →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
