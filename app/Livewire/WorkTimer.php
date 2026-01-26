<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\BreakLog;
use function Livewire\on;
use App\Models\WorkSession;
use Livewire\Attributes\On;

class WorkTimer extends Component
{
    private const clockoutFailsafeHours = 12;
    private const lateHourStart = '16:00';
    public WorkSession $session;
    public ?string $activeBreakStart = null;

    public function mount()
    {
        // Auto-close forgotten open session (failsafe)
        WorkSession::where('user_id', auth()->id())
            ->whereNull('clock_out')
            ->where('clock_in', '<', now()->subHours(self::clockoutFailsafeHours))
            ->update([
                'clock_out' => now(),
                'status'    => 'incomplete'
            ]);

        // Always load today's session if exists
        $session = WorkSession::where('user_id', auth()->id())
            ->where('work_date', now()->toDateString())
            ->first();

        // If no row exists for today, create idle row
        if (!$session) {
            $session = WorkSession::create([
                'user_id'   => auth()->id(),
                'work_date' => now()->toDateString(),
                'status'    => 'idle'
            ]);
        }

        $this->session = $session;

        // Restore break if session is in break state
        if ($this->session->status === 'break') {
            $break = BreakLog::where('work_session_id', $this->session->id)
                ->whereNull('break_end')
                ->first();

            $this->activeBreakStart = $break?->break_start;
        }
    }



    /* ---------------- CLOCK IN ---------------- */

    public function clockIn()
    {
        if ($this->session->clock_in) return;

        $this->session->update([
            'clock_in' => now(),
            'status'   => 'working',
            'is_late'  => now()->format('H:i') > self::lateHourStart
        ]);
        $this->dispatch('hardReload');
    }

    /* ---------------- START BREAK ---------------- */

    public function startBreak()
    {
        if ($this->session->status !== 'working') return;

        $break = BreakLog::create([
            'work_session_id' => $this->session->id,
            'break_start'     => now()
        ]);

        $this->session->update(['status' => 'break']);
        $this->activeBreakStart = $break->break_start;
    }

    /* ---------------- RESUME WORK ---------------- */

    public function resumeWork()
    {
        $break = BreakLog::where('work_session_id', $this->session->id)
            ->whereNull('break_end')
            ->first();

        if ($break) {
            $seconds = (int) ceil(
                now()->floatDiffInSeconds(Carbon::parse($break->break_start), true)
            );

            $break->update([
                'break_end'       => now(),
                'duration_seconds' => $seconds
            ]);
        }

        // Recalculate total break seconds from DB
        $totalBreak = $this->session->breaks()->sum('duration_seconds');

        $this->session->update([
            'total_break_seconds' => max(0, $totalBreak),
            'status'              => 'working'
        ]);

        $this->activeBreakStart = null;
    }

    /* ---------------- CLOCK OUT ---------------- */
    #[On('confirmClockOut')]
    public function clockOut()
    {
        if (!$this->session->clock_in || $this->session->clock_out) return;

        $workedSeconds = Carbon::parse($this->session->clock_in)->diffInSeconds(now()) -
            $this->session->total_break_seconds;

        $status = $workedSeconds >= (9 * 60 * 60)
            ? 'completed'
            : 'incomplete';

        $this->session->update([
            'clock_out'          => now(),
            'total_work_seconds' => $workedSeconds,
            'status'             => $status
        ]);
        $this->dispatch('alert', message: 'Clocked out successfully');
        $this->dispatch('hardReload');
    }

    /* ---------------- RENDER ---------------- */

    public function render()
    {
        return view('livewire.work-timer');
    }
}
