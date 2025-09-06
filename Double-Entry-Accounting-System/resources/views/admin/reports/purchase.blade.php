@extends('layouts.app-admin')
@section('title', 'Purchase Report')

@section('content')

        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Purchase Report</h4>
                    <h6>Manage your Purchase report</h6>
                </div>
            </div> 
        </div>
        <div class="card">
            <div class="card-body pb-1">
                <form action="{{ route('report.purchase') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-lg-10">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Start Date</label>
                                        <div class="input-icon-start">
                                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date', now()->toDateString()) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <div class="input-icon-start">
                                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date', now()->toDateString()) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Store</label>
                                        <select class="form-control" name="store_id">
                                            <option value="">All</option>
                                            @foreach($stores as $store)
                                                <option value="{{ $store->id }}" @if(request('store_id') == $store->id) selected @endif>{{ $store->name }}</option>
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
                                   href="{{ route('report.purchase.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
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
                                <th>Store</th>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>Purchased Qty</th>
                                <th>Purchased Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotalQty = 0;
                                $grandTotalAmount = 0;
                            @endphp
                            @foreach($reportRows as $storeName => $products)
                                @php
                                    $rowspan = count($products);
                                    $storeTotalQty = 0;
                                    $storeTotalAmount = 0;
                                    $first = true;
                                @endphp
                                @foreach($products as $row)
                                    @php
                                        $storeTotalQty += $row['purchased_qty'];
                                        $storeTotalAmount += $row['purchased_amount'];
                                        $grandTotalQty += $row['purchased_qty'];
                                        $grandTotalAmount += $row['purchased_amount'];
                                    @endphp
                                    <tr>
                                        @if($first)
                                            <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $storeName }}</td>
                                            @php $first = false; @endphp
                                        @endif
                                        <td>{{ $row['sku'] }}</td>
                                        <td>{{ $row['product_name'] }}</td>
                                        <td>{{ $row['unit'] }}</td>
                                        <td>{{ $row['purchased_qty'] }}</td>
                                        <td>{{ number_format($row['purchased_amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr style="background:#f9f9f9;">
                                    <th colspan="4" class="text-end">Total for {{ $storeName }}</th>
                                    <th>{{ $storeTotalQty }}</th>
                                    <th>{{ number_format($storeTotalAmount, 2) }}</th>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Grand Total</th>
                                <th>{{ $grandTotalQty }}</th>
                                <th>{{ number_format($grandTotalAmount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
@endsection