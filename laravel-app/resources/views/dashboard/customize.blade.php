<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Customize Dashboard
            </h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('dashboard.preferences.reset') }}" onsubmit="return confirm('Reset dashboard to default layout?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Reset to Default
                    </button>
                </form>
                <a href="{{ route('dashboard.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.preferences.update') }}">
                @csrf

                <!-- Theme Selection -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Theme</h3>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="light" {{ $preferences->theme === 'light' ? 'checked' : '' }} class="mr-2">
                                Light
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="dark" {{ $preferences->theme === 'dark' ? 'checked' : '' }} class="mr-2">
                                Dark
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="auto" {{ $preferences->theme === 'auto' ? 'checked' : '' }} class="mr-2">
                                Auto (System)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Visible Widgets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Visible Widgets</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $availableWidgets = [
                                    'recent_bills' => 'Recent Bills',
                                    'watchlist_summary' => 'Watchlist Summary',
                                    'ai_insights' => 'AI Insights',
                                    'activity_feed' => 'Activity Feed',
                                    'urgent_bills' => 'Urgent Bills',
                                    'high_risk_bills' => 'High-Risk Bills',
                                    'statistics' => 'Statistics Overview',
                                    'chamber_breakdown' => 'Chamber Breakdown Chart',
                                ];
                                $visibleWidgets = $preferences->visible_widgets ?? [];
                            @endphp

                            @foreach($availableWidgets as $key => $label)
                                <label class="flex items-center p-3 border rounded hover:bg-gray-50">
                                    <input
                                        type="checkbox"
                                        name="visible_widgets[]"
                                        value="{{ $key }}"
                                        {{ in_array($key, $visibleWidgets) ? 'checked' : '' }}
                                        class="mr-3">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Widget Layout Preview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Widget Layout</h3>
                        <p class="text-sm text-gray-600 mb-4">Drag and drop functionality will be available in a future update. Current layout:</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($preferences->widget_layout ?? [] as $widget)
                                <div class="p-4 border-2 border-dashed border-gray-300 rounded">
                                    <div class="font-medium">{{ ucfirst(str_replace('_', ' ', $widget['widget'])) }}</div>
                                    <div class="text-sm text-gray-600">
                                        Position: {{ $widget['position'] }} &middot; Size: {{ $widget['size'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Chart Preferences -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Chart Preferences</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default Chart Type</label>
                                <select name="chart_preferences[default_type]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="bar">Bar Chart</option>
                                    <option value="pie">Pie Chart</option>
                                    <option value="line">Line Chart</option>
                                    <option value="doughnut">Doughnut Chart</option>
                                </select>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="chart_preferences[show_labels]" value="1" checked class="mr-2">
                                    Show data labels on charts
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="chart_preferences[animations]" value="1" checked class="mr-2">
                                    Enable chart animations
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
