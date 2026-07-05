<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::set(
            'timezone',
            'Europe/London',
            'string',
            'Mosque display timezone (Al Hidaya Academy, Bradford, UK)'
        );
    }

    public function down(): void
    {
        // Intentionally left blank — timezone is site configuration.
    }
};
