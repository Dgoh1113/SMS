@props([
    'col' => null,
    'label' => '',
    'cellClass' => 'inquiries-header-cell',
    'labelClass' => 'inquiries-header-label',
])

<th
    @if($col !== null) data-col="{{ $col }}" @endif
    {{ $attributes->merge(['class' => $cellClass]) }}
>
    <span class="{{ $labelClass }}">{{ $label }}</span>
    {{ $slot }}
</th>
