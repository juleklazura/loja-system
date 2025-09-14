@if ($paginator->hasPages())
    <nav aria-label="Paginação" class="d-flex justify-content-between align-items-center">
        <!-- Info sobre resultados -->
        <div class="pagination-info text-sm text-muted">
            <span>
                Mostrando 
                <strong>{{ $paginator->firstItem() }}</strong>
                a 
                <strong>{{ $paginator->lastItem() }}</strong>
                de 
                <strong>{{ $paginator->total() }}</strong>
                {{ str()->plural('resultado', $paginator->total()) }}
            </span>
        </div>

        <!-- Links de paginação -->
        <ul class="pagination pagination-sm mb-0">
            {{-- Link para página anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Links das páginas --}}
            @foreach ($elements as $element)
                {{-- "Três pontos" --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array de links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link para próxima página --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    {{-- CSS personalizado para paginação --}}
    <style>
        .pagination-sm .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
            color: #6c757d;
            background-color: #fff;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .pagination-sm .page-link:hover {
            color: #0056b3;
            background-color: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-1px);
        }

        .pagination-sm .page-item.active .page-link {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination-sm .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-sm .page-item {
            margin: 0 2px;
        }

        .pagination-sm .page-item .page-link {
            border-radius: 6px;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .pagination {
            gap: 0.25rem;
        }

        /* Responsivo */
        @media (max-width: 576px) {
            .pagination-info {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }
            
            .pagination-sm .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                min-width: 32px;
                height: 32px;
            }
        }
    </style>
@endif
