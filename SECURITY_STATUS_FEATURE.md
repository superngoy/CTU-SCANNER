# Security Personnel Status Feature - Admin Dashboard

## Overview
A new comprehensive section has been added to the **Admin Dashboard** that displays the real-time status of all security personnel, including whether they are on duty, their shift times, and when shifts are ending.

## Location
**Added to the Admin Dashboard** (`/dashboards/admin/index.php`) right after "Today's Campus Overview" and before "Quick Actions"
- ✅ No new sidebar option was added (as requested)
- ✅ Displayed prominently on the main dashboard

## Features

### 1. Real-Time Security Personnel Status
- **On Duty**: Shows security staff currently working with green background
- **Shift Ending Soon**: Displays when staff has 15 minutes or less left (yellow background)
- **Off Duty**: Shows when staff is not currently scheduled (gray background)
- **Starting Soon**: Shows when next shift is within 30 minutes (blue background)
- **Shift Complete**: Indicates shift has ended (red background)

### 2. Visual Cards for Each Security Personnel
Each card displays:
- ✅ Profile picture (if available) or initials
- ✅ Full name
- ✅ Scheduled hours
- ✅ Current shift start and end times
- ✅ Real-time status with countdown (if ending soon)

### 3. Summary Statistics
Shows at the top:
- Total number currently **On Duty**
- Total number **Off Duty**

### 4. Auto-Refresh
- Page loads security status on startup
- Automatically refreshes every 2 minutes
- Shows current time calculations for accurate status

## Supported Schedule Formats
The system correctly parses multiple schedule formats:
- `6AM-6PM`
- `06:00AM - 11:00PM` (with extra spaces)
- `10:00am - 10:00pm`
- `6:00 AM - 6:00 PM`

## Visual Design

### Card Colors by Status:
```
🟢 On Duty          - Green border, light green background
🟡 Ending Soon      - Gold border, light yellow background
⚫ Off Duty         - Gray border, light gray background
🔵 Starting Soon    - Blue border, light blue background
🔴 Shift Complete   - Red border, light red background
```

### Responsive Layout:
- Desktop: 4 columns (25% width each)
- Tablet: 3-4 columns
- Mobile: 2 columns
- Small devices: 1-2 columns

## API Endpoint

**Endpoint**: `/api/get_security_status.php`

**Response Example**:
```json
{
    "success": true,
    "personnel": [
        {
            "id": "SEC-001",
            "name": "Guard. Leonides Conde",
            "schedule": "06:00AM - 11:00PM",
            "on_duty": true,
            "status_text": "On Duty",
            "status_class": "status-on-duty",
            "minutes_until_end": 45,
            "shift_start": "6:00 AM",
            "shift_end": "11:00 PM",
            "image": null
        },
        ...
    ],
    "total": 3,
    "on_duty_count": 2,
    "off_duty_count": 1
}
```

## Files Modified/Created

1. ✅ `dashboards/admin/index.php`
   - Added security personnel section to dashboard
   - Added CSS styles for cards
   - Added JavaScript to load and display data
   - Auto-refresh functionality

2. ✅ `api/get_security_status.php`
   - New API endpoint
   - Returns all security personnel with status
   - Calculates shift timing
   - Schedule parsing logic

## Status Detection Logic

The system intelligently calculates status:

```
Current Time vs Shift Times:
├── BEFORE shift start
│   └── "Off Duty - Next shift: [time]" or "Starting Soon (X minutes)"
├── DURING shift
│   ├── More than 15 minutes remaining: "On Duty"
│   └── Within 15 minutes: "On Duty - Shift Ending Soon (X minutes)"
└── AFTER shift end
    ├── Next shift not started yet: "Shift Complete"
    └── Tomorrow's shift: "Off Duty - Next shift: [time]"
```

## Usage

1. **Admin logs in** to the Admin Dashboard
2. **Security Personnel Status** section displays automatically
3. Status **updates every 2 minutes** (or refresh manually)
4. **Green = On Duty**, **Yellow = Ending Soon**, **Gray = Off Duty**

## Example Scenario

Time: 10:55 AM
- **SEC-001** (6AM - 11PM) → "On Duty" ✅ Green
- **SEC-002** (10AM - 11:02PM) → "On Duty" ✅ Green  
- **SEC-006** (6AM - 3PM) → "Shift Complete" 🔴 Red (shift ended at 3PM)

Time: 10:50 PM
- **SEC-001** (6AM - 11PM) → "On Duty - Shift Ending Soon (10m)" ⚠️ Yellow
- **SEC-002** (10AM - 11:02PM) → "On Duty" ✅ Green
- **SEC-006** (6AM - 3PM) → "Off Duty - Next shift: 6:00 AM" ⚫ Gray

## Benefits for Admin

✅ Quick overview of security coverage
✅ Identify when shifts are about to end
✅ Monitor security personnel availability
✅ Responsive and mobile-friendly design
✅ No sidebar clutter (all on main dashboard)
✅ Real-time data with auto-refresh

## Integration Complete! 🎉

The feature is fully integrated and ready to use on the admin dashboard.
