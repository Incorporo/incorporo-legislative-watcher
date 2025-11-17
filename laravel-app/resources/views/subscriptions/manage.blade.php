<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionează Subscriere - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen">
    <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                Gestionează Subscriere
            </h1>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            <p class="text-sm text-emerald-800">{{ session('success') }}</p>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-8 text-white">
                <h2 class="text-2xl font-bold mb-2">Setările Tale de Notificare</h2>
                <p class="text-blue-100">{{ $subscription->email }}</p>
                @if(!$subscription->active)
                <div class="mt-4 p-3 bg-amber-500/20 backdrop-blur-sm border border-amber-300 rounded-lg">
                    <p class="text-sm font-medium">⚠️ Subscriere dezactivată - Reactivează mai jos pentru a primi notificări</p>
                </div>
                @endif
            </div>

            <form action="{{ route('subscriptions.update', $subscription->unsubscribe_token) }}" method="POST" class="px-8 py-10">
                @csrf

                @if($errors->any())
                <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">Erori:</h3>
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Keywords -->
                <div class="mb-6">
                    <label for="keywords" class="block text-sm font-medium text-slate-700 mb-2">Cuvinte Cheie</label>
                    <input type="text" name="keywords" id="keywords"
                           value="{{ old('keywords', is_array($subscription->keywords) ? implode(', ', $subscription->keywords) : '') }}"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: educație, sănătate, mediu">
                </div>

                <!-- Chambers -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Camere</label>
                    <div class="grid md:grid-cols-2 gap-3">
                        @foreach($chambers as $value => $label)
                        <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="chambers[]" value="{{ $value }}"
                                   {{ in_array($value, old('chambers', $subscription->chambers ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded">
                            <span class="ml-3 text-sm text-slate-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Statuses -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Statusuri</label>
                    <div class="grid md:grid-cols-3 gap-3">
                        @foreach($statuses as $value => $label)
                        <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="statuses[]" value="{{ $value }}"
                                   {{ in_array($value, old('statuses', $subscription->statuses ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded">
                            <span class="ml-3 text-sm text-slate-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Risk Level -->
                <div class="mb-6">
                    <label for="risk_level" class="block text-sm font-medium text-slate-700 mb-2">Nivel de Risc</label>
                    <select name="risk_level" id="risk_level" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Toate nivelurile</option>
                        @foreach($riskLevels as $value => $label)
                        <option value="{{ $value }}" {{ old('risk_level', $subscription->risk_level) === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Urgent Only -->
                <div class="mb-6">
                    <label class="flex items-center p-4 bg-amber-50 border border-amber-200 rounded-lg cursor-pointer">
                        <input type="checkbox" name="urgent_only" value="1"
                               {{ old('urgent_only', $subscription->urgent_only) ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-600 border-slate-300 rounded">
                        <span class="ml-3 text-sm font-medium text-amber-900">Doar Procedură Urgentă</span>
                    </label>
                </div>

                <!-- Frequency -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Frecvență</label>
                    <div class="space-y-3">
                        @foreach($frequencies as $value => $label)
                        <label class="flex items-center p-4 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="frequency" value="{{ $value }}"
                                   {{ old('frequency', $subscription->frequency) === $value ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600">
                            <span class="ml-3 text-sm font-medium text-slate-900">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Preferred Time -->
                <div class="mb-6">
                    <label for="preferred_time" class="block text-sm font-medium text-slate-700 mb-2">Ora Preferată</label>
                    <input type="time" name="preferred_time" id="preferred_time"
                           value="{{ old('preferred_time', $subscription->preferred_time ? substr($subscription->preferred_time, 0, 5) : '09:00') }}"
                           class="px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- AI Summary -->
                <div class="mb-8">
                    <label class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg cursor-pointer">
                        <input type="checkbox" name="include_ai_summary" value="1"
                               {{ old('include_ai_summary', $subscription->include_ai_summary) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600">
                        <span class="ml-3 text-sm font-medium text-slate-900">Include Analiză AI</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <a href="{{ route('subscriptions.unsubscribe', $subscription->unsubscribe_token) }}"
                       onclick="return confirm('Sigur vrei să dezactivezi notificările?')"
                       class="text-sm text-red-600 hover:text-red-700 font-medium">
                        Dezactivează Notificările
                    </a>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        Salvează Modificările
                    </button>
                </div>
            </form>
        </div>

        @if($recentBills->isNotEmpty())
        <div class="mt-12 bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <h2 class="text-xl font-semibold text-slate-900 mb-6">Proiecte Recente Monitorizate ({{ $recentBills->count() }})</h2>
            <div class="space-y-4">
                @foreach($recentBills as $bill)
                <a href="{{ route('bills.show', $bill->id) }}" class="block p-4 border border-slate-200 rounded-lg hover:border-blue-400 hover:shadow-md transition-all">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-semibold text-slate-900 flex-1">{{ $bill->title }}</h3>
                        <span class="ml-4 px-3 py-1 rounded-full text-xs font-medium {{ $bill->chamber === 'cdep' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $bill->chamber === 'cdep' ? 'CDEP' : 'Senat' }}
                        </span>
                    </div>
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
    </main>
</body>
</html>
