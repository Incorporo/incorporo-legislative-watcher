<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #dbeafe;
            font-size: 16px;
            margin: 0;
        }
        .content {
            padding: 30px;
        }
        .intro {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .bill-card {
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s;
        }
        .bill-card:hover {
            border-color: #3b82f6;
        }
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .bill-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px 0;
            line-height: 1.4;
        }
        .bill-number {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }
        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-chamber-cdep {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-chamber-senat {
            background-color: #e9d5ff;
            color: #6b21a8;
        }
        .badge-urgent {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-risk-high, .badge-risk-critical {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .bill-description {
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
            margin: 12px 0;
        }
        .ai-summary {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            padding: 16px;
            margin: 16px 0;
            border-radius: 6px;
        }
        .ai-summary h4 {
            color: #1e40af;
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
        }
        .ai-summary p {
            color: #1e3a8a;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .pros-cons {
            margin: 12px 0;
        }
        .pros-cons h5 {
            font-size: 13px;
            font-weight: 700;
            margin: 8px 0 4px 0;
        }
        .pros h5 {
            color: #059669;
        }
        .cons h5 {
            color: #dc2626;
        }
        .pros-cons ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
        .pros ul {
            color: #065f46;
        }
        .cons ul {
            color: #991b1b;
        }
        .view-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 12px;
        }
        .stats-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        .stats-box h3 {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .stats-box p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            color: #64748b;
            font-size: 13px;
            margin: 5px 0;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .footer-links {
            margin: 15px 0;
        }
        .footer-links a {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📋 {{ $subscription->frequency === 'instant' ? 'Proiect Legislativ Nou' : 'Rezumat Legislative' }}</h1>
            <p>{{ $bills->count() }} {{ $bills->count() === 1 ? 'proiect nou' : 'proiecte noi' }} monitorizat{{ $bills->count() === 1 ? '' : 'e' }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="intro">
                <p>Bună{{ $subscription->name ? ', ' . $subscription->name : '' }}!</p>
                <p>
                    @if($subscription->frequency === 'instant')
                        Un proiect legislativ care corespunde criteriilor tale a fost înregistrat:
                    @elseif($subscription->frequency === 'daily')
                        Iată proiectele legislative din ultimele 24 de ore care corespund criteriilor tale:
                    @else
                        Iată rezumatul săptămânal al proiectelor legislative care corespund criteriilor tale:
                    @endif
                </p>
            </div>

            <!-- Bills -->
            @foreach($bills as $bill)
            <div class="bill-card">
                <div class="bill-title">{{ $bill->title }}</div>
                <div class="bill-number">{{ $bill->bill_number }}/{{ $bill->year }}</div>

                <!-- Badges -->
                <div class="badges">
                    <span class="badge badge-chamber-{{ $bill->chamber }}">
                        {{ $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
                    </span>
                    @if($bill->urgency_status)
                    <span class="badge badge-urgent">Procedură Urgentă</span>
                    @endif
                    @php
                        $riskLevel = $bill->getHighestRiskLevel();
                    @endphp
                    @if($riskLevel && in_array($riskLevel, ['high', 'critical']))
                    <span class="badge badge-risk-{{ $riskLevel }}">
                        Risc {{ $riskLevel === 'high' ? 'Ridicat' : 'Critic' }}
                    </span>
                    @endif
                </div>

                @if($bill->description)
                <div class="bill-description">{{ $bill->description }}</div>
                @endif

                @if($includeAiSummary)
                    @php
                        $latestAnalysis = $bill->analysis->where('analysis_type', 'ai_assessment')->sortByDesc('analyzed_at')->first();
                        $analysisData = $latestAnalysis ? json_decode($latestAnalysis->analysis_data, true) : null;
                    @endphp

                    @if($analysisData && isset($analysisData['summary']))
                    <div class="ai-summary">
                        <h4>🤖 Analiză AI</h4>
                        <p>{{ $analysisData['summary'] }}</p>

                        @if(isset($analysisData['pros']) || isset($analysisData['cons']))
                        <div class="pros-cons">
                            @if(isset($analysisData['pros']) && count($analysisData['pros']) > 0)
                            <div class="pros">
                                <h5>✓ Aspecte Pozitive:</h5>
                                <ul>
                                    @foreach(array_slice($analysisData['pros'], 0, 3) as $pro)
                                    <li>{{ $pro['point'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(isset($analysisData['cons']) && count($analysisData['cons']) > 0)
                            <div class="cons">
                                <h5>✗ Aspecte Negative:</h5>
                                <ul>
                                    @foreach(array_slice($analysisData['cons'], 0, 3) as $con)
                                    <li>{{ $con['point'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                @endif

                <a href="{{ url('/bills/' . $bill->id) }}" class="view-button">
                    Vezi Detalii Complete →
                </a>
            </div>
            @endforeach

            <!-- Stats Summary -->
            @if($totalCount > $bills->count())
            <div class="stats-box">
                <h3>{{ $totalCount }} Total</h3>
                <p>Afișăm primele {{ $bills->count() }} proiecte. Vezi toate pe platforma noastră.</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Legislative Watcher</strong></p>
            <p>Monitorizare Inteligentă Legislativă</p>

            <div class="footer-links">
                <a href="{{ $manageUrl }}">Gestionează Preferințe</a> •
                <a href="{{ url('/dashboard') }}">Vezi Dashboard</a> •
                <a href="{{ $unsubscribeUrl }}">Dezabonează-te</a>
            </div>

            <p style="margin-top: 20px; font-size: 12px;">
                <strong>Setările tale:</strong> {{ $subscriptionSummary }}<br>
                <strong>Frecvență:</strong> {{ $frequency }}
            </p>

            <p style="margin-top: 15px;">
                © 2025 Legislative Watcher • România
            </p>
        </div>
    </div>
</body>
</html>
