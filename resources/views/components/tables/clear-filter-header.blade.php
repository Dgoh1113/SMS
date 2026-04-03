@props([
    'label' => 'ACTION',
    'buttonId',
    'buttonLabel' => 'Clear filters',
    'buttonClass' => 'inquiries-filter-clear',
    'cellClass' => 'inquiries-col-action inquiries-header-cell',
])

<x-tables.header-cell :label="$label" :cell-class="$cellClass" {{ $attributes }}>
    <button type="button" class="{{ $buttonClass }}" id="{{ $buttonId }}">{{ $buttonLabel }}</button>
</x-tables.header-cell>
