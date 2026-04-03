@props([
    'col',
    'label',
    'inputClass' => 'inquiries-grid-filter',
    'cellClass' => 'inquiries-header-cell',
    'wrapClass' => 'inquiries-filter-wrap',
    'type' => 'text',
    'placeholder' => null,
    'table' => null,
    'inputCol' => null,
    'icon' => true,
    'disabled' => false,
    'readonly' => false,
])

<x-tables.header-cell :col="$col" :label="$label" :cell-class="$cellClass" {{ $attributes }}>
    <span class="{{ $wrapClass }}">
        <input
            type="{{ $type }}"
            class="{{ $inputClass }}"
            data-col="{{ $inputCol ?? $col }}"
            @if($table !== null && $table !== '') data-table="{{ $table }}" @endif
            @if($placeholder !== null) placeholder="{{ $placeholder }}" @endif
            @disabled($disabled)
            @readonly($readonly)
        >
        @if($icon)
            <i class="bi bi-search inquiries-filter-icon"></i>
        @endif
    </span>
</x-tables.header-cell>
