<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Balance Sheet Summary PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .ps-4 {
            padding-left: 1.5rem;
        }

        footer {
            position: fixed;
            margin-bottom: -20px;
            left: 0px;
            right: 0px;
            font-size: 12px;
            font-style: italic;
            bottom: -5px;
        }

        header {
            text-align: center;
            margin-bottom: -25px;
            margin-top: -25px;
        }

        .main_header {
            height: 10px;
            position: relative;
        }

        .main_header .logo {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bolder;
            font-size: 35px;
            position: absolute;
            top: 0;
            right: 0;
        }

        .main_header .logo img {
            max-width: 120px;
            max-height: 75px;
        }

        .main_header .comp_info {
            position: absolute;
            top: 0;
            left: 0;
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: normal;
            opacity: 0.8;
            font-size: 15px;
            font-style: italic;
        }

        .main_header ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main_header ul li {
            margin-right: 20px;
        }

        header p {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-top: 50px;
        }
    </style>
</head>

<header>
    <div class="main_header">
        <div class="container">
            <div class="logo">
                <img src="{{ public_path('assets/img/ryofin-logo.png') }}" alt="Logo">
            </div>

            <ul class="comp_info">
                <li>{{$selected_name}}</li>
                <li>{{$address}}</li>
                <li>{{$phone}}</li>
            </ul>
        </div>
    </div>

    <p>
        Balance Sheet as of {{ date('d/m/Y', strtotime($date)) }}
        (Summary Report)
    </p>
</header>

<footer>
    @php
        date_default_timezone_set('Asia/Dhaka');
    @endphp
    <p>Report Generated from Inventory System at {{ date('d/m/Y h:i:s A') }} | Summary Report</p>
</footer>

<body>
    <table>
        <thead>
            <tr>
                <th class="text-start">Account</th>
                <th class="text-end">Amount (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Assets Section -->
            <tr>
                <td class="text-start fw-bold">Assets</td>
                <td></td>
            </tr>
            @foreach ($assetData as $asset)
                <tr>
                    <td class="text-start ps-4">&nbsp; &nbsp; {{ $asset['title'] }}</td>
                    <td class="text-end">{{ number_format($asset['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-start fw-bold">Total Assets</td>
                <td class="text-end fw-bold">{{ number_format($totalAssets, 2) }}</td>
            </tr>

            <!-- Spacer Row -->
            <tr>
                <td style="border: none; height: 20px;"></td>
                <td style="border: none;"></td>
            </tr>

            <!-- Liabilities & Equity Section -->
            <tr>
                <td class="text-start fw-bold">Liabilities & Equity</td>
                <td></td>
            </tr>
            <!-- Liabilities Section -->
            <tr>
                <td class="text-start fw-bold">Liabilities</td>
                <td></td>
            </tr>
            @foreach ($liabilityData as $liability)
                <tr>
                    <td class="text-start ps-4">&nbsp; &nbsp; {{ $liability['title'] }}</td>
                    <td class="text-end">{{ number_format($liability['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-start fw-bold">Total Liabilities</td>
                <td class="text-end fw-bold">{{ number_format($totalLiabilities, 2) }}</td>
            </tr>

            <!-- Equity Section -->
            <tr>
                <td class="text-start fw-bold">Equity</td>
                <td></td>
            </tr>
            @foreach ($capitalData as $capital)
                <tr>
                    <td class="text-start ps-4">&nbsp; &nbsp; {{ $capital['title'] }}</td>
                    <td class="text-end">{{ number_format($capital['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-start ps-4">&nbsp; &nbsp; Profit</td>
                <td class="text-end">{{ number_format($totalProfit, 2) }}</td>
            </tr>
            <tr>
                <td class="text-start fw-bold">Total Equity</td>
                <td class="text-end fw-bold">{{ number_format($totalCapital, 2) }}</td>
            </tr>

            <!-- Total Liabilities + Equity -->
            <tr>
                <td class="text-start fw-bold">Total Liabilities & Equity</td>
                <td class="text-end fw-bold">{{ number_format($totalLiabilities + $totalCapital + $totalProfit, 2) }}</td>
            </tr>
            @php
                $balanceCheck = ($totalAssets == ($totalLiabilities + $totalCapital + $totalProfit));
            @endphp

            <!-- Balance Check -->
            <tr>
                <td style="border: none; height: 20px;"></td>
                <td style="border: none;"></td>
            </tr>
            <tr>
                <td class="text-center fw-bold">Balance Check</td>
                <td class="text-center fw-bold">{{ $balanceCheck ? 'Balanced' : 'Not Balanced' }}</td>
            </tr>
        </tbody>
    </table>

    <script type="text/php">
        if ( isset($pdf) ) { 
            $pdf->page_script('
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $size = 8;
                    $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                    $y = 805;
                    $x = 502;
                    $pdf->text($x, $y, $pageText, $font, $size);
                } 
            ');
        }
    </script>
</body>

</html>
