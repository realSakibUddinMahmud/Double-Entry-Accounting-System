{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/sale/show.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Sale Details')

@section('content')
    <div class="page-header">
        <div class="page-title">
            <h4>Sale Invoice</h4>
            <h6>Sale #{{ $sale->u_id }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('sales.index') }}" class="btn btn-primary">
                <i class="ti ti-list me-1"></i>Sales List
            </a>
            <button onclick="showActivityHistory()" class="btn btn-info ms-2">
                <i class="ti ti-history me-1"></i>Activity History
            </button>
            <button onclick="printInvoice()" class="btn btn-secondary ms-2">
                <i class="ti ti-printer me-1"></i>Print / PDF
            </button>
        </div>
    </div>

    <div class="card" id="invoice-area">
        <div class="card-body">
            {{-- --- Begin invoice content from "show" --- --}}
            <div class="row justify-content-between align-items-center border-bottom mb-3">
                <div id="toplogo" class="col-md-6">
                    @php
                        $companyId = Auth::user()->tenant_id;
                        $company = \App\Models\Company::find($companyId);
                        $logo = $company->images()->latest()->first();
                    @endphp
                    <div id="botlogo" class="mb-2">
                        @if ($logo)
                            <img src="{{ asset('storage/' . $logo->path) }}" width="130" class="img-fluid"
                                alt="logo">
                        @else
                            <img src="{{ asset('assets/img/ryofin-logo.png') }}" width="130" class="img-fluid"
                                alt="logo">
                        @endif
                    </div>
                    <p>
                        @if ($company)
                            {{ $company->office_address }}
                        @else
                            {{ config('app.address', 'Your Company Address') }}
                        @endif
                    </p>
                </div>
                <div id="invno" class="col-md-6">
                    <div class="text-end mb-3">
                        <h5 class="text-gray mb-1">
                            Invoice No <span class="text-primary">#{{ $sale->u_id ?? $sale->id }}</span>
                        </h5>
                        <p class="mb-1 fw-medium">
                            Created Date : <span
                                class="text-dark">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</span>
                        </p>
                        <p class="fw-medium">
                            Payment Status :
                            {{-- Display payment status with color coding --}}
                            @if ($sale->payment_status == 'Paid')
                                <span class="bg-success text-white fs-10 px-1 rounded"><i
                                        class="ti ti-point-filled"></i>Paid</span>
                            @elseif($sale->payment_status == 'Partial')
                                <span class="bg-warning text-white fs-10 px-1 rounded"><i
                                        class="ti ti-point-filled"></i>Partial</span>
                            @else
                                <span class="bg-danger text-white fs-10 px-1 rounded"><i
                                        class="ti ti-point-filled"></i>Pending</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="row border-bottom mb-3">
                <div id="frominfo" class="col-md-6">
                    <p class="text-dark mb-2 fw-semibold">From</p>
                    <div>
                        <h4 class="mb-1">{{ optional($sale->store)->name ?? '-' }}</h4>
                        <p class="mb-1">{{ optional($sale->store)->address ?? '-' }}</p>
                        <p class="mb-1">Email : <span class="text-dark">{{ optional($sale->store)->email ?? '-' }}</span>
                        </p>
                        <p>Phone : <span class="text-dark">{{ optional($sale->store)->contact_no ?? '-' }}</span></p>
                    </div>
                </div>
                <div id="toinfo" class="col-md-6">
                    <p class="text-dark mb-2 fw-semibold">To</p>
                    <div>
                        <h4 class="mb-1">{{ optional($sale->customer)->name ?? '-' }}</h4>
                        <p class="mb-1">{{ optional($sale->customer)->address ?? '-' }}</p>
                        <p class="mb-1">Email : <span
                                class="text-dark">{{ optional($sale->customer)->email ?? '-' }}</span></p>
                        <p>Phone : <span class="text-dark">{{ optional($sale->customer)->phone ?? '-' }}</span></p>
                    </div>
                </div>
            </div>
            <div>
                <p class="fw-medium">Invoice For : <span
                        class="text-dark fw-medium">{{ $sale->description ?? 'Sales Order' }}</span></p>
                <div class="table-responsive mb-3">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Sl</th>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ optional($item->product)->name ?? '-' }}</td>
                                    <td class="text-gray-9 fw-medium text-end">{{ $item->quantity }}</td>
                                    <td class="text-gray-9 fw-medium text-end">{{ optional($item->unit)->name ?? '-' }}
                                    </td>
                                    <td class="text-gray-9 fw-medium text-end">
                                        {{ number_format($item->per_unit_price, 2) }}</td>
                                    <td class="text-gray-9 fw-medium text-end">
                                        {{ number_format($item->tax_amount, 2) }}
                                        ({{ optional($item->productStore)->tax_method == 'exclusive' ? 'Exc' : 'Inc' }})
                                    </td>
                                    <td class="text-gray-9 fw-medium text-end">{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row border-bottom mb-3">
                <div class="col-md-5 ms-auto mb-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
                        <p class="mb-0">Sub Total</p>
                        <p class="text-dark fw-medium mb-2">{{ number_format($sale->items->sum('total'), 2) }}</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
                        <p class="mb-0">Discount</p>
                        <p class="text-dark fw-medium mb-2">{{ number_format($sale->discount_amount, 2) }}</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">Shipping</p>
                        <p class="text-dark fw-medium mb-2">{{ number_format($sale->shipping_cost, 2) }}</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">Tax</p>
                        <p class="text-dark fw-medium mb-2">
                            {{ number_format($sale->total_tax, 2) }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <h5>Total Amount</h5>
                        <h5>{{ number_format($sale->total_amount, 2) }}</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">Paid</p>
                        <p class="text-success fw-medium mb-2">{{ number_format($sale->paid_amount, 2) }}</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">Due</p>
                        <p class="text-danger fw-medium mb-2">{{ number_format($sale->due_amount, 2) }}</p>
                    </div>
                    <p class="fs-12">
                        Paid Amount in Words:
                        {{ ucwords(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($sale->paid_amount)) }}
                        Taka
                    </p>
                </div>
            </div>
            <div class="row align-items-center border-bottom mb-3">
                <div class="col-md-7">
                    <div>
                        <div class="mb-3">
                            <h6 class="mb-1">Notes</h6>
                            <p>{{ $sale->note ?? 'Please quote invoice number when remitting funds.' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="text-end mb-5" style="height:60px;">
                        {{-- Signature area intentionally left blank --}}
                    </div>
                    <div class="text-end mb-3">
                        <span class="d-inline-block" style="min-width:160px;">
                            <h6 class="fs-14 fw-medium mb-0">{{ config('app.authorized_person', 'Authorized Person') }}
                            </h6>
                            <p class="mb-0">{{ config('app.authorized_designation', 'Manager') }}</p>
                        </span>
                    </div>
                </div>
            </div>
            {{-- --- End invoice content from "show" --- --}}
        </div>
    </div>

    {{-- Journal Display Section - Only show if config allows --}}
    @php
        $companyId = Auth::user()->tenant_id;
        $company = \App\Models\Company::find($companyId);
        $showJournals = $company && $company->config && 
                       json_decode($company->config, true)['show_journal_in_sale_invoice'] ?? false;
    @endphp

    @if($showJournals && $sale->journals->count() > 0)
        <div class="card mt-3" id="journal-section">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ti ti-book me-2"></i>
                    Journal Entries for Sale #{{ $sale->u_id }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Account Title</th>
                                <th class="text-end">Debit (Tk)</th>
                                <th class="text-end">Credit (Tk)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_amount = 0;
                            @endphp
                            
                            @foreach($sale->journals()->orderBy('date')->get() as $journal)
                                @if($journal->debitTransaction && $journal->debitTransaction->account)
                                    <tr>
                                        <td class="text-start">{{ $journal->debitTransaction->account->title }}</td>
                                        <td class="text-end">{{ number_format($journal->debitTransaction->amount, 2) }}</td>
                                        <td class="text-end">-</td>
                                    </tr>
                                    @php
                                        $total_amount += $journal->debitTransaction->amount;
                                    @endphp
                                @endif
                                
                                @if($journal->creditTransaction && $journal->creditTransaction->account)
                                    <tr>
                                        <td class="text-start">{{ $journal->creditTransaction->account->title }}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">{{ number_format($journal->creditTransaction->amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- Grand Total Row --}}
                            <tr class="table-dark" style="font-weight: bold;">
                                <td class="text-end">Total:</td>
                                <td class="text-end">{{ number_format($total_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Activity History Floating Card --}}
    <div id="activity-history-card" class="activity-history-card">
        <div class="activity-history-header">
            <h5 class="mb-0">
                <i class="ti ti-history me-2"></i>
                Activity History - Sale #{{ $sale->u_id }}
            </h5>
            <button type="button" class="btn-close" onclick="hideActivityHistory()" aria-label="Close"></button>
        </div>
        <div class="activity-history-body">
            <div class="activity-history-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading activity history...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Activity History Floating Card Styles */
        .activity-history-card {
            position: fixed;
            top: 0;
            right: -500px;
            width: 500px;
            height: 100vh;
            background: #fff;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1050;
            transition: right 0.3s ease-in-out;
            border-left: 1px solid #e9ecef;
        }

        .activity-history-card.show {
            right: 0;
        }

        .activity-history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .activity-history-header h5 {
            color: #333;
            font-weight: 600;
        }

        .activity-history-body {
            height: calc(100vh - 80px);
            overflow-y: auto;
            padding: 1rem;
        }

        .activity-history-content {
            min-height: 200px;
        }

        .activity-item {
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            margin-bottom: 0.75rem;
            background: #fff;
        }

        .activity-item:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .activity-event {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .activity-event.created {
            background: #d1ecf1;
            color: #0c5460;
        }

        .activity-event.updated {
            background: #fff3cd;
            color: #856404;
        }

        .activity-event.deleted {
            background: #f8d7da;
            color: #721c24;
        }

        .activity-time {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .activity-user {
            font-size: 0.875rem;
            color: #495057;
            font-weight: 500;
        }

        .activity-description {
            margin-top: 0.5rem;
            color: #333;
            line-height: 1.5;
        }

        /* Overlay for background */
        .activity-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1040;
            display: none;
        }

        .activity-overlay.show {
            display: block;
        }

        @media print {
            body * {
                visibility: hidden !important;
            }

            #invoice-area,
            #invoice-area * {
                visibility: visible !important;
            }

            /* Hide journal section completely during printing */
            #journal-section,
            #journal-section * {
                display: none !important;
                visibility: hidden !important;
            }

            @page {
                size: A4;
                margin: 5mm 8mm 1mm 8mm;
                /* Standard margin: top/right/bottom/left */
            }

            #invoice-area {
                width: auto;
                padding: 0;
                background: #fff !important;
                color: #000 !important;
                box-shadow: none !important;
                margin: 0;
            }

            /* Add this new rule to target the header row specifically */
            .row.justify-content-between.align-items-center.border-bottom.mb-3,
            .card-body {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            /* Add these specific rules to target the logo space */
            #toplogo {
                margin-top: -50px !important;
                /* Pulls the logo section up */
                padding-top: -0px !important;
            }

            .page-header,
            .page-btn,
            .d-print-none,
            .d-flex.justify-content-center.align-items-center.mb-4 {
                display: none !important;
            }

            .card,
            .card-body,
            .card-header {
                border: none !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            table {
                background: #fff !important;
            }

            #invoice-area .row.justify-content-end,
            #invoice-area .col-md-6,
            #invoice-area table.table-bordered {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .no-break,
            .no-break * {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Table adjustments */
            table {
                background: #fff !important;
                font-size: 11px;
                margin-top: 2px !important;
            }

            th,
            td {
                padding: 2px 4px !important;
                /* Slightly more compact */
                line-height: 1.2 !important;
            }

            /* Spacing reductions */
            .mb-3,
            .mb-2 {
                margin-bottom: 0.3rem !important;
            }

            .border-bottom {
                padding-bottom: 0.3rem !important;
                margin-bottom: 0.3rem !important;
            }

            /* Signature area */
            .text-end.mb-5 {
                height: 25px !important;
                /* Further reduced */
                margin-bottom: 0.3rem !important;
            }

            /* Totals section */
            .col-md-5.ms-auto {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            /* Prevent any unexpected breaks */
            #invoice-area .row,
            #invoice-area .col-md-6,
            #invoice-area table {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            #frominfo {
                /* margin-bottom: 10px !important; */
                padding-bottom: 18px !important;
            }

            #toinfo {
                /* margin-bottom: 10px !important; */
                padding-bottom: 3px !important;
            }

            #invno {
                margin-top: -70px !important;
                /* Pulls the invoice number section up */
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Get sale ID from the page
        const saleId = {{ $sale->id }};
        
        function printInvoice() {
            window.print();
        }

        function showActivityHistory() {
            const card = document.getElementById('activity-history-card');
            card.classList.add('show');
            
            // Load activity history data
            loadActivityHistory();
        }

        function hideActivityHistory() {
            const card = document.getElementById('activity-history-card');
            card.classList.remove('show');
        }

        function loadActivityHistory() {
            const contentDiv = document.querySelector('.activity-history-content');
            
            // Show loading state
            contentDiv.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading activity history...</p>
                </div>
            `;

            // Fetch activity history data
            console.log('Fetching activity history for sale ID:', saleId);
            fetch(`/activity-log/sale/${saleId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Activity history data:', data);
                    if (data.success && data.activities && data.activities.length > 0) {
                        displayActivities(data.activities);
                    } else {
                        contentDiv.innerHTML = `
                            <div class="text-center py-4">
                                <i class="ti ti-info-circle text-muted" style="font-size: 3rem;"></i>
                                <p class="mt-2 text-muted">No activity history found for this sale.</p>
                                <small class="text-muted">Total activities: ${data.total || 0}</small>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading activity history:', error);
                    contentDiv.innerHTML = `
                        <div class="text-center py-4">
                            <i class="ti ti-alert-circle text-danger" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-danger">Failed to load activity history.</p>
                            <small class="text-muted">Error: ${error.message}</small>
                            <br>
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadActivityHistory()">
                                <i class="ti ti-refresh me-1"></i>Retry
                            </button>
                        </div>
                    `;
                });
        }

        function displayActivities(activities) {
            const contentDiv = document.querySelector('.activity-history-content');
            
            let html = '<div class="mb-3"><small class="text-muted"><i class="ti ti-info-circle me-1"></i>Showing latest activities first</small></div>';
            
            activities.forEach((activity, index) => {
                const eventClass = activity.event.toLowerCase();
                const eventText = activity.event.charAt(0).toUpperCase() + activity.event.slice(1);
                const timeAgo = activity.time_ago;
                const userName = activity.user_name || 'System';
                const description = activity.description;
                
                html += `
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="activity-event ${eventClass}">${eventText}</span>
                            <small class="activity-time">${timeAgo}</small>
                        </div>
                        <div class="activity-user">
                            <i class="ti ti-user me-1"></i>${userName}
                        </div>
                        <div class="activity-description">
                            ${description}
                        </div>
                    </div>
                `;
            });
            
            contentDiv.innerHTML = html;
        }

        // Close card when clicking outside
        document.addEventListener('click', function(event) {
            const card = document.getElementById('activity-history-card');
            const button = event.target.closest('button[onclick="showActivityHistory()"]');
            
            if (!card.contains(event.target) && !button && card.classList.contains('show')) {
                hideActivityHistory();
            }
        });

        // Close card on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideActivityHistory();
            }
        });
    </script>
@endpush
