@extends('layouts.app-admin')
@section('title', 'Stock Adjustments')

@section('content')
@can('stock-adjustment-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>All Stock Adjustments</h4>
            <h6>Manage your Stock Adjustments</h6>
        </div>
    </div>
    @can('stock-adjustment-create')
    <div class="page-btn">
        <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>Add Stock Adjustment
        </a>
    </div>
    @endcan
</div>

<!-- /stock adjustment list -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="ms-auto search-set">
            <div class="search-input">
                <span class="btn-searchset">
                    <i class="ti ti-search fs-14 feather-search"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Date</th>
                        <th>Store</th>
                        <th>Note</th>
                        <th>User</th>
                        <th>Adjusted At</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockAdjustments as $adjustment)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $adjustment->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($adjustment->date)->format('d/m/Y') }}</td>
                            <td>{{ optional($adjustment->store)->name ?? '-' }}</td>
                            <td>{{ $adjustment->note }}</td>
                            <td>{{ optional($adjustment->user)->name ?? '-' }}</td>
                            <td>{{ $adjustment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="action-table-data">
                                <div class="edit-delete-action d-flex align-items-center">
                                    @can('stock-adjustment-show')
                                    <a class="me-2 p-2"
                                       href="{{ route('stock-adjustments.show', $adjustment->id) }}"
                                       title="View Adjustment">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('stock-adjustment-edit')
                                    <a class="me-2 p-2"
                                       href="{{ route('stock-adjustments.edit', $adjustment->id) }}"
                                       title="Edit">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('stock-adjustment-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteStockAdjustmentModal"
                                       data-id="{{ $adjustment->id }}"
                                       title="Delete">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Note:Nothing will be add here --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- /stock adjustment list -->

@can('stock-adjustment-delete')
@include('admin.stock-adjustment.delete-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view stock adjustments.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete Modal
    var deleteModal = document.getElementById('deleteStockAdjustmentModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteStockAdjustmentForm').action = "{{ url('stock-adjustments') }}/" + id;
        });
    }
});
</script>
@endpush





