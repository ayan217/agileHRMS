@for ($m = 1; $m <= 12; $m++)
    @php
        $monthStart = \Carbon\Carbon::create($year, $m, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();
    @endphp

    <div class="month-box">

        <div class="month-title">
            {{ $monthStart->format('F') }}
        </div>

        <div class="month-grid">

            {{-- Week headers --}}
            @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $head)
                <div class="day-head">{{ $head }}</div>
            @endforeach

            {{-- Offset empty cells --}}
            @for ($i = 1; $i < $monthStart->dayOfWeekIso; $i++)
                <div class="day-cell empty"></div>
            @endfor

            {{-- Days --}}
            @for ($d = $monthStart->copy(); $d <= $monthEnd; $d->addDay())
                @php
                    $dateString = $d->toDateString();
                    $isSunday = $d->isSunday();
                    $fromPreset = in_array($dateString, $presetDates ?? []);
                    $fromCustom = in_array($dateString, $customDates ?? []);
                @endphp

                <div class="day-cell
                    @if ($isSunday) disabled @endif
                    @if ($fromPreset) preset-selected @endif
                    @if ($fromCustom) custom-selected @endif
                "
                    @if (($clickable ?? false) && !$isSunday) wire:click="toggleCustomDate('{{ $dateString }}')" @endif>
                    {{ $d->day }}
                </div>
            @endfor

        </div>
    </div>
@endfor
