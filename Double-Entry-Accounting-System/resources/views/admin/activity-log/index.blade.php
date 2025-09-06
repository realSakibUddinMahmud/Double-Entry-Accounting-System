@extends('layouts.app-admin')

@section('title', 'Activity Log')

@section('content')
@can('user-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Activity Log</h4>
            <h6>Track all system activities and changes</h6>
        </div>
    </div>
</div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted text-uppercase fw-semibold">Today</span>
                                <h4 class="mb-0">{{ number_format($stats['today']) }}</h4>
                            </div>
                            <div class="flex-shrink-0 text-end">
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-soft-success rounded fs-3">
                                        <i class="ti ti-calendar-check text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted text-uppercase fw-semibold">This Week</span>
                                <h4 class="mb-0">{{ number_format($stats['week']) }}</h4>
                            </div>
                            <div class="flex-shrink-0 text-end">
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-soft-warning rounded fs-3">
                                        <i class="ti ti-calendar-week text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted text-uppercase fw-semibold">This Month</span>
                                <h4 class="mb-0">{{ number_format($stats['month']) }}</h4>
                            </div>
                            <div class="flex-shrink-0 text-end">
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-soft-info rounded fs-3">
                                        <i class="ti ti-calendar text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
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
            <div class="card-body">
                <form method="GET" action="{{ route('admin.activity-log.index') }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Event Type</label>
                        <select name="event" class="form-select">
                            <option value="">All Events</option>
                            @foreach($events as $eventType)
                                <option value="{{ $eventType }}" {{ $event == $eventType ? 'selected' : '' }}>
                                    {{ ucfirst($eventType) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Model/Table</label>
                        <select name="model" class="form-select">
                            <option value="">All Models</option>
                            @foreach($models as $modelType)
                                <option value="{{ $modelType }}" {{ $model == $modelType ? 'selected' : '' }}>
                                    @if(class_basename($modelType) === 'DeJournal')
                                        Journal
                                    @else
                                        {{ class_basename($modelType) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">User</label>
                        <select name="user" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $userId => $userName)
                                <option value="{{ $userId }}" {{ $user == $userId ? 'selected' : '' }}>
                                    {{ $userName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date Range</label>
                        <select name="date_range" class="form-select">
                            <option value="all" {{ $dateRange == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ti ti-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-secondary">
                            <i class="ti ti-refresh"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h4 class="card-title mb-0">Activity Timeline</h4>
            </div>
            <div class="card-body p-0">
                @if($audits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover activity-log-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 10%;">Event</th>
                                    <th style="width: 12%;">Model</th>
                                    <th style="width: 18%;">User</th>
                                    <th style="width: 30%;">Details</th>
                                    <th style="width: 20%;">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($audits as $audit)
                                    <tr>
                                        <td>
                                            @switch($audit->event)
                                                @case('created')
                                                    <span class="badge bg-success">Created</span>
                                                    @break
                                                @case('updated')
                                                    <span class="badge bg-warning">Updated</span>
                                                    @break
                                                @case('deleted')
                                                    <span class="badge bg-danger">Deleted</span>
                                                    @break
                                                @case('restored')
                                                    <span class="badge bg-info">Restored</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($audit->event) }}
                                            @endswitch
                                        </td>
                                        <td>
                                            <strong>
                                                @if(class_basename($audit->auditable_type) === 'DeJournal')
                                                    Journal
                                                @else
                                                    {{ class_basename($audit->auditable_type) }}
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <strong>{{ $audit->user_name ?? 'System' }}</strong>
                                            @if($audit->user_email)
                                                <br>
                                                <small class="text-muted">{{ $audit->user_email }}</small>
                                            @endif
                                        </td>
                                        <td class="details-column">
                                            @if($audit->old_values || $audit->new_values)
                                                <div class="audit-description">
                                                    <p class="mb-2 text-primary">
                                                        <i class="ti ti-info-circle me-1"></i>
                                                        {{ \App\Helpers\AuditDataHelper::formatAuditData(
                                                            $audit->old_values, 
                                                            $audit->new_values, 
                                                            $audit->event, 
                                                            $audit->auditable_type, 
                                                            $audit->auditable_id
                                                        ) }}
                                                    </p>
                                                    
                                                    @if(class_basename($audit->auditable_type) === 'DeJournal' && isset($audit->journal_connection))
                                                        @if($audit->journal_connection['related_model'])
                                                            <div class="journal-connection-info mt-2 p-2 bg-light rounded">
                                                                <small class="text-muted d-block mb-1">
                                                                    <i class="ti ti-link me-1"></i>
                                                                    <strong>Connected to:</strong> {{ $audit->journal_connection['display_name'] }}
                                                                </small>
                                                                @if($audit->journal_connection['transaction_type'])
                                                                    <small class="text-muted d-block mb-1">
                                                                        <i class="ti ti-tag me-1"></i>
                                                                        <strong>Type:</strong> {{ $audit->journal_connection['transaction_type'] }}
                                                                    </small>
                                                                @endif
                                                                @if($audit->journal_connection['amount'])
                                                                    <small class="text-muted d-block mb-1">
                                                                        <i class="ti ti-currency-dollar me-1"></i>
                                                                        <strong>Amount:</strong> {{ number_format($audit->journal_connection['amount'], 2) }}
                                                                    </small>
                                                                @endif
                                                                @if($audit->journal_connection['note'])
                                                                    <small class="text-muted d-block">
                                                                        <i class="ti ti-note me-1"></i>
                                                                        <strong>Note:</strong> {{ $audit->journal_connection['note'] }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($audit->event !== 'deleted')
                                                        @if(class_basename($audit->auditable_type) === 'Sale')
                                                            <a href="{{ route('sales.show', $audit->auditable_id) }}" class="btn btn-sm btn-primary">
                                                                <i class="ti ti-eye me-1"></i>View Sale
                                                            </a>
                                                        @elseif(class_basename($audit->auditable_type) === 'Purchase')
                                                            <a href="{{ route('purchases.show', $audit->auditable_id) }}" class="btn btn-sm btn-info">
                                                                <i class="ti ti-eye me-1"></i>View Purchase
                                                            </a>
                                                        @elseif(class_basename($audit->auditable_type) === 'DeJournal' && isset($audit->journal_connection))
                                                            @if($audit->journal_connection['related_model'])
                                                                @if(class_basename($audit->journal_connection['journalable_type']) === 'Sale')
                                                                    <a href="{{ route('sales.show', $audit->journal_connection['journalable_id']) }}" class="btn btn-sm btn-primary">
                                                                        <i class="ti ti-eye me-1"></i>View Sale
                                                                    </a>
                                                                @elseif(class_basename($audit->journal_connection['journalable_type']) === 'Purchase')
                                                                    <a href="{{ route('purchases.show', $audit->journal_connection['journalable_id']) }}" class="btn btn-sm btn-info">
                                                                        <i class="ti ti-eye me-1"></i>View Purchase
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No changes recorded</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $audit->created_at->format('M d, Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $audit->created_at->format('H:i:s') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $audit->created_at->diffForHumans() }}</small>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-top">
                        <div class="pagination-info">
                            <small class="text-muted">
                                Showing {{ $audits->firstItem() ?? 0 }} to {{ $audits->lastItem() ?? 0 }} of {{ $audits->total() }} entries
                            </small>
                        </div>
                        
                        @if($audits->hasPages())
                            <nav aria-label="Activity log pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($audits->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="ti ti-chevron-left"></i>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $audits->previousPageUrl() }}" rel="prev">
                                                <i class="ti ti-chevron-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($audits->getUrlRange(1, $audits->lastPage()) as $page => $url)
                                        @if ($page == $audits->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($audits->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $audits->nextPageUrl() }}" rel="next">
                                                <i class="ti ti-chevron-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="ti ti-chevron-right"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-history display-1 text-muted"></i>
                        <h4 class="mt-3">No Activity Found</h4>
                        <p class="text-muted">There are no activity logs to display for the selected filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view activity logs.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan

<style>
.activity-log-table {
    font-size: 0.875rem;
    table-layout: fixed;
}

.activity-log-table th,
.activity-log-table td {
    vertical-align: top;
    padding: 0.75rem 0.5rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
    height: auto;
    min-height: 60px;
}

.activity-log-table .audit-description p {
    font-size: 0.8rem;
    line-height: 1.4;
    word-wrap: break-word;
    overflow-wrap: break-word;
    margin-bottom: 0.5rem;
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
}

.activity-log-table .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    margin-top: 0.25rem;
    white-space: nowrap;
    display: inline-block;
}

.activity-log-table .details-column {
    width: 30%;
    word-wrap: break-word;
    overflow-wrap: break-word;
    overflow: visible;
    white-space: normal;
    height: auto;
}

.activity-log-table .details-column .audit-description {
    height: auto;
    min-height: 40px;
}

.activity-log-table tbody tr {
    height: auto;
}

.activity-log-table tbody td {
    height: auto;
    vertical-align: top;
}

/* Ensure text content doesn't get cut off */
.activity-log-table .audit-description {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
}

/* Journal connection info styling */
.journal-connection-info {
    border-left: 3px solid #0d6efd;
    background-color: #f8f9fa !important;
    border: 1px solid #e9ecef;
}

.journal-connection-info small {
    font-size: 0.75rem;
    line-height: 1.3;
}

.journal-connection-info .text-muted {
    color: #6c757d !important;
}

.journal-connection-info strong {
    color: #495057;
    font-weight: 600;
}

.activity-log-table .audit-description p {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
    word-break: break-word;
}

@media (max-width: 768px) {
    .activity-log-table {
        font-size: 0.8rem;
    }
    
    .activity-log-table th,
    .activity-log-table td {
        padding: 0.5rem 0.25rem;
        min-height: 50px;
    }
    
    .activity-log-table .btn {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
}

@media (max-width: 576px) {
    .activity-log-table th,
    .activity-log-table td {
        padding: 0.4rem 0.2rem;
        min-height: 45px;
    }
    
    .activity-log-table .btn {
        font-size: 0.6rem;
        padding: 0.15rem 0.3rem;
    }
}
</style>
@endsection

@push('styles')
<style>
/* Custom Pagination Styling */
.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    color: #6c757d;
    background-color: #fff;
    transition: all 0.15s ease-in-out;
}

.pagination-sm .page-link:hover {
    color: #495057;
    background-color: #e9ecef;
    border-color: #dee2e6;
    text-decoration: none;
}

.pagination-sm .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.pagination-sm .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 0.875rem;
}

/* Table styling improvements */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}

.table th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

/* Badge improvements */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Collapse button styling */
.btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

/* Pre tag styling for JSON data */
pre {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 0.5rem;
    margin: 0;
    font-size: 0.75rem;
    line-height: 1.4;
    max-height: 150px;
    overflow-y: auto;
}

/* Audit description styling */
.audit-description p {
    font-size: 0.875rem;
    line-height: 1.4;
    margin-bottom: 0.75rem;
}

.audit-description .btn-outline-secondary {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.audit-description .text-primary {
    color: #0d6efd !important;
    font-weight: 500;
}
</style>
@endpush
