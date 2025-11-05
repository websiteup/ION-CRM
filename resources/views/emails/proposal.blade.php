<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ofertă {{ $proposal->proposal_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        {!! $content !!}
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p style="color: #666; font-size: 12px;">
                Acest email a fost trimis automat de sistemul {{ config('app.name') }}.
            </p>
        </div>
    </div>
</body>
</html>

