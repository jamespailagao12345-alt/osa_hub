# Database Normalization Guide

## Overview

This document outlines the database normalization process that has been implemented to improve data structure, reduce redundancy, and enhance maintainability.

## What Was Normalized

The `users` and `students` tables previously contained 100+ columns storing various types of information. These have been normalized into separate, focused tables:

### New Tables Created

1. **addresses** - Stores all address information (polymorphic, supports multiple address types)
2. **emergency_contacts** - Stores emergency contact information
3. **family_members** - Stores father, mother, guardian, and spouse information
4. **educational_backgrounds** - Stores all educational history (elementary, junior high, senior high, college, last school)
5. **student_information** - Stores student-specific information (year level, student types, scholarship info)
6. **personal_information** - Stores personal details (age, civil status, skills, special conditions)
7. **document_checklists** - Stores document presentation status

## Migration Files

The normalization process consists of the following migrations (in order):

1. `2025_12_15_235108_create_addresses_table.php` - Creates addresses table
2. `2025_12_16_000001_create_emergency_contacts_table.php` - Creates emergency contacts table
3. `2025_12_16_000002_create_family_members_table.php` - Creates family members table
4. `2025_12_16_000003_create_educational_backgrounds_table.php` - Creates educational backgrounds table
5. `2025_12_16_000004_create_student_information_table.php` - Creates student information table
6. `2025_12_16_000005_create_personal_information_table.php` - Creates personal information table
7. `2025_12_16_000006_create_document_checklists_table.php` - Creates document checklists table
8. `2025_12_16_000007_migrate_data_to_normalized_tables.php` - **Data migration** - Moves existing data to new tables
9. `2025_12_16_000008_remove_redundant_columns_from_users_and_students.php` - Removes old columns (run after verification)

## Models Created

All new tables have corresponding Eloquent models:

- `App\Models\Address`
- `App\Models\EmergencyContact`
- `App\Models\FamilyMember`
- `App\Models\EducationalBackground`
- `App\Models\StudentInformation`
- `App\Models\PersonalInformation`
- `App\Models\DocumentChecklist`

## User Model Relationships

The `User` model has been updated with the following relationships:

```php
// Addresses (polymorphic)
$user->addresses() // All addresses
$user->homeAddress() // Home address specifically

// Emergency Contacts
$user->emergencyContacts()

// Family Members
$user->familyMembers() // All family members
$user->familyMember('father') // Specific relation

// Educational Backgrounds
$user->educationalBackgrounds() // All educational backgrounds
$user->educationalBackground('elementary') // Specific level

// Student Information
$user->studentInformation()

// Personal Information
$user->personalInformation()

// Document Checklist
$user->documentChecklist()
```

## Implementation Steps

### Step 1: Run Table Creation Migrations

```bash
php artisan migrate
```

This will create all the new normalized tables.

### Step 2: Verify Data Migration

**IMPORTANT**: Before proceeding, verify that the data migration completed successfully:

```bash
# Check counts
php artisan tinker
>>> App\Models\User::count()
>>> App\Models\Address::count()
>>> App\Models\EmergencyContact::count()
>>> App\Models\FamilyMember::count()
>>> App\Models\EducationalBackground::count()
>>> App\Models\StudentInformation::count()
>>> App\Models\PersonalInformation::count()
>>> App\Models\DocumentChecklist::count()
```

### Step 3: Test Relationships

Verify that relationships work correctly:

```php
$user = App\Models\User::with([
    'addresses',
    'emergencyContacts',
    'familyMembers',
    'educationalBackgrounds',
    'studentInformation',
    'personalInformation',
    'documentChecklist'
])->first();

// Test accessing data
$user->homeAddress;
$user->familyMember('father');
$user->educationalBackground('elementary');
```

### Step 4: Update Application Code

Update all controllers, views, and other code that references the old column names to use the new relationships instead.

**Before:**
```php
$user->complete_home_address
$user->emergency_contact_name
$user->father_name
```

**After:**
```php
$user->homeAddress->complete_address
$user->emergencyContacts->first()->name
$user->familyMember('father')->name
```

### Step 5: Remove Redundant Columns (After Testing)

**WARNING**: Only run this after thoroughly testing your application!

```bash
php artisan migrate
```

This will run migration `2025_12_16_000008` which removes all redundant columns from `users` and `students` tables.

## Data Migration Details

The data migration (`2025_12_16_000007`) performs the following:

1. **Addresses**: Migrates `complete_home_address`, `street`, `barangay`, `city_municipality`, `province`, `zip_code`
2. **Emergency Contacts**: Migrates `emergency_contact_name`, `emergency_contact_number`, `emergency_relation`
3. **Family Members**: Migrates father, mother, guardian, and spouse information
4. **Educational Backgrounds**: Migrates all school information (elementary, junior high, senior high, college, last school)
5. **Student Information**: Migrates student-specific fields
6. **Personal Information**: Migrates personal details and special conditions
7. **Document Checklists**: Migrates document presentation status

## Benefits

1. **Reduced Redundancy**: Data is stored once in appropriate tables
2. **Better Organization**: Related data is grouped logically
3. **Easier Maintenance**: Changes to one type of data don't affect others
4. **Improved Performance**: Smaller tables with focused indexes
5. **Scalability**: Easy to add new address types, family members, etc.
6. **Data Integrity**: Foreign key constraints ensure referential integrity
7. **Follows 3NF**: Database follows Third Normal Form principles

## Rollback Considerations

The data migration is **one-way**. If you need to rollback:

1. Restore from database backup
2. Or manually migrate data back (complex, not recommended)

**Recommendation**: Always backup your database before running migrations in production.

## Next Steps

1. Update all controllers and views to use new relationships
2. Update form submissions to save to new tables
3. Update validation rules
4. Update API endpoints if applicable
5. Update any exports/reports
6. Test thoroughly before removing old columns

## Support

If you encounter any issues during the normalization process:

1. Check migration logs: `storage/logs/laravel.log`
2. Verify database connection
3. Ensure all migrations run in order
4. Check for foreign key constraint violations

