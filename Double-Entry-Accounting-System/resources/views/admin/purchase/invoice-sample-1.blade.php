{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/purchase/show.blade.php --}}
@extends('layouts.app-admin')
@section('title', 'Purchase Invoice')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Purchase Invoice</h4>
        <h6>Purchase #{{ $purchase->id }}</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('purchases.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Purchase List
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
                <h5>Supplier</h5>
                <p>
                    <strong>{{ optional($purchase->supplier)->name ?? '-' }}</strong><br>
                    @if(optional($purchase->supplier)->email) {{ $purchase->supplier->email }}<br>@endif
                    @if(optional($purchase->supplier)->phone) {{ $purchase->supplier->phone }}<br>@endif
                    @if(optional($purchase->supplier)->address) {{ $purchase->supplier->address }}<br>@endif
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h5>Invoice Info</h5>
                <p>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}<br>
                    <strong>Store:</strong> {{ optional($purchase->store)->name ?? '-' }}<br>
                    <strong>Invoice #:</strong> {{ $purchase->id }}<br>
                    <strong>Payment Status:</strong>
                    @if($purchase->payment_status == 'paid')
                        <span class="badge bg-success">Paid</span>
                    @elseif($purchase->payment_status == 'partial')
                        <span class="badge bg-warning">Partial</span>
                    @else
                        <span class="badge bg-danger">Pending</span>
                    @endif
                </p>
            </div>
        </div>

        <h5 class="mb-3">Purchase Items</h5>
        <div class="table-responsive no-break">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($item->product)->name ?? '-' }}</td>
                            <td>{{ optional($item->unit)->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->per_unit_cost, 2) }}</td>
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
                            <td class="text-end">{{ number_format($purchase->items->sum('total'), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">+ Shipping Cost:</td>
                            <td class="text-end">{{ number_format($purchase->shipping_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">- Discount:</td>
                            <td class="text-end">{{ number_format($purchase->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">+ Tax:</td>
                            <td class="text-end">
                                @if($purchase->tax && $purchase->total_tax)
                                    {{ $purchase->tax->name }} ({{ number_format($purchase->total_tax, 2) }})
                                @else
                                    0.00
                                @endif
                            </td>
                        </tr>
                        <tr class="table-primary">
                            <td class="text-end bg-light"><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong>{{ number_format($purchase->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">Paid:</td>
                            <td class="text-end text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end bg-light">Due:</td>
                            <td class="text-end text-danger">{{ number_format($purchase->due_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        @if($purchase->note)
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>Note:</strong>
                    <div class="border p-2">{{ $purchase->note }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    /* Only your previous print styles, no print-footer */
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