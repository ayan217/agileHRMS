<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\BreakLog;
use App\Models\WorkSession;

class WorkTimer extends Component
{
    public WorkSession $session;

    private function resolveWorkDate()
    {
        $now = now();

        // If before 5 AM → count as previous day
        if ($now->hour < 5) {
            return $now->subDay()->toDateString();
        }

        return $now->toDateString();
    }


    public function mount()
    {
        $workDate = $this->resolveWorkDate();

        $session = WorkSession::where('user_id', auth()->id())
            ->where('work_date', $workDate)
            ->first();

        // Create new session if none exists
        if (!$session) {
            $session = WorkSession::create([
                'user_id'   => auth()->id(),
                'work_date' => $workDate,
                'status'    => 'idle',
                'total_work_seconds' => 0,
                'total_break_seconds' => 0,
            ]);
        }

        $this->session = $session;
    }

    /* ---------------- CLOCK IN ---------------- */

    public function clockIn()
    {
        $this->dispatch('hardReload');
        
        if ($this->session->status === 'working') {
            return;
        }

        $now = now();

        // If previously clocked out → calculate gap as break
        if ($this->session->clock_out) {

            $gapSeconds = Carbon::parse($this->session->clock_out)
                ->diffInSeconds($now);

            if ($gapSeconds > 0) {

                BreakLog::create([
                    'work_session_id' => $this->session->id,
                    'break_start'     => $this->session->clock_out,
                    'break_end'       => $now,
                    'duration_seconds' => $gapSeconds
                ]);

                $this->session->increment('total_break_seconds', $gapSeconds);
            }
        }

        $this->session->update([
            'clock_in'  => $now,
            'clock_out' => null,
            'status'    => 'working',
        ]);
    }

    /* ---------------- CLOCK OUT ---------------- */

    public function clockOut()
    {
        if ($this->session->status !== 'working') {
            return;
        }

        $now = now();

        $workedSeconds = Carbon::parse($this->session->clock_in)
            ->diffInSeconds($now);

        if ($workedSeconds > 0) {
            $this->session->increment('total_work_seconds', $workedSeconds);
        }

        $this->session->update([
            'clock_out' => $now,
            'status'    => 'idle',
        ]);

        $this->dispatch('hardReload');
    }

    /* ---------------- RENDER ---------------- */

    public function render()
    {
        return view('livewire.work-timer');
    }
}
