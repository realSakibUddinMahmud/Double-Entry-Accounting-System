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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Company/Store</label>
                                    <select class="form-control" name="selected_id">
                                        <option value="company" @if (request('selected_id', 'company') == 'company') selected @endif>Company</option>
                                        <option value="all-store" @if (request('selected_id') == 'all-store') selected @endif>All Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                @if (request('selected_id') == $store->id) selected @endif>{{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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
                        <!-- Operating Income -->
                        <tr>
                            <td class="text-start">Operating Income</td>
                            <td></td>
                        </tr>
                        @foreach ($salesIncomeAcData as $salesIncomeAc)
                            <tr>
                                <td class="text-start">&nbsp; &nbsp; {{ $salesIncomeAc->title }}</td>
                                <td class="text-end">
                                    <a href="{{ route('report.account-transactions', $salesIncomeAc->account_id) }}?start_date={{ request('start_date', now()->toDateString()) }}&end_date={{ request('end_date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                       data-account-id="{{ $salesIncomeAc->account_id }}" 
                                       data-account-title="{{ $salesIncomeAc->title }}"
                                       data-start-date="{{ request('start_date', now()->toDateString()) }}"
                                       data-end-date="{{ request('end_date', now()->toDateString()) }}">
                                        {{ number_format($salesIncomeAc->total_amount, 2) }}
                                    </a>
                                </td>
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
                                <td class="text-end">
                                    <a href="{{ route('report.account-transactions', $cogsAc->account_id) }}?start_date={{ request('start_date', now()->toDateString()) }}&end_date={{ request('end_date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                       data-account-id="{{ $cogsAc->account_id }}" 
                                       data-account-title="{{ $cogsAc->title }}"
                                       data-start-date="{{ request('start_date', now()->toDateString()) }}"
                                       data-end-date="{{ request('end_date', now()->toDateString()) }}">
                                        {{ number_format($cogsAc->total_amount, 2) }}
                                    </a>
                                </td>
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
                                <td class="text-end">
                                    <a href="{{ route('report.account-transactions', $otherIncomeAc->account_id) }}?start_date={{ request('start_date', now()->toDateString()) }}&end_date={{ request('end_date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                       data-account-id="{{ $otherIncomeAc->account_id }}" 
                                       data-account-title="{{ $otherIncomeAc->title }}"
                                       data-start-date="{{ request('start_date', now()->toDateString()) }}"
                                       data-end-date="{{ request('end_date', now()->toDateString()) }}">
                                        {{ number_format($otherIncomeAc->total_amount, 2) }}
                                    </a>
                                </td>
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
                                <td class="text-end">
                                    <a href="{{ route('report.account-transactions', $expenseAc->account_id) }}?start_date={{ request('start_date', now()->toDateString()) }}&end_date={{ request('end_date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                       data-account-id="{{ $expenseAc->account_id }}" 
                                       data-account-title="{{ $expenseAc->title }}"
                                       data-start-date="{{ request('start_date', now()->toDateString()) }}"
                                       data-end-date="{{ request('end_date', now()->toDateString()) }}">
                                        {{ number_format($expenseAc->total_amount, 2) }}
                                    </a>
                                </td>
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
            </div>
        </div>
    </div>

    </div>

@endsection

@push('js')
<script>
// Use vanilla JavaScript for drill-down functionality
document.addEventListener('DOMContentLoaded', function() {
    const drillDownLinks = document.querySelectorAll('.account-drill-down');
    
    drillDownLinks.forEach(function(link) {
        // Add hover effects
        link.addEventListener('mouseenter', function() {
            this.classList.add('text-primary', 'fw-bold');
        });
        
        link.addEventListener('mouseleave', function() {
            this.classList.remove('text-primary', 'fw-bold');
        });

        // Add click handler
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const accountId = this.getAttribute('data-account-id');
            const accountTitle = this.getAttribute('data-account-title');
            const startDate = this.getAttribute('data-start-date');
            const endDate = this.getAttribute('data-end-date');
            
            if (!accountId) {
                alert('Account ID is missing. Please check the data attributes.');
                return;
            }
            
            // Direct redirect to full page view
            const baseUrl = '{{ url("report/account-transactions") }}';
            const ledgerUrl = `${baseUrl}/${accountId}?start_date=${startDate}&end_date=${endDate}`;
            
            window.location.href = ledgerUrl;
        });
    });
});
</script>
@endpush

@push('css')
<style>
.account-drill-down {
    cursor: pointer;
    transition: all 0.2s ease;
    color: #0d6efd !important;
    font-weight: 500;
}

.account-drill-down:hover {
    text-decoration: underline !important;
    color: #0b5ed7 !important;
}
</style>
@endpush
