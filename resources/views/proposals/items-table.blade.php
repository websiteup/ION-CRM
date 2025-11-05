<table>
    <thead>
        <tr>
            <th>Descriere</th>
            <th>Cantitate</th>
            <th>Preț Unit.</th>
            <th>Tax %</th>
            <th style="text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedItems as $category => $subcategories)
            @php
                $categoryTotal = 0;
                foreach ($subcategories as $subcatItems) {
                    foreach ($subcatItems as $item) {
                        $categoryTotal += $item->total;
                    }
                }
            @endphp
            <tr class="category-row">
                <td colspan="4"><strong>{{ $category }}</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($categoryTotal, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></td>
            </tr>
            @foreach($subcategories as $subcategory => $subcatItems)
                @if($subcategory !== 'General')
                    <tr class="subcategory-row">
                        <td colspan="5"><em>{{ $subcategory }}</em></td>
                    </tr>
                @endif
                @foreach($subcatItems as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->tax_rate, 2) }}%</td>
                        <td style="text-align: right;">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($proposal->subtotal, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: right;"><strong>Taxe:</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($proposal->tax_total, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></td>
        </tr>
        <tr class="total-row">
            <td colspan="4" style="text-align: right;"><strong>TOTAL:</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($proposal->total, 2) }} {{ $proposal->currency ? $proposal->currency->symbol : 'RON' }}</strong></td>
        </tr>
    </tfoot>
</table>

