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
            max-width: 600px;
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
            padding: 40px 30px;
        }
        .content p {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .button {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            color: #1e40af;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }
        .info-box p {
            color: #1e3a8a;
            font-size: 14px;
            margin: 5px 0;
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
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔔 Legislative Watcher</h1>
            <p>Confirmă Subscrierea</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Bună{{ $subscription->name ? ', ' . $subscription->name : '' }}!</p>

            <p>
                Mulțumim că te-ai abonat la alertele noastre legislative! Pentru a începe să primești notificări,
                te rugăm să confirmi adresa de email accesând butonul de mai jos:
            </p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">
                    Confirmă Emailul
                </a>
            </div>

            <p style="font-size: 13px; color: #94a3b8; margin-top: 15px;">
                Sau copiază acest link în browser:<br>
                <span style="word-break: break-all;">{{ $verificationUrl }}</span>
            </p>

            <!-- Subscription Details -->
            <div class="info-box">
                <h3>Setările Tale de Notificare</h3>
                <p><strong>Frecvență:</strong> {{ $frequency }}</p>
                @if($subscriptionSummary !== 'Toate proiectele')
                <p><strong>Filtre:</strong> {{ $subscriptionSummary }}</p>
                @else
                <p>Vei primi notificări despre <strong>toate proiectele legislative</strong></p>
                @endif
            </div>

            <p>
                După confirmare, vei primi email-uri conform preferințelor tale cu informații despre proiectele
                legislative relevante, inclusiv analize AI detaliate despre impactul și riscurile acestora.
            </p>

            <p style="font-size: 14px; color: #64748b; margin-top: 30px;">
                Dacă nu te-ai abonat la acest serviciu, poți ignora acest email.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Legislative Watcher</strong></p>
            <p>Monitorizare Inteligentă Legislativă</p>
            <p style="margin-top: 15px;">
                © 2025 Legislative Watcher • România
            </p>
        </div>
    </div>
</body>
</html>
