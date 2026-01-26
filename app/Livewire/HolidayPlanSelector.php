<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HolidayPreset;
use App\Models\HolidayPresetDate;
use App\Models\UserHolidayPlan;
use Carbon\Carbon;

class HolidayPlanSelector extends Component
{
    public const MAX_HOLIDAYS = 12;

    public $selectedPreset = null;
    public $customSelectedDates = [];
    public $presetDates = [];

    public $currentYear;
    public $upcomingYear;

    public $activeTab = 'current';
    public $alreadySubmittedUpcoming = false;

    public function mount()
    {
        $this->currentYear  = now()->year;
        $this->upcomingYear = now()->addYear()->year;

        // Check if upcoming plan already submitted
        $this->alreadySubmittedUpcoming = UserHolidayPlan::where('user_id', auth()->id())
            ->where('year', $this->upcomingYear)
            ->exists();

        // If submitted → preload preset dates for read-only view
        if ($this->alreadySubmittedUpcoming) {
            $plan = UserHolidayPlan::where('user_id', auth()->id())
                ->where('year', $this->upcomingYear)
                ->first();

            $this->presetDates = HolidayPresetDate::where(
                'holiday_preset_id',
                $plan->holiday_preset_id
            )->pluck('holiday_date')->toArray();
        }
    }

    /* When preset changes (only in upcoming December mode) */
    public function updatedSelectedPreset()
    {
        if ($this->selectedPreset === 'custom' || $this->selectedPreset === null) {
            $this->presetDates = [];
            return;
        }

        $this->presetDates = HolidayPresetDate::where(
            'holiday_preset_id',
            $this->selectedPreset
        )->pluck('holiday_date')->toArray();

        $this->customSelectedDates = [];
    }

    /* Toggle custom date */
    public function toggleCustomDate($date)
    {
        $date = Carbon::parse($date)->toDateString();

        if (!in_array($date, $this->customSelectedDates)) {
            if (count($this->customSelectedDates) >= self::MAX_HOLIDAYS) return;
            $this->customSelectedDates[] = $date;
        } else {
            $this->customSelectedDates = array_values(
                array_diff($this->customSelectedDates, [$date])
            );
        }
    }

    /* Submit upcoming plan — December only */
    public function submitPlan()
    {
        // Decide target year based on active tab
        $targetYear = $this->activeTab === 'current'
            ? $this->currentYear
            : $this->upcomingYear;

        // Prevent resubmission for that year
        if (UserHolidayPlan::where('user_id', auth()->id())
            ->where('year', $targetYear)
            ->exists()
        ) {
            return;
        }

        // Preset mode
        if ($this->selectedPreset !== 'custom') {

            if (!$this->selectedPreset) return;

            UserHolidayPlan::create([
                'user_id' => auth()->id(),
                'holiday_preset_id' => $this->selectedPreset,
                'year' => $targetYear
            ]);
        }

        // Custom mode
        if ($this->selectedPreset === 'custom') {

            if (count($this->customSelectedDates) === 0) return;

            $preset = HolidayPreset::create([
                'name' => 'My Custom Holidays',
                'user_id' => auth()->id()
            ]);

            foreach ($this->customSelectedDates as $d) {
                HolidayPresetDate::create([
                    'holiday_preset_id' => $preset->id,
                    'holiday_date' => $d
                ]);
            }

            UserHolidayPlan::create([
                'user_id' => auth()->id(),
                'holiday_preset_id' => $preset->id,
                'year' => $targetYear
            ]);
        }

        // Lock correct tab after submission
        if ($this->activeTab === 'upcoming') {
            $this->alreadySubmittedUpcoming = true;
        }

        // Fire toast + refresh UI
        $this->dispatch('alert', message: 'Holiday plan submitted successfully');
        $this->dispatch('$refresh');
    }



    public function render()
    {
        return view('livewire.holiday-plan-selector', [
            'presets' => HolidayPreset::whereNull('user_id')
                ->orWhere('user_id', auth()->id())
                ->get()
        ]);
    }
}
