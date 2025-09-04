@extends('layouts.app-admin')
@section('title', 'Balance Sheet')

@section('content')
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Balance Sheet</h4>
                <h6>Manage your balance sheet report</h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body pb-1">
            <form action="{{ route('report.balance-sheet') }}" method="GET">
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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Company/Store</label>
                                    {{-- <select class="form-control" name="selected_id">
                                        <option value="">All</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                @if (request('selected_id') == $company->id) selected @endif>{{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="mb-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">View</button>
                            <a class="btn btn-secondary w-100"
                                href="{{ route('report.balance-sheet.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
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
                            <th class="text-end">Amount (Tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Dummy data for balance sheet
                            $start_date = '2025-03-01';
                            $end_date = '2025-03-31';

                            // Assets
                            $assetData = [
                                ['title' => 'Cash', 'total_amount' => 250000],
                                ['title' => 'Accounts Receivable', 'total_amount' => 150000],
                                ['title' => 'Inventory', 'total_amount' => 300000],
                                ['title' => 'Property, Plant & Equipment', 'total_amount' => 500000],
                            ];
                            $totalAssets = array_sum(array_column($assetData, 'total_amount'));

                            // Liabilities
                            $liabilityData = [
                                ['title' => 'Accounts Payable', 'total_amount' => 200000],
                                ['title' => 'Short-term Loans', 'total_amount' => 150000],
                                ['title' => 'Long-term Debt', 'total_amount' => 300000],
                            ];
                            $totalLiabilities = array_sum(array_column($liabilityData, 'total_amount'));

                            // Equity
                            $equityData = [
                                ['title' => 'Common Stock', 'total_amount' => 300000],
                                ['title' => 'Retained Earnings', 'total_amount' => 250000],
                            ];
                            $totalEquity = array_sum(array_column($equityData, 'total_amount'));

                            // Balance check
                            $balanceCheck = $totalAssets == $totalLiabilities + $totalEquity;
                        @endphp

                        <!-- Assets Section -->
                        <tr>
                            <td class="text-start fw-bold">Assets</td>
                            <td></td>
                        </tr>
                        @foreach ($assetData as $asset)
                            <tr>
                                <td class="text-start ps-4">&nbsp; &nbsp; {{ $asset['title'] }}</td>
                                <td class="text-end">{{ number_format($asset['total_amount'], 2) }}</td>
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
                                <td class="text-end">{{ number_format($liability['total_amount'], 2) }}</td>
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
                        @foreach ($equityData as $equity)
                            <tr>
                                <td class="text-start ps-4">&nbsp; &nbsp; {{ $equity['title'] }}</td>
                                <td class="text-end">{{ number_format($equity['total_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-start fw-bold">Total Equity</td>
                            <td class="text-end fw-bold">{{ number_format($totalEquity, 2) }}</td>
                        </tr>

                        <!-- Total Liabilities + Equity -->
                        <tr>
                            <td class="text-start fw-bold">Total Liabilities & Equity</td>
                            <td class="text-end fw-bold">{{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                        </tr>

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
            </div>
        </div>
    </div>
@endsection
