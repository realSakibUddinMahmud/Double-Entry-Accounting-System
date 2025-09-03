@extends('layouts.app-admin')
@section('title', 'Trial Balance')

@section('content')
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Trial Balance</h4>
                <h6>Manage your trial balance report</h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body pb-1">
            <form action="{{ route('report.trial-balance') }}" method="GET">
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
                    <div class="col-lg-2">
                        <div class="mb-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">View</button>
                            <a class="btn btn-secondary w-100"
                                href="{{ route('report.trial-balance.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
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
                            <th class="text-end">Net Debit (Tk)</th>
                            <th class="text-end">Net Credit (Tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trialBalanceData as $category => $accounts)
                            <tr>
                                <td class="text-start fw-bold">{{ $category }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @foreach ($accounts as $account)
                                <tr>
                                    <td class="text-start ps-4">&nbsp; &nbsp; {{ $account['title'] }}</td>
                                    <td class="text-end">{{ number_format($account['debit'], 2) }}</td>
                                    <td class="text-end">{{ number_format($account['credit'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        <tr>
                            <td colspan="3" style="border: none; height: 20px;"></td>
                        </tr>
                        <tr>
                            <td class="text-start fw-bold">Total for Trial Balance</td>
                            <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-center fw-bold">
                                {{ $totalDebit == $totalCredit ? 'Balanced' : 'Not Balanced' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
