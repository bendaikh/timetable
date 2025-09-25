<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Hadeeth extends Model
{
    protected $fillable = [
        'arabic_text',
        'english_translation',
        'reference',
        'is_active',
        'display_date',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_date' => 'date',
        'display_order' => 'integer'
    ];

    public static function getTodayHadeeth()
    {
        $today = Carbon::today();
        
        // First, try to get a hadeeth specifically set for today
        $hadeeth = self::where('is_active', true)
            ->whereDate('display_date', $today)
            ->first();

        // If no specific hadeeth for today, get a random active one
        if (!$hadeeth) {
            $hadeeth = self::where('is_active', true)
                ->whereNull('display_date')
                ->inRandomOrder()
                ->first();
        }

        // If still no hadeeth, get any active one
        if (!$hadeeth) {
            $hadeeth = self::where('is_active', true)
                ->inRandomOrder()
                ->first();
        }

        return $hadeeth;
    }

    public static function getOrderedHadeeths()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('created_at')
            ->get();
    }
}
