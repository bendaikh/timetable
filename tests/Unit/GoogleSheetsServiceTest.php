<?php

namespace Tests\Unit;

use App\Services\GoogleSheetsService;
use Tests\TestCase;

class GoogleSheetsServiceTest extends TestCase
{
    public function test_parse_prayer_times_data_maps_eid_columns_and_treats_placeholders_as_empty(): void
    {
        $service = new GoogleSheetsService();

        $sheetData = [
            [
                'Date',
                'Fajr Beginning',
                'Fajr Jamaat',
                'Sunrise',
                'Zuhr Beginning',
                'Zuhr Jamaat',
                'Asr Beginning',
                'Asr Jamaat',
                'Maghrib Beginning',
                'Maghrib Jamaat',
                'Isha Beginning',
                'Isha Jamaat',
                'Jumma 1',
                'Jumma 2',
                'Eid Prayer 1',
                'Eid Prayer 2',
            ],
            [
                '2026-02-26',
                '05:03',
                '05:18',
                '07:02',
                '12:24',
                '12:45',
                '15:44',
                '16:00',
                '17:43',
                '17:43',
                '19:15',
                '19:45',
                '12:45',
                '--:--',
                '08:00',
                '--:--',
            ],
        ];

        $result = $service->parsePrayerTimesData($sheetData);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['prayer_times']);
        $this->assertSame('08:00:00', $result['prayer_times'][0]['eid_prayer_1']);
        $this->assertNull($result['prayer_times'][0]['eid_prayer_2']);
        $this->assertNull($result['prayer_times'][0]['jumah_2']);
    }
}
