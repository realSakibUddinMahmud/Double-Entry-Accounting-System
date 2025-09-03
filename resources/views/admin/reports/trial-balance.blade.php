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
                                    <label class="form-label">Date</label>
                                    <div class="input-icon-start">
                                        <input type="date" name="date" class="form-control"
                                            value="{{ request('date', now()->toDateString()) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Report Type</label>
                                    <select class="form-control" name="report_type">
                                        <option value="summary" @if(request('report_type', 'summary') == 'summary') selected @endif>Summary Report</option>
                                        <option value="detail" @if(request('report_type') == 'detail') selected @endif>Detail Report</option>
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
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
                            </div> --}}
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="mb-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">View</button>
                            <button type="submit" class="btn btn-secondary w-100" name="format" value="pdf">PDF</button>
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
                            @if(count($accounts) > 0)
                            <tr>
                                <td class="text-start fw-bold">{{ $category }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @foreach ($accounts as $account)
                                    @php
                                        $detailCount = isset($trialBalanceDataDetailed[$category]) ? count(array_filter($trialBalanceDataDetailed[$category], function($item) use ($account) { return $item['title'] === $account['title']; })) : 0;
                                        $showExpandButton = $detailCount > 1;
                                        $isDetailReport = request('report_type') == 'detail';
                                        $initialDisplay = ($isDetailReport && $detailCount > 1) ? 'table-row' : 'none';
                                        $hasMultipleAccounts = $detailCount > 1;
                                        $hasSingleAccount = $detailCount == 1;
                                        $hasNoAccounts = $detailCount == 0;
                                    @endphp

                                    @if($hasMultipleAccounts)
                                        <!-- Group Row (Expandable) -->
                                        <tr class="summary-row" data-category="{{ $category }}" data-title="{{ $account['title'] }}">
                                            <td class="text-start ps-4">
                                                <div class="d-flex align-items-center">
                                                    <button class="btn btn-sm btn-link p-0 me-2 expand-btn" data-expanded="{{ $isDetailReport ? 'true' : 'false' }}">
                                                        <span class="expand-icon">{{ $isDetailReport ? '↓' : '→' }}</span>
                                                    </button>
                                                    <span class="fw-medium">{{ $account['title'] }}</span>
                                                </div>
                                            </td>
                                            @if ($account['type'] === 'debit')
                                                <td class="text-end">{{ $account['amount'] != 0 ? number_format($account['amount'], 2) : '-' }}</td>
                                                <td class="text-end"></td>
                                            @else
                                                <td class="text-end"></td>
                                                <td class="text-end">{{ $account['amount'] != 0 ? number_format($account['amount'], 2) : '-' }}</td>
                                            @endif
                                        </tr>

                                        <!-- Individual Account Rows - Only show if there are multiple accounts -->
                                        @if(isset($trialBalanceDataDetailed[$category]) && $detailCount > 1)
                                            @foreach($trialBalanceDataDetailed[$category] as $detailAccount)
                                                @if($detailAccount['title'] === $account['title'])
                                                    <tr class="detail-row" data-category="{{ $category }}" data-parent="{{ $account['title'] }}" style="display: {{ $initialDisplay }};">
                                                        <td class="text-start ps-5">
                                                            <div class="d-flex align-items-center">
                                                                <span class="me-3">└─</span>
                                                                <span>{{ $detailAccount['title'] }}</span>

                                                                @if($detailAccount['accountable_alias'] && $detailAccount['accountable_alias'] != 'Unknown')
                                                                    <small class="text-muted ms-2">- {{ $detailAccount['accountable_alias'] }}: {{ $detailAccount['accountable_name'] }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        @if ($detailAccount['type'] === 'debit')
                                                            <td class="text-end">
                                                                @if(isset($detailAccount['account_id']) && $detailAccount['account_id'])
                                                                    <a href="{{ route('report.account-transactions', $detailAccount['account_id']) }}?start_date={{ request('date', now()->toDateString()) }}&end_date={{ request('date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                                                       data-account-id="{{ $detailAccount['account_id'] }}" 
                                                                       data-account-title="{{ $detailAccount['title'] }}"
                                                                       data-start-date="{{ request('date', now()->toDateString()) }}"
                                                                       data-end-date="{{ request('date', now()->toDateString()) }}">
                                                                        {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                    </a>
                                                                @else
                                                                    {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                @endif
                                                            </td>
                                                            <td class="text-end"></td>
                                                        @else
                                                            <td class="text-end"></td>
                                                            <td class="text-end">
                                                                @if(isset($detailAccount['account_id']) && $detailAccount['account_id'])
                                                                    <a href="{{ route('report.account-transactions', $detailAccount['account_id']) }}?start_date={{ request('date', now()->toDateString()) }}&end_date={{ request('date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                                                       data-account-id="{{ $detailAccount['account_id'] }}" 
                                                                       data-account-title="{{ $detailAccount['title'] }}"
                                                                       data-start-date="{{ request('date', now()->toDateString()) }}"
                                                                       data-end-date="{{ request('date', now()->toDateString()) }}">
                                                                        {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                    </a>
                                                                @else
                                                                    {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                @endif
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @elseif($hasSingleAccount)
                                        <!-- Single Individual Account - Show Only Account (No Group Row) -->
                                        @foreach($trialBalanceDataDetailed[$category] as $detailAccount)
                                            @if($detailAccount['title'] === $account['title'])
                                                <tr>
                                                    <td class="text-start ps-4">{{ $detailAccount['title'] }}
                                                        @if($detailAccount['accountable_alias'] && $detailAccount['accountable_alias'] != 'Unknown')
                                                            <small class="text-muted ms-2">- {{ $detailAccount['accountable_alias'] }}: {{ $detailAccount['accountable_name'] }}</small>
                                                        @endif
                                                    </td>
                                                    @if ($detailAccount['type'] === 'debit')
                                                        <td class="text-end">
                                                            @if(isset($detailAccount['account_id']) && $detailAccount['account_id'])
                                                                <a href="{{ route('report.account-transactions', $detailAccount['account_id']) }}?start_date={{ request('date', now()->toDateString()) }}&end_date={{ request('date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                                                   data-account-id="{{ $detailAccount['account_id'] }}" 
                                                                   data-account-title="{{ $detailAccount['title'] }}"
                                                                   data-start-date="{{ request('date', now()->toDateString()) }}"
                                                                   data-end-date="{{ request('date', now()->toDateString()) }}">
                                                                    {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                </a>
                                                            @else
                                                                {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                            @endif
                                                        </td>
                                                        <td class="text-end"></td>
                                                    @else
                                                        <td class="text-end"></td>
                                                        <td class="text-end">
                                                            @if(isset($detailAccount['account_id']) && $detailAccount['account_id'])
                                                                <a href="{{ route('report.account-transactions', $detailAccount['account_id']) }}?start_date={{ request('date', now()->toDateString()) }}&end_date={{ request('date', now()->toDateString()) }}" class="account-drill-down text-decoration-none" 
                                                                   data-account-id="{{ $detailAccount['account_id'] }}" 
                                                                   data-account-title="{{ $detailAccount['title'] }}"
                                                                   data-start-date="{{ request('date', now()->toDateString()) }}"
                                                                   data-end-date="{{ request('date', now()->toDateString()) }}">
                                                                    {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                                </a>
                                                            @else
                                                                {{ $detailAccount['amount'] != 0 ? number_format($detailAccount['amount'], 2) : '-' }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <!-- No Individual Accounts - Show Group Only -->
                                        <tr>
                                            <td class="text-start ps-4">{{ $account['title'] }}</td>
                                    @if ($account['type'] === 'debit')
                                        <td class="text-end">{{ $account['amount'] != 0 ? number_format($account['amount'], 2) : '-' }}</td>
                                        <td class="text-end"></td>
                                    @else
                                        <td class="text-end"></td>
                                        <td class="text-end">{{ $account['amount'] != 0 ? number_format($account['amount'], 2) : '-' }}</td>
                                    @endif
                                </tr>
                                    @endif
                            @endforeach
                            @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing trial balance functionality...');

    const expandButtons = document.querySelectorAll('.expand-btn');
    console.log('Found expand buttons:', expandButtons.length);

    expandButtons.forEach(function(button, index) {
        console.log(`Setting up expand button ${index + 1}:`, button);

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Expand button clicked!');

            const summaryRow = this.closest('.summary-row');
            const category = summaryRow.getAttribute('data-category');
            const title = summaryRow.getAttribute('data-title');
            const isExpanded = this.getAttribute('data-expanded') === 'true';

            console.log('Button details:', { category, title, isExpanded });

            // Toggle expanded state
            if (isExpanded) {
                // Collapse
                this.setAttribute('data-expanded', 'false');
                this.querySelector('.expand-icon').textContent = '→';

                // Hide detail rows
                const detailRows = document.querySelectorAll(`.detail-row[data-category="${category}"][data-parent="${title}"]`);
                console.log('Hiding detail rows:', detailRows.length);
                detailRows.forEach(row => {
                    row.style.display = 'none';
                });
            } else {
                // Expand
                this.setAttribute('data-expanded', 'true');
                this.querySelector('.expand-icon').textContent = '↓';

                // Show detail rows
                const detailRows = document.querySelectorAll(`.detail-row[data-category="${category}"][data-parent="${title}"]`);
                console.log('Showing detail rows:', detailRows.length);
                detailRows.forEach(row => {
                    row.style.display = 'table-row';
                });
            }
        });
    });

    // Handle drill-down links
    const drillDownLinks = document.querySelectorAll('.account-drill-down');
    console.log('Found drill-down links:', drillDownLinks.length);

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

    // Auto-expand logic for detail report type
    const reportType = '{{ request("report_type", "summary") }}';
    if (reportType === 'detail') {
        console.log('Detail report detected, auto-expanding all accounts...');

        // Find all summary rows and expand them if they have detail rows
        const summaryRows = document.querySelectorAll('.summary-row');
        summaryRows.forEach(function(summaryRow) {
            const category = summaryRow.getAttribute('data-category');
            const title = summaryRow.getAttribute('data-title');
            const detailRows = document.querySelectorAll(`.detail-row[data-category="${category}"][data-parent="${title}"]`);

            if (detailRows.length > 0) {
                // Show detail rows
                detailRows.forEach(row => {
                    row.style.display = 'table-row';
                });

                // Update expand button state if it exists
                const expandBtn = summaryRow.querySelector('.expand-btn');
                if (expandBtn) {
                    expandBtn.setAttribute('data-expanded', 'true');
                    const expandIcon = expandBtn.querySelector('.expand-icon');
                    if (expandIcon) {
                        expandIcon.textContent = '↓';
                    }
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.account-drill-down {
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
}

.account-drill-down:hover {
    text-decoration: underline !important;
}

.expand-btn {
    color: #6c757d !important;
    transition: all 0.2s ease;
    border: none;
    background: none;
    padding: 0;
    margin: 0;
    line-height: 1;
    text-decoration: none !important;
}

.expand-btn:hover {
    color: #495057 !important;
    transform: scale(1.1);
}

.expand-icon {
    transition: transform 0.2s ease;
    font-size: 14px;
}

.summary-row {
    background-color: #f8f9fa;
}

.summary-row:hover {
    background-color: #e9ecef;
}

.detail-row {
    background-color: #ffffff;
}

.detail-row:hover {
    background-color: #f8f9fa;
}
</style>
@endpush
