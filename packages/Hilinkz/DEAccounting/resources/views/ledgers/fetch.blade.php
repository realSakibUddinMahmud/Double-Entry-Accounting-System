@extends('admin.layouts.new_admin')

@section('custom_style')
    @include('styles.general')
    @include('styles.data-table')
@endsection

@section('content')

    <!-- Content Header -->
    <section class="content" id="content-header">
        <div class="container-fluid">
            <div class="row">
                @include('admin.layouts.impersonate-leave')
                <div class="col-12">
                    <div class="card m-0 p-0">
                        <div class="card-body py-0">
                            <div class="row">
                                <div class="col-md-6 offset-md-3 d-flex justify-content-center align-items-center">
                                    <p class="page-name m-0">Ledgers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content Header Ends -->

    <!-- Main Content -->
    <section class="content my-3">
        <div class="container-fluid">
            <!-- Card with search filters -->
            <div class="card card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <form method="GET" class="row">
                            <div class="col-sm-2">
                                <label>From</label>
                                <input type="date" class="form-control form-control-sm" name="start_date"
                                    value="{{ date('Y-m-d', strtotime($start_date ?? (app('request')->input('start_date') ?? today()))) }}"
                                    data-date-format="dd/mm/yy" max="{{ date('Y-m-d', strtotime(today())) }}">
                            </div>
                            <div class="col-sm-2">
                                <label>To</label>
                                <input type="date" class="form-control form-control-sm" name="end_date"
                                    value="{{ date('Y-m-d', strtotime($end_date ?? (app('request')->input('end_date') ?? today()))) }}"
                                    data-date-format="dd/mm/yy" max="{{ date('Y-m-d', strtotime(today())) }}">
                            </div>
                            <div class="col-sm-3">
                                <label>Fuel Station</label>
                                @php
                                    $activeGasStations = App\Models\GasStation::where('status', 'ACTIVE')
                                        ->oldest('name')
                                        ->get();
                                @endphp
                                <select class="form-control form-control-sm" name="gs_id" id='gs_id'>
                                    @if (app('request')->input('gs_id'))
                                        @php
                                            $selected_gs = App\Models\GasStation::where('status', 'ACTIVE')->find(app('request')->input('gs_id'));
                                        @endphp
                                        @if ($selected_gs)
                                            <option value="{{ $selected_gs->id }}">{{ $selected_gs->name }}</option>
                                        @endif
                                    @endif
                                    <option value="NATIVE"> Company's Native AC</option>
                                    <option value="{{ null }}"> All Station </option>
                                    @foreach ($activeGasStations as $active_gs)
                                        @if (app('request')->input('gs_id') && app('request')->input('gs_id') != $active_gs->id)
                                            <option value="{{ $active_gs->id }}">{{ $active_gs->name }}</option>
                                        @else
                                            <option value="{{ $active_gs->id }}">{{ $active_gs->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label>Account</label>

                                <select class="form-control form-control-sm" name="account_title" id='account_title'
                                    required autocomplete="false">
                                    @if (app('request')->input('account_title'))
                                        @php
                                            $selected_ac = App\Models\Account::where('status', 'ACTIVE')
                                                ->where('title', app('request')->input('account_title'))
                                                ->first();
                                        @endphp
                                        @if ($selected_ac)
                                            <option value="{{ $selected_ac->title }}">{{ $selected_ac->title }}</option>
                                        @endif
                                    @endif
                                    <option> Select an account </option>
                                    @foreach ($accounts as $account)
                                        @if (app('request')->input('account_title') && app('request')->input('account_title') != $account->title)
                                            <option value="{{ $account->title }}">{{ $account->title }}</option>
                                        @else
                                            <option value="{{ $account->title }}">{{ $account->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2 d-flex-sm">
                                <label>&nbsp;</label>
                                <br>
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center" onclick="myPreloader()">
                                    View </button>
                                <button type="submit" class="btn btn-dark btn-md d-inline-flex align-items-center" name="download"
                                    value="YES">PDF</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card with fetched data -->
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Note</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Current Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through the fetched ledger data -->
                            @php
                                $total_debit_amount = 0;
                                $total_credit_amount = 0;
                                $balance_amount = 0;
                            @endphp
                            @foreach($ledgers as $ledger)
                                <!-- Output each data field -->
                                <tr>
                                    <td>{{ $ledger->date }}</td>
                                    <td>{{ $ledger->title }}</td>
                                    <td>{{ $ledger->note }}</td>
                                    <td>{{ $ledger->debit }}</td>
                                    <td>{{ $ledger->credit }}</td>
                                    <td>{{ $ledger->cbalance }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- Main Content Ends -->
@endsection
