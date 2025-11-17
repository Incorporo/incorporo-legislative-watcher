<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $bill->title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        * {
            font-family: DejaVu Sans, sans-serif;
        }
        body {
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }
        h1 {
            font-size: 18pt;
            color: #0284c7;
            margin-bottom: 10pt;
            border-bottom: 2pt solid #0284c7;
            padding-bottom: 5pt;
        }
        h2 {
            font-size: 14pt;
            color: #0369a1;
            margin-top: 15pt;
            margin-bottom: 8pt;
        }
        h3 {
            font-size: 12pt;
            color: #475569;
            margin-top: 10pt;
            margin-bottom: 6pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20pt;
            padding-bottom: 10pt;
            border-bottom: 1pt solid #e2e8f0;
        }
        .bill-number {
            font-size: 24pt;
            font-weight: bold;
            color: #0284c7;
        }
        .badge {
            display: inline-block;
            padding: 2pt 8pt;
            border-radius: 4pt;
            font-size: 8pt;
            font-weight: bold;
            margin: 0 2pt;
        }
        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-purple {
            background-color: #e9d5ff;
            color: #6b21a8;
        }
        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin: 10pt 0;
            border: 1pt solid #e2e8f0;
            border-radius: 4pt;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 6pt;
            font-weight: bold;
            background-color: #f8fafc;
            width: 30%;
            border-bottom: 1pt solid #e2e8f0;
        }
        .info-value {
            display: table-cell;
            padding: 6pt;
            border-bottom: 1pt solid #e2e8f0;
        }
        .ai-section {
            background-color: #eff6ff;
            padding: 10pt;
            margin: 15pt 0;
            border: 2pt solid #3b82f6;
            border-radius: 6pt;
        }
        .pros-cons {
            display: table;
            width: 100%;
            margin: 10pt 0;
        }
        .pros {
            display: table-cell;
            width: 48%;
            padding: 8pt;
            background-color: #f0fdf4;
            border: 1pt solid #10b981;
            border-radius: 4pt;
            vertical-align: top;
        }
        .cons {
            display: table-cell;
            width: 48%;
            padding: 8pt;
            background-color: #fef2f2;
            border: 1pt solid #ef4444;
            border-radius: 4pt;
            margin-left: 4%;
            vertical-align: top;
        }
        .risk-item {
            background-color: #fffbeb;
            padding: 6pt;
            margin: 6pt 0;
            border-left: 3pt solid #f59e0b;
        }
        .timeline-item {
            padding: 8pt;
            margin: 6pt 0;
            background-color: #f8fafc;
            border-left: 3pt solid #0284c7;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1pt solid #e2e8f0;
            padding-top: 10pt;
        }
        .page-break {
            page-break-after: always;
        }
        ul {
            margin: 5pt 0;
            padding-left: 15pt;
        }
        li {
            margin: 3pt 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="bill-number">{{ $bill->bill_number }}/{{ $bill->year }}</div>
        <div style="margin-top: 10pt;">
            <span class="badge badge-{{ $bill->chamber === 'cdep' ? 'blue' : 'purple' }}">
                {{ $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
            </span>
            @if($bill->urgency_status)
                <span class="badge badge-amber">Procedură Urgentă</span>
            @endif
            @php $riskLevel = $bill->getHighestRiskLevel(); @endphp
            @if($riskLevel)
                <span class="badge badge-red">Risc {{ ucfirst($riskLevel) }}</span>
            @endif
        </div>
    </div>

    <!-- Title -->
    <h1>{{ $bill->title }}</h1>

    @if($bill->description)
    <p style="font-size: 11pt; margin: 10pt 0; line-height: 1.6;">{{ $bill->description }}</p>
    @endif

    <!-- Basic Information -->
    <h2>Informații Generale</h2>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value">{{ ucfirst(str_replace('_', ' ', $bill->status ?? 'N/A')) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data Înregistrării</div>
            <div class="info-value">{{ $bill->registration_date?->format('d.m.Y') ?? 'N/A' }}</div>
        </div>
        @if($bill->type)
        <div class="info-row">
            <div class="info-label">Tip Proiect</div>
            <div class="info-value">{{ $bill->type }}</div>
        </div>
        @endif
        @if($bill->first_chamber)
        <div class="info-row">
            <div class="info-label">Prima Cameră</div>
            <div class="info-value">{{ $bill->first_chamber }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Progres</div>
            <div class="info-value">{{ $progressPercentage }}%</div>
        </div>
    </div>

    <!-- Initiators -->
    @if($bill->initiators->isNotEmpty())
    <h2>Inițiatori ({{ $bill->initiators->count() }})</h2>
    <ul>
        @foreach($bill->initiators as $initiator)
        <li><strong>{{ $initiator->name }}</strong> - {{ ucfirst($initiator->type) }}@if($initiator->party), {{ $initiator->party }}@endif</li>
        @endforeach
    </ul>
    @endif

    @if($latestAnalysis && $analysisData)
    <!-- AI Assessment Section -->
    <div class="page-break"></div>
    <div class="ai-section">
        <h2 style="margin-top: 0;">Analiză Inteligentă AI</h2>
        <p style="font-size: 8pt; color: #64748b; margin-bottom: 10pt;">
            Analizat cu {{ $latestAnalysis->model_version }} •
            Încredere: {{ number_format(($analysisData['confidence_score'] ?? 0) * 100) }}% •
            {{ $latestAnalysis->analyzed_at->format('d.m.Y H:i') }}
        </p>

        @if(isset($analysisData['summary']))
        <h3>Rezumat</h3>
        <p>{{ $analysisData['summary'] }}</p>
        @endif

        @if(isset($analysisData['impact_assessment']))
        <h3>Evaluare Impact</h3>
        <ul>
            <li><strong>Domeniu:</strong> {{ $analysisData['impact_assessment']['scope'] ?? 'N/A' }}</li>
            <li><strong>Magnitudine:</strong> {{ ucfirst($analysisData['impact_assessment']['magnitude'] ?? 'low') }}</li>
            <li><strong>Termen:</strong> {{ ucfirst(str_replace('-', ' ', $analysisData['impact_assessment']['timeframe'] ?? 'medium-term')) }}</li>
        </ul>
        @endif

        @if(isset($analysisData['pros']) || isset($analysisData['cons']))
        <h3>Aspecte Pozitive și Negative</h3>
        <div class="pros-cons">
            @if(isset($analysisData['pros']) && count($analysisData['pros']) > 0)
            <div class="pros">
                <strong style="color: #059669;">Aspecte Pozitive:</strong>
                <ul style="margin-top: 5pt;">
                    @foreach($analysisData['pros'] as $pro)
                    <li>
                        <strong>{{ $pro['point'] }}</strong><br/>
                        <span style="font-size: 9pt;">{{ $pro['explanation'] }}</span>
                        @if(isset($pro['stakeholders']) && count($pro['stakeholders']) > 0)
                        <br/><em style="font-size: 8pt; color: #059669;">Beneficiari: {{ implode(', ', $pro['stakeholders']) }}</em>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(isset($analysisData['cons']) && count($analysisData['cons']) > 0)
            <div class="cons">
                <strong style="color: #dc2626;">Aspecte Negative:</strong>
                <ul style="margin-top: 5pt;">
                    @foreach($analysisData['cons'] as $con)
                    <li>
                        <strong>{{ $con['point'] }}</strong><br/>
                        <span style="font-size: 9pt;">{{ $con['explanation'] }}</span>
                        @if(isset($con['stakeholders']) && count($con['stakeholders']) > 0)
                        <br/><em style="font-size: 8pt; color: #dc2626;">Afectați: {{ implode(', ', $con['stakeholders']) }}</em>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        @if(isset($analysisData['risks']) && count($analysisData['risks']) > 0)
        <h3>Riscuri Potențiale</h3>
        @foreach($analysisData['risks'] as $risk)
        <div class="risk-item">
            <strong>{{ $risk['risk'] }}</strong>
            <span style="font-size: 8pt; background-color: #fef3c7; padding: 2pt 4pt; border-radius: 2pt; margin-left: 5pt;">
                {{ ucfirst($risk['severity']) }}
            </span>
            <span style="font-size: 8pt; background-color: #e2e8f0; padding: 2pt 4pt; border-radius: 2pt; margin-left: 3pt;">
                Prob: {{ ucfirst($risk['probability']) }}
            </span>
            @if(isset($risk['mitigation']))
            <br/><span style="font-size: 9pt;"><strong>Mitigare:</strong> {{ $risk['mitigation'] }}</span>
            @endif
        </div>
        @endforeach
        @endif

        @if(isset($analysisData['economic_impact']))
        <h3>Impact Economic</h3>
        <ul>
            @if(isset($analysisData['economic_impact']['budget_required']))
            <li><strong>Buget Necesar:</strong> {{ $analysisData['economic_impact']['budget_required'] }}</li>
            @endif
            @if(isset($analysisData['economic_impact']['employment_impact']))
            <li><strong>Impact Ocupare:</strong> {{ ucfirst($analysisData['economic_impact']['employment_impact']) }}</li>
            @endif
            @if(isset($analysisData['economic_impact']['affected_sectors']))
            <li><strong>Sectoare Afectate:</strong> {{ implode(', ', $analysisData['economic_impact']['affected_sectors']) }}</li>
            @endif
        </ul>
        @endif

        @if(isset($analysisData['recommendations']) && count($analysisData['recommendations']) > 0)
        <h3>Recomandări</h3>
        <ul>
            @foreach($analysisData['recommendations'] as $recommendation)
            <li>{{ $recommendation }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

    <!-- Timeline -->
    @if($bill->timeline->isNotEmpty())
    <div class="page-break"></div>
    <h2>Cronologie Evenimente</h2>
    @foreach($bill->timeline->take(10) as $event)
    <div class="timeline-item">
        <strong>{{ $event->description }}</strong>
        <span style="float: right; color: #64748b; font-size: 9pt;">{{ $event->event_date->format('d.m.Y') }}</span>
        @if($event->deadline)
        <br/><span style="font-size: 9pt; color: #92400e;">Termen: {{ $event->deadline->format('d.m.Y') }}</span>
        @endif
    </div>
    @endforeach
    @endif

    <!-- Documents -->
    @if($bill->documents->isNotEmpty())
    <h2>Documente Atașate</h2>
    <ul>
        @foreach($bill->documents as $doc)
        <li><strong>{{ $doc->title }}</strong>@if($doc->document_type) - {{ ucfirst($doc->document_type) }}@endif</li>
        @endforeach
    </ul>
    @endif

    <!-- Committee Assignments -->
    @if($bill->committeeAssignments->isNotEmpty())
    <h2>Comisii Parlamentare</h2>
    <ul>
        @foreach($bill->committeeAssignments as $assignment)
        <li>
            <strong>{{ $assignment->committee->name }}</strong>
            @if($assignment->status) - Status: {{ ucfirst($assignment->status) }}@endif
            @if($assignment->review_deadline)
            <br/><span style="font-size: 9pt; color: #92400e;">Termen: {{ $assignment->review_deadline->format('d.m.Y') }}</span>
            @endif
        </li>
        @endforeach
    </ul>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Generat de Legislative Watcher • {{ now()->format('d.m.Y H:i') }} • {{ $bill->url ?? '' }}</p>
    </div>
</body>
</html>
