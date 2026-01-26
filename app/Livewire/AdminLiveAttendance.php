<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkSession;
use App\Models\UserHolidayPlan;
use App\Models\HolidayPresetDate;
use Carbon\Carbon;

class AdminLiveAttendance extends Component
{
    public $selectedDate;

    // Holiday modal state
    public $showHolidayModal = false;
    public $holidayUserId = null;
    public $holidayDates = [];

    public function mount()
    {
        $this->selectedDate = now()->toDateString();
    }

    /* Quick filters */
    public function setToday()
    {
        $this->selectedDate = now()->toDateString();
    }

    public function setPreviousDay()
    {
        $this->selectedDate = now()->subDay()->toDateString();
    }

    /* Open employee holiday list modal */
    public function openHolidayList($userId)
    {
        $this->holidayUserId = $userId;
        $this->holidayDates = [];

        $plan = UserHolidayPlan::where('user_id', $userId)
            ->where('year', now()->year)
            ->first();

        if ($plan) {
            $this->holidayDates = HolidayPresetDate::where('holiday_preset_id', $plan->holiday_preset_id)
                ->pluck('holiday_date')
                ->toArray();
        }

        $this->showHolidayModal = true;
    }

    public function closeHolidayList()
    {
        $this->showHolidayModal = false;
    }

    /* Main attendance dataset */
    public function getTodaySessionsProperty()
    {
        $today = $this->selectedDate;

        return User::where('role', 'user')
            ->with(['workSessions' => function ($q) use ($today) {
                $q->where('work_date', $today)->latest('id');
            }])
            ->get()
            ->map(function ($user) use ($today) {

                $session = $user->workSessions->first();

                /* ===== If no session → Absent ===== */
                if (!$session) {

                    // Next holiday calc
                    $nextHolidayInDays = $this->getNextHolidayDays($user->id);

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'status' => 'absent',
                        'clock_in' => null,
                        'clock_out' => null,
                        'worked' => 0,
                        'break' => 0,
                        'late' => false,
                        'next_holiday' => $nextHolidayInDays
                    ];
                }

                /* ===== Worked seconds ===== */
                if (!$session->clock_out && $session->clock_in && $today === now()->toDateString()) {
                    $worked = Carbon::parse($session->clock_in)
                        ->diffInSeconds(now()) - $session->total_break_seconds;
                } else {
                    $worked = $session->total_work_seconds;
                }

                /* ===== Next holiday ===== */
                $nextHolidayInDays = $this->getNextHolidayDays($user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $session->status,
                    'clock_in' => $session->clock_in,
                    'clock_out' => $session->clock_out,
                    'worked' => max(0, $worked),
                    'break' => $session->total_break_seconds,
                    'late' => $session->is_late,
                    'next_holiday' => $nextHolidayInDays
                ];
            });
    }

    /* ===== Helper: Get next holiday in days ===== */
    private function getNextHolidayDays($userId)
    {
        $plan = UserHolidayPlan::where('user_id', $userId)
            ->where('year', now()->year)
            ->first();

        if (!$plan) return null;

        $today = now()->toDateString();

        $nextDate = HolidayPresetDate::where('holiday_preset_id', $plan->holiday_preset_id)
            ->where('holiday_date', '>', $today)
            ->orderBy('holiday_date')
            ->value('holiday_date');

        if (!$nextDate) return null;

        return now()->startOfDay()->diffInDays(Carbon::parse($nextDate)->startOfDay());
    }

    public function render()
    {
        return view('livewire.admin-live-attendance');
    }
}
