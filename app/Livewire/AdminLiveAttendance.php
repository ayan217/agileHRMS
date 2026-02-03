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

    private function resolveBusinessDate($date = null)
    {
        $now = $date ? Carbon::parse($date) : now();

        if ($now->hour < 5) {
            return $now->subDay()->toDateString();
        }

        return $now->toDateString();
    }


    public function mount()
    {
        $this->selectedDate = $this->resolveBusinessDate();
    }

    /* Quick filters */
    public function setToday()
    {
        $this->selectedDate = $this->resolveBusinessDate();
    }

    public function setPreviousDay()
    {
        $this->selectedDate = Carbon::parse($this->resolveBusinessDate())
            ->subDay()
            ->toDateString();
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

        // Resolve current business day (5 AM boundary)
        $currentBusinessDate = now()->hour < 5
            ? now()->subDay()->toDateString()
            : now()->toDateString();

        return User::where('role', 'user')
            ->with(['workSessions' => function ($q) use ($today) {
                $q->where('work_date', $today)->latest('id');
            }])
            ->get()
            ->map(function ($user) use ($today, $currentBusinessDate) {

                $session = $user->workSessions->first();

                /* ===== If no session → Absent ===== */
                if (!$session) {

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

                /* ===== Worked seconds (NEW CORRECT LOGIC) ===== */

                $worked = $session->total_work_seconds ?? 0;

                // If currently working AND admin is viewing active business day
                if (
                    $session->status === 'working' &&
                    !$session->clock_out &&
                    $today === $currentBusinessDate
                ) {
                    $worked += Carbon::parse($session->clock_in)->diffInSeconds(now());
                }

                $nextHolidayInDays = $this->getNextHolidayDays($user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $session->status,
                    'clock_in' => $session->clock_in,
                    'clock_out' => $session->clock_out,
                    'worked' => max(0, $worked),
                    'break' => $session->total_break_seconds ?? 0,
                    'late' => $session->is_late ?? false,
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

    public function resetSessionToStandardShift($userId)
    {
        $date = Carbon::parse($this->selectedDate);

        // Define fixed shift
        $clockIn  = $date->copy()->setTime(15, 0);   // 3:00 PM
        $clockOut = $date->copy()->addDay()->setTime(0, 0); // 12:00 AM next day

        // Calculate worked seconds
        $workedSeconds = $clockIn->diffInSeconds($clockOut);

        // Find or create session
        $session = WorkSession::firstOrCreate(
            [
                'user_id'   => $userId,
                'work_date' => $this->selectedDate,
            ],
            [
                'total_work_seconds'  => 0,
                'total_break_seconds' => 0,
                'status'              => 'idle',
            ]
        );

        // Reset break logs (optional but recommended)
        $session->breaks()->delete();

        // Update session
        $session->update([
            'clock_in'             => $clockIn,
            'clock_out'            => $clockOut,
            'total_work_seconds'   => $workedSeconds,
            'total_break_seconds'  => 0,
            'status'               => 'completed',
            'is_late'              => false,
        ]);

        // Refresh UI
        $this->dispatch('$refresh');
    }


    public function render()
    {
        return view('livewire.admin-live-attendance');
    }
}
