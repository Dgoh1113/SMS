<div class="inquiries-assigned-pagination {{ $containerClass ?? '' }}" 
     id="{{ $id }}" 
     data-total="{{ $total ?? 0 }}" 
     data-per-page="{{ $perPage ?? 10 }}" 
     data-current-page="{{ $currentPage ?? 1 }}" 
     data-last-page="{{ $lastPage ?? 1 }}"
     {!! $attributes ?? '' !!}>
    <span class="inquiries-assigned-pagination-info" id="{{ $infoId ?? ($id . 'Info') }}">
        Showing {{ ($total ?? 0) === 0 ? 0 : (($currentPage ?? 1) - 1) * ($perPage ?? 10) + 1 }} 
        to {{ min(($currentPage ?? 1) * ($perPage ?? 10), ($total ?? 0)) }} 
        of {{ $total ?? 0 }} entries (Page {{ $currentPage ?? 1 }})
    </span>
    <div class="inquiries-assigned-pagination-nav">
        <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" 
                id="{{ $id }}First" data-page="first" title="First Page" aria-label="First Page">
            <i class="bi bi-chevron-double-left"></i>
        </button>
        <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" 
                id="{{ $id }}Prev" data-page="prev" title="Previous Page" aria-label="Previous Page">
            <i class="bi bi-chevron-left"></i>
        </button>
        
        <span class="inquiries-assigned-page-numbers" id="{{ $pageNumbersId ?? ($id . 'PageNumbers') }}">
            @if(isset($slot) && $slot)
                {!! $slot !!}
            @endif
        </span>
        
        <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" 
                id="{{ $id }}Next" data-page="next" title="Next Page" aria-label="Next Page">
            <i class="bi bi-chevron-right"></i>
        </button>
        <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" 
                id="{{ $id }}Last" data-page="last" title="Last Page" aria-label="Last Page">
            <i class="bi bi-chevron-double-right"></i>
        </button>
    </div>
</div>
