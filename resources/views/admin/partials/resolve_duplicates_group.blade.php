<div class="duplicates-group-card" data-lead-ids="{{ implode(',', array_map(function($l) { return $l->LEADID; }, $group['leads'])) }}" style="border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="duplicates-group-header" style="background: #f9fafb; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div>
            <span style="font-weight: 700; color: #1f2937; font-size: 13.5px;">{{ $group['company'] }}</span>
            <span style="color: #6b7280; font-size: 12px; margin-left: 12px;">Phone: {{ $group['phone'] }} • Email: {{ $group['email'] }}</span>
        </div>
        <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 4px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ count($group['leads']) }} Inquiries</span>
    </div>
    <div class="duplicates-group-list" style="padding: 12px 16px; background: #fff; overflow-x: auto; max-width: 100%;">
        @php
            $hasAssignedLead = false;
            foreach ($group['leads'] as $l) {
                if (!empty(trim((string)($l->ASSIGNEDTO ?? '')))) {
                    $hasAssignedLead = true;
                    break;
                }
            }
            
            $hasDiff = function($field) use ($group) {
                $vals = [];
                foreach ($group['leads'] as $l) {
                    $vals[] = strtolower(trim((string) ($l->{$field} ?? '')));
                }
                return count(array_unique($vals)) > 1;
            };
            
            $locationDiff = $hasDiff('POSTCODE') || $hasDiff('CITY') || $hasDiff('STATE') || $hasDiff('COUNTRY');
            
            $getCellStyle = function($field) use ($hasDiff) {
                if ($hasDiff($field)) {
                    return 'background: #fffbeb; color: #b45309; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 10px 12px;';
                }
                return 'background: #fff; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding: 10px 12px;';
            };
            
            $locationStyle = $locationDiff 
                ? 'background: #fffbeb; color: #b45309; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 10px 12px;'
                : 'background: #fff; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding: 10px 12px;';
        @endphp
        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; min-width: 1750px; border: 1px solid #e5e7eb;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 180px;">Action</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 100px;">Inquiry ID</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 130px;">Date</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 150px;">Contact Name</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 340px;">Location</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 280px;">Business Nature</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 150px;">Existing SW</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 120px;">Demo Mode</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 90px;">Product ID</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 90px;">User Count</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; width: 110px;">Referral Code</th>
                    <th style="padding: 10px 12px; font-weight: 700; color: #374151; min-width: 300px;">Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group['leads'] as $leadIdx => $lead)
                    @php
                        $isAssigned = !empty(trim((string)($lead->ASSIGNEDTO ?? '')));
                        if ($hasAssignedLead) {
                            $shouldKeep = $isAssigned;
                            $shouldDeleteByDefault = !$isAssigned;
                        } else {
                            $shouldKeep = ($leadIdx === 0);
                            $shouldDeleteByDefault = ($leadIdx > 0);
                        }
                    @endphp
                    <tr style="background: {{ $isAssigned ? '#f0f9ff' : '#ffffff' }};">
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                @if($isAssigned)
                                    <input type="checkbox" disabled style="width: 15px; height: 15px; cursor: not-allowed; opacity: 0.5;" title="Already assigned to a dealer">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Keep (Assigned: {{ $lead->ASSIGNEDTO }})</span>
                                @else
                                    <input type="checkbox" class="duplicate-checkbox" data-lead-id="{{ $lead->LEADID }}" data-is-duplicate="{{ $shouldDeleteByDefault ? '1' : '0' }}" {{ $shouldDeleteByDefault ? 'checked' : '' }} style="width: 15px; height: 15px; accent-color: #6d28d9; cursor: pointer;">
                                    @if($shouldKeep)
                                        <span style="background: #e9d5ff; color: #6d28d9; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Keep (Newest)</span>
                                    @else
                                        <span style="background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Duplicate</span>
                                    @endif
                                    <button type="button" class="duplicate-single-delete-btn" data-lead-id="{{ $lead->LEADID }}" title="Delete this inquiry" style="background: none; border: 1px solid #fca5a5; border-radius: 4px; padding: 2px 5px; cursor: pointer; color: #ef4444; font-size: 13px; line-height: 1; display: inline-flex; align-items: center; transition: background 0.15s, border-color 0.15s;" onmouseenter="this.style.background='#fef2f2'; this.style.borderColor='#ef4444';" onmouseleave="this.style.background='none'; this.style.borderColor='#fca5a5';"><i class="bi bi-trash3"></i></button>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-weight: 800; color: #4c1d95; white-space: nowrap;">#SQL-{{ $lead->LEADID }}</td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">{{ date('d/m/Y H:i', strtotime($lead->CREATEDAT)) }}</td>
                        <td style="{{ $getCellStyle('CONTACTNAME') }}">
                            {{ $lead->CONTACTNAME }}
                        </td>
                        <td style="{{ $locationStyle }}">
                            {{ $lead->POSTCODE }}, {{ $lead->CITY }}, {{ $lead->STATE }}
                        </td>
                        <td style="{{ $getCellStyle('BUSINESSNATURE') }}">
                            {{ $lead->BUSINESSNATURE }}
                        </td>
                        <td style="{{ $getCellStyle('EXISTINGSOFTWARE') }}">
                            {{ $lead->EXISTINGSOFTWARE }}
                        </td>
                        <td style="{{ $getCellStyle('DEMOMODE') }}">
                            {{ $lead->DEMOMODE }}
                        </td>
                        <td style="{{ $getCellStyle('PRODUCTID') }}">
                            {{ $lead->PRODUCTID }}
                        </td>
                        <td style="{{ $getCellStyle('USERCOUNT') }}">
                            {{ $lead->USERCOUNT }}
                        </td>
                        <td style="{{ $getCellStyle('REFERRALCODE') }}">
                            {{ $lead->REFERRALCODE }}
                        </td>
                        <td style="{{ $getCellStyle('DESCRIPTION') }}; max-width: 500px;">
                            {{ $lead->DESCRIPTION }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
