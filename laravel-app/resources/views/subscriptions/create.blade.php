<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonare Alerte Legislative - Legislative Watcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'IBM Plex Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                        Legislative Watcher
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">Monitorizare Inteligentă Legislativă</p>
                </div>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition-colors">
                    ← Înapoi la Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <!-- Hero Section -->
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-10 text-white">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold mb-3">Fii Mereu Informat</h2>
                    <p class="text-blue-100 text-lg leading-relaxed">
                        Primește notificări instant sau rezumate periodice despre proiectele legislative care te interesează.
                        Personalizează-ți alertele după cuvinte cheie, camere parlamentare, nivel de risc și multe altele.
                    </p>
                </div>
            </div>

            <!-- Form Section -->
            <form action="{{ route('subscriptions.store') }}" method="POST" class="px-8 py-10">
                @csrf

                @if($errors->any())
                <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800 mb-1">Erori la validare:</h3>
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800">{{ session('error') }}</p>
                </div>
                @endif

                <!-- Contact Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-3">1</span>
                        Informații de Contact
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6 ml-11">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="email@exemplu.ro">
                            <p class="mt-1.5 text-xs text-slate-500">Vei primi un email de confirmare</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                Nume (opțional)
                            </label>
                            <input type="text" name="name" id="name"
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Numele tău">
                        </div>
                    </div>
                </div>

                <!-- Subscription Filters -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-3">2</span>
                        Ce Proiecte Vrei să Monitorizezi?
                    </h3>

                    <div class="ml-11 space-y-6">
                        <!-- Keywords -->
                        <div>
                            <label for="keywords" class="block text-sm font-medium text-slate-700 mb-2">
                                Cuvinte Cheie
                            </label>
                            <input type="text" name="keywords" id="keywords"
                                   value="{{ old('keywords') }}"
                                   class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Ex: educație, sănătate, mediu (separate prin virgulă)">
                            <p class="mt-1.5 text-xs text-slate-500">Lasă gol pentru a primi notificări despre toate proiectele</p>
                        </div>

                        <!-- Chambers -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Camere Parlamentare</label>
                            <div class="grid md:grid-cols-2 gap-3">
                                @foreach($chambers as $value => $label)
                                <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                    <input type="checkbox" name="chambers[]" value="{{ $value }}"
                                           {{ in_array($value, old('chambers', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Statuses -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Statusuri</label>
                            <div class="grid md:grid-cols-3 gap-3">
                                @foreach($statuses as $value => $label)
                                <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                    <input type="checkbox" name="statuses[]" value="{{ $value }}"
                                           {{ in_array($value, old('statuses', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Risk Level -->
                        <div>
                            <label for="risk_level" class="block text-sm font-medium text-slate-700 mb-2">
                                Nivel de Risc
                            </label>
                            <select name="risk_level" id="risk_level"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Toate nivelurile</option>
                                @foreach($riskLevels as $value => $label)
                                <option value="{{ $value }}" {{ old('risk_level') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Urgent Only -->
                        <div>
                            <label class="flex items-center p-4 bg-amber-50 border border-amber-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="urgent_only" value="1"
                                       {{ old('urgent_only') ? 'checked' : '' }}
                                       class="w-4 h-4 text-amber-600 border-slate-300 rounded focus:ring-amber-500">
                                <span class="ml-3 text-sm font-medium text-amber-900">Doar Proiecte cu Procedură Urgentă</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-3">3</span>
                        Cum Vrei să Fii Notificat?
                    </h3>

                    <div class="ml-11 space-y-6">
                        <!-- Frequency -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Frecvență Notificări <span class="text-red-500">*</span></label>
                            <div class="space-y-3">
                                @foreach($frequencies as $value => $label)
                                <label class="flex items-start p-4 border border-slate-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors {{ old('frequency', 'daily') === $value ? 'bg-blue-50 border-blue-500' : '' }}">
                                    <input type="radio" name="frequency" value="{{ $value }}"
                                           {{ old('frequency', 'daily') === $value ? 'checked' : '' }}
                                           required
                                           class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500 mt-0.5">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-slate-900">{{ $label }}</span>
                                        @if($value === 'instant')
                                        <span class="text-xs text-slate-500">Primești email imediat când apare un proiect care se potrivește</span>
                                        @elseif($value === 'daily')
                                        <span class="text-xs text-slate-500">Primești un rezumat zilnic dimineața cu toate proiectele</span>
                                        @else
                                        <span class="text-xs text-slate-500">Primești un rezumat în fiecare luni dimineața</span>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Preferred Time -->
                        <div>
                            <label for="preferred_time" class="block text-sm font-medium text-slate-700 mb-2">
                                Ora Preferată (pentru rezumate)
                            </label>
                            <input type="time" name="preferred_time" id="preferred_time"
                                   value="{{ old('preferred_time', '09:00') }}"
                                   class="px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <!-- AI Summary -->
                        <div>
                            <label class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="include_ai_summary" value="1"
                                       {{ old('include_ai_summary', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-slate-900">Include Analiză AI în Notificări</span>
                                    <span class="text-xs text-slate-600">Primești rezumate și evaluări generate de inteligență artificială</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <p class="text-xs text-slate-500 max-w-md">
                        Vei primi un email de confirmare. Poți modifica sau anula oricând subscrierea folosind link-urile din email.
                    </p>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-cyan-700 transition-all transform hover:-translate-y-0.5">
                        Abonează-te Acum
                    </button>
                </div>
            </form>
        </div>

        <!-- Features -->
        <div class="mt-12 grid md:grid-cols-3 gap-6">
            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-6 border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Alerte Instant</h3>
                <p class="text-sm text-slate-600">Fii primul care află despre proiectele legislative relevante pentru tine</p>
            </div>

            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-6 border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Analiză AI</h3>
                <p class="text-sm text-slate-600">Primești rezumate inteligente și evaluări de risc pentru fiecare proiect</p>
            </div>

            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-6 border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Control Total</h3>
                <p class="text-sm text-slate-600">Personalizează filtrele, frecvența și tipul de notificări oricând dorești</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-16 py-8 border-t border-slate-200 bg-white/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-slate-600">
                © 2025 Legislative Watcher • Monitorizare Inteligentă Legislativă
            </p>
        </div>
    </footer>
</body>
</html>
