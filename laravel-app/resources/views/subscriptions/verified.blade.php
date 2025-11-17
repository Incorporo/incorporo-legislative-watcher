<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriere Activată - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-12 text-center">
            <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Subscriere Activată!</h1>
            <p class="text-emerald-100 text-lg">Vei primi notificări conform preferințelor tale</p>
        </div>

        <div class="px-8 py-10">
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">Setările Tale</h2>
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-6 border border-blue-200">
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-600 mb-1">Email</p>
                            <p class="font-semibold text-slate-900">{{ $subscription->email }}</p>
                        </div>
                        <div>
                            <p class="text-slate-600 mb-1">Frecvență</p>
                            <p class="font-semibold text-slate-900">{{ $subscription->getFrequencyLabel() }}</p>
                        </div>
                        @if($subscription->getSummary() !== 'Toate proiectele')
                        <div class="md:col-span-2">
                            <p class="text-slate-600 mb-1">Filtre Active</p>
                            <p class="font-semibold text-slate-900">{{ $subscription->getSummary() }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-6 border-t border-blue-200 flex justify-center">
                        <a href="{{ $subscription->getManageUrl() }}" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Gestionează Preferințele
                        </a>
                    </div>
                </div>
            </div>

            @if($sampleBills->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">Exemple de Proiecte Monitorizate</h2>
                <div class="space-y-4">
                    @foreach($sampleBills->take(3) as $bill)
                    <a href="{{ route('bills.show', $bill->id) }}" class="block p-4 border border-slate-200 rounded-lg hover:border-blue-400 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-slate-900 flex-1">{{ $bill->title }}</h3>
                            <span class="ml-4 px-3 py-1 rounded-full text-xs font-medium {{ $bill->chamber === 'cdep' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $bill->chamber === 'cdep' ? 'CDEP' : 'Senat' }}
                            </span>
                        </div>
                        @if($bill->description)
                        <p class="text-sm text-slate-600 mb-2 line-clamp-2">{{ $bill->description }}</p>
                        @endif
                        <div class="flex items-center text-xs text-slate-500">
                            <span>{{ $bill->bill_number }}/{{ $bill->year }}</span>
                            @if($bill->urgency_status)
                            <span class="ml-3 px-2 py-0.5 bg-amber-100 text-amber-700 rounded">Urgent</span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                <h3 class="font-semibold text-slate-900 mb-3">Ce urmează?</h3>
                <ul class="space-y-2 text-sm text-slate-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Vei primi email-uri când apar proiecte legislative care corespund criteriilor tale</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Poți modifica oricând preferințele folosind linkul din fiecare email</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-emerald-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Poți anula subscrierea oricând cu un singur click</span>
                    </li>
                </ul>
            </div>

            <div class="mt-8 text-center space-x-4">
                <a href="{{ route('dashboard') }}" class="inline-block px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-medium rounded-lg hover:shadow-lg transition-all">
                    Explorează Proiecte Legislative
                </a>
                <a href="{{ $subscription->getManageUrl() }}" class="inline-block px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition-colors">
                    Gestionează Setări
                </a>
            </div>
        </div>
    </div>
</body>
</html>
