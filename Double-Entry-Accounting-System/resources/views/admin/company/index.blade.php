@extends('layouts.app-admin')
@section('title', 'Companies')

@section('content')
@can('company-view')
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>All Companies</h4>
                <h6>Manage your Companies</h6>
            </div>
        </div>
        @can('company-create')
        <div class="page-btn">
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>Add Company
            </a>
        </div>
        @endcan
    </div>

    <div class="row">
        <!-- Total Companies -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="avatar avatar-lg bg-primary flex-shrink-0">
                            <i class="ti ti-building fs-16"></i>
                        </span>
                        <div class="ms-2 overflow-hidden">
                            <p class="fs-12 fw-medium mb-1 text-truncate">Total Companies</p>
                            <h4>{{ $totalCompanies }}</h4>
                        </div>
                    </div>
                    <div id="total-chart"></div>
                </div>
            </div>
        </div>

        <!-- Active Companies -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="avatar avatar-lg bg-success flex-shrink-0">
                            <i class="ti ti-building fs-16"></i>
                        </span>
                        <div class="ms-2 overflow-hidden">
                            <p class="fs-12 fw-medium mb-1 text-truncate">Active Companies</p>
                            <h4>{{ $activeCompanies }}</h4>
                        </div>
                    </div>
                    <div id="active-chart"></div>
                </div>
            </div>
        </div>

        <!-- Inactive Companies -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="avatar avatar-lg bg-danger flex-shrink-0">
                            <i class="ti ti-building fs-16"></i>
                        </span>
                        <div class="ms-2 overflow-hidden">
                            <p class="fs-12 fw-medium mb-1 text-truncate">Inactive Companies</p>
                            <h4>{{ $inactiveCompanies }}</h4>
                        </div>
                    </div>
                    <div id="inactive-chart"></div>
                </div>
            </div>
        </div>

        <!-- Company Location -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="avatar avatar-lg bg-skyblue flex-shrink-0">
                            <i class="ti ti-map-pin-check fs-16"></i>
                        </span>
                        <div class="ms-2 overflow-hidden">
                            <p class="fs-12 fw-medium mb-1 text-truncate">Company Regions</p>
                            <h4>{{ $totalRegions }}</h4>
                        </div>
                    </div>
                    <div id="location-chart"></div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Company list -->
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
                <table class="table datatable table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="no-sort">
                                <label class="checkboxs">
                                    <input type="checkbox" id="select-all">
                                    <span class="checkmarks"></span>
                                </label>
                            </th>
                            <th>Name</th>
                            <th>Region</th>
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Domain Name(s)</th>
                            <th>Status</th>
                            <th class="no-sort"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox" value="{{ $company->id }}">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">
                                    {{ $company->name }}
                                </td>
                                <td>
                                    {{ $company->region ?? '-' }}
                                </td>
                                <td>
                                    {{ $company->contact_no }}
                                </td>
                                <td>
                                    {{ $company->email ?? '-' }}
                                </td>
                                <td>
                                    @if($company->domains && $company->domains->count())
                                        @foreach($company->domains as $domain)
                                            <div>{{ $domain }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($company->status === 'ACTIVE')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        @can('company-user-manage')
                                        <a class="me-2 p-2" href="{{ route('companies.users', $company->id) }}" data-bs-toggle="tooltip" title="Manage Users">
                                            <i data-feather="user" class="feather-user"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('company-user-manage')
                                        <a class="me-2 p-2" href="javascript:void(0);" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addUserModal"
                                            data-company-id="{{ $company->id }}"
                                            data-company-name="{{ $company->name }}"
                                            title="Add User">
                                            <i data-feather="user-plus" class="feather-user-plus"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('company-view')
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal"
                                            data-bs-target="#viewCompanyModal" data-id="{{ $company->id }}"
                                            data-name="{{ $company->name }}" data-region="{{ $company->region }}"
                                            data-contact_no="{{ $company->contact_no }}"
                                            data-email="{{ $company->email }}"
                                            data-office_address="{{ $company->office_address }}"
                                            data-status="{{ $company->status }}"
                                            title="View Company">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('company-edit')
                                        <a class="me-2 p-2" href="{{ route('companies.edit', $company->id) }}" title="Edit Company">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('company-delete')
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal"
                                            data-bs-target="#deleteCompanyModal" data-id="{{ $company->id }}" title="Delete Company">
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
    <!-- /Company list -->

    @can('company-delete')
    @include('admin.company.delete-modal')
    @endcan

    @can('company-view')
    @include('admin.company.view-modal')
    @endcan

    @can('company-user-manage')
    @include('admin.company.add-user-modal')
    @endcan

@else
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <h4>Access Denied</h4>
                <p>You don't have permission to view companies.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
@endcan
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete Modal
        var deleteModal = document.getElementById('deleteCompanyModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button) return;
                var id = button.getAttribute('data-id');
                document.getElementById('deleteCompanyForm').action = "{{ url('companies') }}/" + id;
            });
        }

        // View Modal
        var viewModal = document.getElementById('viewCompanyModal');
        if (viewModal) {
            viewModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button) return;
                document.getElementById('view_company_name').textContent = button.getAttribute('data-name');
                document.getElementById('view_company_region').textContent = button.getAttribute('data-region') || '-';
                document.getElementById('view_company_contact_no').textContent = button.getAttribute('data-contact_no');
                document.getElementById('view_company_email').textContent = button.getAttribute('data-email') || '-';
                document.getElementById('view_company_office_address').textContent = button.getAttribute('data-office_address') || '-';
                document.getElementById('view_company_status').textContent = button.getAttribute('data-status') === 'ACTIVE' ? 'Active' : 'Inactive';
            });
        }
    });
</script>

