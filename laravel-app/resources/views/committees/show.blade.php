@extends('layouts.app')

@section('title', $committee->name_short ?? $committee->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('committees.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Înapoi la comisii
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    {{ $committee->name }}
                </h1>
                @if($committee->name_short && $committee->name !== $committee->name_short)
                <p class="text-lg text-gray-600 mb-3">{{ $committee->name_short }}</p>
                @endif

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $committee->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : ($committee->chamber === 'senate' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                        @if($committee->chamber === 'cdep')
                            Camera Deputaților
                        @elseif($committee->chamber === 'senate')
                            Senat
                        @else
                            Comisie Comună
                        @endif
                    </span>
                    @if($committee->active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Activă
                    </span>
                    @endif
                </div>

                @if($committee->description)
                <p class="mt-4 text-gray-700">{{ $committee->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            title="Total Membri"
            :value="$stats['total_members']"
            color="indigo"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
        />
        <x-stat-card
            title="Proiecte alocate"
            :value="$stats['bills_assigned']"
            color="blue"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'"
        />
        <x-stat-card
            title="În lucru"
            :value="$stats['bills_pending']"
            color="orange"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
        />
        <x-stat-card
            title="Finalizate"
            :value="$stats['bills_completed']"
            color="green"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Current Assignments -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Proiecte în lucru</h2>
                    <span class="text-sm text-gray-600">{{ $currentAssignments->count() }} proiecte</span>
                </div>

                @forelse($currentAssignments as $assignment)
                <div class="border-l-4 border-orange-500 pl-4 mb-4 last:mb-0 pb-4 last:pb-0 border-b last:border-b-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <a href="{{ route('bills.show', $assignment->bill->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                {{ $assignment->bill->bill_number }}/{{ $assignment->bill->year }}
                            </a>
                            <h3 class="text-sm text-gray-900 mt-1">{{ Str::limit($assignment->bill->title, 120) }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $assignment->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $assignment->status === 'assigned' ? 'Alocat' : 'În analiză' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Alocat la {{ $assignment->assigned_date?->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Niciun proiect în lucru momentan</p>
                </div>
                @endforelse
            </div>

            <!-- Completed Assignments -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Proiecte finalizate</h2>
                    <span class="text-sm text-gray-600">{{ $completedAssignments->count() }} proiecte</span>
                </div>

                @forelse($completedAssignments as $assignment)
                <div class="border-l-4 border-green-500 pl-4 mb-4 last:mb-0 pb-4 last:pb-0 border-b last:border-b-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <a href="{{ route('bills.show', $assignment->bill->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                {{ $assignment->bill->bill_number }}/{{ $assignment->bill->year }}
                            </a>
                            <h3 class="text-sm text-gray-900 mt-1">{{ Str::limit($assignment->bill->title, 120) }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Raportat
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $assignment->report_date?->format('d M Y') }}
                                </span>
                            </div>
                            @if($assignment->notes)
                            <p class="text-xs text-gray-600 mt-2">{{ Str::limit($assignment->notes, 100) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Niciun proiect finalizat încă</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Committee Leadership -->
            @if($committee->chair)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Președinte</h2>
                <a href="{{ route('legislators.show', $committee->chair->id) }}" class="block p-4 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold">
                            {{ strtoupper(substr($committee->chair->first_name ?? '', 0, 1) . substr($committee->chair->last_name ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">
                                {{ $committee->chair->first_name }} {{ $committee->chair->last_name }}
                            </h3>
                            <p class="text-sm text-gray-600">{{ $committee->chair->party ?? 'Independent' }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- Committee Members -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Membri</h2>
                    <span class="text-sm text-gray-600">{{ $committee->members->count() }}</span>
                </div>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($committee->members as $member)
                    <a href="{{ route('legislators.show', $member->id) }}" class="block p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </h3>
                                <p class="text-xs text-gray-600 truncate">{{ $member->party ?? 'Independent' }}</p>
                            </div>
                        </div>
                    </a>
                    @empty
                    <p class="text-gray-500 text-sm text-center py-4">Nu există membri</p>
                    @endforelse
                </div>
            </div>

            <!-- Activity Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Performanță</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Rată de finalizare</span>
                        <span class="font-bold text-green-600">
                            {{ $stats['bills_assigned'] > 0 ? round(($stats['bills_completed'] / $stats['bills_assigned']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Timp mediu procesare</span>
                        <span class="font-bold text-indigo-600">
                            @php
                                $avgDays = \App\Models\CommitteeAssignment::where('committee_id', $committee->id)
                                    ->where('status', 'reported')
                                    ->whereNotNull('report_date')
                                    ->selectRaw('AVG(DATEDIFF(report_date, assigned_date)) as avg_days')
                                    ->value('avg_days');
                            @endphp
                            {{ $avgDays ? round($avgDays) : '-' }} zile
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Proiecte luna aceasta</span>
                        <span class="font-bold text-blue-600">
                            {{ \App\Models\CommitteeAssignment::where('committee_id', $committee->id)
                                ->whereMonth('assigned_date', now()->month)
                                ->whereYear('assigned_date', now()->year)
                                ->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
