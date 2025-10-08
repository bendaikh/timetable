# Minutes After Prayer Limit Update

## Change Summary
Increased the maximum limit for "Minutes After Prayer" from **120 minutes (2 hours)** to **480 minutes (8 hours)**.

## Why This Change?
This provides **more flexibility** for scheduling media after prayer times:
- ✅ Short displays (1-5 minutes) - Quick announcements
- ✅ Medium displays (10-60 minutes) - Standard content
- ✅ Long displays (2-8 hours) - Extended information boards
- ✅ All-day content after Fajr (up to 8 hours until next major prayer)

## What Changed

### 1. Create Form
**File:** `resources/views/admin/media-schedules/create.blade.php`

**Before:**
```html
<input type="number" ... min="1" max="120">
<div class="form-text">How many minutes after prayer to start displaying (1-120 minutes)</div>
```

**After:**
```html
<input type="number" ... min="1" max="480">
<div class="form-text">How many minutes after prayer to start displaying (1-480 minutes / up to 8 hours)</div>
```

### 2. Edit Form
**File:** `resources/views/admin/media-schedules/edit.blade.php`

Same update as create form.

### 3. Validation Rules - Store
**File:** `app/Http/Controllers/Admin/MediaScheduleController.php` (store method)

**Before:**
```php
'minutes_after_prayer' => 'nullable|integer|min:1|max:120',
```

**After:**
```php
'minutes_after_prayer' => 'nullable|integer|min:1|max:480',
```

### 4. Validation Rules - Update
**File:** `app/Http/Controllers/Admin/MediaScheduleController.php` (update method)

Same update as store method.

## Current Limits Summary

| Setting | Min | Max | Description |
|---------|-----|-----|-------------|
| **Minutes Before Prayer** | 5 | 120 | 5 min to 2 hours before Jamaat |
| **Minutes After Prayer** | 1 | **480** | 1 min to **8 hours** after Jamaat |

## Use Cases Enabled

### Use Case 1: After Fajr Display
```
Prayer: Fajr (6:00 AM)
Minutes After: 240 (4 hours)
Display Time: 6:00 AM - 10:00 AM

Perfect for: Morning announcements, event info
```

### Use Case 2: After Zohar Display
```
Prayer: Zohar (1:00 PM)
Minutes After: 180 (3 hours)
Display Time: 1:00 PM - 4:00 PM

Perfect for: Afternoon programs, educational content
```

### Use Case 3: After Maghrib Display
```
Prayer: Maghrib (7:00 PM)
Minutes After: 300 (5 hours)
Display Time: 7:00 PM - 12:00 AM

Perfect for: Evening announcements, night programs
```

### Use Case 4: Quick Announcement
```
Prayer: Any
Minutes After: 2
Display Time: 2 minutes after prayer

Perfect for: Urgent announcements, reminders
```

## Notes

### Minutes Before Prayer (Unchanged)
- Still limited to **120 minutes (2 hours)**
- This is appropriate because:
  - People don't usually arrive more than 2 hours early
  - Prevents display too far before prayer time
  - Keeps displays relevant to upcoming prayer

### Why 480 Minutes (8 Hours)?
- **Covers gaps between prayers**:
  - Fajr → Zohar: ~6-7 hours
  - Zohar → Asr: ~3-4 hours
  - Asr → Maghrib: ~2-3 hours
- **Practical for all-day content**
- **Still reasonable limit** (not unlimited)
- **Prevents accidental overlaps** with next day

### Validation
All validation rules updated to match:
- Frontend (HTML) enforces 1-480
- Backend (Laravel) validates 1-480
- Consistent across create and edit forms

## Testing

### Test 1: Short Display (2 minutes)
```
✓ Can set: 2 minutes after prayer
✓ Validates: Accepts value
✓ Displays: 2 minutes after Jamaat
```

### Test 2: Medium Display (60 minutes)
```
✓ Can set: 60 minutes after prayer
✓ Validates: Accepts value
✓ Displays: 1 hour after Jamaat
```

### Test 3: Long Display (480 minutes)
```
✓ Can set: 480 minutes after prayer (max)
✓ Validates: Accepts value
✓ Displays: 8 hours after Jamaat
```

### Test 4: Over Limit (500 minutes)
```
✗ Can set: Input field prevents >480
✗ Validates: Backend rejects if bypassed
✗ Error shown: "The minutes after prayer must not be greater than 480."
```

## User Benefits

✅ **More Flexibility** - Can schedule content for longer periods  
✅ **Better Coverage** - Fill gaps between prayers with content  
✅ **All-Day Displays** - After morning prayer, display until afternoon  
✅ **Event Scheduling** - Show event info hours after announcement  
✅ **Clear Limits** - Still controlled with reasonable maximum

## Migration Notes

- ✅ **No database changes** required
- ✅ **Backward compatible** - existing schedules (≤120) still valid
- ✅ **Immediate effect** - works right away
- ✅ **No breaking changes** - all existing functionality intact

---

**Status:** ✅ Complete  
**Date:** October 8, 2025  
**Change Type:** Configuration/Limit Adjustment  
**Impact:** Positive - More scheduling flexibility  
**Old Limit:** 120 minutes (2 hours)  
**New Limit:** 480 minutes (8 hours)

