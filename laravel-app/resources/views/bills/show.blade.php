@extends('layouts.app')
@section('title', $bill->title)
@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <a href="{{ route('bills.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-indigo-600">
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Înapoi la listă
    </a>

    <!-- Bill Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $bill->title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-lg font-semibold text-indigo-600">{{ $bill->bill_number }}/{{ $bill->year }}</span>
                    <span class="status-badge {{ $bill->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ $bill->chamber === 'cdep' ? 'CDEP' : 'Senat' }}</span>
                    @if($bill->urgency_status)<span class="status-badge bg-orange-100 text-orange-800">⚡ Urgență</span>@endif
                    @php $riskLevel = $bill->getHighestRiskLevel(); @endphp
                    @if($riskLevel)<span class="status-badge badge-{{ $riskLevel }}">{{ ucfirst($riskLevel) }} Risk</span>@endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-600">Progres</div>
                <div class="text-3xl font-bold text-indigo-600">{{ $progressPercentage }}%</div>
            </div>
        </div>
        @if($bill->description)
        <p class="text-gray-700 mt-4">{{ $bill->description }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Cronologie</h2>
                <div class="space-y-4">
                    @forelse($bill->timeline as $event)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ $event->description }}</p>
                                <span class="text-xs text-gray-500">{{ $event->event_date->format('d.m.Y') }}</span>
                            </div>
                            @if($event->deadline)<p class="text-xs text-gray-600 mt-1">Termen: {{ $event->deadline->format('d.m.Y') }}</p>@endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Nu există evenimente înregistrate</p>
                    @endforelse
                </div>
            </div>

            <!-- Documents -->
            @if($bill->documents->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Documente</h2>
                <div class="space-y-2">
                    @foreach($bill->documents as $doc)
                    <a href="{{ $doc->url }}" target="_blank" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                            <span class="text-sm text-gray-900">{{ $doc->title }}</span>
                        </div>
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Risks -->
            @if($bill->risks->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Riscuri Identificate
                </h2>
                <div class="space-y-4">
                    @foreach($bill->risks as $risk)
                    <div class="border-l-4 border-{{ $risk->risk_level === 'critical' ? 'red' : ($risk->risk_level === 'high' ? 'orange' : 'yellow') }}-500 bg-gray-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <span class="status-badge badge-{{ $risk->risk_level }}">{{ ucfirst($risk->risk_level) }}</span>
                            <span class="text-xs text-gray-600">{{ $risk->risk_category }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $risk->description }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $risk->justification }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Informații</h3>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-600">Status</dt><dd class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $bill->status ?? 'N/A')) }}</dd></div>
                    <div><dt class="text-gray-600">Data înregistrării</dt><dd class="font-medium text-gray-900">{{ $bill->registration_date?->format('d.m.Y') ?? 'N/A' }}</dd></div>
                    @if($bill->type)<div><dt class="text-gray-600">Tip</dt><dd class="font-medium text-gray-900">{{ $bill->type }}</dd></div>@endif
                    @if($bill->first_chamber)<div><dt class="text-gray-600">Prima cameră</dt><dd class="font-medium text-gray-900">{{ $bill->first_chamber }}</dd></div>@endif
                    @if($bill->decision_chamber)<div><dt class="text-gray-600">Cameră decizională</dt><dd class="font-medium text-gray-900">{{ $bill->decision_chamber }}</dd></div>@endif
                </dl>
            </div>

            <!-- Initiators -->
            @if($bill->initiators->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Inițiatori</h3>
                <div class="space-y-2">
                    @foreach($bill->initiators as $initiator)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-xs font-medium text-gray-600">{{ substr($initiator->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $initiator->name }}</p>
                            <p class="text-xs text-gray-600">{{ ucfirst($initiator->type) }} @if($initiator->party)- {{ $initiator->party }}@endif</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
