<div class="holiday-plan-wrapper">

    <!-- Tabs -->
    <div class="tab-row">
        <button wire:click="$set('activeTab','current')" class="{{ $activeTab === 'current' ? 'tab-active' : '' }}">
            Current Year Holidays ({{ $currentYear }})
        </button>

        <button wire:click="$set('activeTab','upcoming')" class="{{ $activeTab === 'upcoming' ? 'tab-active' : '' }}">
            Upcoming Holiday Selection ({{ $upcomingYear }})
        </button>
    </div>


    <!-- ================= CURRENT YEAR VIEW ================= -->
    @if ($activeTab === 'current')

        @php
            $plan = \App\Models\UserHolidayPlan::where('user_id', auth()->id())
                ->where('year', $currentYear)
                ->first();
        @endphp

        <h3>Holiday List — {{ $currentYear }}</h3>

        {{-- If plan exists → Read-only calendar --}}
        @if ($plan)

            @php
                $currentDates = \App\Models\HolidayPresetDate::where('holiday_preset_id', $plan->holiday_preset_id)
                    ->pluck('holiday_date')
                    ->toArray();
            @endphp

            <div class="year-grid">
                @include('livewire.partials.year-calendar', [
                    'year' => $currentYear,
                    'presetDates' => $currentDates,
                    'customDates' => [],
                    'clickable' => false,
                ])
            </div>

            {{-- If no plan exists → Show selector + editable calendar --}}
        @else
            <p class="info-text">
                No holiday plan submitted for {{ $currentYear }}.
                Please select a preset or create a custom list.
            </p>

            <select wire:model.live="selectedPreset">
                <option value="">Select Holiday Preset</option>
                @foreach ($presets as $preset)
                    <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                @endforeach
                <option value="custom">Custom List</option>
            </select>

            @if ($selectedPreset === 'custom')
                <p>Maximum holidays: {{ \App\Livewire\HolidayPlanSelector::MAX_HOLIDAYS }}</p>
            @endif

            <div class="year-grid">
                @include('livewire.partials.year-calendar', [
                    'year' => $currentYear,
                    'presetDates' => $presetDates,
                    'customDates' => $customSelectedDates,
                    'clickable' => $selectedPreset === 'custom',
                ])
            </div>

            <button wire:click="submitPlan">
                Submit Holiday Plan
            </button>

        @endif
    @endif




    <!-- ================= UPCOMING VIEW ================= -->
    @if ($activeTab === 'upcoming')

        <h3>Holiday Plan — {{ $upcomingYear }}</h3>

        {{-- If already submitted OR not December → read-only --}}
        @if ($alreadySubmittedUpcoming || now()->month !== 12)

            <p class="info-text">
                Holiday plan for {{ $upcomingYear }}
                {{ $alreadySubmittedUpcoming ? 'has been submitted.' : 'selection opens in December.' }}
            </p>

            <div class="year-grid">
                @include('livewire.partials.year-calendar', [
                    'year' => $upcomingYear,
                    'presetDates' => $presetDates,
                    'customDates' => [],
                    'clickable' => false,
                ])
            </div>
        @else
            {{-- December + not submitted → selection allowed --}}

            <select wire:model.live="selectedPreset">
                <option value="">Select Holiday Preset</option>
                @foreach ($presets as $preset)
                    <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                @endforeach
                <option value="custom">Custom List</option>
            </select>

            @if ($selectedPreset === 'custom')
                <p>Maximum holidays: {{ \App\Livewire\HolidayPlanSelector::MAX_HOLIDAYS }}</p>
            @endif

            <div class="year-grid">
                @include('livewire.partials.year-calendar', [
                    'year' => $upcomingYear,
                    'presetDates' => $presetDates,
                    'customDates' => $customSelectedDates,
                    'clickable' => $selectedPreset === 'custom',
                ])
            </div>

            <button wire:click="submitPlan">Submit Holiday Plan</button>

        @endif
    @endif

</div>
