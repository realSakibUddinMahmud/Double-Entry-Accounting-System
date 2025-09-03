<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income Statement PDF</title>

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

        th, td {
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
            font-size: 16px;
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
        Income Statement (
        @if (date('d-M-Y', strtotime($start_date ?? today())) != date('d-M-Y', strtotime($end_date ?? today())))
            {{ date('d/m/Y', strtotime($start_date)) }} to {{ date('d/m/Y', strtotime($end_date)) }}
        @else
            {{ date('d/m/Y', strtotime($start_date)) }}
        @endif
        )
    </p>
</header>

<footer>
    @php
        date_default_timezone_set('Asia/Dhaka');
    @endphp
    <p>Report Generated from {{ env('APP_NAME') }} at {{ date('d/m/Y h:i:s A') }}</p>
</footer>

<body>
    <table>
        <thead>
            <tr>
                <th class="text-start">Account</th>
                <th class="text-end">Total (Tk)</th>
            </tr>
        </thead>
                    <tbody>
                        <!-- Operating Income -->
                        <tr>
                            <td class="text-start">Operating Income</td>
                            <td></td>
                        </tr>
                        @foreach ($salesIncomeAcData as $salesIncomeAc)
                            <tr>
                                <td class="text-start">&nbsp; &nbsp; {{ $salesIncomeAc->title }}</td>
                                <td class="text-end">{{ number_format($salesIncomeAc->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                        
                        <tr>
                            <td class="text-start fw-bold">Total for Operating Income</td>
                            <td class="text-end fw-bold">{{ number_format($salesIncomeAcData->sum('total_amount'), 2) }}</td>
                        </tr>

                        <!-- COGS and Gross Profit -->
                        <tr>
                            <td class="text-start fw-bold">Cost of Goods Sold</td>
                            <td></td>
                        </tr>
                        @foreach ($cogsAcData as $cogsAc)
                            <tr>
                                <td class="text-start ps-4">{{ $cogsAc->title }}</td>
                                <td class="text-end">{{ number_format($cogsAc->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-center fw-bold">Gross Profit/Loss</td>
                            <td class="text-end fw-bold">
                                {{ number_format($salesIncomeAcData->sum('total_amount') - $cogsAcData->sum('total_amount'), 2) }}
                            </td>
                        </tr>

                        <!-- Other Income -->
                        <tr>
                            <td class="text-start fw-bold">Add: Other Income</td>
                            <td></td>
                        </tr>
                        @foreach ($otherIncomeAcData as $otherIncomeAc)
                            <tr>
                                <td class="text-start ps-4">{{ $otherIncomeAc->title }}</td>
                                <td class="text-end">{{ number_format($otherIncomeAc->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-start fw-bold">Other Income Total</td>
                            <td class="text-end fw-bold">{{ number_format($otherIncomeAcData->sum('total_amount'), 2) }}</td>
                        </tr>

                        <!-- Total Income -->
                        <tr>
                            <td class="text-start fw-bold">Total Income</td>
                            <td class="text-end fw-bold">
                                {{ number_format($salesIncomeAcData->sum('total_amount') - $cogsAcData->sum('total_amount') + $otherIncomeAcData->sum('total_amount'), 2) }}
                            </td>
                        </tr>

                        <!-- Expenses -->
                        <tr>
                            <td class="text-start fw-bold">Less: Expenses</td>
                            <td></td>
                        </tr>
                        @foreach ($expenseAcData as $expenseAc)
                            <tr>
                                <td class="text-start ps-4">{{ $expenseAc->title }}</td>
                                <td class="text-end">{{ number_format($expenseAc->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-start fw-bold">Total Expenses</td>
                            <td class="text-end fw-bold">({{ number_format($expenseAcData->sum('total_amount'), 2) }})</td>
                        </tr>

                        <!-- Net Profit -->
                        <tr>
                            <td class="text-center fw-bold">Net Profit/Loss</td>
                            <td class="text-end fw-bold">
                                {{ number_format($salesIncomeAcData->sum('total_amount') - $cogsAcData->sum('total_amount') + $otherIncomeAcData->sum('total_amount') - $expenseAcData->sum('total_amount'), 2) }}
                            </td>
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