# Troubleshooting: Media Not Showing

## Quick Fix Applied

I just fixed a critical issue with time calculation. The problem was using `diffInSeconds()` incorrectly, which could return wrong elapsed time.

**Changed:**
- ❌ `$now->diffInSeconds($scheduleStart)` - Always positive (absolute)
- ✅ `$scheduleStart->diffInSeconds($now, false)` - Signed difference (can be negative)

This ensures we correctly check if a schedule has started and calculate elapsed time.

## Debug Steps

### 1. Check Debug Endpoint
Open this URL in your browser:
```
http://your-site.com/api/debug-schedules
```

This will show:
- All active schedules
- Current media info
- What the system thinks should display right now

### 2. Check Browser Console
1. Open your timetable page
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Look for messages like:
   - "New media in sequence, displaying: ..."
   - "No media returned from API"
   - Any error messages

### 3. Check Network Tab
1. In Developer Tools, go to Network tab
2. Filter by "current-media"
3. Click on the request
4. Check the Response to see what the API is returning

### 4. Verify Schedule Settings

Make sure your schedule has:

**✓ Active Status**
- Schedule is marked as "Active"
- Media items are marked as "Active"

**✓ Correct Time Settings**
- Schedule Type is set correctly
- Prayer is selected (if not Full Time Poster)
- Minutes before/after is set
- Jamaat offset is configured in settings

**✓ Multiple Media Configured**
- At least one media selected
- Each media has Duration set
- Each media has Priority set (1, 2, 3...)

**✓ Prayer Times Exist**
- Today's prayer times are in the database
- Check Admin → Prayer Times

## Common Issues & Solutions

### Issue 1: "No media returned from API"
**Cause:** Schedule might not be active yet, or already ended

**Check:**
```
Schedule Time: When should it display?
Current Time: What time is it now?
```

**Solution:**
- For testing, use "Full Time Poster" (displays all day)
- Or set "Minutes After Prayer" with a recent prayer time

### Issue 2: Media shows but doesn't transition
**Cause:** Frontend tracking or timing issue

**Solution:**
- Hard refresh the page (Ctrl + F5)
- Clear browser cache
- Check console for JavaScript errors

### Issue 3: First media shows, second doesn't
**Cause:** The boundary condition fix should have resolved this

**Solution:**
- The latest code changes fixed this
- Try creating a new schedule to test

### Issue 4: No media displays at all
**Possible causes:**
1. Schedule not active
2. Prayer times missing
3. Wrong prayer selected
4. Schedule time hasn't arrived yet
5. Schedule time already passed

**Debug steps:**
```
1. Check /api/debug-schedules
2. Verify prayer times exist for today
3. Check schedule's prayer and timing
4. Ensure media is active
```

## Manual Test Procedure

### Test 1: Full Time Poster (Simplest)
```
1. Create new schedule
2. Schedule Type: Full Time Poster
3. Select 2 media items
4. Set durations (e.g., 30s, 30s)
5. Set priorities (1, 2)
6. Mark as Active
7. Save
8. Open timetable page
9. Should see media cycling immediately
```

### Test 2: Minutes After Prayer
```
1. Check current time (e.g., 3:45 PM)
2. Find a prayer that already happened (e.g., Asr at 3:00 PM)
3. Create schedule:
   - Type: Minutes After Prayer
   - Prayer: Asr
   - Minutes: 1
   - Media: 2 items with durations
   - Priorities: 1, 2
4. This schedule already passed, so won't display
5. Instead, use NEXT prayer and set it for future

Better: Use prayer happening soon
```

### Test 3: Check Current API Response
Open browser console and run:
```javascript
fetch('/api/current-media')
  .then(r => r.json())
  .then(d => console.log('Media API:', d))
```

Should return either:
- `{ media: {...} }` - Media should display
- `{ media: null }` - No media scheduled now

## What The Fix Changed

### Before (Broken):
```php
$elapsedSeconds = $now->diffInSeconds($scheduleStart);
// Always positive! Could be wrong if schedule hasn't started
```

### After (Fixed):
```php
$elapsedSeconds = $scheduleStart->diffInSeconds($now, false);
// Negative if schedule hasn't started yet
// Positive if schedule is running
if ($elapsedSeconds < 0) {
    return null; // Schedule hasn't started
}
```

## Expected Behavior Now

### Scenario 1: Schedule Active
```
Schedule Start: 4:00 PM (Asr + 1 min)
Current Time: 4:00:15 PM (15 seconds into schedule)

Media 1: Duration 30s (0-30s)
Media 2: Duration 30s (30-60s)

At t=15s: Shows Media 1 ✓
At t=30s: Shows Media 2 ✓
At t=45s: Shows Media 2 ✓
At t=60s: Schedule ended, hide media ✓
```

### Scenario 2: Schedule Not Started
```
Schedule Start: 5:00 PM
Current Time: 4:30 PM

Result: No media displays ✓
(Correctly waiting for schedule time)
```

### Scenario 3: Schedule Ended
```
Schedule: Media 1 (30s), Media 2 (30s)
Start: 4:00 PM
End: 4:01 PM (60 seconds total)
Current Time: 4:05 PM

Result: No media displays ✓
(Schedule already finished)
```

## If Still Not Working

1. **Share the debug endpoint output:**
   - Visit `/api/debug-schedules`
   - Copy the JSON response
   - Share with developer

2. **Share schedule details:**
   - Schedule Type
   - Prayer selected
   - Minutes before/after
   - Media items (titles, durations, priorities)
   - Active status

3. **Share timing info:**
   - Current time
   - Prayer time (beginning + jamaat)
   - When schedule should run

4. **Check Laravel logs:**
   - `storage/logs/laravel.log`
   - Look for errors

5. **Browser console errors:**
   - Any red error messages
   - JavaScript errors
   - Network failures

---

**The latest fixes should resolve the media sequencing issues. Try refreshing the page and testing with a Full Time Poster schedule first.**

