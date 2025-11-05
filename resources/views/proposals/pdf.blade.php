<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertă {{ $proposal->proposal_number }}</title>
    @if(isset($preview) && $preview)
        <style>
            .preview-toolbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #007bff;
                color: white;
                padding: 10px 20px;
                z-index: 1000;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            .preview-toolbar .btn {
                background: white;
                color: #007bff;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                margin-left: 10px;
                font-size: 14px;
            }
            .preview-toolbar .btn:hover {
                background: #f0f0f0;
            }
            body {
                margin-top: 60px !important;
            }
            @media print {
                .preview-toolbar {
                    display: none !important;
                }
                body {
                    margin-top: 0 !important;
                }
            }
        </style>
    @endif
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0 auto;
            padding: 15mm;
            color: #333;
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-sizing: border-box;
        }
        
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 15mm;
            }
            .preview-toolbar {
                display: none !important;
            }
        }
        .header {
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .proposal-info {
            text-align: right;
            margin-bottom: 20px;
        }
        .content {
            margin: 30px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .category-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .subcategory-row {
            background-color: #f8f8f8;
            font-style: italic;
        }
        .total-row {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    @if(isset($preview) && $preview)
        <div class="preview-toolbar">
            <div>
                <strong>Preview Ofertă {{ $proposal->proposal_number }}</strong>
            </div>
            <div>
                <a href="{{ route('admin.proposals.view', $proposal->id) }}" class="btn">Înapoi</a>
                <a href="{{ route('admin.proposals.pdf', $proposal->id) }}" class="btn" target="_blank">Deschide în tab nou</a>
                <button onclick="window.print()" class="btn">Imprimă</button>
            </div>
        </div>
    @endif
    <div class="header">
        <div class="company-info">
            @if($company)
                <h2>{{ $company->name }}</h2>
                @if($company->address)
                    <p>{{ $company->address }}</p>
                @endif
                @if($company->phone)
                    <p>Tel: {{ $company->phone }}</p>
                @endif
                @if($company->email)
                    <p>Email: {{ $company->email }}</p>
                @endif
            @else
                <h2>{{ config('app.name') }}</h2>
            @endif
        </div>
        
        <div class="proposal-info">
            <h1>Ofertă</h1>
            <p><strong>Număr:</strong> {{ $proposal->proposal_number }}</p>
            <p><strong>Data:</strong> {{ $proposal->proposal_date->format('d.m.Y') }}</p>
            <p><strong>Valabil până:</strong> {{ $proposal->valid_until->format('d.m.Y') }}</p>
        </div>
    </div>

    @if($proposal->client)
        <div class="client-info">
            <h3>Client:</h3>
            <p>
                <strong>{{ $proposal->client->first_name }} {{ $proposal->client->last_name }}</strong><br>
                @if($proposal->client->email){{ $proposal->client->email }}<br>@endif
                @if($proposal->client->phone)Tel: {{ $proposal->client->phone }}<br>@endif
                @if($proposal->client->address){{ $proposal->client->address }}@endif
            </p>
        </div>
    @endif

    <div class="content">
        @if($content)
            {!! $content !!}
        @else
            <h2>{{ $proposal->title }}</h2>
            
            @include('proposals.items-table', ['proposal' => $proposal, 'groupedItems' => $groupedItems])
            
            <div style="margin-top: 30px;">
                <table style="width: 300px; margin-left: auto;">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td style="text-align: right;">{{ number_format($proposal->subtotal, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Taxe:</strong></td>
                        <td style="text-align: right;">{{ number_format($proposal->tax_total, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>TOTAL:</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($proposal->total, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></td>
                    </tr>
                </table>
            </div>
        @endif
    </div>

    @if($proposal->notes)
        <div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-left: 4px solid #007bff;">
            <p><strong>Note:</strong></p>
            <p>{{ $proposal->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Toate prețurile sunt nete, plus taxe aplicabile.</p>
        <p>Oferta este valabilă până la {{ $proposal->valid_until->format('d.m.Y') }}.</p>
    </div>
</body>
</html>

