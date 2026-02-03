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
        $userId = auth()->id();
        $now = now();

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ Define 5 AM Business Day Cutoff
    |--------------------------------------------------------------------------
    */

        $cutoffToday = $now->copy()->setTime(5, 0);

        // If current time is before 5 AM,
        // the cutoff belongs to yesterday
        if ($now->lt($cutoffToday)) {
            $cutoffToday->subDay();
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ Auto-close any open session that crossed 5 AM
    |--------------------------------------------------------------------------
    */

        $openSession = WorkSession::where('user_id', $userId)
            ->whereNull('clock_out')
            ->first();

        if (
            $openSession && Carbon::parse($openSession->clock_in)->lt($cutoffToday)
        ) {

            $workedSeconds = $openSession->clock_in
                ->diffInSeconds($cutoffToday);

            $openSession->increment('total_work_seconds', $workedSeconds);

            $openSession->update([
                'clock_out' => $cutoffToday,
                'status'    => 'idle',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ Resolve Correct Work Date (Business Day)
    |--------------------------------------------------------------------------
    */

        $workDate = $now->hour < 5
            ? $now->copy()->subDay()->toDateString()
            : $now->toDateString();

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ Load or Create Today's Session
    |--------------------------------------------------------------------------
    */

        $session = WorkSession::where('user_id', $userId)
            ->where('work_date', $workDate)
            ->first();

        if (!$session) {
            $session = WorkSession::create([
                'user_id' => $userId,
                'work_date' => $workDate,
                'status' => 'idle',
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
