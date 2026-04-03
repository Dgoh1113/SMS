@props([
    'col',
    'label',
    'options' => [],
    'selectClass' => 'inquiries-grid-filter inquiries-grid-filter-select',
    'cellClass' => 'inquiries-header-cell',
    'wrapClass' => 'inquiries-filter-wrap',
    'table' => null,
    'inputCol' => null,
    'placeholderOption' => 'All',
])

<x-tables.header-cell :col="$col" :label="$label" :cell-class="$cellClass" {{ $attributes }}>
    <span class="{{ $wrapClass }}">
        <select
            class="{{ $selectClass }}"
            data-col="{{ $inputCol ?? $col }}"
            @if($table !== null && $table !== '') data-table="{{ $table }}" @endif
        >
            @if($placeholderOption !== null)
                <option value="">{{ $placeholderOption }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                @php
                    $value = is_int($optionValue) ? $optionLabel : $optionValue;
                    $labelText = is_int($optionValue) ? $optionLabel : $optionLabel;
                @endphp
                <option value="{{ $value }}">{{ $labelText }}</option>
            @endforeach
        </select>
    </span>
</x-tables.header-cell>
