# Security Dashboard - Recent Entries/Exits Fix

## Summary
Fixed the "Recent Entries" and "Recent Exits" sections in the security dashboard to display only today's scans with accurate Manila timezone formatting and automatic data clearing when the date changes.

## Changes Made

### 1. **dashboards/security/realtime_data.php**
   - **Case 'entries'**: Updated to filter entries by today's date only
     - Added `WHERE DATE(e.Timestamp) = ? OR (DATE(e.Timestamp) = DATE(NOW()))` condition
     - Uses Manila timezone from database configuration (UTC+8)
     - Only retrieves 20 most recent entries from today
   
   - **Case 'exits'**: Updated to filter exits by today's date only
     - Added `WHERE DATE(e.Timestamp) = ? OR (DATE(e.Timestamp) = DATE(NOW()))` condition
     - Uses Manila timezone from database configuration (UTC+8)
     - Only retrieves 20 most recent exits from today

### 2. **assets/js/security.js**
   - **Constructor**: Added date tracking properties
     - `currentDate`: Tracks the current date in Manila timezone
     - `dateCheckInterval`: Interval for checking date changes
   
   - **init() method**: Added call to `startDateChangeDetection()`
   
   - **createActivityItem() method**: Enhanced timestamp formatting
     - Now uses `toLocaleTimeString()` with `timeZone: 'Asia/Manila'`
     - Displays time in 12-hour format with AM/PM (e.g., "02:30 PM")
   
   - **NEW: startDateChangeDetection() method**:
     - Checks every minute if the date has changed (in Manila timezone)
     - When date changes, automatically clears the Recent Entries and Recent Exits
     - Reloads all data for the new day
     - Reloads analytics data for the new day
   
   - **destroy() method**: Updated to clean up date check interval

## How It Works

1. **Daily Reset**: The dashboard automatically detects when the calendar date changes (in Manila time)
2. **Today Only Filter**: PHP queries only fetch records from the current date (Manila timezone)
3. **Accurate Time Display**: All timestamps are displayed in Manila timezone (UTC+8) in 12-hour format
4. **Auto-Clear**: When the date changes, the Recent Entries and Recent Exits sections are automatically cleared
5. **Real-time Updates**: Dashboard continues to update every 5 seconds with only today's data

## Database Configuration
- Timezone is already configured in `config/database.php`:
  - PHP: `date_default_timezone_set('Asia/Manila')`
  - MySQL: `SET time_zone = '+08:00'` (UTC+8 = Manila Time)

## Testing
The changes ensure:
- ✓ Only today's entries and exits are shown
- ✓ Timestamps display in Manila timezone (12-hour format)
- ✓ Data automatically clears when the date changes
- ✓ No duplicate old logs are shown
- ✓ Analytics and statistics only include today's data
