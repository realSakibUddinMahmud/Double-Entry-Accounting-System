@extends('layouts.app-admin')
@section('title', 'Income Statement')

@section('content')
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Income Statement</h4>
                <h6>Manage your income statement report</h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body pb-1">
            <form action="{{ route('report.income-statement') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-10">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <div class="input-icon-start">
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ request('start_date', now()->toDateString()) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <div class="input-icon-start">
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ request('end_date', now()->toDateString()) }}">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Company/Store</label>
                                    <select class="form-control" name="selected_id">
                                        <option value="">All</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                @if (request('selected_id') == $company->id) selected @endif>{{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="mb-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">View</button>
                            <a class="btn btn-secondary w-100"
                                href="{{ route('report.income-statement.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card no-search">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-start">Account</th>
                            <th class="text-end">Total (Tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Use demo values
                            $totalRevenue = $totalSales = 500000;
                            $cogs = $cogs = 300000;
                            $shippingCharge = 5000;
                            $incomeData = collect([
                                (object) ['title' => 'Interest Income', 'total_amount' => 5000],
                                (object) ['title' => 'Commission Received', 'total_amount' => 10000],
                            ]);
                            $expenseData = collect([
                                (object) ['title' => 'Rent Expense', 'total_amount' => 40000],
                                (object) ['title' => 'Salary Expense', 'total_amount' => 60000],
                            ]);
                            $grossProfit = $totalRevenue + $shippingCharge - $cogs;
                            $otherIncomeTotal = $incomeData->sum('total_amount');
                            $totalIncome = $grossProfit + $otherIncomeTotal;
                            $totalExpenses = $expenseData->sum('total_amount');
                            $netProfit = $totalIncome - $totalExpenses;
                        @endphp

                        <!-- Operating Income -->
                        <tr>
                            <td class="text-start">Operating Income</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-start">&nbsp; &nbsp; Sales</td>
                            <td class="text-end">{{ number_format($totalRevenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start ps-4">&nbsp; &nbsp;  Shipping Charge</td>
                            <td class="text-end">{{ number_format($shippingCharge, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-bold">Total for Operating Income</td>
                            <td class="text-end fw-bold">{{ number_format($totalRevenue + $shippingCharge, 2) }}</td>
                        </tr>

                        <!-- COGS and Gross Profit -->
                        <tr>
                            <td class="text-start fw-bold">Cost of Goods Sold</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-start">&nbsp; &nbsp; Cost of Goods Sold</td>
                            <td class="text-end">({{ number_format($cogs, 2) }})</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold">Gross Profit/Loss</td>
                            <td class="text-end fw-bold">{{ number_format($grossProfit, 2) }}</td>
                        </tr>

                        <!-- Other Income -->
                        <tr>
                            <td class="text-start fw-bold">Add: Other Income</td>
                            <td></td>
                        </tr>
                        {{-- @foreach ($incomeData as $income)
                            <tr>
                                <td class="text-start ps-4">{{ $income->title }}</td>
                                <td class="text-end">{{ number_format($income->total_amount, 2) }}</td>
                            </tr>
                        @endforeach --}}
                        <tr>
                            <td class="text-start fw-bold">Other Income Total</td>
                            <td class="text-end fw-bold">{{ number_format($otherIncomeTotal, 2) }}</td>
                        </tr>

                        <!-- Total Income -->
                        <tr>
                            <td class="text-start fw-bold">Total Income</td>
                            <td class="text-end fw-bold">{{ number_format($totalIncome, 2) }}</td>
                        </tr>

                        <!-- Expenses -->
                        <tr>
                            <td class="text-start fw-bold">Less: Expenses</td>
                            <td></td>
                        </tr>
                        {{-- @foreach ($expenseData as $expense)
                            <tr>
                                <td class="text-start ps-4">{{ $expense->title }}</td>
                                <td class="text-end">{{ number_format($expense->total_amount, 2) }}</td>
                            </tr>
                        @endforeach --}}
                        <tr>
                            <td class="text-start fw-bold">Total Expenses</td>
                            <td class="text-end fw-bold">({{ number_format($totalExpenses, 2) }})</td>
                        </tr>

                        <!-- Net Profit -->
                        <tr>
                            <td class="text-center fw-bold">Net Profit/Loss</td>
                            <td class="text-end fw-bold">{{ number_format($netProfit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
