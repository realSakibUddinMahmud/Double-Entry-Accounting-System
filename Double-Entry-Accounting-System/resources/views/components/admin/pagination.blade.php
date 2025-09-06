@props([
    'paginator',
    'showInfo' => true,
    'infoText' => 'entries',
    'size' => 'sm' // sm, md, lg
])

@if($paginator->hasPages())
<div class="card-footer admin-pagination-footer">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        @if($showInfo)
        <div class="pagination-info">
            <span class="text-muted">
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} {{ $infoText }}
            </span>
        </div>
        @endif

        <nav aria-label="Pagination Navigation">
            <ul class="pagination pagination-{{ $size }} mb-0 admin-pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="ti ti-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="ti ti-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @if($paginator->lastPage() > 1)
                    @php
                        $start = max(1, $paginator->currentPage() - 2);
                        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                        
                        // Ensure we always show 5 pages when possible
                        if ($end - $start < 4) {
                            if ($start == 1) {
                                $end = min($paginator->lastPage(), $start + 4);
                            } else {
                                $start = max(1, $end - 4);
                            }
                        }
                    @endphp

                    {{-- First page --}}
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    {{-- Page range --}}
                    @for($i = $start; $i <= $end; $i++)
                        @if ($i == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $i }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                            </li>
                        @endif
                    @endfor

                    {{-- Last page --}}
                    @if($end < $paginator->lastPage())
                        @if($end < $paginator->lastPage() - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                        </li>
                    @endif
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
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
    </div>
</div>
@endif

@once
@push('styles')
<style>
    /* Admin Pagination Styling */
    .admin-pagination {
        margin: 0;
        gap: 2px;
    }
    
    .admin-pagination .page-link {
        border: 1px solid #e9ecef;
        color: #495057;
        background-color: #fff;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.15s ease-in-out;
        text-decoration: none;
    }
    
    .admin-pagination .page-link:hover {
        background-color: #fff5f2;
        border-color: #ff6b35;
        color: #ff6b35;
        text-decoration: none;
    }
    
    .admin-pagination .page-item.active .page-link {
        background-color: #ff6b35;
        border-color: #ff6b35;
        color: #fff;
    }
    
    .admin-pagination .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    
    .admin-pagination .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        outline: 0;
    }
    
    /* Card Footer Styling */
    .admin-pagination-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 1rem;
    }
    
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    /* Size variants */
    .admin-pagination.pagination-sm .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.8125rem;
    }
    
    .admin-pagination.pagination-lg .page-link {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .admin-pagination-footer .d-flex {
            flex-direction: column;
            align-items: center !important;
            gap: 1rem !important;
        }
        
        .admin-pagination {
            justify-content: center;
        }
        
        .admin-pagination .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.8125rem;
        }
    }
    
    @media (max-width: 576px) {
        .admin-pagination .page-link {
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush
@endonce
