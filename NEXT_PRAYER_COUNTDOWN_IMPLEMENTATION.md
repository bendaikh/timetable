# Next Prayer Countdown Implementation

## Overview
Added "Next prayer in:" countdown functionality to the Prayer Times box, showing a live countdown timer to the next prayer time.

## Changes Made

### 1. BoxSetting Model (app/Models/BoxSetting.php)
- Added `note_prayer_box` configuration to the default box settings
- Configuration includes:
  - Content settings: text label and countdown toggle
  - Styling settings: colors, fonts, borders, padding
  - Layout settings: position and text alignment

### 2. Timetable View (resources/views/timetable/index.blade.php)
- Added countdown display elements in the prayer times section:
  - Label: "Next prayer in:" (customizable)
  - Timer: Shows countdown in HH:MM:SS format
  - Prayer name: Shows which prayer is next
- Added JavaScript functionality:
  - Calculates time remaining to next prayer
  - Uses Jamaat times when available
  - Updates countdown every second
  - Shows next prayer name (Fajr, Zohar, Asr, Maghrib, Isha)
  - Automatically rolls over to next day's Fajr after Isha

### 3. Boxes Management Interface
- **Index View (resources/views/admin/boxes/index.blade.php)**
  - Added preview case for `note_prayer_box`
  - Shows example countdown display in management interface

- **Edit View (resources/views/admin/boxes/edit.blade.php)**
  - Added preview case for live editing
  - Added conditional include for note_prayer_box settings

- **Settings Partial (resources/views/admin/boxes/partials/note-prayer-box-settings.blade.php)**
  - New file for box-specific settings
  - Allows customization of countdown label text
  - Toggle to show/hide countdown timer

## Features

### Display Elements
1. **Label Text**: Customizable text (default: "Next prayer in:")
2. **Countdown Timer**: Shows HH:MM:SS format
3. **Prayer Name**: Shows which prayer is next
4. **Styling**: Fully customizable colors, fonts, borders, and layout

### Automatic Behavior
- Calculates next prayer based on current time
- Uses Jamaat time if available, otherwise uses regular prayer time
- Updates every second for accurate countdown
- Automatically handles day transitions (rolls to next day's Fajr after Isha)
- Shows the prayer name that's coming next

### Box Styling Options
- Background color
- Text color
- Font family
- Font size
- Border color, width, and radius
- Padding
- Text alignment

## How to Use

### Method 1: Initialize Defaults (Recommended for Fresh Setup)
1. Go to Admin Dashboard → Boxes Management
2. Click "Initialize Defaults" button
3. Confirm the action
4. The note_prayer_box will be created automatically with default settings

### Method 2: View the Countdown
1. Go to the timetable view (main display)
2. Look in the Prayer Times box (left column)
3. Below the prayer times table, you'll see:
   - "Next prayer in:" label
   - Countdown timer (e.g., "02:45:32")
   - Prayer name (e.g., "Asr")

### Method 3: Customize the Countdown Box
1. Go to Admin Dashboard → Boxes Management
2. Find "Next Prayer Countdown" box
3. Click "Edit Box"
4. Customize:
   - Change the label text
   - Adjust colors and styling
   - Toggle countdown visibility
5. Click "Save Changes"
6. Refresh the timetable to see changes

## Display Behavior

### When Box Styling is Enabled
- Shows with custom styling from box settings
- Uses configured colors, fonts, and layout
- Displays below prayer times table

### When Box Styling is Disabled
- Shows with default styling
- Still functional and displays countdown
- Uses standard layout

## Technical Details

### JavaScript Logic
```javascript
// Prayer times are passed from PHP to JavaScript
// Countdown calculates time difference between now and next prayer
// Updates every second
// Format: HH:MM:SS
// Shows prayer name below countdown
```

### Time Calculation
- Uses current browser time
- Compares against prayer times from database
- Prefers Jamaat times over regular prayer times
- Handles midnight rollover automatically

## Troubleshooting

### Countdown not showing
1. Check if prayer times are set for today
2. Verify box is active in Boxes Management
3. Ensure "show_countdown" is enabled in box settings
4. Check browser console for JavaScript errors

### Incorrect countdown time
1. Verify prayer times in database are correct
2. Check browser time is accurate
3. Ensure Jamaat times are properly configured

### Styling issues
1. Reset box to defaults from Boxes Management
2. Re-customize styling settings
3. Clear browser cache and refresh

## Database Structure

The `note_prayer_box` settings are stored in the `box_settings` table with:
- `box_type`: 'note_prayer_box'
- `box_name`: 'Next Prayer Countdown'
- `content_settings`: JSON with text and show_countdown
- `styling_settings`: JSON with colors, fonts, borders
- `layout_settings`: JSON with position and alignment
- `is_active`: Boolean (default: true)

## Summary

This implementation adds a fully functional, real-time countdown timer to the prayer times display, showing users exactly how much time remains until the next prayer. The countdown is:
- Accurate (updates every second)
- Automatic (no manual intervention needed)
- Customizable (full styling control)
- Smart (uses Jamaat times, handles day transitions)
- User-friendly (clear display with prayer name)

The feature integrates seamlessly with the existing boxes management system and can be easily customized through the admin interface.

