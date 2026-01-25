<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySelector extends Component
{
    private $totalHolidays = 12;
    public $month;
    public $year;

    public function mount()
    {
        $this->month = now()->month;
        $this->year  = now()->year;
    }

    public function selectDate($date)
    {
        $user = auth()->user();
        $d = Carbon::parse($date);

        // Rules
        if ($d->isBefore(now()->addDays(14)->startOfDay())) return;
        if ($d->isSunday()) return;


        $count = Holiday::where('user_id', $user->id)
            ->whereYear('holiday_date', $this->year)
            ->count();

        if ($count >= $this->totalHolidays) return;

        Holiday::firstOrCreate([
            'user_id' => $user->id,
            'holiday_date' => $d->toDateString()
        ]);
    }

    public function removeDate($date)
    {
        Holiday::where('user_id', auth()->id())
            ->where('holiday_date', $date)
            ->delete();
    }

    public function changeMonth($direction)
    {
        $date = Carbon::create($this->year, $this->month, 1)
            ->addMonths($direction);

        $this->month = $date->month;
        $this->year  = $date->year;
    }

    public function render()
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $end   = $start->copy()->endOfMonth();

        $holidays = Holiday::where('user_id', auth()->id())
            ->whereYear('holiday_date', $this->year)
            ->pluck('holiday_date')
            ->toArray();

        $remaining = $this->totalHolidays - count($holidays);

        return view('livewire.holiday-selector', [
            'start' => $start,
            'end' => $end,
            'holidays' => $holidays,
            'remaining' => $remaining
        ]);
    }
}
