# Position vs Designation Usage Guide

## Rule
- **"position"** is used ONLY for **assistant-staff** (role 3)
- **"designation"** is used ONLY for **staff** (role 2)

## Files Fixed

### 1. resources/views/admin/assistants/index.blade.php
- **Fixed**: Changed `$a->designation` to `$a->position` for assistant display
- **Line 67**: Now correctly shows position instead of designation

### 2. app/Http/Controllers/Admin/DashboardController.php
- **Fixed**: Removed duplicate `designation` field from assistant arrays
- **Lines 3224-3232, 3266-3274, 3369-3377, 3411-3419**: Now only uses `position` field for assistants

### 3. resources/views/assistant/partials/sidebar.blade.php
- **Fixed**: Added logic to use `position` for assistant-staff (role 3) and `designation` for staff
- **Lines 161-175**: Now correctly distinguishes between assistant-staff and staff

### 4. resources/views/assistant-staff-dashboard.blade.php
- **Fixed**: Changed from using `designation` to `position` for assistant-staff
- **Lines 7-8**: Now uses `$position` instead of `$designation`
- **Line 23**: Passes `$position` to dashboard-header component

### 5. resources/views/components/dashboard-header.blade.php
- **Fixed**: Added logic to check for `position` if user is assistant-staff (role 3)
- **Lines 15-28**: Now correctly handles both assistant-staff (position) and staff (designation)

## Verification

All files have been checked and corrected to follow the rule:
- Assistant-staff (role 3) → use `position`
- Staff (role 2) → use `designation`

No linter errors found in the updated files.

