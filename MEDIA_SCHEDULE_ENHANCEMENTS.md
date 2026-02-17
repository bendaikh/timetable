# Media Schedule Enhancements - Implementation Summary

## Overview
This document describes the enhancements made to the media schedule system to provide more granular control over media display.

## ✨ New Features

### 1. ⏰ Expiry Date & Time (Full-Time Poster)
Each media item in a **Full Time Poster** schedule can now have its own expiry date and time.

**How it works:**
- When a media item reaches its expiry date/time, it automatically stops displaying
- Each media within the same poster has independent expiry settings
- Example: Media A expires at 2 PM on Oct 15, while Media B expires at 5 PM on Oct 20
- Both fields are optional - leave blank for media that should never expire

**Location in UI:**
- Available when creating/editing a Full Time Poster schedule
- Found in the "Media Configuration" section for each selected media
- Fields: "Expiry Date" and "Expiry Time"

---

### 2. ⏱️ Gap Duration Between Medias (Full-Time Poster)
Define a gap/pause between each media in a **Full Time Poster** schedule.

**How it works:**
- After a media finishes displaying, the system waits for the specified gap duration before showing the next media
- Gap duration is set per media in seconds (0-3600 seconds / 1 hour max)
- During the gap, the system can show the timetable or remain blank
- Default gap duration: 0 seconds (no gap)

**Use case example:**
- Media 1 displays for 30 seconds, then a 10-second gap
- Media 2 displays for 45 seconds, then a 5-second gap
- Media 3 displays for 60 seconds, then a 15-second gap
- Cycle repeats

**Location in UI:**
- Available when creating/editing a Full Time Poster schedule
- Found in the "Media Configuration" section for each selected media
- Field: "Gap Duration (seconds)" with helper text

---

### 3. 📅 Days of the Week per Media
Days of the week are now assigned to **each individual media** instead of the entire schedule.

**How it works:**
- Each media item can be set to display only on specific days of the week
- If no days are selected, the media displays all days (default behavior)
- This applies to ALL schedule types: Full Time Poster, Before Prayer, and After Prayer
- **UI Change**: Schedule-level "Days of Week" has been completely removed from the UI (since per-media control is more granular and flexible)
- All days-of-week control is now done at the individual media level

**Use case example:**
- Schedule: Full Time Poster (active all days)
  - Media 1: Only Monday, Wednesday, Friday
  - Media 2: Only Tuesday, Thursday
  - Media 3: Only Saturday, Sunday
  - Media 4: All days (no selection)

**Location in UI:**
- Available for ALL schedule types
- Found in the "Media Configuration" section for each selected media
- Checkboxes for each day: Mon, Tue, Wed, Thu, Fri, Sat, Sun

---

### 4. ⚖️ Poster Display Priority
Before Prayer and After Prayer posters now **override** Full Time posters.

**How it works:**
- **Priority 1**: Before Prayer and After Prayer schedules (highest priority)
- **Priority 2**: Full Time Poster schedules (fallback)
- When a Before/After Prayer schedule's display time arrives, it takes over even if a Full Time Poster is currently active
- After the Before/After Prayer display ends, the system returns to showing Full Time Poster or the timetable

**Example scenario:**
1. 10:00 AM - Full Time Poster displays Media A
2. 10:15 AM - "Before Zohar Prayer" schedule activates → Full Time Poster is paused
3. 10:20 AM - "Before Zohar Prayer" schedule ends → Full Time Poster resumes
4. Zohar Prayer time arrives → Prayer display (existing behavior)

---

## 🗄️ Database Changes

### Migration: `2025_10_11_183431_add_new_fields_to_media_schedule_media_table`

Added the following columns to the `media_schedule_media` pivot table:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `expiry_date` | DATE | Yes | NULL | Expiry date for the media (Full Time Poster only) |
| `expiry_time` | TIME | Yes | NULL | Expiry time for the media (Full Time Poster only) |
| `gap_duration` | INTEGER | Yes | 0 | Gap in seconds between medias (Full Time Poster only) |
| `days_of_week` | JSON | Yes | NULL | Array of days (1-7) when this media should display |

---

## 💻 Code Changes

### Models Updated
1. **`MediaSchedule.php`**: Updated `mediaItems()` relationship to include new pivot fields
2. **`Media.php`**: Updated `schedules()` relationship to include new pivot fields

### Service Logic Updated
**`MediaDisplayService.php`** - Enhanced with:

1. **Priority Override System**: 
   - `getCurrentMedia()` now categorizes schedules by type
   - Returns prayer-based media first, falls back to full-time poster

2. **Expiry Check for Full Time Poster**:
   - Filters out expired media based on date/time
   - Only applies to Full Time Poster schedules

3. **Gap Duration Implementation**:
   - Calculates cycle duration including gaps
   - Determines correct media to display considering gap periods

4. **Per-Media Days of Week**:
   - Filters media based on current day
   - Applies to both Full Time Poster and prayer-based schedules

### Controller Updated
**`MediaScheduleController.php`** - Updated:

1. **Validation Rules**: Added validation for new fields
   - `media_expiry_dates.*`: nullable date
   - `media_expiry_times.*`: nullable time (H:i format)
   - `media_gap_durations.*`: nullable integer (0-3600)
   - `media_days_of_week.*`: nullable array of integers (1-7)

2. **Store & Update Methods**: 
   - Now handle and save new pivot data
   - Properly encode `days_of_week` as JSON

### Views Updated
1. **`create.blade.php`**: 
   - Added UI fields for expiry date/time (Full Time Poster only)
   - Added gap duration field (Full Time Poster only)
   - Added days of week checkboxes per media (all schedule types)
   - Updated JavaScript to dynamically show/hide fields based on schedule type

2. **`edit.blade.php`**: 
   - Same updates as create view
   - Properly loads existing pivot data for editing
   - Handles JSON parsing of existing `days_of_week` data

---

## 📝 How to Use

### Creating a Full Time Poster with New Features

1. Navigate to **Admin → Media Schedules → Create New**
2. Select **Schedule Type**: "Full Time Poster"
3. Select your media items from the list
4. For each media, configure:
   - **Duration**: How long to display (seconds)
   - **Priority**: Display order
   - **Expiry Date** (optional): When this media should stop showing
   - **Expiry Time** (optional): Specific time on expiry date
   - **Gap Duration**: Pause after this media (seconds)
   - **Days of Week**: Select specific days or leave all unchecked for all days
5. Click "Save Schedule"

### Example: Weekend Special Poster

**Scenario**: Display special announcements only on weekends with specific timings

1. Schedule Type: Full Time Poster
2. Media Configuration:
   - **Media 1**: "Friday Prayer Announcement"
     - Duration: 30 seconds
     - Days: Friday only ✓
     - Gap: 5 seconds
   - **Media 2**: "Weekend Events"
     - Duration: 45 seconds
     - Days: Saturday ✓, Sunday ✓
     - Gap: 10 seconds
   - **Media 3**: "Ramadan Schedule"
     - Duration: 60 seconds
     - Expiry: 2025-04-30 23:59
     - Days: All days (no selection)
     - Gap: 0 seconds

**Result**:
- On Friday: Shows Media 1 and Media 3
- On Saturday/Sunday: Shows Media 2 and Media 3
- On other weekdays: Shows only Media 3
- After April 30, 2025: Media 3 stops showing

---

## 🔄 Backward Compatibility

- **Schedule-level "Days of Week"** is still available and functional
- Existing schedules without new fields will continue to work
- Default values are used when new fields are not set:
  - Gap Duration: 0 seconds (no gap)
  - Days of Week: NULL (displays all days)
  - Expiry: NULL (never expires)

---

## 🧪 Testing Recommendations

1. **Test Expiry Feature**:
   - Create a Full Time Poster with media expiring in 5 minutes
   - Verify the media stops displaying after expiry

2. **Test Gap Duration**:
   - Create a Full Time Poster with 3 media items
   - Set different gap durations (e.g., 5s, 10s, 15s)
   - Observe the gaps between media displays

3. **Test Per-Media Days**:
   - Create media for specific days (e.g., Monday only)
   - Verify it only displays on those days

4. **Test Priority Override**:
   - Have an active Full Time Poster
   - Create a Before Prayer schedule
   - Verify prayer schedule takes over when its time arrives

---

## 📋 Summary of Files Changed

### Database
- `database/migrations/2025_10_11_183431_add_new_fields_to_media_schedule_media_table.php` (NEW)

### Models
- `app/Models/MediaSchedule.php` (MODIFIED)
- `app/Models/Media.php` (MODIFIED)

### Services
- `app/Services/MediaDisplayService.php` (MODIFIED)

### Controllers
- `app/Http/Controllers/Admin/MediaScheduleController.php` (MODIFIED)

### Views
- `resources/views/admin/media-schedules/create.blade.php` (MODIFIED)
- `resources/views/admin/media-schedules/edit.blade.php` (MODIFIED)

---

## 🎯 Key Benefits

1. **More Control**: Fine-grained control over when each media displays
2. **Automated Expiry**: No need to manually remove expired media
3. **Better Pacing**: Gap duration prevents overwhelming viewers
4. **Flexible Scheduling**: Different media on different days within same schedule
5. **Smart Priority**: Important prayer-related media always takes precedence

---

## 🚀 Next Steps

1. Test the new features in a development environment
2. Create sample schedules to verify functionality
3. Train administrators on the new UI fields
4. Monitor media display behavior
5. Adjust gap durations and expiry dates as needed

---

**Implementation Date**: October 11, 2025  
**Version**: 2.0  
**Status**: ✅ Complete

