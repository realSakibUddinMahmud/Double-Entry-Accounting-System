<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RyoGas-Export To PDF</title>

    <style>
        ul {
            list-style-type: none;
        }

        .sales_table {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border: 1px solid;
            border-spacing: 0px;
            border-color: #36454F;
            /*border-collapse: collapse;*/
        }

        .sales_table td,
        .sales_table th,
        .sales_table tr {
            border: 1px solid;
            border-color: #36454F;
            padding: 3px;
        }


        .sales_table th {
            padding-top: 2px;
            padding-bottom: 2px;
            text-align: center;
            background-color: white;
            color: black;
            font-size: 12px;
        }

        .sales_table td {
            font-size: 14px;
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

        body {
            margin-bottom: 30px;
        }

        header {
            /* background-color: #87c7e0; */

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
            font-size: 18px;
            font-weight: bold;
            margin-top: 50px;
            /* Adjust as needed */
        }
    </style>
</head>

<body>
    <header>

        <div class="main_header">
            <div class="container">
                <!--  <p>{ Auth::user() }}</p> -->
                @php
                    $companyId = Auth::user()->tenant_id;
                    $company = \App\Models\Company::find($companyId);
                @endphp

                <div class="logo">
                    @php
                        $img = $company->images()->latest()->first();
                    @endphp

                    @if ($img)
                        {{-- <img src="{{ $img->url }}" alt="Company Logo"> --}}
                        <img src="{{ public_path('storage/' . $img->path) }}" alt="Company Logo">
                    @else
                        <!-- If no image is available -->
                        {{-- <img src="https://ryogasnative140203-dev.s3.ap-southeast-1.amazonaws.com/web-folder/logo/ryogas-only-logo.png"alt="Logo"> --}}
                        <img src="{{ public_path('assets/img/ryofin-logo.png') }}" alt="Logo">
                    @endif
                </div>

                <ul class="comp_info">
                    <li>{{ $company->name }}</li>
                    <li style="white-space: pre-line; margin-top: -19px;"> <!-- Adjust negative margin as needed -->
                        {{ trim(implode(",\n", array_map(fn($chunk) => implode(', ', $chunk), array_chunk(explode(', ', $company->office_address), 3)))) }}
                    </li>
                    <li>{{ $company->contact_no }}</li>
                    {{-- <li>Company Name</li>
                <li>Company Address</li>
                <li>Company Contact Info</li> --}}
                </ul>
            </div>
        </div>

        <p>
            <br>
            Ledgers (
            @if (date('d-M-Y', strtotime($start_date ?? today())) != date('d-M-Y', strtotime($end_date ?? today())))
                {{ date('d/m/Y', strtotime($start_date)) }} to {{ date('d/m/Y', strtotime($end_date)) }}
            @else
                {{ date('d/m/Y', strtotime($start_date)) }}
            @endif
            )
        </p>
        <br>
    </header>

    <footer>
        @php
            date_default_timezone_set('Asia/Dhaka');
        @endphp
        <p>Report Generated from RyoFin at
            {{ date('d/m/Y h:i:s A') }}</p>
    </footer>

    <body>
        @include('de-accounting::ledgers.data-table-new')

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

        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_script('
                    if ($PAGE_NUM > 1) {
                        $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                        $size = 10;
                        $compName = App\Models\Company::find(Auth::user()->tenant_id)->name;
                        $pageText = $compName . ",Ledgers (@if(date('d-M-Y',strtotime($start_date ?? today()))!= date('d-M-Y', strtotime($end_date ?? today()))){{date('d/m/Y', strtotime($start_date))}} to {{date('d/m/Y',strtotime($end_date))}}@else{{date('d/m/Y', strtotime($start_date))}}@endif)";
                        $x = 40;
                        $y = 22;
                        $pdf->text($x, $y, $pageText, $font, $size);
                    }
                ');
            }
        </script>

    </body>



</html>
