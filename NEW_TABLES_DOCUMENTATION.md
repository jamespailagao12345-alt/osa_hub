# New Database Tables Documentation

## Overview
Five new tables have been created to further normalize the database structure for nationality, PWD (Person with Disability), indigenous members, government affiliations, and fraternity members.

## Tables Created

### 1. `nationalities` (Lookup Table)
**Purpose**: Reference table for nationality/country information

**Structure**:
- `id` (bigint, primary key)
- `name` (string, unique) - Name of the nationality/country
- `code` (string, 3 chars, nullable) - ISO 3166-1 alpha-3 country code
- `is_active` (boolean, default: true) - Whether the nationality is active
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `name`
- Index on `code`
- Unique constraint on `name`

**Migration**: `2025_12_16_073928_create_nationalities_table.php`

---

### 2. `pwd_information`
**Purpose**: Stores Person with Disability (PWD) information for users

**Structure**:
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key → users.id, unique, cascade delete)
- `is_pwd` (boolean, default: false) - Whether user is PWD
- `pwd_id_image` (text, nullable) - Path to PWD ID image
- `pwd_id_number` (string, nullable) - PWD ID number
- `disability_type` (text, nullable) - Type of disability
- `notes` (text, nullable) - Additional notes
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `user_id`
- Index on `is_pwd`
- Unique constraint on `user_id` (one record per user)

**Migration**: `2025_12_16_073936_create_pwd_information_table.php`

---

### 3. `indigenous_members`
**Purpose**: Stores indigenous group membership information for users

**Structure**:
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key → users.id, unique, cascade delete)
- `is_indigenous_group_member` (boolean, default: false) - Whether user is an indigenous group member
- `indigenous_group_specify` (string, nullable) - Name of indigenous group
- `tribal_affiliation` (text, nullable) - Tribal affiliation details
- `notes` (text, nullable) - Additional notes
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `user_id`
- Index on `is_indigenous_group_member`
- Unique constraint on `user_id` (one record per user)

**Migration**: `2025_12_16_073942_create_indigenous_members_table.php`

---

### 4. `government_affiliations`
**Purpose**: Stores government affiliation/membership information for users

**Structure**:
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key → users.id, unique, cascade delete)
- `is_government_member` (enum: 'no', 'yes', nullable) - Whether user is a government member
- `government_level` (enum: 'barangay', 'municipal_city', 'provincial', 'national', nullable) - Level of government
- `government_role_position` (text, nullable) - Role/position in government
- `government_unit_name` (string, nullable) - Name of government unit/office
- `start_date` (date, nullable) - Start date of affiliation
- `end_date` (date, nullable) - End date of affiliation
- `notes` (text, nullable) - Additional notes
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `user_id`
- Index on `is_government_member`
- Index on `government_level`
- Unique constraint on `user_id` (one record per user)

**Migration**: `2025_12_16_073948_create_government_affiliations_table.php`

---

### 5. `fraternity_members`
**Purpose**: Stores fraternity/sorority membership information for users

**Structure**:
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key → users.id, unique, cascade delete)
- `fraternity_sorority_name` (string, nullable) - Name of fraternity/sorority
- `fraternity_sorority_position` (string, nullable) - Position in fraternity/sorority
- `type` (enum: 'fraternity', 'sorority', nullable) - Type of organization
- `membership_start_date` (date, nullable) - Start date of membership
- `membership_end_date` (date, nullable) - End date of membership
- `notes` (text, nullable) - Additional notes
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `user_id`
- Index on `fraternity_sorority_name`
- Unique constraint on `user_id` (one record per user)

**Migration**: `2025_12_16_073954_create_fraternity_members_table.php`

---

## Relationships

All tables (except `nationalities`) have a **one-to-one** relationship with the `users` table:
- Each user can have **one** PWD information record
- Each user can have **one** indigenous member record
- Each user can have **one** government affiliation record
- Each user can have **one** fraternity member record

The `nationalities` table is a **lookup/reference table** that can be referenced by other tables (e.g., `personal_information` table can have a `nationality_id` foreign key).

## Next Steps

1. **Run Migrations**: Execute `php artisan migrate` to create these tables in the database
2. **Create Models**: Create Eloquent models for each table:
   - `Nationality`
   - `PwdInformation`
   - `IndigenousMember`
   - `GovernmentAffiliation`
   - `FraternityMember`
3. **Update PersonalInformation Model**: Add relationships to these new tables
4. **Data Migration**: Create a migration to move existing data from `personal_information` table to these new tables
5. **Update Controllers**: Update controllers to use the new table structure
6. **Update Views**: Update views to work with the new normalized structure

## Notes

- All user-related tables use `cascade delete` - when a user is deleted, their related records in these tables are also deleted
- All tables have `unique` constraint on `user_id` to ensure one record per user
- The `nationalities` table is designed as a lookup table and can be seeded with common nationalities
- Additional fields have been added beyond the original structure to provide more flexibility (e.g., dates, notes, etc.)

