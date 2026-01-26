<div class="admin-attendance-card">

    <div class="admin-attendance-title">
        Live Attendance — {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
    </div>

    <!-- Filters -->
    <div class="admin-filter-bar">

        <div class="filter-left">
            <button wire:click="setToday" class="filter-btn">Today</button>
            <button wire:click="setPreviousDay" class="filter-btn">Previous Day</button>
        </div>

        <div class="filter-right">
            <input type="date" wire:model.live="selectedDate" class="filter-date">
        </div>

    </div>

    <!-- Attendance Table -->
    <table class="admin-attendance-table" wire:poll.10s>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Worked</th>
                <th>Break</th>
                <th>Late</th>
                <th>Next Holiday</th>
                <th>Holiday List</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($this->todaySessions as $row)
                @php
                    $statusClass = match ($row['status']) {
                        'working' => 'status-working',
                        'break' => 'status-break',
                        'completed' => 'status-completed',
                        'incomplete' => 'status-incomplete',
                        default => 'status-absent',
                    };
                @endphp

                <tr>
                    <td>{{ $row['name'] }}</td>

                    <td class="{{ $statusClass }}">
                        {{ strtoupper($row['status']) }}
                    </td>

                    <td>
                        {{ $row['clock_in'] ? \Carbon\Carbon::parse($row['clock_in'])->format('H:i:s') : '--' }}
                    </td>

                    <td>
                        {{ $row['clock_out'] ? \Carbon\Carbon::parse($row['clock_out'])->format('H:i:s') : '--' }}
                    </td>

                    <td>{{ gmdate('H:i:s', $row['worked']) }}</td>

                    <td>{{ gmdate('H:i:s', $row['break']) }}</td>

                    <td>
                        {{ $row['late'] ? 'Yes' : 'No' }}
                        @if ($row['late'])
                            <span class="late-badge">Late</span>
                        @endif
                    </td>

                    <!-- Next Holiday -->
                    <td>
                        @if ($row['next_holiday'] !== null)
                            {{ $row['next_holiday'] }} {{ $row['next_holiday'] == 1 ? 'day' : 'days' }}
                        @else
                            --
                        @endif

                    </td>

                    <!-- Holiday List Button -->
                    <td>
                        <button class="filter-btn" wire:click="openHolidayList({{ $row['id'] }})">
                            Check
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- Holiday List Modal -->
    @if ($showHolidayModal)
        <div class="admin-modal-backdrop">

            <div class="admin-modal">

                <div class="admin-modal-header">
                    <span>Employee Holiday List — {{ now()->year }}</span>
                    <button wire:click="closeHolidayList">✕</button>
                </div>

                <div class="year-grid">
                    @include('livewire.partials.year-calendar', [
                        'year' => now()->year,
                        'presetDates' => $holidayDates,
                        'customDates' => [],
                        'clickable' => false,
                    ])
                </div>

            </div>
        </div>
    @endif

</div>
