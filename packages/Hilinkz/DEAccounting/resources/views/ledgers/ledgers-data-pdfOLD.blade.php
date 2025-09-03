<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RyoGas-Export To PDF</title>

    <style>
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

        header p {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-top: 50px;
            /* Adjust as needed */
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
            margin-top: -40px;
            margin-right: -10px;
        }

        .main_header .logo .img {
            max-width: 120px;
            max-height: 75px;
        }

        .main_header .comp_info {
            position: absolute;
            margin-top: -72px;
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

        header #p1 {
            text-align: center;
            font-size: 14px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            margin-top: 10px;
            padding-bottom: -5px;
            /* Adjust as needed */
        }

        ul {
            list-style-type: none;
        }

        @page {
            header: html_otherpageheader;
            footer: html_otherpagesfooter;
        }

        @page :first {
            header: html_firstpageheader;
            footer: html_firstpagefooter;
        }
    </style>


</head>

<body>
    <htmlpageheader name="firstpageheader" style="display:none">
        <div style="text-align:center"></div>
    </htmlpageheader>

    <htmlpagefooter name="firstpagefooter" style="display:none">
        <div style="font-size: 11px;">
            <div style="text-align: right; float: right; padding-bottom: -16px; font-style:italic;">Page {PAGENO} of
                {nbpg}</div>
            <div style="text-align: left; float: left; font-style:italic;">Report Generated from RyoGas (www.ryogas.com) at 
                {{  date('j-m-Y h:i:s A') }}</div>
            <div style="clear: both;"></div>
        </div>
    </htmlpagefooter>

    <htmlpagefooter name="otherpagesfooter" style="display:none">
        <div style="font-size: 11px;">
            <div style="text-align: right; float: right; padding-bottom: -16px; font-style:italic; ">Page {PAGENO} of
                {nbpg}</div>
            <div style="text-align: left; float: left; font-style:italic;">Report Generated from RyoGas (www.ryogas.com) at {{  date('j-m-Y h:i:s A') }}</div>
            <div style="clear: both;"></div>
        </div>
    </htmlpagefooter>

    <htmlpageheader name="otherpageheader" style="display:none">
        <div style="text-align:left;font-family: Arial, Helvetica, sans-serif;font-size: 12px; font-style:italic; ">
            {{ App\Models\Company::find(Auth::user()->company_id)->name }}, Ledgers
            (
            @if (date('d-M-Y', strtotime($start_date ?? today())) != date('d-M-Y', strtotime($end_date ?? today())))
                {{ date('d/m/Y', strtotime($start_date)) }} to {{ date('d/m/Y', strtotime($end_date)) }}
            @else
                {{ date('d/m/Y', strtotime($start_date)) }}
            @endif
            )
        </div>
    </htmlpageheader>

<header>

    <div class="main_header">
        <div class="container">
            {{-- <p>{{ Auth::user() }}</p> --}}
            @php
                $company = App\Models\Company::find(Auth::user()->company_id);
            @endphp

            <div class="logo">
                @php
                    $img = $company->images()->latest()->first();
                @endphp

                @if ($img)
                    <img src="{{ $img->url }}" style=" max-width: 120px; max-height: 75px; float: right;"
                        alt="Company Logo">
                @else
                    <!-- If no image is available -->
                    <img src="https://ryogasnative140203-dev.s3.ap-southeast-1.amazonaws.com/web-folder/logo/ryogas-only-logo.png"
                        style=" max-width: 120px; max-height: 75px; float: right;" alt="Company Logo">
                @endif
            </div>

            <ul class="comp_info">
                <li>{{ $company->name }}</li>
                <li>{{ $company->office_address }}</li>
                <li>{{ $company->contact_no }}</li>
            </ul>
        </div>
    </div>
    <p id="p1">
        Ledgers
        (
            @if (date('d-M-Y', strtotime($start_date ?? today())) != date('d-M-Y', strtotime($end_date ?? today())))
                {{ date('d/m/Y', strtotime($start_date)) }} to {{ date('d/m/Y', strtotime($end_date)) }}
            @else
                {{ date('d/m/Y', strtotime($start_date)) }}
            @endif
            )
        </p>
        <br>
</header>


<body>

    @include('de-accounting::ledgers.data-table-new')

</body>



</html>
