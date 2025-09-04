{{-- filepath: resources/views/admin/reports/stock-export-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>Stock Report</h2>
    <table>
        <thead>
            <tr>
                <th>Store</th>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Unit</th>
                <th>Initial Stock</th>
                <th>Purchase Qty</th>
                <th>Sales Qty</th>
                <th>Adjustment Qty</th>
                <th>InStock Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportRows as $storeName => $products)
                @php
                    $rowspan = count($products);
                    $first = true;
                    $storeInitialStock = 0;
                    $storePurchaseQty = 0;
                    $storeSalesQty = 0;
                    $storeAdjustmentQty = 0;
                    $storeInStockQty = 0;
                @endphp
                @foreach($products as $row)
                    @php
                        $storeInitialStock += $row['initial_stock'] ?? 0;
                        $storePurchaseQty += $row['purchase_qty'];
                        $storeSalesQty += $row['sales_qty'];
                        $storeAdjustmentQty += $row['adjustment_total'];
                        $storeInStockQty += $row['instock_qty'];
                    @endphp
                    <tr>
                        @if($first)
                            <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $storeName }}</td>
                            @php $first = false; @endphp
                        @endif
                        <td>{{ $row['sku'] }}</td>
                        <td>{{ $row['product_name'] }}</td>
                        <td>{{ $row['unit'] }}</td>
                        <td>{{ $row['initial_stock'] ?? 0 }}</td>
                        <td>{{ $row['purchase_qty'] }}</td>
                        <td>{{ $row['sales_qty'] }}</td>
                        <td>{{ $row['adjustment_qty'] }}</td>
                        <td>{{ $row['instock_qty'] }}</td>
                    </tr>
                @endforeach
                <tr style="background:#f9f9f9;">
                    <th colspan="4" class="text-end">Total for {{ $storeName }}</th>
                    <th>{{ $storeInitialStock }}</th>
                    <th>{{ $storePurchaseQty }}</th>
                    <th>{{ $storeSalesQty }}</th>
                    <th>{{ $storeAdjustmentQty }}</th>
                    <th>{{ $storeInStockQty }}</th>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>