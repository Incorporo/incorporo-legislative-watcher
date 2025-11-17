<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmă Email - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-12 text-center">
            <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Verifică-ți Email-ul</h1>
            <p class="text-blue-100 text-lg">Am trimis un email de confirmare la</p>
            <p class="text-white font-semibold text-xl mt-2">{{ $subscription->email }}</p>
        </div>

        <div class="px-8 py-10">
            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-4 flex-shrink-0">1</div>
                    <div>
                        <h3 class="font-semibold text-slate-900 mb-1">Verifică inbox-ul</h3>
                        <p class="text-sm text-slate-600">Caută emailul de la Legislative Watcher. Poate dura câteva minute.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-4 flex-shrink-0">2</div>
                    <div>
                        <h3 class="font-semibold text-slate-900 mb-1">Click pe linkul de confirmare</h3>
                        <p class="text-sm text-slate-600">Accesează linkul din email pentru a activa subscrierea.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-4 flex-shrink-0">3</div>
                    <div>
                        <h3 class="font-semibold text-slate-900 mb-1">Începe să primești alerte</h3>
                        <p class="text-sm text-slate-600">După confirmare, vei primi notificări conform preferințelor tale.</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 p-6 bg-amber-50 border border-amber-200 rounded-xl">
                <h3 class="font-semibold text-amber-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                    </svg>
                    Nu ai primit emailul?
                </h3>
                <ul class="text-sm text-amber-800 space-y-1 ml-7">
                    <li>• Verifică folderul de spam/junk</li>
                    <li>• Adaugă noreply@legislative-watcher.ro la contacte</li>
                    <li>• Așteaptă câteva minute și verifică din nou</li>
                </ul>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-200">
                <h3 class="font-semibold text-slate-900 mb-3">Setările Tale</h3>
                <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-700">
                    <p><strong>Frecvență:</strong> {{ $subscription->getFrequencyLabel() }}</p>
                    @if($subscription->getSummary() !== 'Toate proiectele')
                    <p class="mt-2"><strong>Filtre:</strong> {{ $subscription->getSummary() }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    ← Înapoi la Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
