<div class="inquiries-assign-modal" id="assignModal" hidden>
    <div class="inquiries-assign-backdrop" data-assign-close="1"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="assignModalTitle">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" id="assignModalTitle">Assign lead</div>
            <button type="button" class="inquiries-assign-close" aria-label="Close" data-assign-close="1">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.inquiries.assign') }}" class="inquiries-assign-body">
            @csrf
            <input type="hidden" name="LEADID" id="assignLeadId">
            <input type="hidden" name="assignedTo" id="assignToHidden" required>
            <div class="inquiries-assign-row">
                <div class="inquiries-assign-label">Lead</div>
                <div class="inquiries-assign-value" id="assignLeadLabel">-</div>
            </div>
            <div class="inquiries-assign-dealers">
                <div class="inquiries-assign-dealers-title">Select dealer</div>
                <div class="inquiries-assign-dealers-tablewrap">
                    <table class="inquiries-assign-dealers-table">
                        <thead>
                            <tr>
                                <th>Alias</th>
                                <th>Company</th>
                                <th>Postcode</th>
                                <th>City</th>
                                <th>Email</th>
                                <th>Active</th>
                                <th>Total Lead</th>
                                <th>Total Closed</th>
                                <th>Conversion Rate</th>
                            </tr>
                            <tr class="inquiries-assign-dealers-filter-row">
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="alias"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="company"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="email"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="active"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th>
                                    <span class="inquiries-filter-wrap dealer-operator-search-wrap">
                                        <span class="dealer-operator-search-box">
                                            <button
                                                type="button"
                                                class="dealer-operator-btn"
                                                data-col="totallead"
                                                data-op="="
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Filter operator"
                                            >
                                                =
                                            </button>
                                            <div class="dealer-operator-dropdown" hidden>
                                                <button type="button" data-op="=">= Equals</button>
                                                <button type="button" data-op="!=">!= Does not equal</button>
                                                <button type="button" data-op="<">&lt; Less than</button>
                                                <button type="button" data-op="<=">&lt;= Less than or equal to</button>
                                                <button type="button" data-op=">">&gt; Greater than</button>
                                                <button type="button" data-op=">=">&gt;= Greater than or equal to</button>
                                            </div>
                                            <input type="text" class="inquiries-assign-filter" data-col="totallead" placeholder="0">
                                        </span>
                                    </span>
                                </th>
                                <th>
                                    <span class="inquiries-filter-wrap dealer-operator-search-wrap">
                                        <span class="dealer-operator-search-box">
                                            <button
                                                type="button"
                                                class="dealer-operator-btn"
                                                data-col="totalclosed"
                                                data-op="="
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Filter operator"
                                            >
                                                =
                                            </button>
                                            <div class="dealer-operator-dropdown" hidden>
                                                <button type="button" data-op="=">= Equals</button>
                                                <button type="button" data-op="!=">!= Does not equal</button>
                                                <button type="button" data-op="<">&lt; Less than</button>
                                                <button type="button" data-op="<=">&lt;= Less than or equal to</button>
                                                <button type="button" data-op=">">&gt; Greater than</button>
                                                <button type="button" data-op=">=">&gt;= Greater than or equal to</button>
                                            </div>
                                            <input type="text" class="inquiries-assign-filter" data-col="totalclosed" placeholder="0">
                                        </span>
                                    </span>
                                </th>
                                <th>
                                    <span class="inquiries-filter-wrap dealer-operator-search-wrap">
                                        <span class="dealer-operator-search-box">
                                            <button
                                                type="button"
                                                class="dealer-operator-btn"
                                                data-col="conversion"
                                                data-op="="
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Filter operator"
                                            >
                                                =
                                            </button>
                                            <div class="dealer-operator-dropdown" hidden>
                                                <button type="button" data-op="=">= Equals</button>
                                                <button type="button" data-op="!=">!= Does not equal</button>
                                                <button type="button" data-op="<">&lt; Less than</button>
                                                <button type="button" data-op="<=">&lt;= Less than or equal to</button>
                                                <button type="button" data-op=">">&gt; Greater than</button>
                                                <button type="button" data-op=">=">&gt;= Greater than or equal to</button>
                                            </div>
                                            <input type="text" class="inquiries-assign-filter" data-col="conversion" placeholder="0">
                                        </span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($dealers ?? []) as $d)
                                @php
                                    $uid = trim((string)($d->USERID ?? ''));
                                    $email = trim((string)($d->EMAIL ?? ''));
                                    $postcode = trim((string)($d->POSTCODE ?? ''));
                                    $city = trim((string)($d->CITY ?? ''));
                                    $active = (int)($d->ISACTIVE ?? 0) ? 'Yes' : 'No';
                                    $company = trim((string)($d->COMPANY ?? ''));
                                    $alias = trim((string)($d->ALIAS ?? ''));
                                    $totalLead = (int)($d->TOTAL_LEAD ?? 0);
                                    $totalClosed = (int)($d->TOTAL_CLOSED ?? 0);
                                    $conv = (float)($d->CONVERSION_RATE ?? 0);
                                    $convLabel = $conv > 0 ? number_format($conv, 1) . '%' : '0%';
                                    $label = $alias !== '' ? $alias : ($company !== '' ? $company : ($email !== '' ? $email : $uid));
                                @endphp
                                <tr class="inquiries-assign-dealer-row"
                                    data-assign-userid="{{ $uid }}"
                                    data-assign-label="{{ e($label) }}"
                                    data-assign-postcode="{{ $postcode }}"
                                    data-assign-city="{{ e($city) }}"
                                    data-assign-order="{{ $loop->index }}">
                                    <td data-col="alias">{{ $alias ?: '-' }}</td>
                                    <td data-col="company">{{ $company ?: '-' }}</td>
                                    <td data-col="postcode">{{ $postcode ?: '-' }}</td>
                                    <td data-col="city">{{ $city ?: '-' }}</td>
                                    <td data-col="email">{{ $email ?: '-' }}</td>
                                    <td data-col="active">{{ $active }}</td>
                                    <td data-col="totallead">{{ $totalLead }}</td>
                                    <td data-col="totalclosed">{{ $totalClosed }}</td>
                                    <td data-col="conversion">{{ $convLabel }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="inquiries-empty">No dealers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="inquiries-assign-hint">Tip: click a row to select.</div>
            </div>
            <div class="inquiries-assign-actions">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" data-assign-close="1">Cancel</button>
                <button type="submit" class="inquiries-btn inquiries-btn-primary" id="assignSubmitBtn" disabled>Assign</button>
            </div>
        </form>
    </div>
</div>

