@extends('layouts.app-admin')
@section('title', 'Stock Report')

@section('content')

        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Stock Report</h4>
                    <h6>View your stock by store and product</h6>
                </div>
            </div> 
        </div>
        <div class="card">
            <div class="card-body pb-1">
                <form action="{{ route('report.stock') }}" method="GET">
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
                                   href="{{ route('report.stock.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
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
                                <th>Initial Stock</th>
                                <th>Purchase Qty</th>
                                <th>Sales Qty</th>
                                <th>Adjustment Qty</th>
                                <th>InStock Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportRows as $storeName => $products)
                                @php
                                    $rowspan = count($products);
                                    $first = true;
                                    $storeInitialStock = 0;
                                    $storePurchaseQty = 0;
                                    $storeSalesQty = 0;
                                    $storeAdjustmentQty = 0;
                                    $storeInStockQty = 0;
                                @endphp
                                @foreach($products as $row)
                                    @php
                                        $storeInitialStock += $row['initial_stock'] ?? 0;
                                        $storePurchaseQty += $row['purchase_qty'];
                                        $storeSalesQty += $row['sales_qty'];
                                        $storeAdjustmentQty += $row['adjustment_total'];
                                        $storeInStockQty += $row['instock_qty'];
                                    @endphp
                                    <tr>
                                        @if($first)
                                            <td rowspan="{{ $rowspan }}" style="vertical-align: middle;">{{ $storeName }}</td>
                                            @php $first = false; @endphp
                                        @endif
                                        <td>{{ $row['sku'] }}</td>
                                        <td>{{ $row['product_name'] }}</td>
                                        <td>{{ $row['unit'] }}</td>
                                        <td>{{ $row['initial_stock'] ?? 0 }}</td>
                                        <td>{{ $row['purchase_qty'] }}</td>
                                        <td>{{ $row['sales_qty'] }}</td>
                                        <td>{{ $row['adjustment_qty'] }}</td>
                                        <td>{{ $row['instock_qty'] }}</td>
                                    </tr>
                                @endforeach
                                <tr style="background:#f9f9f9;">
                                    <th colspan="4" class="text-end">Total for {{ $storeName }}</th>
                                    <th>{{ $storeInitialStock }}</th>
                                    <th>{{ $storePurchaseQty }}</th>
                                    <th>{{ $storeSalesQty }}</th>
                                    <th>{{ $storeAdjustmentQty }}</th>
                                    <th>{{ $storeInStockQty }}</th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /stock list -->
@endsection