@props([
    'col',
    'label',
    'inputClass' => 'inquiries-grid-filter',
    'cellClass' => 'inquiries-header-cell',
    'wrapClass' => 'inquiries-filter-wrap dealer-operator-search-wrap',
    'type' => 'text',
    'placeholder' => '0',
    'table' => null,
    'inputCol' => null,
    'defaultOperator' => '=',
    'buttonTitle' => 'Filter operator',
    'operators' => ['=', '!=', '<', '<=', '>', '>='],
])

@php
    $resolvedOperators = collect($operators)->map(function ($operator) {
        if (is_array($operator)) {
            return [
                'value' => $operator['value'] ?? '=',
                'label' => $operator['label'] ?? ($operator['value'] ?? '='),
            ];
        }

        return match ((string) $operator) {
            '=' => ['value' => '=', 'label' => '= Equals'],
            '!=' => ['value' => '!=', 'label' => '!= Does not equal'],
            '<' => ['value' => '<', 'label' => '< Less than'],
            '<=' => ['value' => '<=', 'label' => '<= Less than or equal to'],
            '>' => ['value' => '>', 'label' => '> Greater than'],
            '>=' => ['value' => '>=', 'label' => '>= Greater than or equal to'],
            default => ['value' => (string) $operator, 'label' => (string) $operator],
        };
    });
@endphp

<x-tables.header-cell :col="$col" :label="$label" :cell-class="$cellClass" {{ $attributes }}>
    <span class="{{ $wrapClass }}">
        <span class="dealer-operator-search-box">
            <button
                type="button"
                class="dealer-operator-btn"
                data-col="{{ $inputCol ?? $col }}"
                data-op="{{ $defaultOperator }}"
                aria-haspopup="true"
                aria-expanded="false"
                title="{{ $buttonTitle }}"
            >
                {{ $defaultOperator }}
            </button>
            <div class="dealer-operator-dropdown" hidden>
                @foreach($resolvedOperators as $operator)
                    <button type="button" data-op="{{ $operator['value'] }}">{{ $operator['label'] }}</button>
                @endforeach
            </div>
            <input
                type="{{ $type }}"
                class="{{ $inputClass }}"
                data-col="{{ $inputCol ?? $col }}"
                @if($table !== null && $table !== '') data-table="{{ $table }}" @endif
                @if($placeholder !== null) placeholder="{{ $placeholder }}" @endif
            >
        </span>
    </span>
</x-tables.header-cell>
