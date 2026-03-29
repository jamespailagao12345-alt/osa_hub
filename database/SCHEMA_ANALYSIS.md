# OSA Hub Database Schema Analysis

Generated: 2025-12-16 14:09:40

This document lists all database tables and their structure as defined in migration files.

## Table of Contents

- [addresses](#addresses)
- [admin_files](#admin-files)
- [appointment_files](#appointment-files)
- [appointments](#appointments)
- [assistant_assignments](#assistant-assignments)
- [assistant_leadership_backgrounds](#assistant-leadership-backgrounds)
- [attendances](#attendances)
- [cache](#cache)
- [cache_locks](#cache-locks)
- [courses](#courses)
- [departments](#departments)
- [designations](#designations)
- [document_checklists](#document-checklists)
- [educational_backgrounds](#educational-backgrounds)
- [emergency_contacts](#emergency-contacts)
- [event_feedback](#event-feedback)
- [event_files](#event-files)
- [event_participants](#event-participants)
- [event_requirements](#event-requirements)
- [events](#events)
- [failed_jobs](#failed-jobs)
- [family_members](#family-members)
- [fraternity_members](#fraternity-members)
- [government_affiliations](#government-affiliations)
- [indigenous_members](#indigenous-members)
- [job_batches](#job-batches)
- [jobs](#jobs)
- [messages](#messages)
- [nationalities](#nationalities)
- [notifications](#notifications)
- [org_structure_configs](#org-structure-configs)
- [organization_files](#organization-files)
- [organization_registration_requests](#organization-registration-requests)
- [organization_staff](#organization-staff)
- [organization_user](#organization-user)
- [organizations](#organizations)
- [password_reset_tokens](#password-reset-tokens)
- [password_resets](#password-resets)
- [personal_information](#personal-information)
- [pwd_information](#pwd-information)
- [scholarships](#scholarships)
- [sessions](#sessions)
- [staff](#staff)
- [staff_message_attachments](#staff-message-attachments)
- [staff_message_mentions](#staff-message-mentions)
- [staff_messages](#staff-messages)
- [staff_organization_files](#staff-organization-files)
- [staff_profiles](#staff-profiles)
- [status_changes](#status-changes)
- [student_information](#student-information)
- [student_points](#student-points)
- [students](#students)
- [users](#users)

---

## addresses

**Migration File:** `2025_12_15_235108_create_addresses_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `addressable` | morphs | No | No | |
| `type` | enum | No | No | |
| `street` | text | No | No | |
| `barangay` | text | No | No | |
| `city_municipality` | text | No | No | |
| `province` | text | No | No | |
| `zip_code` | string | No | No | |
| `complete_address` | text | No | No | |

---

## admin_files

**Migration File:** `2025_11_13_135426_create_admin_files_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `uploaded_by` | foreignId | No | No | |
| `file_name` | string | No | No | |
| `file_path` | string | No | No | |
| `file_type` | string | No | No | |
| `file_category` | string | No | No | |
| `description` | text | No | No | |
| `mime_type` | string | No | No | |
| `status` | enum | No | No | |

### Foreign Keys

- `uploaded_by`

---

## appointment_files

**Migration File:** `2025_10_05_125305_create_appointment_files_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `appointment_id` | foreignId | No | No | |
| `file_name` | string | No | No | |
| `file_path` | string | No | No | |
| `uploaded_by` | foreignId | No | No | |

### Foreign Keys

- `appointment_id`
- `uploaded_by`

---

## appointments

**Migration File:** `2025_01_15_000001_add_indexes_for_performance.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `full_name` | string | No | No | |
| `email` | string | No | No | |
| `contact_number` | string | No | No | |
| `appointment_date` | date | No | No | |
| `concern` | string | No | No | |
| `message` | text | No | No | |
| `assigned_staff_id` | foreignId | No | No | |
| `status` | enum | No | No | |
| `action_taken` | enum | No | No | |
| `action_reason` | text | No | No | |
| `rescheduled_date` | date | No | No | |
| `reason_for_counseling` | string | No | No | |
| `category` | string | No | No | |
| `session` | enum | No | No | |
| `reminder_sent_at` | timestamp | No | No | |
| `rescheduled_reminder_sent_at` | timestamp | No | No | |
| `remarks` | text | No | No | |

---

## assistant_assignments

**Migration File:** `2025_10_16_203000_create_assistant_assignments_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `department_id` | foreignId | No | No | |
| `position` | string | No | No | |
| `supervisor_id` | foreignId | No | No | |
| `active` | boolean | No | No | |

### Foreign Keys

- `user_id`
- `organization_id`

---

## assistant_leadership_backgrounds

**Migration File:** `2025_12_02_000002_create_assistant_leadership_backgrounds_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `organization` | string | No | No | |
| `position` | string | No | No | |
| `year` | string | No | No | |
| `order` | integer | No | No | |

### Foreign Keys

- `user_id`

---

## attendances

**Migration File:** `2025_10_17_000002_create_attendances_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `student_id` | foreignId | No | No | |
| `event_id` | foreignId | No | No | |
| `scan_time` | dateTime | No | No | |
| `status` | enum | No | No | |
| `excuse_letter` | string | No | No | |

### Foreign Keys

- `student_id`
- `event_id`

---

## cache

**Migration File:** `0001_01_01_000001_create_cache_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `key` | string | No | No | |
| `expiration` | integer | No | No | |
| `owner` | string | No | No | |

---

## cache_locks

**Migration File:** `0001_01_01_000001_create_cache_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `key` | string | No | No | |
| `expiration` | integer | No | No | |
| `owner` | string | No | No | |

---

## courses

**Migration File:** `2025_10_05_125113_create_courses_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `department_id` | foreignId | No | No | |
| `name` | string | No | No | |

### Foreign Keys

- `department_id`

---

## departments

**Migration File:** `0001_01_01_000000_create_departments_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |

---

## designations

**Migration File:** `2025_10_15_000000_create_designations_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |

---

## document_checklists

**Migration File:** `2025_12_15_235159_create_document_checklists_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `form_137_presented` | boolean | No | No | |
| `tor_presented` | boolean | No | No | |
| `good_moral_cert_presented` | boolean | No | No | |
| `birth_cert_presented` | boolean | No | No | |
| `marriage_cert_presented` | boolean | No | No | |
| `personal_data_sheet_image` | text | No | No | |

### Foreign Keys

- `user_id`

---

## educational_backgrounds

**Migration File:** `2025_12_15_235142_create_educational_backgrounds_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `level` | enum | No | No | |
| `school_name` | string | No | No | |
| `address` | text | No | No | |
| `year_graduated` | string | No | No | |
| `year_completed` | string | No | No | |
| `course` | string | No | No | |
| `track_strand` | string | No | No | |
| `lrn` | string | No | No | |
| `honors_awards` | text | No | No | |

### Foreign Keys

- `user_id`
- `user_id`

---

## emergency_contacts

**Migration File:** `2025_12_15_235130_create_emergency_contacts_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `name` | string | No | No | |
| `contact_number` | string | No | No | |
| `relation` | string | No | No | |

### Foreign Keys

- `user_id`
- `user_id`

---

## event_feedback

**Migration File:** `2025_11_29_020253_create_event_feedback_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `event_id` | foreignId | No | No | |
| `user_id` | foreignId | No | No | |
| `feedback_text` | text | No | No | |
| `rating` | integer | No | No | |
| `points_awarded` | boolean | No | No | |
| `submitted_at` | timestamp | No | No | |

### Foreign Keys

- `event_id`
- `user_id`

---

## event_files

**Migration File:** `2025_11_21_023743_create_event_files_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `event_id` | foreignId | No | No | |
| `uploaded_by` | foreignId | No | No | |
| `file_name` | string | No | No | |
| `file_path` | string | No | No | |
| `file_type` | string | No | No | |
| `mime_type` | string | No | No | |
| `description` | text | No | No | |

### Foreign Keys

- `event_id`
- `uploaded_by`

---

## event_participants

**Migration File:** `2025_10_05_125244_create_event_participants_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `event_id` | foreignId | No | No | |
| `user_id` | foreignId | No | No | |
| `qr_scanned` | boolean | No | No | |
| `scanned_at` | timestamp | No | No | |
| `scanned_by` | foreignId | No | No | |
| `attendance_status` | enum | No | No | |

### Foreign Keys

- `event_id`
- `user_id`

---

## event_requirements

**Migration File:** `2025_10_05_125235_create_event_requirements_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `event_id` | foreignId | No | No | |
| `requirement_name` | string | No | No | |
| `is_uploaded` | boolean | No | No | |
| `uploaded_by` | foreignId | No | No | |
| `file_path` | string | No | No | |

### Foreign Keys

- `event_id`

---

## events

**Migration File:** `2025_01_15_000001_add_indexes_for_performance.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |
| `description` | text | No | No | |
| `start_time` | dateTime | No | No | |
| `end_time` | dateTime | No | No | |
| `qr_code_path` | string | No | No | |
| `created_by` | foreignId | No | No | |
| `status` | string | No | No | |
| `event_date` | dateTime | No | No | |
| `organization_id` | foreignId | No | No | |
| `location` | string | No | No | |
| `end_date` | date | No | No | |
| `decline_reason` | text | No | No | |
| `coordinator_name` | string | No | No | |
| `required_student_participation` | boolean | No | No | |
| `monitoring_started` | boolean | No | No | |
| `monitoring_started_at` | timestamp | No | No | |
| `attended_threshold_minutes` | integer | No | No | |
| `late_threshold_minutes` | integer | No | No | |
| `absent_threshold_minutes` | integer | No | No | |
| `points` | integer | No | No | |

### Foreign Keys

- `created_by`

---

## failed_jobs

**Migration File:** `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `queue` | text | No | No | |
| `name` | string | No | No | |
| `total_jobs` | integer | No | No | |
| `pending_jobs` | integer | No | No | |
| `failed_jobs` | integer | No | No | |
| `cancelled_at` | integer | No | No | |
| `created_at` | integer | No | No | |
| `finished_at` | integer | No | No | |
| `uuid` | string | No | No | |
| `connection` | text | No | No | |
| `failed_at` | timestamp | No | No | |

---

## family_members

**Migration File:** `2025_12_15_235136_create_family_members_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `relation` | enum | No | No | |
| `name` | string | No | No | |
| `contact_number` | string | No | No | |
| `occupation` | string | No | No | |
| `workplace` | string | No | No | |
| `monthly_income` | string | No | No | |
| `relationship` | string | No | No | |

### Foreign Keys

- `user_id`
- `user_id`

---

## fraternity_members

**Migration File:** `2025_12_16_073954_create_fraternity_members_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `fraternity_sorority_name` | string | No | No | |
| `fraternity_sorority_position` | string | No | No | |
| `type` | enum | No | No | |
| `membership_start_date` | date | No | No | |
| `membership_end_date` | date | No | No | |
| `notes` | text | No | No | |

---

## government_affiliations

**Migration File:** `2025_12_16_073948_create_government_affiliations_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `is_government_member` | enum | No | No | |
| `government_level` | enum | No | No | |
| `government_role_position` | text | No | No | |
| `government_unit_name` | string | No | No | |
| `start_date` | date | No | No | |
| `end_date` | date | No | No | |
| `notes` | text | No | No | |

---

## indigenous_members

**Migration File:** `2025_12_16_073942_create_indigenous_members_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `is_indigenous_group_member` | boolean | No | No | |
| `indigenous_group_specify` | string | No | No | |
| `tribal_affiliation` | text | No | No | |
| `notes` | text | No | No | |

---

## job_batches

**Migration File:** `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `queue` | text | No | No | |
| `name` | string | No | No | |
| `total_jobs` | integer | No | No | |
| `pending_jobs` | integer | No | No | |
| `failed_jobs` | integer | No | No | |
| `cancelled_at` | integer | No | No | |
| `created_at` | integer | No | No | |
| `finished_at` | integer | No | No | |
| `uuid` | string | No | No | |
| `connection` | text | No | No | |
| `failed_at` | timestamp | No | No | |

---

## jobs

**Migration File:** `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `queue` | text | No | No | |
| `name` | string | No | No | |
| `total_jobs` | integer | No | No | |
| `pending_jobs` | integer | No | No | |
| `failed_jobs` | integer | No | No | |
| `cancelled_at` | integer | No | No | |
| `created_at` | integer | No | No | |
| `finished_at` | integer | No | No | |
| `uuid` | string | No | No | |
| `connection` | text | No | No | |
| `failed_at` | timestamp | No | No | |

---

## messages

**Migration File:** `2025_10_10_000001_create_messages_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `content` | text | No | No | |
| `is_read` | boolean | No | No | |

---

## nationalities

**Migration File:** `2025_12_16_073928_create_nationalities_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |
| `code` | string | No | No | |
| `is_active` | boolean | No | No | |

---

## notifications

**Migration File:** `2025_10_14_112204_create_notifications_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `type` | string | No | No | |
| `notifiable` | morphs | No | No | |
| `data` | text | No | No | |
| `read_at` | timestamp | No | No | |

---

## org_structure_configs

**Migration File:** `2025_11_05_071950_create_org_structure_configs_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `config_key` | string | No | No | |
| `max_levels` | integer | No | No | |

---

## organization_files

**Migration File:** `2025_11_25_191417_add_status_to_file_tables.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `status` | enum | No | No | |
| `organization_id` | foreignId | No | No | |
| `uploaded_by` | foreignId | No | No | |
| `file_name` | string | No | No | |
| `file_path` | string | No | No | |
| `file_type` | string | No | No | |
| `description` | text | No | No | |
| `mime_type` | string | No | No | |
| `file_category` | string | No | No | |

### Foreign Keys

- `organization_id`
- `uploaded_by`

---

## organization_registration_requests

**Migration File:** `2025_10_27_000000_create_organization_registration_requests_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `status` | enum | No | No | |
| `details` | text | No | No | |
| `position` | string | No | No | |

---

## organization_staff

**Migration File:** `2025_10_29_000001_create_organization_staff_table.php`

---

## organization_user

**Migration File:** `2025_10_15_140000_create_organization_user_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `position` | string | No | No | |

### Foreign Keys

- `user_id`
- `organization_id`

---

## organizations

**Migration File:** `2025_10_05_125144_create_organizations_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |
| `is_special` | boolean | No | No | |
| `department_id` | foreignId | No | No | |
| `official_email` | string | No | No | |
| `acronym` | string | No | No | |
| `mailing_address` | text | No | No | |
| `date_established` | date | No | No | |

---

## password_reset_tokens

**Migration File:** `2025_10_05_125210_create_users_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `first_name` | string | No | No | |
| `middle_name` | string | No | No | |
| `last_name` | string | No | No | |
| `email` | string | No | No | |
| `gender` | enum | No | No | |
| `birth_date` | date | No | No | |
| `department_id` | foreignId | No | No | |
| `course_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `year_level` | tinyInteger | No | No | |
| `student_type1` | enum | No | No | |
| `student_type2` | enum | No | No | |
| `scholarship_id` | foreignId | No | No | |
| `contact_number` | string | No | No | |
| `emergency_contact_name` | string | No | No | |
| `emergency_contact_number` | string | No | No | |
| `emergency_relation` | string | No | No | |
| `role` | tinyInteger | No | No | |
| `email_verified_at` | timestamp | No | No | |
| `password` | string | No | No | |
| `token` | string | No | No | |
| `created_at` | timestamp | No | No | |
| `ip_address` | string | No | No | |
| `user_agent` | text | No | No | |
| `last_activity` | integer | No | No | |

---

## password_resets

**Migration File:** `2014_10_12_100000_create_password_resets_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `email` | string | No | No | |
| `token` | string | No | No | |
| `created_at` | timestamp | No | No | |

---

## personal_information

**Migration File:** `2025_12_15_235153_create_personal_information_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `age` | integer | No | No | |
| `civil_status` | enum | No | No | |
| `maiden_name` | string | No | No | |
| `place_of_birth` | string | No | No | |
| `nationality` | string | No | No | |
| `religion` | string | No | No | |
| `sport` | text | No | No | |
| `arts` | text | No | No | |
| `technical` | text | No | No | |
| `is_indigenous_group_member` | boolean | No | No | |
| `indigenous_group_specify` | string | No | No | |
| `is_pwd` | boolean | No | No | |
| `pwd_id_image` | text | No | No | |
| `is_government_member` | enum | No | No | |
| `government_level` | enum | No | No | |
| `government_role_position` | text | No | No | |
| `living_arrangement` | enum | No | No | |
| `living_arrangement_others_specify` | text | No | No | |
| `is_single_parent` | boolean | No | No | |
| `fraternity_sorority_name` | string | No | No | |
| `fraternity_sorority_position` | string | No | No | |
| `has_criminal_record` | boolean | No | No | |
| `nationality_id` | foreignId | No | No | |

### Foreign Keys

- `user_id`

---

## pwd_information

**Migration File:** `2025_12_16_073936_create_pwd_information_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `is_pwd` | boolean | No | No | |
| `pwd_id_image` | text | No | No | |
| `pwd_id_number` | string | No | No | |
| `disability_type` | text | No | No | |
| `notes` | text | No | No | |

---

## scholarships

**Migration File:** `2025_10_05_125159_create_scholarships_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `name` | string | No | No | |

---

## sessions

**Migration File:** `2025_10_05_125210_create_users_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `first_name` | string | No | No | |
| `middle_name` | string | No | No | |
| `last_name` | string | No | No | |
| `email` | string | No | No | |
| `gender` | enum | No | No | |
| `birth_date` | date | No | No | |
| `department_id` | foreignId | No | No | |
| `course_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `year_level` | tinyInteger | No | No | |
| `student_type1` | enum | No | No | |
| `student_type2` | enum | No | No | |
| `scholarship_id` | foreignId | No | No | |
| `contact_number` | string | No | No | |
| `emergency_contact_name` | string | No | No | |
| `emergency_contact_number` | string | No | No | |
| `emergency_relation` | string | No | No | |
| `role` | tinyInteger | No | No | |
| `email_verified_at` | timestamp | No | No | |
| `password` | string | No | No | |
| `token` | string | No | No | |
| `created_at` | timestamp | No | No | |
| `ip_address` | string | No | No | |
| `user_agent` | text | No | No | |
| `last_activity` | integer | No | No | |

---

## staff

**Migration File:** `2025_01_15_000001_add_indexes_for_performance.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `first_name` | string | No | No | |
| `last_name` | string | No | No | |
| `middle_name` | string | No | No | |
| `email` | string | No | No | |
| `password` | string | No | No | |
| `designation` | string | No | No | |
| `department_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `admin_id` | foreignId | No | No | |
| `contact_number` | string | No | No | |
| `image` | string | No | No | |
| `birth_date` | date | No | No | |
| `gender` | string | No | No | |
| `user_id` | string | No | No | |
| `service_order` | string | No | No | |
| `contract_end_at` | dateTime | No | No | |
| `employment_status` | string | No | No | |
| `length_of_service` | string | No | No | |
| `about_me` | text | No | No | |

### Foreign Keys

- `admin_id`

---

## staff_message_attachments

**Migration File:** `2025_10_15_160200_create_staff_message_attachments_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `message_id` | foreignId | No | No | |
| `original_name` | string | No | No | |
| `path` | string | No | No | |
| `mime_type` | string | No | No | |

### Foreign Keys

- `message_id`

---

## staff_message_mentions

**Migration File:** `2025_10_15_160300_create_staff_message_mentions_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `message_id` | foreignId | No | No | |
| `mentioned_user_id` | foreignId | No | No | |

### Foreign Keys

- `message_id`
- `mentioned_user_id`

---

## staff_messages

**Migration File:** `2025_10_15_160000_create_staff_messages_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `type` | enum | No | No | |
| `content` | text | No | No | |
| `parent_id` | foreignId | No | No | |

### Foreign Keys

- `user_id`

---

## staff_organization_files

**Migration File:** `2025_11_25_191417_add_status_to_file_tables.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `status` | enum | No | No | |
| `staff_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `file_name` | string | No | No | |
| `file_path` | string | No | No | |
| `file_type` | string | No | No | |
| `file_category` | string | No | No | |
| `file_size` | integer | No | No | |
| `mime_type` | string | No | No | |
| `description` | text | No | No | |
| `uploaded_by` | foreignId | No | No | |

### Foreign Keys

- `staff_id`
- `uploaded_by`

---

## staff_profiles

**Migration File:** `2025_10_11_130000_create_staff_profiles_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `department_id` | foreignId | No | No | |
| `designation` | string | No | No | |
| `contact_number` | string | No | No | |
| `image` | string | No | No | |
| `birth_date` | date | No | No | |
| `gender` | string | No | No | |
| `middle_name` | string | No | No | |

### Foreign Keys

- `user_id`

---

## status_changes

**Migration File:** `2025_10_29_000001_create_status_changes_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `auditable_type` | string | No | No | |
| `from_status` | string | No | No | |
| `to_status` | string | No | No | |

---

## student_information

**Migration File:** `2025_12_15_235147_create_student_information_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `year_level` | integer | No | No | |
| `student_type1` | enum | No | No | |
| `student_type2` | enum | No | No | |
| `student_type` | enum | No | No | |
| `school_year` | string | No | No | |
| `semester` | string | No | No | |
| `academic_year` | string | No | No | |
| `scholarship_id` | foreignId | No | No | |
| `is_active_scholar` | boolean | No | No | |
| `scholarship_grant_name` | string | No | No | |

### Foreign Keys

- `user_id`

---

## student_points

**Migration File:** `2025_11_29_020255_create_student_points_table.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `event_id` | foreignId | No | No | |
| `feedback_id` | foreignId | No | No | |
| `points` | integer | No | No | |
| `notes` | text | No | No | |
| `awarded_at` | timestamp | No | No | |

### Foreign Keys

- `user_id`
- `event_id`

---

## students

**Migration File:** `2025_01_15_000001_add_indexes_for_performance.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `student_id` | string | No | No | |
| `first_name` | string | No | No | |
| `middle_name` | string | No | No | |
| `last_name` | string | No | No | |
| `email` | string | No | No | |
| `gender` | enum | No | No | |
| `birth_date` | date | No | No | |
| `year_level` | integer | No | No | |
| `student_type1` | enum | No | No | |
| `student_type2` | enum | No | No | |
| `contact_number` | string | No | No | |
| `emergency_contact_name` | string | No | No | |
| `emergency_contact_number` | string | No | No | |
| `emergency_relation` | string | No | No | |
| `age` | integer | No | No | |
| `civil_status` | enum | No | No | |
| `maiden_name` | string | No | No | |
| `place_of_birth` | string | No | No | |
| `complete_home_address` | text | No | No | |
| `parent_spouse_guardian` | string | No | No | |
| `parent_spouse_guardian_address` | text | No | No | |
| `elementary_school` | string | No | No | |
| `elementary_address` | string | No | No | |
| `elementary_year_graduated` | string | No | No | |
| `high_school` | string | No | No | |
| `high_school_address` | string | No | No | |
| `high_school_year_graduated` | string | No | No | |
| `college_name` | string | No | No | |
| `college_address` | string | No | No | |
| `college_course` | string | No | No | |
| `college_year` | string | No | No | |
| `school_year` | string | No | No | |
| `semester` | string | No | No | |
| `student_type` | enum | No | No | |
| `form_137_presented` | boolean | No | No | |
| `tor_presented` | boolean | No | No | |
| `good_moral_cert_presented` | boolean | No | No | |
| `birth_cert_presented` | boolean | No | No | |
| `marriage_cert_presented` | boolean | No | No | |
| `personal_data_sheet_image` | string | No | No | |
| `ext_name` | string | No | No | |
| `street` | text | No | No | |
| `barangay` | text | No | No | |
| `city_municipality` | text | No | No | |
| `province` | text | No | No | |
| `zip_code` | string | No | No | |
| `nationality` | text | No | No | |
| `religion` | text | No | No | |
| `tel_no` | string | No | No | |
| `spouse_name` | text | No | No | |
| `spouse_contact_no` | string | No | No | |
| `sport` | text | No | No | |
| `arts` | text | No | No | |
| `technical` | text | No | No | |
| `junior_high_school_name` | text | No | No | |
| `junior_high_school_year_completed` | string | No | No | |
| `junior_high_school_address` | text | No | No | |
| `junior_high_school_honors_awards` | text | No | No | |
| `senior_high_school_name` | text | No | No | |
| `senior_high_school_year_graduated` | string | No | No | |
| `senior_high_school_track_strand` | text | No | No | |
| `senior_high_school_lrn` | string | No | No | |
| `senior_high_school_address` | text | No | No | |
| `senior_high_school_honors_awards` | text | No | No | |
| `last_school_attended` | text | No | No | |
| `last_school_course` | text | No | No | |
| `last_school_address` | text | No | No | |
| `last_school_year_attended` | string | No | No | |
| `father_name` | text | No | No | |
| `father_contact_number` | string | No | No | |
| `father_occupation` | text | No | No | |
| `father_workplace` | text | No | No | |
| `father_monthly_income` | text | No | No | |
| `mother_name` | text | No | No | |
| `mother_contact_number` | string | No | No | |
| `mother_occupation` | text | No | No | |
| `mother_workplace` | text | No | No | |
| `mother_monthly_income` | text | No | No | |
| `guardian_name` | text | No | No | |
| `guardian_relationship` | text | No | No | |
| `guardian_contact_number` | string | No | No | |
| `guardian_occupation` | text | No | No | |
| `guardian_workplace` | text | No | No | |
| `guardian_monthly_income` | text | No | No | |
| `is_active_scholar` | boolean | No | No | |
| `scholarship_grant_name` | text | No | No | |
| `is_indigenous_group_member` | boolean | No | No | |
| `indigenous_group_specify` | text | No | No | |
| `is_pwd` | boolean | No | No | |
| `pwd_id_image` | text | No | No | |
| `is_government_member` | enum | No | No | |
| `government_level` | enum | No | No | |
| `government_role_position` | text | No | No | |
| `living_arrangement` | enum | No | No | |
| `living_arrangement_others_specify` | text | No | No | |
| `is_single_parent` | boolean | No | No | |
| `fraternity_sorority_name` | text | No | No | |
| `fraternity_sorority_position` | text | No | No | |
| `has_criminal_record` | boolean | No | No | |

---

## users

**Migration File:** `2025_01_15_000001_add_indexes_for_performance.php`

### Columns

| Column Name | Type | Nullable | Unique | Description |
|------------|------|----------|--------|-------------|
| `user_id` | foreignId | No | No | |
| `first_name` | string | No | No | |
| `middle_name` | string | No | No | |
| `last_name` | string | No | No | |
| `email` | string | No | No | |
| `gender` | enum | No | No | |
| `birth_date` | date | No | No | |
| `department_id` | foreignId | No | No | |
| `course_id` | foreignId | No | No | |
| `organization_id` | foreignId | No | No | |
| `year_level` | tinyInteger | No | No | |
| `student_type1` | enum | No | No | |
| `student_type2` | enum | No | No | |
| `scholarship_id` | foreignId | No | No | |
| `contact_number` | string | No | No | |
| `emergency_contact_name` | string | No | No | |
| `emergency_contact_number` | string | No | No | |
| `emergency_relation` | string | No | No | |
| `role` | tinyInteger | No | No | |
| `email_verified_at` | timestamp | No | No | |
| `password` | string | No | No | |
| `token` | string | No | No | |
| `created_at` | timestamp | No | No | |
| `ip_address` | string | No | No | |
| `user_agent` | text | No | No | |
| `last_activity` | integer | No | No | |
| `designation` | string | No | No | |
| `name` | string | No | No | |
| `supervisor_id` | foreignId | No | No | |
| `position` | string | No | No | |
| `image` | string | No | No | |
| `service_order` | string | No | No | |
| `length_of_service` | integer | No | No | |
| `contract_end_at` | date | No | No | |
| `suspended` | boolean | No | No | |
| `last_imported_worksheet` | string | No | No | |
| `age` | integer | No | No | |
| `civil_status` | enum | No | No | |
| `maiden_name` | string | No | No | |
| `place_of_birth` | string | No | No | |
| `complete_home_address` | text | No | No | |
| `parent_spouse_guardian` | string | No | No | |
| `parent_spouse_guardian_address` | text | No | No | |
| `elementary_school` | string | No | No | |
| `elementary_address` | string | No | No | |
| `elementary_year_graduated` | string | No | No | |
| `high_school` | string | No | No | |
| `high_school_address` | string | No | No | |
| `high_school_year_graduated` | string | No | No | |
| `college_name` | string | No | No | |
| `college_address` | string | No | No | |
| `college_course` | string | No | No | |
| `college_year` | string | No | No | |
| `school_year` | string | No | No | |
| `semester` | string | No | No | |
| `student_type` | enum | No | No | |
| `form_137_presented` | boolean | No | No | |
| `tor_presented` | boolean | No | No | |
| `good_moral_cert_presented` | boolean | No | No | |
| `birth_cert_presented` | boolean | No | No | |
| `marriage_cert_presented` | boolean | No | No | |
| `ext_name` | string | No | No | |
| `street` | text | No | No | |
| `barangay` | text | No | No | |
| `city_municipality` | text | No | No | |
| `province` | text | No | No | |
| `zip_code` | string | No | No | |
| `nationality` | text | No | No | |
| `religion` | text | No | No | |
| `tel_no` | string | No | No | |
| `spouse_name` | text | No | No | |
| `spouse_contact_no` | string | No | No | |
| `sport` | text | No | No | |
| `arts` | text | No | No | |
| `technical` | text | No | No | |
| `junior_high_school_name` | text | No | No | |
| `junior_high_school_year_completed` | string | No | No | |
| `junior_high_school_address` | text | No | No | |
| `junior_high_school_honors_awards` | text | No | No | |
| `senior_high_school_name` | text | No | No | |
| `senior_high_school_year_graduated` | string | No | No | |
| `senior_high_school_track_strand` | text | No | No | |
| `senior_high_school_lrn` | string | No | No | |
| `senior_high_school_address` | text | No | No | |
| `senior_high_school_honors_awards` | text | No | No | |
| `last_school_attended` | text | No | No | |
| `last_school_course` | text | No | No | |
| `last_school_address` | text | No | No | |
| `last_school_year_attended` | string | No | No | |
| `father_name` | text | No | No | |
| `father_contact_number` | string | No | No | |
| `father_occupation` | text | No | No | |
| `father_workplace` | text | No | No | |
| `father_monthly_income` | text | No | No | |
| `mother_name` | text | No | No | |
| `mother_contact_number` | string | No | No | |
| `mother_occupation` | text | No | No | |
| `mother_workplace` | text | No | No | |
| `mother_monthly_income` | text | No | No | |
| `guardian_name` | text | No | No | |
| `guardian_relationship` | text | No | No | |
| `guardian_contact_number` | string | No | No | |
| `guardian_occupation` | text | No | No | |
| `guardian_workplace` | text | No | No | |
| `guardian_monthly_income` | text | No | No | |
| `is_active_scholar` | boolean | No | No | |
| `scholarship_grant_name` | text | No | No | |
| `is_indigenous_group_member` | boolean | No | No | |
| `indigenous_group_specify` | text | No | No | |
| `is_pwd` | boolean | No | No | |
| `pwd_id_image` | text | No | No | |
| `is_government_member` | enum | No | No | |
| `government_level` | enum | No | No | |
| `government_role_position` | text | No | No | |
| `living_arrangement` | enum | No | No | |
| `living_arrangement_others_specify` | text | No | No | |
| `is_single_parent` | boolean | No | No | |
| `fraternity_sorority_name` | text | No | No | |
| `fraternity_sorority_position` | text | No | No | |
| `has_criminal_record` | boolean | No | No | |
| `suspension_reason` | text | No | No | |
| `about_me` | text | No | No | |
| `academic_year` | string | No | No | |

---

