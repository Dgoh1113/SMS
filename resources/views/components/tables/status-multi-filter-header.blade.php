@props([
    'col',
    'label',
    'options' => [],
    'selectClass' => 'inquiries-grid-filter-select',
    'id' => null,
])

@php
    $uniqueId = $id ?? 'status-filter-' . $col . '-' . uniqid();
@endphp

<x-tables.header-cell :col="$col" :label="$label" {{ $attributes->merge(['class' => 'status-multi-filter-cell']) }}>
    <div class="status-multi-filter" id="{{ $uniqueId }}" data-col="{{ $col }}">
        <button type="button" class="status-multi-filter-toggle" aria-haspopup="true" aria-expanded="false">
            <span class="status-multi-filter-label">All</span>
            <i class="bi bi-chevron-down"></i>
        </button>
        <div class="status-multi-filter-menu" hidden>
            <div class="status-multi-filter-options">
                @foreach($options as $optionValue => $optionLabel)
                    @php
                        $value = is_int($optionValue) ? $optionLabel : $optionValue;
                        $text = is_int($optionValue) ? $optionLabel : $optionLabel;
                    @endphp
                    <label class="status-multi-filter-option inquiries-columns-check">
                        <input type="checkbox" value="{{ $value }}" checked class="status-multi-filter-checkbox {{ $selectClass }}" data-col="{{ $col }}">
                        <span class="status-multi-filter-option-label">{{ $text }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    (function() {
        const wrap = document.getElementById('{{ $uniqueId }}');
        if (!wrap) return;
        const btn = wrap.querySelector('.status-multi-filter-toggle');
        const menu = wrap.querySelector('.status-multi-filter-menu');
        const labelEl = wrap.querySelector('.status-multi-filter-label');
        const checkboxes = wrap.querySelectorAll('input[type="checkbox"]');

        function updateLabel() {
            const checked = Array.from(checkboxes).filter(c => c.checked);
            if (checked.length === checkboxes.length) {
                labelEl.textContent = 'All';
            } else if (checked.length === 0) {
                labelEl.textContent = 'None';
            } else {
                const names = checked.map(c => c.parentElement.querySelector('span').textContent.trim());
                if (names.length > 2) {
                    labelEl.textContent = names.length + ' Selected';
                } else {
                    labelEl.textContent = names.join(', ');
                }
            }
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = !menu.hidden;
            
            // Close any other open status menus
            document.querySelectorAll('.status-multi-filter-menu').forEach(m => {
                if (m !== menu) m.hidden = true;
            });

            menu.hidden = isOpen;
            btn.setAttribute('aria-expanded', !isOpen);
        });

        document.addEventListener('click', function() {
            menu.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
        });

        menu.addEventListener('click', function(e) { e.stopPropagation(); });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateLabel();
                // We dispatch a change event on the wrapper so that the table filtering logic can detect it
                wrap.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        // Initial label
        updateLabel();

        // Expose reset function for global clear filters
        wrap._resetStatusFilter = function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateLabel();
        };
    })();
    </script>
</x-tables.header-cell>
