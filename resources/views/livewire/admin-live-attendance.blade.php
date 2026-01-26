<div class="admin-attendance-card">

    <div class="admin-attendance-title">
        Today's Live Attendance
    </div>
    <div class="admin-filter-bar">

        <div class="filter-left">
            <button wire:click="setToday" class="filter-btn">Today</button>
            <button wire:click="setPreviousDay" class="filter-btn">Previous Day</button>
        </div>

        <div class="filter-right">
            <input type="date" wire:model.live="selectedDate" class="filter-date">
        </div>

    </div>

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
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
