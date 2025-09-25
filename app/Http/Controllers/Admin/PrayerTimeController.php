<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrayerTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrayerTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prayerTimes = PrayerTime::orderBy('date', 'desc')->paginate(15);
        return view('admin.prayer-times.index', compact('prayerTimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.prayer-times.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:prayer_times,date',
            'fajr' => 'required|date_format:H:i',
            'zohar' => 'required|date_format:H:i',
            'asr' => 'required|date_format:H:i',
            'maghrib' => 'required|date_format:H:i',
            'isha' => 'required|date_format:H:i',
            'sun_rise' => 'nullable|date_format:H:i',
            'jumah_1' => 'nullable|date_format:H:i',
            'jumah_2' => 'nullable|date_format:H:i',
        ]);

        $data = $request->all();
        // Convert time format to include seconds
        foreach (['fajr', 'zohar', 'asr', 'maghrib', 'isha', 'sun_rise', 'jumah_1', 'jumah_2'] as $field) {
            if ($data[$field]) {
                $data[$field] = $data[$field] . ':00';
            }
        }

        PrayerTime::create($data);

        return redirect()->route('admin.prayer-times.index')
            ->with('success', 'Prayer times created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PrayerTime $prayerTime)
    {
        return view('admin.prayer-times.show', compact('prayerTime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrayerTime $prayerTime)
    {
        return view('admin.prayer-times.edit', compact('prayerTime'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PrayerTime $prayerTime)
    {
        $request->validate([
            'date' => 'required|date|unique:prayer_times,date,' . $prayerTime->id,
            'fajr' => 'required|date_format:H:i',
            'zohar' => 'required|date_format:H:i',
            'asr' => 'required|date_format:H:i',
            'maghrib' => 'required|date_format:H:i',
            'isha' => 'required|date_format:H:i',
            'sun_rise' => 'nullable|date_format:H:i',
            'jumah_1' => 'nullable|date_format:H:i',
            'jumah_2' => 'nullable|date_format:H:i',
        ]);

        $data = $request->all();
        // Convert time format to include seconds
        foreach (['fajr', 'zohar', 'asr', 'maghrib', 'isha', 'sun_rise', 'jumah_1', 'jumah_2'] as $field) {
            if ($data[$field]) {
                $data[$field] = $data[$field] . ':00';
            }
        }

        $prayerTime->update($data);

        return redirect()->route('admin.prayer-times.index')
            ->with('success', 'Prayer times updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PrayerTime $prayerTime)
    {
        $prayerTime->delete();

        return redirect()->route('admin.prayer-times.index')
            ->with('success', 'Prayer times deleted successfully.');
    }
}
