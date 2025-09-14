@if ($paginator->hasPages())
    <nav aria-label="Paginação" class="d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
            {{-- Link para página anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Anterior</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Anterior</span>
                    </a>
                </li>
            @endif

            {{-- Link para próxima página --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span class="d-none d-sm-inline me-1">Próximo</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">
                        <span class="d-none d-sm-inline me-1">Próximo</span>
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    {{-- CSS personalizado para paginação simples --}}
    <style>
        .pagination-sm .page-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            color: #495057;
            background-color: #fff;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            display: flex;
            align-items: center;
        }

        .pagination-sm .page-link:hover {
            color: #0056b3;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .pagination-sm .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.65;
            cursor: not-allowed;
        }

        .pagination-sm .page-item {
            margin: 0 0.25rem;
        }

        @media (max-width: 576px) {
            .pagination-sm .page-link {
                padding: 0.375rem 0.75rem;
                font-size: 0.8rem;
            }
        }
    </style>
@endif
