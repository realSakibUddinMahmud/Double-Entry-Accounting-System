@extends('layouts.app-admin')
@section('title', 'Suppliers')

@section('content')
@can('supplier-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Suppliers</h4>
            <h6>Manage your Suppliers</h6>
        </div>
    </div>
    @can('supplier-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-supplier">
            <i class="ti ti-circle-plus me-1"></i>Add Supplier
        </a>
    </div>
    @endcan
</div>
<!-- /supplier list -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <div class="search-input">
                <span class="btn-searchset">
                    <i class="ti ti-search fs-14 feather-search"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $supplier->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ number_format($supplier->total_paid, 2) }}</td>
                            <td>{{ number_format($supplier->total_due, 2) }}</td>
                            <td>
                                @if($supplier->status == 'Archived' || $supplier->status == false)
                                    <span class="badge bg-secondary">Archived</span>
                                @elseif($supplier->status == 'Active' || $supplier->status == true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-light text-dark">Unknown</span>
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('supplier-show')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-supplier"
                                       data-id="{{ $supplier->id }}"
                                       data-name="{{ $supplier->name }}"
                                       data-contact_person="{{ $supplier->contact_person }}"
                                       data-phone="{{ $supplier->phone }}"
                                       data-email="{{ $supplier->email }}"
                                       data-address="{{ $supplier->address }}"
                                       data-status="{{ $supplier->status }}"
                                       title="View Supplier">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('supplier-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-supplier"
                                       data-id="{{ $supplier->id }}"
                                       data-name="{{ $supplier->name }}"
                                       data-contact_person="{{ $supplier->contact_person }}"
                                       data-phone="{{ $supplier->phone }}"
                                       data-email="{{ $supplier->email }}"
                                       data-address="{{ $supplier->address }}"
                                       title="Edit Supplier">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('supplier-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $supplier->id }}"
                                       title="Delete Supplier">
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
<!-- /supplier list -->

<!-- Include Modals Based on Permissions -->
@can('supplier-create')
@include('admin.suppliers.create-modal')
@endcan

@can('supplier-edit')
@include('admin.suppliers.edit-modal')
@endcan

@can('supplier-delete')
@include('admin.suppliers.delete-modal')
@endcan

@can('supplier-show')
@include('admin.suppliers.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view suppliers.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-supplier');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var contact_person = button.getAttribute('data-contact_person');
            var phone = button.getAttribute('data-phone');
            var email = button.getAttribute('data-email');
            var address = button.getAttribute('data-address');

            document.getElementById('editSupplierForm').action = "{{ url('suppliers') }}/" + id;

            document.getElementById('edit_supplier_name').value = name;
            document.getElementById('edit_supplier_contact_person').value = contact_person;
            document.getElementById('edit_supplier_phone').value = phone;
            document.getElementById('edit_supplier_email').value = email;
            document.getElementById('edit_supplier_address').value = address;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteSupplierForm').action = "{{ url('suppliers') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-supplier');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            
            document.getElementById('view_supplier_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_supplier_contact_person').textContent = button.getAttribute('data-contact_person');
            document.getElementById('view_supplier_phone').textContent = button.getAttribute('data-phone');
            document.getElementById('view_supplier_email').textContent = button.getAttribute('data-email');
            document.getElementById('view_supplier_address').textContent = button.getAttribute('data-address');
            document.getElementById('view_supplier_status').innerHTML =
        button.getAttribute('data-status') == 'Archived' || button.getAttribute('data-status') == '0'
            ? '<span class="badge bg-secondary">Archived</span>'
            : '<span class="badge bg-success">Active</span>';
        });
    }
});
</script>