<?php

namespace Database\Seeders;

use App\Models\HolidayPreset;
use Illuminate\Database\Seeder;
use App\Models\HolidayPresetDate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HolidayPresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* ============================
           US HOLIDAYS — 2026
        ============================ */

        $usPreset = HolidayPreset::create([
            'name' => 'US Holidays',
            'user_id' => null
        ]);

        $usDates = [
            '2026-01-01', // New Year's Day
            '2026-01-19', // Martin Luther King Jr. Day
            '2026-02-16', // Presidents' Day
            '2026-05-25', // Memorial Day
            '2026-07-04', // Independence Day
            '2026-09-07', // Labor Day
            '2026-10-12', // Columbus Day
            '2026-11-11', // Veterans Day
            '2026-11-26', // Thanksgiving
            '2026-12-25', // Christmas
        ];

        foreach ($usDates as $date) {
            HolidayPresetDate::create([
                'holiday_preset_id' => $usPreset->id,
                'holiday_date' => $date
            ]);
        }

        /* ============================
           INDIAN HOLIDAYS — 2026
        ============================ */

        $indiaPreset = HolidayPreset::create([
            'name' => 'Indian Holidays',
            'user_id' => null
        ]);

        $indiaDates = [
            '2026-01-26', // Republic Day
            '2026-03-06', // Holi
            '2026-03-29', // Good Friday
            '2026-04-14', // Dr. Ambedkar Jayanti
            '2026-05-01', // Labour Day
            '2026-08-15', // Independence Day
            '2026-09-05', // Ganesh Chaturthi
            '2026-10-02', // Gandhi Jayanti
            '2026-10-20', // Diwali
            '2026-12-25', // Christmas
        ];

        foreach ($indiaDates as $date) {
            HolidayPresetDate::create([
                'holiday_preset_id' => $indiaPreset->id,
                'holiday_date' => $date
            ]);
        }
    }
}
