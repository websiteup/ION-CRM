<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ofertă {{ $proposal->proposal_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h2>Ofertă {{ $proposal->proposal_number }}</h2>
        
        <p>Bună ziua {{ $proposal->client ? ($proposal->client->first_name . ' ' . $proposal->client->last_name) : 'Client' }},</p>
        
        <p>Vă prezentăm oferta noastră:</p>
        
        <h3>{{ $proposal->title }}</h3>
        
        <p><strong>Data ofertei:</strong> {{ $proposal->proposal_date->format('d.m.Y') }}</p>
        <p><strong>Valabil până la:</strong> {{ $proposal->valid_until->format('d.m.Y') }}</p>
        
        @if($proposal->notes)
            <div style="margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-left: 4px solid #007bff;">
                <p><strong>Note:</strong></p>
                <p>{{ $proposal->notes }}</p>
            </div>
        @endif
        
        <p>Total: <strong>{{ number_format($proposal->total, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></p>
        
        <p>Vă rugăm să ne contactați pentru orice întrebări.</p>
        
        <p>Cu respect,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>

