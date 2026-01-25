<div class="holiday-card">

    <div class="holiday-header">
        <h3>My Holidays</h3>
        <span>Remaining: {{ $remaining }}</span>
    </div>

    <div class="calendar-nav">
        <button wire:click="changeMonth(-1)">‹</button>
        <strong>{{ $start->format('F Y') }}</strong>
        <button wire:click="changeMonth(1)">›</button>
    </div>

    <div class="calendar-grid">

        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div class="calendar-day-head">{{ $day }}</div>
        @endforeach

        {{-- Empty slots before month starts --}}
        @for ($i = 1; $i < $start->dayOfWeekIso; $i++)
            <div class="calendar-cell empty"></div>
        @endfor

        {{-- Days --}}
        @for ($d = $start->copy(); $d <= $end; $d->addDay())
            @php
                $dateString = $d->toDateString();
                $isSunday = $d->isSunday();
                $isPast = $d->isPast();
                $isHoliday = in_array($dateString, $holidays);
                $isLocked = $d->isBefore(now()->addDays(14)->startOfDay());
            @endphp

            <div class="calendar-cell
    @if ($isSunday) sunday @endif
    @if ($isPast || $isLocked) past @endif
    @if ($isHoliday) holiday @endif
"
                @if (!$isSunday && !$isPast && !$isLocked) wire:click="{{ $isHoliday ? "removeDate('$dateString')" : "selectDate('$dateString')" }}" @endif>
                {{ $d->day }}
            </div>
        @endfor
    </div>

    <p class="calendar-note">
        Click a date to add or remove a holiday.
        Holidays must be selected at least 14 days in advance.
        Past dates and Sundays are disabled.
    </p>

</div>
