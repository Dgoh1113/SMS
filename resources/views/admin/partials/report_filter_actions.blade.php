@php
    $filterActionsWrapperClass = trim((string) ($wrapperClass ?? ''));
    $filterActionsApplyClass = trim((string) ($applyClass ?? ''));
    $filterActionsClearClass = trim((string) ($clearClass ?? ''));
    $filterActionsApplyLabel = $applyLabel ?? 'Apply';
    $filterActionsClearLabel = $clearLabel ?? 'Clear';
@endphp

<div class="{{ $filterActionsWrapperClass }}">
    <button type="submit" class="{{ $filterActionsApplyClass }}">{{ $filterActionsApplyLabel }}</button>
    <a href="{{ $clearUrl }}" class="{{ $filterActionsClearClass }}">{{ $filterActionsClearLabel }}</a>
</div>
