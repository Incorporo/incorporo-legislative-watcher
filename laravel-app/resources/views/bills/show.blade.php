@extends('layouts.app')

@section('title', $bill->title)

@section('content')
<div class="space-y-6">
    <!-- Back Button & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('bills.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Înapoi la listă
        </a>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Tipărește
            </button>
            <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-sm text-sm font-bold text-white hover:shadow-lg transition-all">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Urmărește
            </button>
        </div>
    </div>

    <!-- Bill Header -->
    <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="text-2xl font-bold text-blue-600">{{ $bill->bill_number }}/{{ $bill->year }}</span>
                    <span class="status-badge {{ $bill->chamber === 'cdep' ? 'bg-blue-100 text-blue-800 ring-blue-600/20' : 'bg-purple-100 text-purple-800 ring-purple-600/20' }}">
                        {{ $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
                    </span>
                    @if($bill->urgency_status)
                    <span class="status-badge bg-amber-100 text-amber-800 ring-amber-600/20">
                        <svg class="h-3.5 w-3.5 mr-1 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        Procedură Urgentă
                    </span>
                    @endif
                    @php $riskLevel = $bill->getHighestRiskLevel(); @endphp
                    @if($riskLevel)
                    <span class="status-badge badge-{{ $riskLevel }}">
                        <svg class="h-3.5 w-3.5 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Risc {{ ucfirst($riskLevel) }}
                    </span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-slate-900 mb-3 leading-tight">{{ $bill->title }}</h1>
                @if($bill->description)
                <p class="text-lg text-slate-700 leading-relaxed">{{ $bill->description }}</p>
                @endif
            </div>

            <!-- Progress Circle -->
            <div class="flex-shrink-0 ml-8">
                <div class="relative w-32 h-32">
                    <svg class="transform -rotate-90 w-32 h-32">
                        <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-200"/>
                        <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="transparent"
                                stroke-dasharray="351.86"
                                stroke-dashoffset="{{ 351.86 * (1 - $progressPercentage/100) }}"
                                class="text-blue-600 transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span class="text-3xl font-bold text-blue-600">{{ $progressPercentage }}%</span>
                        <span class="text-xs text-slate-600 font-semibold">Progres</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-slate-200">
            <div class="text-center">
                <div class="text-sm text-slate-600 font-medium">Status</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ ucfirst(str_replace('_', ' ', $bill->status ?? 'N/A')) }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-slate-600 font-medium">Data Înregistrării</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ $bill->registration_date?->format('d M Y') ?? 'N/A' }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-slate-600 font-medium">Inițiatori</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ $bill->initiators->count() }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-slate-600 font-medium">Documente</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ $bill->documents->count() }}</div>
            </div>
        </div>
    </div>

    @php
        $latestAnalysis = $bill->analysis->where('analysis_type', 'ai_assessment')->first();
        $analysisData = $latestAnalysis?->analysis_result;
    @endphp

    @if($latestAnalysis && $analysisData)
    <!-- AI Assessment Section - THE STAR OF THE SHOW -->
    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl shadow-md border-2 border-blue-200 p-8">
        <div class="flex items-center mb-6">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center mr-4 shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-slate-900">Analiză Inteligentă AI</h2>
                <p class="text-sm text-slate-600 font-medium mt-1">
                    Analizat cu {{ $latestAnalysis->model_version }} •
                    Încredere: {{ number_format(($analysisData['confidence_score'] ?? 0) * 100) }}% •
                    {{ $latestAnalysis->analyzed_at->diffForHumans() }}
                </p>
            </div>
            <span class="px-4 py-2 rounded-lg bg-white border border-blue-300 text-sm font-bold text-blue-700 shadow-sm">
                {{ ucfirst($analysisData['overall_assessment'] ?? 'neutral') }}
            </span>
        </div>

        <!-- Summary -->
        @if(isset($analysisData['summary']))
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-blue-100">
            <h3 class="text-lg font-bold text-slate-900 mb-3 flex items-center">
                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Rezumat
            </h3>
            <p class="text-base text-slate-700 leading-relaxed">{{ $analysisData['summary'] }}</p>
        </div>
        @endif

        <!-- Impact Assessment -->
        @if(isset($analysisData['impact_assessment']))
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-blue-100">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Evaluare Impact</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <div class="text-xs text-slate-600 font-semibold uppercase tracking-wider mb-2">Domeniu</div>
                    <div class="text-base font-bold text-slate-900">{{ $analysisData['impact_assessment']['scope'] ?? 'N/A' }}</div>
                </div>
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <div class="text-xs text-slate-600 font-semibold uppercase tracking-wider mb-2">Magnitudine</div>
                    <div class="text-base font-bold text-slate-900">
                        @php
                            $magnitude = $analysisData['impact_assessment']['magnitude'] ?? 'low';
                            $color = $magnitude === 'high' ? 'text-red-600' : ($magnitude === 'medium' ? 'text-amber-600' : 'text-green-600');
                        @endphp
                        <span class="{{ $color }}">{{ ucfirst($magnitude) }}</span>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <div class="text-xs text-slate-600 font-semibold uppercase tracking-wider mb-2">Termen</div>
                    <div class="text-base font-bold text-slate-900">{{ ucfirst(str_replace('-', ' ', $analysisData['impact_assessment']['timeframe'] ?? 'medium-term')) }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pros and Cons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Pros -->
            @if(isset($analysisData['pros']) && count($analysisData['pros']) > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-100">
                <h3 class="text-lg font-bold text-emerald-700 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aspecte Pozitive
                </h3>
                <div class="space-y-4">
                    @foreach($analysisData['pros'] as $pro)
                    <div class="border-l-4 border-emerald-500 bg-emerald-50 p-4 rounded-r-lg">
                        <div class="font-bold text-slate-900 mb-1">{{ $pro['point'] }}</div>
                        <p class="text-sm text-slate-700 mb-2">{{ $pro['explanation'] }}</p>
                        @if(isset($pro['stakeholders']) && count($pro['stakeholders']) > 0)
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($pro['stakeholders'] as $stakeholder)
                            <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full font-semibold">{{ $stakeholder }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Cons -->
            @if(isset($analysisData['cons']) && count($analysisData['cons']) > 0)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-red-100">
                <h3 class="text-lg font-bold text-red-700 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aspecte Negative
                </h3>
                <div class="space-y-4">
                    @foreach($analysisData['cons'] as $con)
                    <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded-r-lg">
                        <div class="font-bold text-slate-900 mb-1">{{ $con['point'] }}</div>
                        <p class="text-sm text-slate-700 mb-2">{{ $con['explanation'] }}</p>
                        @if(isset($con['stakeholders']) && count($con['stakeholders']) > 0)
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($con['stakeholders'] as $stakeholder)
                            <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full font-semibold">{{ $stakeholder }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Risks -->
        @if(isset($analysisData['risks']) && count($analysisData['risks']) > 0)
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-amber-100">
            <h3 class="text-lg font-bold text-amber-700 mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Riscuri Potențiale
            </h3>
            <div class="space-y-3">
                @foreach($analysisData['risks'] as $risk)
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <div class="flex items-start justify-between mb-2">
                        <span class="font-bold text-slate-900">{{ $risk['risk'] }}</span>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-1 rounded-full font-bold
                                {{ $risk['severity'] === 'critical' ? 'bg-red-100 text-red-800' : ($risk['severity'] === 'high' ? 'bg-orange-100 text-orange-800' : ($risk['severity'] === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ ucfirst($risk['severity']) }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full font-bold bg-slate-200 text-slate-700">
                                Prob: {{ ucfirst($risk['probability']) }}
                            </span>
                        </div>
                    </div>
                    @if(isset($risk['mitigation']))
                    <p class="text-sm text-slate-700"><span class="font-semibold">Mitigare:</span> {{ $risk['mitigation'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Economic Impact -->
        @if(isset($analysisData['economic_impact']))
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-blue-100">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Impact Economic</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if(isset($analysisData['economic_impact']['budget_required']))
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-xs text-blue-700 font-semibold uppercase tracking-wider mb-1">Buget Necesar</div>
                    <div class="text-sm font-bold text-slate-900">{{ $analysisData['economic_impact']['budget_required'] }}</div>
                </div>
                @endif
                @if(isset($analysisData['economic_impact']['employment_impact']))
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-xs text-blue-700 font-semibold uppercase tracking-wider mb-1">Impact Ocupare</div>
                    <div class="text-sm font-bold text-slate-900">{{ ucfirst($analysisData['economic_impact']['employment_impact']) }}</div>
                </div>
                @endif
                @if(isset($analysisData['economic_impact']['affected_sectors']))
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-xs text-blue-700 font-semibold uppercase tracking-wider mb-1">Sectoare Afectate</div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($analysisData['economic_impact']['affected_sectors'] as $sector)
                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">{{ $sector }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recommendations -->
        @if(isset($analysisData['recommendations']) && count($analysisData['recommendations']) > 0)
        <div class="bg-white rounded-xl p-6 shadow-sm border border-blue-100">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Recomandări
            </h3>
            <ul class="space-y-2">
                @foreach($analysisData['recommendations'] as $recommendation)
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm text-slate-700">{{ $recommendation }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - Left Side (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cronologie Evenimente
                </h2>
                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 via-cyan-500 to-transparent"></div>

                    <div class="space-y-6">
                        @forelse($bill->timeline->take(10) as $index => $event)
                        <div class="relative flex items-start pl-14">
                            <!-- Timeline dot -->
                            <div class="absolute left-0 flex items-center justify-center">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center shadow-lg ring-4 ring-white">
                                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Event content -->
                            <div class="flex-1 bg-slate-50 rounded-lg p-4 border border-slate-200 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <p class="text-base font-bold text-slate-900">{{ $event->description }}</p>
                                    <span class="text-sm text-slate-500 font-semibold whitespace-nowrap ml-4">
                                        {{ $event->event_date->format('d.m.Y') }}
                                    </span>
                                </div>
                                @if($event->deadline)
                                <div class="flex items-center text-sm text-amber-700 bg-amber-50 px-3 py-1 rounded-md inline-flex">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Termen: {{ $event->deadline->format('d.m.Y') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500 font-medium">Nu există evenimente înregistrate</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Documents -->
            @if($bill->documents->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Documente Atașate
                </h2>
                <div class="space-y-2">
                    @foreach($bill->documents as $doc)
                    <a href="{{ $doc->url }}" target="_blank" class="group flex items-center justify-between p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                        <div class="flex items-center flex-1">
                            <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                <svg class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 group-hover:text-blue-700">{{ $doc->title }}</div>
                                @if($doc->document_type)
                                <div class="text-xs text-slate-500">{{ ucfirst($doc->document_type) }}</div>
                                @endif
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Committee Assignments -->
            @if($bill->committeeAssignments->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Comisii Parlamentare
                </h2>
                <div class="space-y-3">
                    @foreach($bill->committeeAssignments as $assignment)
                    <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-bold text-slate-900">{{ $assignment->committee->name }}</div>
                                @if($assignment->status)
                                <div class="text-sm text-slate-600 mt-1">Status: {{ ucfirst($assignment->status) }}</div>
                                @endif
                            </div>
                            @if($assignment->review_deadline)
                            <span class="text-xs px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-bold">
                                Termen: {{ $assignment->review_deadline->format('d.m.Y') }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Right Side (1/3) -->
        <div class="space-y-6">
            <!-- Key Info -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informații Cheie
                </h3>
                <dl class="space-y-4 text-sm">
                    @if($bill->type)
                    <div class="flex justify-between">
                        <dt class="text-slate-600 font-medium">Tip Proiect</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $bill->type }}</dd>
                    </div>
                    @endif
                    @if($bill->first_chamber)
                    <div class="flex justify-between">
                        <dt class="text-slate-600 font-medium">Prima Cameră</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $bill->first_chamber }}</dd>
                    </div>
                    @endif
                    @if($bill->decision_chamber)
                    <div class="flex justify-between">
                        <dt class="text-slate-600 font-medium">Cameră Decizională</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $bill->decision_chamber }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-600 font-medium">Ultima Actualizare</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $bill->last_scraped_at?->format('d.m.Y H:i') ?? 'N/A' }}</dd>
                    </div>
                    @if($bill->url)
                    <div class="pt-4 border-t border-slate-200">
                        <a href="{{ $bill->url }}" target="_blank" class="inline-flex items-center text-sm font-bold text-blue-600 hover:text-blue-700">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Vezi pe site-ul oficial
                        </a>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Initiators -->
            @if($bill->initiators->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Inițiatori ({{ $bill->initiators->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($bill->initiators->take(10) as $initiator)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-sm">
                            <span class="text-sm font-bold text-white">{{ substr($initiator->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-bold text-slate-900">{{ $initiator->name }}</p>
                            <p class="text-xs text-slate-600">
                                {{ ucfirst($initiator->type) }}
                                @if($initiator->party)
                                • {{ $initiator->party }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Similar Bills -->
            @if($similarBills->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Proiecte Similare
                </h3>
                <div class="space-y-2">
                    @foreach($similarBills as $similarBill)
                    <a href="{{ route('bills.show', $similarBill->id) }}" class="block p-3 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                        <div class="text-sm font-bold text-blue-600 hover:text-blue-700">{{ $similarBill->bill_number }}/{{ $similarBill->year }}</div>
                        <div class="text-xs text-slate-700 mt-1 line-clamp-2">{{ $similarBill->title }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
