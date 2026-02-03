<div class="work-card">

    <!-- Header -->
    <div class="work-header">
        <h3>Work Session</h3>

        <span class="status-badge status-{{ $session->status }}">
            {{ strtoupper($session->status) }}
        </span>
    </div>

    <!-- Global Timer -->
    <div class="timer-section">
        <div class="label">Session Timer</div>
        <div id="globalTimer" class="global-timer">00:00:00</div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">

        <div class="info-box">
            <span class="info-label">Clock In</span>
            <span class="info-value">
                {{ $session->clock_in ? \Carbon\Carbon::parse($session->clock_in)->format('h:i:s A') : '--' }}
            </span>
        </div>

        <div class="info-box">
            <span class="info-label">Clock Out</span>
            <span class="info-value">
                {{ $session->clock_out ? \Carbon\Carbon::parse($session->clock_out)->format('h:i:s A') : '--' }}
            </span>
        </div>

        <div class="info-box">
            <span class="info-label">Break Taken</span>
            <span class="info-value">
                {{ gmdate('H:i:s', $session->total_break_seconds ?? 0) }}
            </span>
        </div>

        <div class="info-box">
            <span class="info-label">Worked Time</span>
            <span class="info-value">
                @php
                    $workedSeconds = $session->total_work_seconds ?? 0;

                    if ($session->status === 'working' && $session->clock_in) {
                        $workedSeconds += \Carbon\Carbon::parse($session->clock_in)->diffInSeconds(now());
                    }
                @endphp

                {{ gmdate('H:i:s', $workedSeconds) }}

            </span>
        </div>

    </div>

    <!-- Action Buttons -->
    <div class="button-row">

        @if ($session->status !== 'working')
            <button type="button" wire:click="clockIn" class="btn btn-green">
                Clock In
            </button>
        @endif

        @if ($session->status === 'working')
            <button type="button" wire:click="clockOut" class="btn btn-red">
                Clock Out
            </button>
        @endif

    </div>


</div>



<!-- SINGLE GLOBAL TIMER -->
<script>
    document.addEventListener('DOMContentLoaded', startGlobalTimer);
    document.addEventListener('livewire:navigated', startGlobalTimer);
    document.addEventListener('livewire:init', startGlobalTimer);

    function startGlobalTimer() {

        if (window.globalTimerLoop) {
            clearInterval(window.globalTimerLoop);
        }

        const status = @json($session->status);
        const clockIn = @json($session->clock_in);
        const totalWorked = @json($session->total_work_seconds ?? 0);

        const display = document.getElementById('globalTimer');

        let baseSeconds = parseInt(totalWorked) || 0;

        function format(sec) {
            sec = Math.max(0, Math.floor(sec));
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            return String(h).padStart(2, '0') + ':' +
                String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
        }

        // If not currently working → just show stored total
        if (status !== 'working' || !clockIn) {
            display.innerText = format(baseSeconds);
            return;
        }

        const tClockIn = new Date(clockIn).getTime();

        function tick() {
            const now = Date.now();
            const runningSeconds = (now - tClockIn) / 1000;

            const total = baseSeconds + runningSeconds;

            display.innerText = format(total);
        }

        tick();
        window.globalTimerLoop = setInterval(tick, 1000);
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('hardReload', () => {
            window.location.reload(true);
        });
    });
</script>
