{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/customers/index.blade.php --}}

@extends('layouts.app-admin')
@section('title', 'Customers')

@section('content')
@can('customer-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Customers</h4>
            <h6>Manage your Customers</h6>
        </div>
    </div>
    @can('customer-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-customer">
            <i class="ti ti-circle-plus me-1"></i>Add Customer
        </a>
    </div>
    @endcan
</div>

<!-- /customer list -->
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
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $customer->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ number_format($customer->total_paid, 2) }}</td>
                            <td>{{ number_format($customer->total_due, 2) }}</td>
                            <td>
                                @if($customer->status == false)
                                    <span class="badge bg-secondary">Archived</span>
                                @elseif($customer->status == true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-light text-dark">Unknown</span>
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('customer-show')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-customer"
                                       data-id="{{ $customer->id }}"
                                       data-name="{{ $customer->name }}"
                                       data-phone="{{ $customer->phone }}"
                                       data-email="{{ $customer->email }}"
                                       data-address="{{ $customer->address }}"
                                       data-status="{{ $customer->status }}"
                                       title="View Customer">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('customer-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-customer"
                                       data-id="{{ $customer->id }}"
                                       data-name="{{ $customer->name }}"
                                       data-phone="{{ $customer->phone }}"
                                       data-email="{{ $customer->email }}"
                                       data-address="{{ $customer->address }}"
                                       title="Edit Customer">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('customer-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $customer->id }}"
                                       title="Delete Customer">
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
<!-- /customer list -->

<!-- Include Modals Based on Permissions -->
@can('customer-create')
@include('admin.customers.create-modal')
@endcan

@can('customer-edit')
@include('admin.customers.edit-modal')
@endcan

@can('customer-delete')
@include('admin.customers.delete-modal')
@endcan

@can('customer-show')
@include('admin.customers.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view customers.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-customer');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var phone = button.getAttribute('data-phone');
            var email = button.getAttribute('data-email');
            var address = button.getAttribute('data-address');

            document.getElementById('editCustomerForm').action = "{{ url('customers') }}/" + id;

            document.getElementById('edit_customer_name').value = name;
            document.getElementById('edit_customer_phone').value = phone;
            document.getElementById('edit_customer_email').value = email;
            document.getElementById('edit_customer_address').value = address;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteCustomerForm').action = "{{ url('customers') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-customer');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            
            document.getElementById('view_customer_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_customer_phone').textContent = button.getAttribute('data-phone');
            document.getElementById('view_customer_email').textContent = button.getAttribute('data-email');
            document.getElementById('view_customer_address').textContent = button.getAttribute('data-address');
        });
    }
});
</script>