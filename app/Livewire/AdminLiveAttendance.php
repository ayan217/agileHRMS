<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WorkSession;
use Carbon\Carbon;

class AdminLiveAttendance extends Component
{
    protected $listeners = ['refreshAdmin' => '$refresh'];

    public function getTodaySessionsProperty()
    {
        $today = now()->toDateString();

        return User::where('role', 'user')
            ->with(['workSessions' => function ($q) use ($today) {
                $q->where('work_date', $today)
                    ->latest('id');
            }])
            ->get()
            ->map(function ($user) {
                $session = $user->workSessions->first();

                if (!$session) {
                    return [
                        'name' => $user->name,
                        'status' => 'absent',
                        'clock_in' => null,
                        'clock_out' => null,
                        'worked' => 0,
                        'break' => 0,
                        'late' => false
                    ];
                }

                // Live worked seconds if still open
                if (!$session->clock_out && $session->clock_in) {
                    $worked = Carbon::parse($session->clock_in)
                        ->diffInSeconds(now()) - $session->total_break_seconds;
                } else {
                    $worked = $session->total_work_seconds;
                }

                return [
                    'name' => $user->name,
                    'status' => $session->status,
                    'clock_in' => $session->clock_in,
                    'clock_out' => $session->clock_out,
                    'worked' => max(0, $worked),
                    'break' => $session->total_break_seconds,
                    'late' => $session->is_late
                ];
            });
    }

    public function render()
    {
        return view('livewire.admin-live-attendance');
    }
}
