@extends('layouts.app')

@section('title', 'Comparație Proiecte de Lege')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('bills.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Înapoi la proiecte
        </a>
    </div>

    <!-- Header -->
    <div class="gradient-bg rounded-2xl p-8 text-white shadow-xl mb-8">
        <h1 class="text-3xl font-bold mb-2">Comparație Proiecte de Lege</h1>
        <p class="text-lg opacity-90">Analizați diferențele și similaritățile între proiecte</p>
    </div>

    <!-- Comparison Grid -->
    <div class="grid grid-cols-{{ count($billsWithProgress) }} gap-6 mb-8">
        @foreach($billsWithProgress as $item)
        @php $bill = $item['bill']; $progress = $item['progress']; @endphp

        <div class="space-y-6">
            <!-- Bill Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-t-4 border-indigo-500">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <a href="{{ route('bills.show', $bill->id) }}" class="text-xl font-bold text-indigo-600 hover:text-indigo-700">
                            {{ $bill->bill_number }}/{{ $bill->year }}
                        </a>
                        <h2 class="text-lg font-medium text-gray-900 mt-2">{{ $bill->title }}</h2>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $bill->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                    </span>
                    @if($bill->urgency_status)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Urgență
                    </span>
                    @endif
                </div>
            </div>

            <!-- Progress -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Progres legislativ</h3>
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <span class="text-xs font-semibold inline-block text-indigo-600">
                            {{ $progress }}%
                        </span>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-100">
                        <div style="width:{{ $progress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-500"></div>
                    </div>
                </div>
            </div>

            <!-- Key Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Detalii cheie</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Data înregistrării</p>
                        <p class="text-sm font-medium text-gray-900">{{ $bill->registration_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tip proiect</p>
                        <p class="text-sm font-medium text-gray-900">{{ ucfirst($bill->type ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Cameră decizională</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $bill->deciding_chamber ? ($bill->deciding_chamber === 'cdep' ? 'Camera Deputaților' : 'Senat') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Initiators -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Inițiatori</h3>
                <div class="space-y-2">
                    @forelse($bill->initiators->take(5) as $initiator)
                    @if($initiator->legislator)
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($initiator->legislator->first_name ?? '', 0, 1) . substr($initiator->legislator->last_name ?? '', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('legislators.show', $initiator->legislator->id) }}" class="text-xs font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                {{ $initiator->legislator->first_name }} {{ $initiator->legislator->last_name }}
                            </a>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-600">{{ $initiator->initiator_name }}</p>
                    @endif
                    @empty
                    <p class="text-xs text-gray-500">Niciun inițiator</p>
                    @endforelse
                    @if($bill->initiators->count() > 5)
                    <p class="text-xs text-gray-500">+{{ $bill->initiators->count() - 5 }} mai mulți</p>
                    @endif
                </div>
            </div>

            <!-- Risks -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Riscuri identificate</h3>
                @forelse($bill->risks->take(3) as $risk)
                <div class="mb-3 last:mb-0">
                    <x-risk-badge :level="$risk->risk_level" />
                    <p class="text-xs text-gray-900 mt-1">{{ $risk->risk_category }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($risk->justification, 80) }}</p>
                </div>
                @empty
                <div class="flex items-center gap-2 text-green-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">Niciun risc identificat</span>
                </div>
                @endforelse
                @if($bill->risks->count() > 3)
                <p class="text-xs text-gray-500 mt-2">+{{ $bill->risks->count() - 3 }} mai multe</p>
                @endif
            </div>

            <!-- Committee Assignments -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Comisii</h3>
                @forelse($bill->committeeAssignments->take(3) as $assignment)
                <div class="mb-2 last:mb-0">
                    <a href="{{ route('committees.show', $assignment->committee->id) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        {{ $assignment->committee->name_short ?? $assignment->committee->name }}
                    </a>
                    <p class="text-xs text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                    </p>
                </div>
                @empty
                <p class="text-xs text-gray-500">Nealocate încă</p>
                @endforelse
            </div>

            <!-- Timeline Events -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Istoric</h3>
                <div class="space-y-2">
                    @forelse($bill->timeline->take(5) as $event)
                    <div class="flex gap-2">
                        <div class="flex-shrink-0">
                            <div class="h-2 w-2 rounded-full bg-indigo-500 mt-1"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500">{{ $event->event_date?->format('d M Y') }}</p>
                            <p class="text-xs text-gray-900">{{ Str::limit($event->description, 60) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-500">Niciun eveniment</p>
                    @endforelse
                    @if($bill->timeline->count() > 5)
                    <p class="text-xs text-gray-500">+{{ $bill->timeline->count() - 5 }} mai multe</p>
                    @endif
                </div>
            </div>

            <!-- Description -->
            @if($bill->description)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Descriere</h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ Str::limit($bill->description, 200) }}</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Comparison Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Rezumat comparativ</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criteriu</th>
                        @foreach($billsWithProgress as $item)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $item['bill']->bill_number }}/{{ $item['bill']->year }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Progres</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['progress'] }}%</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Cameră</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['bill']->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
                        </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Inițiatori</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['bill']->initiators->count() }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Riscuri</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($item['bill']->risks->count() > 0)
                                {{ $item['bill']->risks->count() }}
                                <x-risk-badge :level="$item['bill']->getHighestRiskLevel()" />
                            @else
                                <span class="text-green-600">Niciun risc</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Evenimente</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['bill']->timeline->count() }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Urgență</td>
                        @foreach($billsWithProgress as $item)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($item['bill']->urgency_status)
                                <span class="text-red-600 font-medium">Da</span>
                            @else
                                <span class="text-gray-500">Nu</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
