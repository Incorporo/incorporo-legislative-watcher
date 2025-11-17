{{-- Advanced AI Analysis for Bills (Phase 2) --}}

@if($bill->ai_assessed && $bill->ai_assessment_status === 'completed')
    <div class="space-y-6">
        {{-- AI Summary --}}
        @if($bill->ai_summary)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">AI Executive Summary</h3>
                <p class="text-blue-800">{{ $bill->ai_summary }}</p>
                <p class="text-sm text-blue-600 mt-2">
                    Generated {{ $bill->ai_assessed_at->diffForHumans() }}
                </p>
            </div>
        @endif

        {{-- Stakeholder Impact Matrix --}}
        @if($bill->stakeholder_impact)
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Stakeholder Impact Analysis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($bill->stakeholder_impact as $stakeholder => $impact)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $stakeholder) }}</h4>
                                @php
                                    $impactColors = [
                                        'high' => 'bg-red-100 text-red-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'low' => 'bg-green-100 text-green-800',
                                    ];
                                    $level = $impact['impact_level'] ?? 'low';
                                @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded {{ $impactColors[$level] }}">
                                    {{ strtoupper($level) }} IMPACT
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 mb-2">{{ $impact['description'] }}</p>
                            @if(isset($impact['affected_groups']) && count($impact['affected_groups']) > 0)
                                <div class="mt-2">
                                    <p class="text-xs font-medium text-gray-600">Affected Groups:</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($impact['affected_groups'] as $group)
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ $group }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Voting Predictions --}}
        @if($bill->voting_predictions)
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Voting Outcome Prediction</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Passage Likelihood</p>
                        @php
                            $likelihood = $bill->voting_predictions['passage_likelihood'] ?? 'unknown';
                            $likelihoodColors = [
                                'high' => 'text-green-600',
                                'medium' => 'text-yellow-600',
                                'low' => 'text-red-600',
                            ];
                        @endphp
                        <p class="text-2xl font-bold {{ $likelihoodColors[$likelihood] ?? 'text-gray-600' }} capitalize">
                            {{ $likelihood }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Confidence Level</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $bill->voting_predictions['confidence'] ?? 'N/A' }}%
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Amendments Expected</p>
                        <p class="text-2xl font-bold text-gray-700">
                            {{ ($bill->voting_predictions['amendments_likely'] ?? false) ? 'Yes' : 'No' }}
                        </p>
                    </div>
                </div>

                @if(isset($bill->voting_predictions['key_factors']) && count($bill->voting_predictions['key_factors']) > 0)
                    <div class="mt-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Key Factors:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($bill->voting_predictions['key_factors'] as $factor)
                                <li class="text-sm text-gray-700">{{ $factor }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($bill->voting_predictions['timeline_estimate']))
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Timeline Estimate:</span> {{ $bill->voting_predictions['timeline_estimate'] }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Conflict Analysis --}}
        @if($bill->conflict_analysis)
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Conflict & Opposition Analysis</h3>

                @if(isset($bill->conflict_analysis['legal_challenges']) && count($bill->conflict_analysis['legal_challenges']) > 0)
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Legal Challenges:</h4>
                        <div class="space-y-3">
                            @foreach($bill->conflict_analysis['legal_challenges'] as $challenge)
                                <div class="border-l-4 border-orange-400 bg-orange-50 p-4 rounded">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold bg-orange-200 text-orange-800 rounded capitalize">
                                            {{ $challenge['type'] ?? 'Unknown' }}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-800 rounded capitalize">
                                            {{ $challenge['severity'] ?? 'Unknown' }} Severity
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $challenge['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($bill->conflict_analysis['political_opposition']))
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Political Opposition:</h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-medium">Expected Level:</span>
                                <span class="capitalize">{{ $bill->conflict_analysis['political_opposition']['expected_level'] ?? 'Unknown' }}</span>
                            </p>
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-medium">Reasoning:</span> {{ $bill->conflict_analysis['political_opposition']['reasoning'] }}
                            </p>
                            @if(isset($bill->conflict_analysis['political_opposition']['key_opponents']) && count($bill->conflict_analysis['political_opposition']['key_opponents']) > 0)
                                <div class="mt-2">
                                    <p class="text-xs font-medium text-gray-600">Key Opponents:</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($bill->conflict_analysis['political_opposition']['key_opponents'] as $opponent)
                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ $opponent }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Policy Recommendations --}}
        @if($bill->policy_recommendations)
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Policy Recommendations</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(isset($bill->policy_recommendations['supporters']) && count($bill->policy_recommendations['supporters']) > 0)
                        <div>
                            <h4 class="font-semibold text-green-900 mb-3">For Supporters:</h4>
                            <div class="space-y-3">
                                @foreach($bill->policy_recommendations['supporters'] as $rec)
                                    <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                                        <p class="text-sm font-medium text-green-900">{{ $rec['action'] }}</p>
                                        <p class="text-xs text-green-700 mt-1">Target: {{ $rec['audience'] }}</p>
                                        <p class="text-xs text-green-600 mt-1">Impact: {{ $rec['impact'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($bill->policy_recommendations['opponents']) && count($bill->policy_recommendations['opponents']) > 0)
                        <div>
                            <h4 class="font-semibold text-red-900 mb-3">For Opponents:</h4>
                            <div class="space-y-3">
                                @foreach($bill->policy_recommendations['opponents'] as $rec)
                                    <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                                        <p class="text-sm font-medium text-red-900">{{ $rec['action'] }}</p>
                                        <p class="text-xs text-red-700 mt-1">Target: {{ $rec['audience'] }}</p>
                                        @if(isset($rec['focus_areas']) && count($rec['focus_areas']) > 0)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($rec['focus_areas'] as $area)
                                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ $area }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if(isset($bill->policy_recommendations['improvements']) && count($bill->policy_recommendations['improvements']) > 0)
                    <div class="mt-6">
                        <h4 class="font-semibold text-blue-900 mb-3">Suggested Improvements:</h4>
                        <div class="space-y-2">
                            @foreach($bill->policy_recommendations['improvements'] as $improvement)
                                <div class="bg-blue-50 p-3 rounded">
                                    <p class="text-sm font-medium text-blue-900">{{ $improvement['suggestion'] }}</p>
                                    <p class="text-xs text-blue-700 mt-1">Benefit: {{ $improvement['benefit'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

@elseif($bill->ai_assessment_status === 'processing')
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
        <div class="flex items-center">
            <svg class="animate-spin h-5 w-5 text-yellow-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-yellow-800">AI analysis is currently being generated...</p>
        </div>
    </div>

@elseif($bill->ai_assessment_status === 'failed')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <p class="text-red-800 font-medium">AI assessment failed</p>
        @if($bill->ai_assessment_error)
            <p class="text-sm text-red-700 mt-1">Error: {{ $bill->ai_assessment_error }}</p>
        @endif
        <p class="text-sm text-red-600 mt-2">Please contact support if this issue persists.</p>
    </div>

@else
    <div class="bg-gray-50 border-l-4 border-gray-300 p-4 rounded">
        <p class="text-gray-700">AI analysis has not been generated for this bill yet.</p>
        <p class="text-sm text-gray-600 mt-1">Analysis will be automatically generated within 24-48 hours.</p>
    </div>
@endif
