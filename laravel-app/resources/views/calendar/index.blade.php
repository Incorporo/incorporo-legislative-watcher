@extends('layouts.app')

@section('title', 'Calendar Legislativ')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="gradient-bg rounded-2xl p-8 text-white shadow-xl mb-8">
        <h1 class="text-3xl font-bold mb-2">Calendar Legislativ</h1>
        <p class="text-lg opacity-90">Evenimente, termene și activitate parlamentară</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Calendar -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Month Navigation -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $currentDate->format('F Y') }}
                    </h2>
                    <div class="flex gap-2">
                        <a href="{{ route('calendar', ['month' => $currentDate->copy()->subMonth()->month, 'year' => $currentDate->copy()->subMonth()->year]) }}"
                           class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ route('calendar') }}"
                           class="px-4 py-2 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors text-sm font-medium">
                            Astăzi
                        </a>
                        <a href="{{ route('calendar', ['month' => $currentDate->copy()->addMonth()->month, 'year' => $currentDate->copy()->addMonth()->year]) }}"
                           class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-2">
                    <!-- Day headers -->
                    @foreach(['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'] as $day)
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">
                        {{ $day }}
                    </div>
                    @endforeach

                    <!-- Calendar days -->
                    @php
                        $startOfMonth = $currentDate->copy()->startOfMonth();
                        $endOfMonth = $currentDate->copy()->endOfMonth();
                        $startDay = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $endDay = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::MONDAY);
                        $today = now();
                    @endphp

                    @for($date = $startDay->copy(); $date->lte($endDay); $date->addDay())
                        @php
                            $isCurrentMonth = $date->month === $currentDate->month;
                            $isToday = $date->isSameDay($today);
                            $dateKey = $date->format('Y-m-d');
                            $dayEvents = $events->get($dateKey, collect());
                        @endphp
                        <div class="aspect-square p-2 rounded-lg border {{ $isCurrentMonth ? 'border-gray-200' : 'border-gray-100 bg-gray-50' }} {{ $isToday ? 'ring-2 ring-indigo-500' : '' }} hover:bg-gray-50 transition-colors"
                             x-data="{ showEvents: false }"
                             @click="showEvents = true">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm {{ $isCurrentMonth ? 'text-gray-900 font-medium' : 'text-gray-400' }} {{ $isToday ? 'bg-indigo-600 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs' : '' }}">
                                        {{ $date->day }}
                                    </span>
                                    @if($dayEvents->count() > 0)
                                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                    @endif
                                </div>
                                @if($dayEvents->count() > 0)
                                <div class="mt-1 space-y-1">
                                    @foreach($dayEvents->take(2) as $event)
                                    <div class="text-xs px-1 py-0.5 rounded bg-indigo-100 text-indigo-700 truncate">
                                        {{ Str::limit($event->bill->bill_number ?? 'Event', 10) }}
                                    </div>
                                    @endforeach
                                    @if($dayEvents->count() > 2)
                                    <div class="text-xs text-gray-500">+{{ $dayEvents->count() - 2 }} mai multe</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- This Week's Activity -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Activitate săptămâna curentă</h2>
                @forelse($thisWeekEvents as $event)
                <div class="flex items-start gap-4 mb-4 pb-4 last:mb-0 last:pb-0 border-b last:border-b-0">
                    <div class="flex-shrink-0 text-center">
                        <div class="text-2xl font-bold text-indigo-600">{{ $event->event_date->format('d') }}</div>
                        <div class="text-xs text-gray-600">{{ $event->event_date->format('M') }}</div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $event->event_date->format('H:i') }}</span>
                        </div>
                        <h3 class="font-medium text-gray-900">{{ $event->description }}</h3>
                        @if($event->bill)
                        <a href="{{ route('bills.show', $event->bill->id) }}" class="text-sm text-indigo-600 hover:text-indigo-700 mt-1 inline-block">
                            {{ $event->bill->bill_number }}/{{ $event->bill->year }} - {{ Str::limit($event->bill->title, 60) }}
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Nicio activitate în această săptămână</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Upcoming Deadlines -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Termene apropiate</h2>
                    <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                @forelse($upcomingDeadlines as $deadline)
                <div class="mb-4 pb-4 last:mb-0 last:pb-0 border-b last:border-b-0">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                <span class="text-sm font-bold text-orange-600">
                                    {{ $deadline->deadline->diffInDays(now()) }}d
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">
                                {{ $deadline->deadline->format('d M Y, H:i') }}
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">{{ $deadline->description }}</h3>
                            @if($deadline->bill)
                            <a href="{{ route('bills.show', $deadline->bill->id) }}" class="text-xs text-indigo-600 hover:text-indigo-700">
                                {{ $deadline->bill->bill_number }}/{{ $deadline->bill->year }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Niciun termen apropiat</p>
                </div>
                @endforelse
            </div>

            <!-- Overdue Deadlines -->
            @if($overdueDeadlines->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Termene depășite</h2>
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                @foreach($overdueDeadlines as $deadline)
                <div class="mb-4 pb-4 last:mb-0 last:pb-0 border-b last:border-b-0">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-red-600 mb-1">
                                Depășit cu {{ $deadline->deadline->diffForHumans() }}
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">{{ $deadline->description }}</h3>
                            @if($deadline->bill)
                            <a href="{{ route('bills.show', $deadline->bill->id) }}" class="text-xs text-indigo-600 hover:text-indigo-700">
                                {{ $deadline->bill->bill_number }}/{{ $deadline->bill->year }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Legend -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Legendă</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-indigo-500"></div>
                        <span class="text-gray-700">Evenimente programate</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center">01</div>
                        <span class="text-gray-700">Ziua curentă</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-lg bg-orange-100"></div>
                        <span class="text-gray-700">Termene apropiate</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-lg bg-red-100"></div>
                        <span class="text-gray-700">Termene depășite</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Statistici</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Evenimente luna aceasta</span>
                        <span class="font-bold text-indigo-600">{{ $events->flatten()->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Termene săptămâna aceasta</span>
                        <span class="font-bold text-orange-600">{{ $upcomingDeadlines->filter(fn($d) => $d->deadline->lte(now()->addWeek()))->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Termene depășite</span>
                        <span class="font-bold text-red-600">{{ $overdueDeadlines->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
