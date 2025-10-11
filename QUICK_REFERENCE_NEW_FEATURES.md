# Quick Reference: New Media Schedule Features

## 🎯 Four Major Enhancements

### 1️⃣ Expiry Date & Time (Full-Time Poster Only)
**What**: Set when each media should stop displaying  
**Where**: Media Configuration → Expiry Date & Expiry Time fields  
**Optional**: Yes  

```
Example:
Media: "Summer Camp Poster"
Expiry Date: 2025-08-31
Expiry Time: 23:59
→ Automatically stops showing after August 31, 2025 at 11:59 PM
```

---

### 2️⃣ Gap Duration (Full-Time Poster Only)
**What**: Add pause/gap between media items  
**Where**: Media Configuration → Gap Duration field  
**Range**: 0-3600 seconds (0-1 hour)  
**Default**: 0 seconds (no gap)  

```
Example:
Media 1: Display 30s, Gap 10s
Media 2: Display 45s, Gap 5s
Media 3: Display 60s, Gap 0s
→ Media 1 shows for 30s, then 10s gap, then Media 2, etc.
```

---

### 3️⃣ Days of Week per Media (All Schedule Types)
**What**: Each media can have different active days  
**Where**: Media Configuration → Days of Week checkboxes  
**Optional**: Yes (unchecked = all days)  
**Note**: Schedule-level "Days of Week" has been completely removed - all control is now per-media

```
Example Schedule: "Weekly Announcements" (Full Time Poster)
├─ Media 1: "Friday Jummah" → ✓ Friday only
├─ Media 2: "Weekend Events" → ✓ Saturday, ✓ Sunday
└─ Media 3: "General Info" → All days (none checked)

Result:
- Friday: Shows Media 1 & 3
- Saturday/Sunday: Shows Media 2 & 3
- Mon-Thu: Shows Media 3 only
```

---

### 4️⃣ Prayer Schedule Priority Override
**What**: Before/After Prayer schedules override Full-Time Posters  
**How**: Automatic (no configuration needed)  
**Priority Order**:  
1. Before Prayer Schedules (Highest)
2. After Prayer Schedules (Highest)
3. Full-Time Poster Schedules (Fallback)
4. Timetable (Default)

```
Timeline Example:
10:00 AM → Full-Time Poster playing
10:15 AM → "Before Zohar" schedule starts → Takes over display
10:20 AM → "Before Zohar" schedule ends → Returns to Full-Time Poster
12:30 PM → Zohar Prayer → Shows prayer display
12:32 PM → "After Zohar" schedule starts → Takes over display
12:35 PM → "After Zohar" ends → Returns to Full-Time Poster
```

---

## 📊 Feature Availability Matrix

| Feature | Full-Time Poster | Before Prayer | After Prayer |
|---------|------------------|---------------|--------------|
| Expiry Date/Time | ✅ | ❌ | ❌ |
| Gap Duration | ✅ | ❌ | ❌ |
| Days of Week (per media) | ✅ | ✅ | ✅ |
| Priority Override | Lowest | Highest | Highest |

---

## 🎨 UI Changes Summary

### Media Configuration Section (NEW/ENHANCED)
When you select media items, you'll now see for each media:

**All Schedule Types:**
- Duration (seconds) - *existing*
- Priority - *existing*
- **Days of Week** - *NEW: 7 checkboxes for each day*

**Full-Time Poster Only (additional):**
- **Expiry Date** - *NEW: date picker*
- **Expiry Time** - *NEW: time picker*
- **Gap Duration** - *NEW: number input (seconds)*

---

## 💡 Common Use Cases

### Use Case 1: Seasonal Poster with Auto-Expiry
```
Schedule: Full-Time Poster
Media: "Ramadan Special"
- Duration: 60 seconds
- Expiry Date: 2025-04-30
- Expiry Time: 23:59
- Days: All (no selection)
- Gap: 0 seconds

→ Displays all month, automatically stops after Ramadan
```

### Use Case 2: Different Posters for Different Days
```
Schedule: Full-Time Poster
├─ "Friday Jummah Reminder" → ✓ Friday, Duration: 45s, Gap: 10s
├─ "Weekend Classes" → ✓ Sat, ✓ Sun, Duration: 30s, Gap: 5s
└─ "Daily Prayer Times" → All days, Duration: 60s, Gap: 0s

→ Tailored content for each day of the week
```

### Use Case 3: Paced Media Display
```
Schedule: Full-Time Poster
├─ Media 1 → 30s display, 15s gap
├─ Media 2 → 45s display, 20s gap
└─ Media 3 → 60s display, 10s gap

→ Viewers get breaks between media for better comprehension
```

### Use Case 4: Priority Override in Action
```
Schedules:
1. Full-Time Poster: "General Announcements" (all day)
2. Before Prayer (Zohar): "Prayer Preparation" (12:25-12:30)
3. After Prayer (Zohar): "Community News" (12:32-12:35)

Timeline:
├─ 12:00 → General Announcements playing
├─ 12:25 → Prayer Preparation takes over
├─ 12:30 → Prayer time
├─ 12:32 → Community News takes over
└─ 12:35 → Back to General Announcements
```

---

## ⚙️ Default Behaviors

| Field | Default Value | Meaning |
|-------|---------------|---------|
| Expiry Date | NULL | Never expires |
| Expiry Time | NULL | Never expires |
| Gap Duration | 0 | No gap, continuous play |
| Days of Week | NULL / Empty | Active all days |

---

## 🔍 Validation Rules

| Field | Validation |
|-------|------------|
| Expiry Date | Valid date format |
| Expiry Time | HH:MM format (24-hour) |
| Gap Duration | 0-3600 seconds (max 1 hour) |
| Days of Week | Array of integers 1-7 (Mon-Sun) |

---

## 🐛 Troubleshooting

**Q: Media not showing on expected days?**  
A: Check the per-media "Days of Week" checkboxes in the Media Configuration section. Each media has independent day control.

**Q: Expiry not working?**  
A: Make sure BOTH expiry date AND expiry time are set. One without the other won't trigger expiry.

**Q: Gap too long or too short?**  
A: Gap duration is in SECONDS (not minutes). 60 seconds = 1 minute.

**Q: Prayer schedule not overriding full-time poster?**  
A: Check that prayer times are correct and the prayer schedule's time window is active.

**Q: Old schedules missing new fields?**  
A: All new fields are optional with safe defaults. Existing schedules work without changes.

---

## 📱 Admin Interface Location

```
Admin Panel
└── Media Schedules
    ├── Create New Schedule
    │   └── Media Configuration (new fields here)
    └── Edit Schedule
        └── Media Configuration (new fields here)
```

---

## ✅ Checklist for Creating Schedule with New Features

- [ ] Select Schedule Type
- [ ] Select Media Items
- [ ] Configure Duration & Priority for each media
- [ ] **[NEW]** Set Days of Week for each media (if needed)
- [ ] **[NEW - Full-Time only]** Set Expiry Date/Time (if needed)
- [ ] **[NEW - Full-Time only]** Set Gap Duration (if needed)
- [ ] Set Schedule Active
- [ ] Save Schedule

---

**Created**: October 11, 2025  
**For**: Timetable Media Schedule System v2.0

