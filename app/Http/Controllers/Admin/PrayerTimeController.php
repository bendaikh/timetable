<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrayerTime;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    /**
     * Show the Google Sheets import form.
     */
    public function showImport()
    {
        return view('admin.prayer-times.import');
    }

    /**
     * Process Google Sheets import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:url,file',
            'google_sheets_url' => 'required_if:import_type,url|nullable|url',
            'google_sheets_file' => 'required_if:import_type,file|nullable|file|mimes:csv,xlsx,xls|max:10240',
            'range' => 'nullable|string',
            'overwrite_existing' => 'boolean',
        ]);

        try {
            $googleSheetsService = new GoogleSheetsService();
            
            // Get data based on import type
            if ($request->import_type === 'file') {
                $sheetData = $googleSheetsService->getSheetDataFromFile($request->file('google_sheets_file'));
            } else {
                // Validate URL
                $googleSheetsService->validateUrl($request->google_sheets_url);
                
                // Get data from Google Sheets
                $range = $request->range ?: 'A:Z';
                $sheetData = $googleSheetsService->getSheetData($request->google_sheets_url, $range);
            }
            
            // Parse prayer times data
            $result = $googleSheetsService->parsePrayerTimesData($sheetData);
            $prayerTimes = $result['prayer_times'];
            $errors = $result['errors'];
            
            if (empty($prayerTimes)) {
                return redirect()->back()
                    ->with('error', 'No valid prayer times found in the data. Please check the data format.')
                    ->with('import_errors', $errors);
            }
            
            // Import prayer times
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            
            DB::beginTransaction();
            
            try {
                foreach ($prayerTimes as $prayerTimeData) {
                    $existing = PrayerTime::where('date', $prayerTimeData['date'])->first();
                    
                    if ($existing) {
                        if ($request->overwrite_existing) {
                            $existing->update($prayerTimeData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        PrayerTime::create($prayerTimeData);
                        $imported++;
                    }
                }
                
                DB::commit();
                
                $message = "Import completed successfully! ";
                $message .= "Imported: {$imported}, Updated: {$updated}, Skipped: {$skipped}";
                
                if (!empty($errors)) {
                    $message .= ". " . count($errors) . " rows had errors.";
                }
                
                return redirect()->route('admin.prayer-times.index')
                    ->with('success', $message)
                    ->with('import_errors', $errors);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Preview Google Sheets data before import.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:url,file',
            'google_sheets_url' => 'required_if:import_type,url|nullable|url',
            'google_sheets_file' => 'required_if:import_type,file|nullable|file|mimes:csv,xlsx,xls|max:10240',
            'range' => 'nullable|string',
        ]);

        try {
            $googleSheetsService = new GoogleSheetsService();
            
            // Get data based on import type
            if ($request->import_type === 'file') {
                $sheetData = $googleSheetsService->getSheetDataFromFile($request->file('google_sheets_file'));
            } else {
                // Validate URL
                $googleSheetsService->validateUrl($request->google_sheets_url);
                
                // Get data from Google Sheets
                $range = $request->range ?: 'A:Z';
                $sheetData = $googleSheetsService->getSheetData($request->google_sheets_url, $range);
            }
            
            // Parse prayer times data
            $result = $googleSheetsService->parsePrayerTimesData($sheetData);
            $prayerTimes = $result['prayer_times'];
            $errors = $result['errors'];
            
            // Check for existing prayer times
            $existingDates = [];
            if (!empty($prayerTimes)) {
                $dates = collect($prayerTimes)->pluck('date')->toArray();
                $existingDates = PrayerTime::whereIn('date', $dates)->pluck('date')->toArray();
            }
            
            return view('admin.prayer-times.preview', compact('prayerTimes', 'errors', 'existingDates', 'request'));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Preview failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Bulk delete prayer times.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'prayer_time_ids' => 'required|array|min:1',
            'prayer_time_ids.*' => 'exists:prayer_times,id',
        ]);

        try {
            $prayerTimeIds = $request->prayer_time_ids;
            $deletedCount = PrayerTime::whereIn('id', $prayerTimeIds)->delete();
            
            if ($deletedCount > 0) {
                $message = $deletedCount === 1 
                    ? "Successfully deleted 1 prayer time." 
                    : "Successfully deleted {$deletedCount} prayer times.";
                    
                return redirect()->route('admin.prayer-times.index')
                    ->with('success', $message);
            } else {
                return redirect()->back()
                    ->with('error', 'No prayer times were deleted.');
            }
                
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Bulk delete failed. Please try again.');
        }
    }
}
