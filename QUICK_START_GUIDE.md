# Quick Start Guide - Multiple Media Schedule Feature

## ✅ What's New

Your timetable system now supports **multiple media per schedule** with individual duration and priority settings!

## 🚀 Getting Started

### Step 1: Understanding the New Flow

**Before (Old System):**
- One schedule = One media item
- Global priority per schedule
- Fixed duration from media settings

**Now (New System):**
- One schedule = Multiple media items
- Each media has its own priority and duration
- Three schedule types: Minutes Before Prayer, Minutes After Prayer, **Full Time Poster**

### Step 2: Creating Your First Multi-Media Schedule

1. **Navigate to:** Admin Panel → Media Schedules → Add New Schedule

2. **Select Schedule Type:**
   - **Minutes Before Prayer** - Display media X minutes before Jamaat time
   - **Minutes After Prayer** - Display media X minutes after Jamaat time  
   - **Full Time Poster** - ⭐ NEW! Continuously cycle through media all day

3. **Select Prayer** (if not Full Time Poster):
   - Choose from: Fajr, Zohar, Asr, Maghrib, Isha
   - ⚠️ This refers to **Jamaat Time** (congregation time), not prayer start time

4. **Select Media:**
   - Check multiple media items from the list
   - Selected media will show in the configuration section below

5. **Configure Each Media:**
   - **Duration:** How long this media displays (in seconds)
   - **Priority:** Display order (1 = first, 2 = second, etc.)

6. **Example Setup:**
   ```
   Schedule: Minutes After Prayer
   Prayer: Asr
   Minutes: 1
   
   Selected Media:
   ✓ Fundraiser Poster    → Priority: 1, Duration: 30s
   ✓ Event Announcement   → Priority: 2, Duration: 45s
   ✓ Donation Information → Priority: 3, Duration: 30s
   ```

7. **Save** - Your schedule is ready!

## 📺 How Media Displays

### Prayer-Based Schedules (Before/After)

**Timeline Example (After Asr, 1 minute delay):**
```
4:00 PM - Asr Jamaat Time
4:01 PM - Media 1 starts (30 seconds)
4:01:30 PM - Media 2 starts (45 seconds)
4:02:15 PM - Media 3 starts (30 seconds)
4:02:45 PM - All done, back to timetable
```

### Full Time Poster

**Continuous Cycle:**
```
All day long:
Media 1 (60s) → Media 2 (30s) → Media 3 (45s) → [REPEAT] → ...

Total cycle: 135 seconds (2 min 15 sec)
Repeats approximately 640 times per day
```

## 💡 Use Cases

### Use Case 1: Event Promotions
```
Type: Minutes Before Prayer
Prayer: Maghrib
Minutes Before: 10

Media:
1. Main Event Poster (Priority 1, 45s)
2. Ticket Information (Priority 2, 30s)
3. Contact Details (Priority 3, 30s)
```
**Result:** Event promotion sequence 10 minutes before Maghrib

### Use Case 2: Daily Information Board
```
Type: Full Time Poster

Media:
1. Masjid Services (Priority 1, 60s)
2. Prayer Timetable (Priority 2, 30s)
3. Weekly Classes (Priority 3, 45s)
4. Donation Appeal (Priority 4, 30s)
```
**Result:** Continuous information display throughout the day

### Use Case 3: Ramadan Special
```
Type: Minutes After Prayer
Prayer: Isha
Minutes After: 2

Media:
1. Sehri Time Tomorrow (Priority 1, 30s)
2. Taraweeh Schedule (Priority 2, 30s)
3. Iftar Menu (Priority 3, 45s)
```
**Result:** Ramadan information after Isha prayer

## 🎯 Best Practices

### Priority Numbers
- **Lower number = Displays first** (1, 2, 3, 4...)
- Keep numbering sequential for clarity
- Can use any number 1-100

### Duration Guidelines
- **Images:** 30-60 seconds recommended
- **Videos:** Use actual video length
- **Important announcements:** 45-60 seconds
- **Quick info:** 15-30 seconds
- **Maximum allowed:** 300 seconds (5 minutes)

### Schedule Organization
- Group related media in one schedule
- Use descriptive days of week filters
- Don't overlap schedules unnecessarily
- Use Full Time Poster for permanent displays

## 🔍 Viewing Your Schedules

### In the Schedule List
- See up to 2 media items per schedule
- Shows priority and duration for each
- Click "View" to see all media in the schedule

### In Schedule Details
- Grid view of all media with previews
- Display sequence order
- Total cycle duration
- Edit anytime to add/remove media

## ⚙️ Managing Schedules

### Edit Schedule
1. Click edit button on any schedule
2. Check/uncheck media to add/remove
3. Adjust duration and priority
4. Changes sync immediately on save

### Clone Schedule (Workaround)
Since there's no clone button:
1. Open schedule you want to copy
2. Click edit
3. Change schedule details (time, prayer, etc.)
4. Click "Save" (creates as update)

Better: Create new schedule and select same media

### Deactivate Schedule
- Click "Active" button on schedule list
- Or uncheck "Active" when editing
- Inactive schedules won't display

## 🐛 Troubleshooting

### Media Not Displaying?
✓ Check schedule is Active  
✓ Verify media items are Active  
✓ Confirm prayer times are set for today  
✓ Check "Days of Week" settings  
✓ Use debug endpoint: `/api/debug-schedules`

### Wrong Display Order?
✓ Verify priority numbers (1 = first)  
✓ Check schedule start time is correct  
✓ Confirm media durations are set

### Media Displaying Too Long/Short?
✓ Check individual media duration settings  
✓ Remember: duration is per media, not total schedule  

### Full Time Poster Not Cycling?
✓ Ensure multiple media are selected  
✓ Check each media has a duration  
✓ Verify schedule is Active

## 📊 Quick Reference

| Schedule Type | Prayer Required? | Behavior |
|--------------|------------------|----------|
| Minutes Before Prayer | Yes | Displays X minutes before Jamaat, stops 5 min before |
| Minutes After Prayer | Yes | Displays X minutes after Jamaat, sequential playback |
| Full Time Poster | No | Continuous cycling all day long |

## 🎓 Advanced Tips

### Seamless Transitions
Set durations in multiples of 15 for smooth viewing:
- 15s, 30s, 45s, 60s, 90s, 120s

### Peak Time Displays
Use "Minutes Before Prayer" for maximum visibility:
- 10-15 minutes before major prayers
- Catches early arrivals

### Information Overload Prevention
- Limit to 3-5 media per schedule
- Keep total sequence under 3 minutes
- Use clear, readable fonts

### Day-Specific Content
Filter by days for:
- Friday-only content (Jumu'ah specials)
- Weekend programs
- Weekday vs weekend schedules

## 📞 Need Help?

Check these in order:
1. This guide
2. Full documentation: `IMPLEMENTATION_SUMMARY.md`
3. Debug endpoint: `yoursite.com/api/debug-schedules`
4. Laravel logs: `storage/logs/laravel.log`
5. Browser console for JavaScript errors

---

**Happy Scheduling! 🎉**

The new system gives you complete control over your media displays. Experiment with different combinations to find what works best for your community!

