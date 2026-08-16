<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\CssUnits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::orderBy('display_order')
            ->orderBy('id')
            ->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextDisplayOrder = Announcement::nextDisplayOrder();
        return view('admin.announcements.create', compact('nextDisplayOrder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateAnnouncement($request);
        [$startDate, $endDate] = $this->resolveScheduleWindow($validated);

        [$autoRepeat, $repeatDays] = $this->resolveDayRestriction($request);

        Announcement::create([
            'title' => $this->nullableTitle($validated['title'] ?? null),
            'content' => $this->normalizeAnnouncementContent($validated['content']),
            'title_font_size' => $this->normalizeStoredRem($validated['title_font_size'] ?? null, 2.25),
            'display_duration' => $validated['display_duration'],
            'font_size' => $this->normalizeStoredRem($validated['font_size'] ?? null, 1.5),
            'scroll_speed' => $validated['scroll_speed'],
            'text_color' => $validated['text_color'],
            'background_color' => $validated['background_color'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $request->boolean('is_active'),
            'auto_repeat' => $autoRepeat,
            'repeat_days' => $repeatDays,
            'priority' => $validated['priority'] ?? 1,
            'display_order' => (int) ($validated['display_order'] ?? Announcement::nextDisplayOrder()),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $this->validateAnnouncement($request);
        [$startDate, $endDate] = $this->resolveScheduleWindow($validated);

        [$autoRepeat, $repeatDays] = $this->resolveDayRestriction($request);

        $announcement->update([
            'title' => $this->nullableTitle($validated['title'] ?? null),
            'content' => $this->normalizeAnnouncementContent($validated['content']),
            'title_font_size' => $this->normalizeStoredRem($validated['title_font_size'] ?? null, 2.25),
            'display_duration' => $validated['display_duration'],
            'font_size' => $this->normalizeStoredRem($validated['font_size'] ?? null, 1.5),
            'scroll_speed' => $validated['scroll_speed'],
            'text_color' => $validated['text_color'],
            'background_color' => $validated['background_color'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $request->boolean('is_active'),
            'auto_repeat' => $autoRepeat,
            'repeat_days' => $repeatDays,
            'priority' => $validated['priority'] ?? 1,
            'display_order' => (int) ($validated['display_order'] ?? Announcement::nextDisplayOrder()),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'title_font_size' => 'required|numeric|min:0.75|max:10',
            'display_duration' => 'required|integer|min:1|max:120',
            'font_size' => 'required|numeric|min:0.75|max:10',
            'scroll_speed' => 'required|integer|min:1|max:10',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            // Separate date + time keep hours/minutes clear in the admin UI.
            // Still stored as combined datetime columns (start_date / end_date).
            'start_date' => 'nullable|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i|required_with:start_date',
            'end_date' => 'nullable|date_format:Y-m-d',
            'end_time' => 'nullable|date_format:H:i|required_with:end_date',
            'is_active' => 'sometimes|boolean',
            'auto_repeat' => 'sometimes|boolean',
            'repeat_days' => 'nullable|array',
            'repeat_days.*' => 'string|in:' . implode(',', Announcement::WEEKDAYS),
            'priority' => 'nullable|integer|between:1,3',
            'display_order' => 'required|integer|min:1|max:9999',
        ]);
    }

    /**
     * Persist day-of-week restriction only when enabled; clear leftover days otherwise.
     *
     * @return array{0: bool, 1: ?list<string>}
     */
    private function resolveDayRestriction(Request $request): array
    {
        $autoRepeat = $request->boolean('auto_repeat');
        $days = [];

        foreach ((array) $request->input('repeat_days', []) as $day) {
            if (!is_string($day)) {
                continue;
            }

            $normalized = strtolower(trim($day));
            if (in_array($normalized, Announcement::WEEKDAYS, true)) {
                $days[] = $normalized;
            }
        }

        $days = array_values(array_unique($days));

        if ($autoRepeat && $days === []) {
            throw ValidationException::withMessages([
                'repeat_days' => 'Select at least one day when "Only on specific days" is enabled.',
            ]);
        }

        return [$autoRepeat, $autoRepeat ? $days : null];
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveScheduleWindow(array $validated): array
    {
        $startDate = Announcement::combineScheduleDateTime(
            $validated['start_date'] ?? null,
            $validated['start_time'] ?? null,
            false
        );
        $endDate = Announcement::combineScheduleDateTime(
            $validated['end_date'] ?? null,
            $validated['end_time'] ?? null,
            true
        );

        if ($startDate && $endDate && $endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date/time must be the same as or after the start date/time.',
                'end_time' => 'End date/time must be the same as or after the start date/time.',
            ]);
        }

        return [$startDate, $endDate];
    }

    private function nullableTitle(mixed $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $trimmed = trim((string) $title);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Keep paragraph/line breaks from the textarea; only normalize newline style.
     */
    private function normalizeAnnouncementContent(string $content): string
    {
        return str_replace(["\r\n", "\r"], "\n", $content);
    }

    /**
     * Persist bare rem numbers (matches SlidingText / CssUnits::normalizeBoxRem).
     */
    private function normalizeStoredRem(mixed $value, float $default): float
    {
        $normalized = CssUnits::normalizeBoxRem($value, $default . 'rem');
        $numeric = (float) rtrim(strtolower($normalized), 'rem');

        if ($numeric <= 0) {
            return $default;
        }

        return round($numeric, 3);
    }
}
