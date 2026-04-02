@php
    $scopeOptions = $options ?? $reportScopeOptions ?? [];
    $scopeSelected = (string) ($selected ?? $selectedReportScope ?? 'all');
    $scopeName = $name ?? 'report_scope';
    $quickFilterKeys = ['all', 'all_dealers', 'estream'];
    $quickFilters = [];
    $individualUsers = [];

    foreach ($scopeOptions as $scopeValue => $scopeLabel) {
        if (in_array($scopeValue, $quickFilterKeys, true)) {
            $quickFilters[$scopeValue] = $scopeLabel;
        } else {
            $individualUsers[$scopeValue] = $scopeLabel;
        }
    }
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
        <script>
            (function () {
                function getQuickFilterValues(select) {
                    return Array.prototype.filter.call(select.options || [], function (option) {
                        return option.dataset.scopeKind === 'quick';
                    }).map(function (option) {
                        return option.value;
                    });
                }

                function getScopeQuery(instance) {
                    if (!instance) return '';

                    var dropdownInput = instance.dropdown ? instance.dropdown.querySelector('.dropdown-input') : null;
                    if (dropdownInput && typeof dropdownInput.value === 'string') {
                        return dropdownInput.value.trim();
                    }

                    if (instance.control_input && typeof instance.control_input.value === 'string') {
                        return instance.control_input.value.trim();
                    }

                    return '';
                }

                function syncScopeOptgroups(instance, forcedQuery) {
                    if (!instance || !instance.dropdown) return;

                    var query = typeof forcedQuery === 'string' ? forcedQuery.trim() : getScopeQuery(instance);
                    var quickOnly = query === '';
                    var quickValues = instance.settings.reportQuickValues || [];
                    var dropdownOptions = Array.prototype.slice.call(instance.dropdown.querySelectorAll('[data-selectable]'));
                    var optgroups = Array.prototype.slice.call(instance.dropdown.querySelectorAll('.optgroup'));
                    var optgroupHeaders = Array.prototype.slice.call(instance.dropdown.querySelectorAll('.optgroup-header'));
                    var totalVisible = 0;

                    dropdownOptions.forEach(function (option) {
                        var value = option.getAttribute('data-value') || '';
                        var isQuick = quickValues.indexOf(value) !== -1;
                        var shouldShow = quickOnly ? isQuick : !isQuick;
                        option.hidden = !shouldShow;

                        if (shouldShow) {
                            totalVisible += 1;
                        }
                    });

                    optgroups.forEach(function (group, index) {
                        var groupVisibleCount = Array.prototype.filter.call(
                            group.querySelectorAll('[data-selectable]'),
                            function (option) {
                                return !option.hidden;
                            }
                        ).length;

                        group.hidden = groupVisibleCount === 0;
                        group.style.display = groupVisibleCount === 0 ? 'none' : '';

                        var header = optgroupHeaders[index];
                        if (header) {
                            header.hidden = groupVisibleCount === 0;
                            header.style.display = groupVisibleCount === 0 ? 'none' : '';
                        }
                    });

                    var noResults = instance.dropdown.querySelector('.no-results');
                    if (noResults) {
                        noResults.hidden = quickOnly || totalVisible > 0;
                    }
                }

                function initReportScopeTomSelect() {
                    if (typeof TomSelect === 'undefined') return;

                    document.querySelectorAll('[data-report-scope-select]').forEach(function (select) {
                        if (select.tomselect) return;

                        var quickValues = getQuickFilterValues(select);

                        new TomSelect(select, {
                            maxItems: 1,
                            closeAfterSelect: true,
                            create: false,
                            hideSelected: false,
                            allowEmptyOption: false,
                            sortField: [{ field: '$order' }],
                            searchField: ['text'],
                            copyClassesToDropdown: false,
                            reportQuickValues: quickValues,
                            controlInput: '<input type="text" autocomplete="off" placeholder="Type Company Name or Alias">',
                            onInitialize: function () {
                                if (this.wrapper) {
                                    this.wrapper.classList.add('report-scope-ts-wrapper');
                                }
                                if (this.control) {
                                    this.control.classList.add('report-scope-ts-control');
                                }
                                if (this.dropdown) {
                                    this.dropdown.classList.add('report-scope-ts-dropdown');
                                }
                                if (this.control_input) {
                                    this.control_input.setAttribute('placeholder', 'Type Company Name or Alias');
                                }
                                syncScopeOptgroups(this, '');
                            },
                            onDropdownOpen: function () {
                                if (this.control_input) {
                                    this.control_input.setAttribute('placeholder', 'Type Company Name or Alias');
                                    this.control_input.focus();
                                }
                                syncScopeOptgroups(this);
                            },
                            onType: function (value) {
                                syncScopeOptgroups(this, value);
                            },
                            onDropdownClose: function () {
                                syncScopeOptgroups(this, '');
                            }
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initReportScopeTomSelect, { once: true });
                } else {
                    initReportScopeTomSelect();
                }

                window.addEventListener('pageshow', initReportScopeTomSelect);
            })();
        </script>
    @endpush
@endonce

<div class="report-scope-field">
    <select
        name="{{ $scopeName }}"
        class="report-scope-select"
        data-report-scope-select
        placeholder="Type Company Name or Alias"
    >
        @if (!empty($quickFilters))
            <optgroup label="Quick Filters">
                @foreach ($quickFilters as $scopeValue => $scopeLabel)
                    <option value="{{ $scopeValue }}" data-scope-kind="quick" {{ $scopeSelected === $scopeValue ? 'selected' : '' }}>
                        {{ $scopeLabel }}
                    </option>
                @endforeach
            </optgroup>
        @endif

        @if (!empty($individualUsers))
            @foreach ($individualUsers as $scopeValue => $scopeLabel)
                <option value="{{ $scopeValue }}" data-scope-kind="individual" {{ $scopeSelected === $scopeValue ? 'selected' : '' }}>
                    {{ $scopeLabel }}
                </option>
            @endforeach
        @endif
    </select>
</div>
