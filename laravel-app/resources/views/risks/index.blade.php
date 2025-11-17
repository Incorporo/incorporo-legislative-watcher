@extends('layouts.app')
@section('title', 'Monitorizare Riscuri')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-3xl font-bold text-gray-900">Monitorizare Riscuri</h1><p class="mt-2 text-sm text-gray-600">Analiză automată AI pentru identificarea riscurilor legislative</p></div>
    </div>
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <div class="text-sm text-gray-600">Critic</div>
            <div class="text-3xl font-bold text-red-600">{{ $stats['critical'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="text-sm text-gray-600">Ridicat</div>
            <div class="text-3xl font-bold text-orange-600">{{ $stats['high'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Mediu</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $stats['medium'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Scăzut</div>
            <div class="text-3xl font-bold text-green-600">{{ $stats['low'] }}</div>
        </div>
    </div>
    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <select name="level" class="border-gray-300 rounded-lg">
                <option value="">Toate nivelurile</option>
                <option value="critical" {{ request('level') == 'critical' ? 'selected' : '' }}>Critic</option>
                <option value="high" {{ request('level') == 'high' ? 'selected' : '' }}>Ridicat</option>
                <option value="medium" {{ request('level') == 'medium' ? 'selected' : '' }}>Mediu</option>
                <option value="low" {{ request('level') == 'low' ? 'selected' : '' }}>Scăzut</option>
            </select>
            <select name="category" class="border-gray-300 rounded-lg">
                <option value="">Toate categoriile</option>
                @foreach($categories as $category)
                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2 hover:bg-indigo-700">Filtrează</button>
        </div>
    </form>
    <!-- Risks List -->
    <div class="space-y-4">
        @forelse($risks as $risk)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <a href="{{ route('bills.show', $risk->bill_id) }}" class="text-lg font-semibold text-indigo-600 hover:text-indigo-700">{{ $risk->bill->bill_number }}/{{ $risk->bill->year }}</a>
                        <span class="status-badge badge-{{ $risk->risk_level }}">{{ ucfirst($risk->risk_level) }}</span>
                        <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $risk->risk_category)) }}</span>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 mb-2">{{ $risk->bill->title }}</h3>
                    <p class="text-sm text-gray-700 mb-2"><strong>Risc:</strong> {{ $risk->description }}</p>
                    <p class="text-sm text-gray-600">{{ $risk->justification }}</p>
                    @if($risk->affected_parties)<p class="text-xs text-gray-500 mt-2"><strong>Afectați:</strong> {{ $risk->affected_parties }}</p>@endif
                </div>
                <a href="{{ route('bills.show', $risk->bill_id) }}" class="flex-shrink-0 ml-4">
                    <svg class="h-6 w-6 text-gray-400 hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Niciun risc detectat</h3>
            <p class="mt-1 text-sm text-gray-500">Nu există riscuri active pentru filtrele selectate.</p>
        </div>
        @endforelse
    </div>
    @if($risks->hasPages())<div class="mt-6">{{ $risks->links() }}</div>@endif
</div>
@endsection
