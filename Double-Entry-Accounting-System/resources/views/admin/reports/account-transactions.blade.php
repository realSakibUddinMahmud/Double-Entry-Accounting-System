@extends('layouts.app-admin')
@section('title', 'Account Transactions - ' . $account->title)

@section('content')
    <div class="page-header">
        <div class="add-item d-flex">
                    <div class="page-title">
            <h4>Account Transactions</h4>
            <h6>
                {{ $account->title }} - 
                @if($isCumulativeBalance)
                    Cumulative Balance as of {{ date('d M Y', strtotime($displayEndDate)) }}
                @else
                    Transactions from {{ date('d M Y', strtotime($displayStartDate)) }} to {{ date('d M Y', strtotime($displayEndDate)) }}
                @endif
            </h6>
        </div>
        </div>
        <ul class="table-top-head">
            <li>
                <button onclick="window.history.back()" class="btn btn-sm btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </button>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body pb-1">
            @if($isCumulativeBalance)
                <div class="alert alert-info mb-3">
                    <i class="feather-info me-2"></i>
                    <strong>Cumulative Balance View:</strong> Showing all transactions up to {{ date('d M Y', strtotime($displayEndDate)) }}
                </div>
            @endif
            <form action="{{ route('report.account-transactions', $accountId) }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    @if($isCumulativeBalance)
                                        <input type="text" class="form-control" value="Beginning" readonly>
                                        <input type="hidden" name="start_date" value="{{ $displayEndDate }}">
                                    @else
                                        <input type="date" name="start_date" class="form-control" value="{{ $displayStartDate }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $displayEndDate }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a class="btn btn-success" href="{{ route('report.account-transactions', $accountId) }}?start_date={{ $displayStartDate !== 'Beginning' ? $displayStartDate : $displayEndDate }}&end_date={{ $displayEndDate }}&export=pdf">
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card no-search">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $account->title }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 20%;">Account</th>
                            <th style="width: 20%;">Transaction Details</th>
                            <th style="width: 18%;">Transaction Type</th>
                            <th style="width: 10%;" class="text-end">Debit</th>
                            <th style="width: 10%;" class="text-end">Credit</th>
                            <th style="width: 10%;" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Use opening balance from stored procedure
                            $runningBalance = $openingBalance;
                        @endphp
                        
                        <!-- Opening Balance Row -->
                        <tr class="table-info">
                            <td>
                                @if($isCumulativeBalance)
                                    Beginning Balance
                                @else
                                    As On {{ date('d M Y', strtotime($displayStartDate)) }}
                                @endif
                            </td>
                            <td><strong>Opening Balance</strong></td>
                            <td>-</td>
                            <td>-</td>
                            <td class="text-end">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end">
                                <strong>
                                    {{ number_format(abs($openingBalance), 2) }}
                                    @if($account->root_type == 1 || $account->root_type == 2)
                                        {{ $openingBalance >= 0 ? ' Dr' : ' Cr' }}
                                    @else
                                        {{ $openingBalance >= 0 ? ' Cr' : ' Dr' }}
                                    @endif
                                </strong>
                            </td>
                        </tr>

                        @forelse($journals as $journal)
                            @php
                                $isDebit = $journal->debitTransaction && $journal->debitTransaction->account_id == $accountId;
                                $contraAccount = $isDebit ? 
                                    ($journal->creditTransaction ? $journal->creditTransaction->account->title : 'N/A') :
                                    ($journal->debitTransaction ? $journal->debitTransaction->account->title : 'N/A');
                                
                                $debitAmount = $isDebit ? $journal->amount : 0;
                                $creditAmount = $isDebit ? 0 : $journal->amount;
                                
                                // Calculate running balance
                                $transactionAmount = $debitAmount - $creditAmount;
                                $runningBalance += $transactionAmount;

                                // Determine transaction type and details
                                $transactionType = '';
                                $transactionNumber = '';
                                $transactionDetails = $journal->note ?? '-';
                                $invoiceUrl = '';

                                if($journal->journalable_type && $journal->journalable_id) {
                                    try {
                                        // Use the journalable alias attribute from the model
                                        $journalableAlias = $journal->journalable_alias;
                                        
                                        if ($journalableAlias && $journal->journalable) {
                                            if (in_array($journalableAlias, ['Sale', 'Purchase'])) {
                                                $transactionTypeLabel = $journalableAlias; // Use Sale/Purchase directly
                                                if (isset($journal->journalable->u_id)) {
                                                    $transactionNumber = $journal->journalable->u_id;
                                                    $transactionType = $transactionTypeLabel . ' - ' . $transactionNumber;
                                                } else {
                                                    $transactionType = $transactionTypeLabel;
                                                }
                                                
                                                // Set invoice URL for clickable amounts
                                                if ($journalableAlias == 'Sale') {
                                                    $invoiceUrl = route('sales.show', $journal->journalable_id);
                                                } elseif ($journalableAlias == 'Purchase') {
                                                    $invoiceUrl = route('purchases.show', $journal->journalable_id);
                                                }
                                                
                                                // Get customer/supplier name for transaction details
                                                if ($journalableAlias == 'Sale' && isset($journal->journalable->customer)) {
                                                    $transactionDetails = $journal->journalable->customer->name ?? 'Unknown Customer';
                                                } elseif ($journalableAlias == 'Purchase' && isset($journal->journalable->supplier)) {
                                                    $transactionDetails = $journal->journalable->supplier->name ?? 'Unknown Supplier';
                                                }
                                            } else {
                                                $transactionType = $journalableAlias;
                                                if (isset($journal->journalable->name)) {
                                                    $transactionDetails = $journal->journalable->name;
                                                } elseif (isset($journal->journalable->first_name) && isset($journal->journalable->last_name)) {
                                                    $transactionDetails = $journal->journalable->first_name . ' ' . $journal->journalable->last_name;
                                                }
                                            }
                                        } else {
                                            // Fallback to just using the alias or class name
                                            $transactionType = $journalableAlias ?: class_basename($journal->journalable_type);
                                        }
                                    } catch (Exception $e) {
                                        $transactionType = 'Manual Entry';
                                    }
                                } else {
                                    $transactionType = 'Manual Entry';
                                }
                            @endphp
                            <tr>
                                <td>{{ date('d M Y', strtotime($journal->date)) }}</td>
                                <td>{{ $contraAccount }}</td>
                                <td>{{ $transactionDetails }}</td>
                                <td>{{ $transactionType ?: 'Manual Entry' }}</td>
                                <td class="text-end">
                                    @if($debitAmount > 0)
                                        @if($invoiceUrl)
                                            <a href="{{ $invoiceUrl }}" class="text-decoration-none">
                                                <span class="fw-bold text-primary">{{ number_format($debitAmount, 2) }}</span>
                                            </a>
                                        @else
                                            <span class="fw-bold">{{ number_format($debitAmount, 2) }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($creditAmount > 0)
                                        @if($invoiceUrl)
                                            <a href="{{ $invoiceUrl }}" class="text-decoration-none">
                                                <span class="fw-bold text-primary">{{ number_format($creditAmount, 2) }}</span>
                                            </a>
                                        @else
                                            <span class="fw-bold">{{ number_format($creditAmount, 2) }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    @if($invoiceUrl)
                                        <a href="{{ $invoiceUrl }}" class="text-decoration-none">
                                            <span class="text-primary">
                                                {{ number_format(abs($runningBalance), 2) }}
                                                @if(in_array($account->type, ['Asset', 'Expense']))
                                                    {{ $runningBalance >= 0 ? ' Dr' : ' Cr' }}
                                                @else
                                                    {{ $runningBalance >= 0 ? ' Cr' : ' Dr' }}
                                                @endif
                                            </span>
                                        </a>
                                    @else
                                        {{ number_format(abs($runningBalance), 2) }}
                                        @if(in_array($account->type, ['Asset', 'Expense']))
                                            {{ $runningBalance >= 0 ? ' Dr' : ' Cr' }}
                                        @else
                                            {{ $runningBalance >= 0 ? ' Cr' : ' Dr' }}
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <em>No transactions found for the selected date range</em>
                                </td>
                            </tr>
                        @endforelse

                        @if($journals->count() > 0)
                            <!-- Closing Balance Row -->
                            <tr class="table-success">
                                <td>As On {{ date('d M Y', strtotime($displayEndDate)) }}</td>
                                <td><strong>Closing Balance</strong></td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                                <td class="text-end">
                                    <strong>
                                        {{ number_format(abs($runningBalance), 2) }}
                                        @if($account->root_type == 1 || $account->root_type == 2)
                                            {{ $runningBalance >= 0 ? ' Dr' : ' Cr' }}
                                        @else
                                            {{ $runningBalance >= 0 ? ' Cr' : ' Dr' }}
                                        @endif
                                    </strong>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            @if($journals->hasPages())
                <div class="card-footer">
                    {{ $journals->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Account Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Account Name:</strong></td>
                            <td>{{ $account->title }}</td>
                        </tr>
                        <tr>
                            <td><strong>Account Type:</strong></td>
                            <td>
                                @switch($account->root_type)
                                    @case(1) Asset @break
                                    @case(2) Expense @break
                                    @case(3) Liability @break
                                    @case(4) Income @break
                                    @case(5) Equity @break
                                    @default Unknown
                                @endswitch
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Period Summary</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Report Period:</strong></td>
                            <td>
                                @if($isCumulativeBalance)
                                    Beginning to {{ date('d M Y', strtotime($displayEndDate)) }}
                                @else
                                    {{ date('d M Y', strtotime($displayStartDate)) }} to {{ date('d M Y', strtotime($displayEndDate)) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total Transactions:</strong></td>
                            <td>{{ $journals->total() ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
