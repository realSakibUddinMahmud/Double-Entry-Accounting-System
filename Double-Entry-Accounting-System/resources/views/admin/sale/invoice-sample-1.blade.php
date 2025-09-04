{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/sale/show.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Sale Invoice')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Sale Invoice</h4>
        <h6>Sale #{{ $sale->id }}</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('sales.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Sales List
        </a>
        <button onclick="printInvoice()" class="btn btn-secondary ms-2">
            <i class="ti ti-printer me-1"></i>Print / PDF
        </button>
    </div>
</div>

<div class="card" id="invoice-area">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Customer</h5>
                <p>
                    <strong>{{ optional($sale->customer)->name ?? '-' }}</strong><br>
                    @if(optional($sale->customer)->email) {{ $sale->customer->email }}<br>@endif
                    @if(optional($sale->customer)->phone) {{ $sale->customer->phone }}<br>@endif
                    @if(optional($sale->customer)->address) {{ $sale->customer->address }}<br>@endif
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h5>Invoice Info</h5>
                <p>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}<br>
                    <strong>Store:</strong> {{ optional($sale->store)->name ?? '-' }}<br>
                    <strong>Invoice #:</strong> {{ $sale->u_id ?? $sale->id }}<br>
                    <strong>Payment Status:</strong>
                    @if(strtolower($sale->payment_status) == 'paid')
                        <span class="badge bg-success">Paid</span>
                    @elseif(strtolower($sale->payment_status) == 'partial')
                        <span class="badge bg-warning">Partial</span>
                    @else
                        <span class="badge bg-danger">Pending</span>
                    @endif
                </p>
            </div>
        </div>

        <h5 class="mb-3">Sale Items</h5>
        <div class="table-responsive no-break">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($item->product)->name ?? '-' }}</td>
                            <td>{{ optional($item->unit)->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->per_unit_price, 2) }}</td>
                            <td>{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <td class="text-end bg-light">Subtotal:</td>
                            <td class="text-end">{{ number_format($sale->items->sum('total'), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">+ Shipping Cost:</td>
                            <td class="text-end">{{ number_format($sale->shipping_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">- Discount:</td>
                            <td class="text-end">{{ number_format($sale->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">+ Tax:</td>
                            <td class="text-end">
                                @if($sale->tax && $sale->total_tax)
                                    {{ $sale->tax->name }} ({{ number_format($sale->total_tax, 2) }})
                                @else
                                    0.00
                                @endif
                            </td>
                        </tr>
                        <tr class="table-primary">
                            <td class="text-end bg-light"><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">Paid:</td>
                            <td class="text-end text-success">{{ number_format($sale->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">Due:</td>
                            <td class="text-end text-danger">{{ number_format($sale->due_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        @if($sale->note)
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>Note:</strong>
                    <div class="border p-2">{{ $sale->note }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #invoice-area, #invoice-area * {
        visibility: visible !important;
    }
    #invoice-area {
        width: 210mm;
        padding: 20mm 15mm 20mm 15mm;
        background: #fff !important;
        color: #000 !important;
        box-shadow: none !important;
        margin: 0 auto;
    }
    .page-header, .page-btn, .d-print-none {
        display: none !important;
    }
    .card, .card-body, .card-header {
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
    .no-break, .no-break * {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function printInvoice() {
    window.print();
}
</script>
@endpush