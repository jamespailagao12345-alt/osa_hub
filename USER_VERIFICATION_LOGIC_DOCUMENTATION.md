# User Verification Logic Documentation

## Overview
This document describes the updated user verification and synchronization logic implemented in the OSA Hub system.

## Table Structure
The system uses the following table structure:
- **users** - Core user table (all users)
- **staff_information** - Staff and admin information (renamed from `staff`)
- **student_information** - Student information (renamed from `students`)
- **student_leaders_information** - Assistant/leader assignments (renamed from `assistant_assignments`)

## Role-Based Data Distribution

### Role 4 (Admin) and Role 2 (Staff)
- **Primary Table:** `users`
- **Secondary Table:** `staff_information`
- **Logic:** All users with role 4 or 2 must have a corresponding record in `staff_information`
- **Sync Trigger:** Automatically synced on user creation and update via `UserObserver`

### Role 1 (Student)
- **Primary Table:** `users`
- **Secondary Table:** `student_information`
- **Logic:** All users with role 1 must have a corresponding record in `student_information`
- **Sync Trigger:** Automatically synced on user creation and update via `UserObserver`

### Role 3 (Assistant)
- **Primary Table:** `users`
- **Secondary Tables:** 
  - `student_information` (assistants are also students)
  - `student_leaders_information` (assistant assignments)
- **Logic:** 
  - All users with role 3 must have a record in `student_information`
  - All users with role 3 must have a record in `student_leaders_information`
- **Sync Trigger:** Automatically synced on user creation and update via `UserObserver`

## Implementation Details

### UserObserver (`app/Observers/UserObserver.php`)

The `UserObserver` automatically handles synchronization when users are created or updated:

#### Methods:

1. **`created(User $user)`**
   - Called when a new user is created
   - Routes to `syncUserToAppropriateTables()` based on role

2. **`updated(User $user)`**
   - Called when a user is updated
   - Routes to `syncUserToAppropriateTables()` based on role
   - Handles email changes for staff (role 2 and 4)

3. **`syncUserToAppropriateTables(User $user)`**
   - Main routing method
   - Role 4 or 2 → `syncUserToStaff()`
   - Role 1 → `syncUserToStudent()`
   - Role 3 → `syncUserToStudent()` AND `syncUserToStudentLeaders()`

4. **`syncUserToStaff(User $user)`**
   - Creates or updates record in `staff_information`
   - Maps user fields to staff fields
   - Handles `admin_id` assignment (role 4 users are their own admin)

5. **`syncUserToStudent(User $user)`**
   - Creates or updates record in `student_information`
   - Works for both role 1 and role 3 users
   - Uses normalized `student_information` table data if available

6. **`syncUserToStudentLeaders(User $user)`**
   - Creates or updates record in `student_leaders_information`
   - Only for role 3 users
   - Sets `active = true` by default

7. **`handleStaffEmailChange(User $user)`**
   - Handles email updates for staff
   - Updates all related records to maintain consistency

### Verification Command (`app/Console/Commands/VerifyAndSyncUsers.php`)

A console command to verify and sync all existing users:

```bash
# Dry run (shows what would be synced)
php artisan users:verify-and-sync --dry-run

# Actual sync
php artisan users:verify-and-sync
```

**Features:**
- Checks all users by role
- Verifies they exist in appropriate tables
- Creates missing records
- Updates existing records
- Provides detailed output and statistics

## Data Flow

### User Creation Flow

```
User Created (any role)
    ↓
UserObserver::created()
    ↓
syncUserToAppropriateTables()
    ↓
    ├─→ Role 4 or 2 → syncUserToStaff()
    │   └─→ Create/Update staff_information
    │
    ├─→ Role 1 → syncUserToStudent()
    │   └─→ Create/Update student_information
    │
    └─→ Role 3 → syncUserToStudent() + syncUserToStudentLeaders()
        ├─→ Create/Update student_information
        └─→ Create/Update student_leaders_information
```

### User Update Flow

```
User Updated (any role)
    ↓
UserObserver::updated()
    ↓
syncUserToAppropriateTables()
    ↓
    ├─→ Role 4 or 2 → syncUserToStaff()
    │   └─→ Update staff_information
    │   └─→ If email changed → handleStaffEmailChange()
    │
    ├─→ Role 1 → syncUserToStudent()
    │   └─→ Update student_information
    │
    └─→ Role 3 → syncUserToStudent() + syncUserToStudentLeaders()
        ├─→ Update student_information
        └─→ Update student_leaders_information
```

## Field Mapping

### User → Staff (staff_information)
- `first_name` → `first_name`
- `last_name` → `last_name`
- `middle_name` → `middle_name`
- `user_id` → `user_id`
- `email` → `email`
- `designation` → `designation`
- `department_id` → `department_id`
- `organization_id` → `organization_id`
- `contact_number` → `contact_number`
- `birth_date` → `birth_date`
- `gender` → `gender`
- `age` → `age`
- `image` → `image`
- `service_order` → `service_order`
- `length_of_service` → `length_of_service`
- `contract_end_at` → `contract_end_at`
- `employment_status` → `employment_status`
- `about_me` → `about_me`
- `admin_id` → Set based on role (role 4 = own id, role 2 = find admin)

### User → Student (student_information)
- `user_id` → `user_id`
- `first_name` → `first_name`
- `middle_name` → `middle_name`
- `last_name` → `last_name`
- `email` → `email`
- `contact_number` → `contact_number`
- `gender` → `gender`
- `birth_date` → `birth_date`
- `department_id` → `department_id`
- `course_id` → `course_id`
- `organization_id` → `organization_id`
- `scholarship_id` → From `student_information` normalized table or `users.scholarship_id`
- `year_level` → From `student_information` normalized table
- `student_type1` → From `student_information` normalized table
- `student_type2` → From `student_information` normalized table
- Additional fields mapped if they exist in user model

### User → Student Leaders (student_leaders_information)
- `user_id` → `user_id`
- `organization_id` → `organization_id`
- `department_id` → `department_id`
- `supervisor_id` → `supervisor_id`
- `position` → `position` (if exists in user model)
- `active` → `true` (default)

## Important Notes

1. **Recursion Prevention:** The observer uses a static `$syncing` flag to prevent infinite recursion when syncing between tables.

2. **Email Consistency:** When a staff user's email is updated, the system:
   - Updates the staff_information record
   - Updates all other user records with the old email
   - Updates all other staff records with the old email

3. **Normalized Data:** The system prioritizes data from normalized tables (e.g., `student_information` relationship) when available, falling back to user table fields.

4. **Table Existence Checks:** The verification command checks if tables exist before attempting to sync, preventing errors.

5. **Automatic Sync:** All user creation and updates automatically trigger synchronization via the observer, ensuring data consistency.

## Usage

### Automatic Sync (Recommended)
The observer automatically handles sync on user create/update. No manual intervention needed.

### Manual Verification
Run the verification command to check and sync all existing users:

```bash
php artisan users:verify-and-sync
```

### Dry Run
Test what would be synced without making changes:

```bash
php artisan users:verify-and-sync --dry-run
```

## Troubleshooting

### Issue: Table doesn't exist
**Solution:** Ensure migrations have been run. The rename migration should have renamed the tables.

### Issue: Users not syncing
**Solution:** 
1. Check that `UserObserver` is registered in `AppServiceProvider`
2. Verify user creation/update is triggering the observer
3. Check for exceptions in logs

### Issue: Missing data in secondary tables
**Solution:** Run the verification command to sync all existing users:
```bash
php artisan users:verify-and-sync
```

## Migration History

The following migration renamed the tables:
- `2025_12_19_005226_rename_tables_students_staff_assistant_assignment.php`
  - Renamed `students` → `student_information`
  - Renamed `staff` → `staff_information`
  - Renamed `assistant_assignments` → `student_leaders_information`

