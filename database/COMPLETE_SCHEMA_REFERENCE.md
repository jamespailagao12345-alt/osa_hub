# OSA Hub Database Schema - Complete Reference

**Generated:** 2025-12-16 14:41:56

This document provides a complete reference of all database tables, columns, and relationships as implemented in the system.

**Total Tables:** 53

---

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

**Defined in migrations:**
- `2025_12_15_235108_create_addresses_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `addressable` | morphs | No | - | No | - |
| `type` | enum | No | - | No | home, work, school, emergency, parent_guardian |
| `street` | text | No | - | No | - |
| `barangay` | text | No | - | No | - |
| `city_municipality` | text | No | - | No | - |
| `province` | text | No | - | No | - |
| `zip_code` | string | No | - | No | - |
| `complete_address` | text | No | - | No | - |

---

## admin_files

**Defined in migrations:**
- `2025_11_13_135426_create_admin_files_table.php`
- `2025_11_25_191417_add_status_to_file_tables.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `uploaded_by` | foreignId | No | - | No | - |
| `file_name` | string | No | - | No | - |
| `file_path` | string | No | - | No | - |
| `file_type` | string | No | - | No | - |
| `file_category` | string | No | - | No | - |
| `description` | text | No | - | No | - |
| `file_size` | unsignedBigInteger | No | - | No | - |
| `mime_type` | string | No | - | No | - |
| `status` | enum | No | - | No | pending, approved, rejected |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `uploaded_by` | `users` | restrict |

---

## appointment_files

**Defined in migrations:**
- `2025_10_05_125305_create_appointment_files_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `appointment_id` | foreignId | No | - | No | - |
| `file_name` | string | No | - | No | - |
| `file_path` | string | No | - | No | - |
| `uploaded_by` | foreignId | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `uploaded_by` | `users` | restrict |

---

## appointments

**Defined in migrations:**
- `2025_01_15_000001_add_indexes_for_performance.php`
- `2025_10_05_125253_create_appointments_table.php`
- `2025_10_14_000001_drop_message_from_appointments_table.php`
- `2025_11_01_045158_add_action_fields_to_appointments_table.php`
- `2025_11_07_013957_add_counseling_fields_to_appointments_table.php`
- `2025_11_12_052123_add_session_to_appointments_table.php`
- `2025_11_12_052809_add_session_to_appointments_table.php`
- `2025_11_12_055823_add_reminder_fields_to_appointments_table.php`
- `2025_11_13_014519_add_remarks_to_appointments_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `full_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `appointment_date` | date | No | - | No | - |
| `appointment_time` | time | No | - | No | - |
| `concern` | string | No | - | No | - |
| `message` | text | No | - | No | - |
| `assigned_staff_id` | foreignId | No | - | No | - |
| `status` | enum | No | - | No | pending, approved, cancelled, rescheduled |
| `action_taken` | enum | No | - | No | approve, decline, reschedule |
| `action_reason` | text | No | - | No | - |
| `rescheduled_date` | date | No | - | No | - |
| `rescheduled_time` | time | No | - | No | - |
| `reason_for_counseling` | string | No | - | No | - |
| `category` | string | No | - | No | - |
| `session` | enum | No | - | No | Finish, On Going |
| `reminder_sent_at` | timestamp | No | - | No | - |
| `rescheduled_reminder_sent_at` | timestamp | No | - | No | - |
| `remarks` | text | No | - | No | - |

---

## assistant_assignments

**Defined in migrations:**
- `2025_10_16_203000_create_assistant_assignments_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `position` | string | No | - | No | - |
| `supervisor_id` | foreignId | No | - | No | - |
| `active` | boolean | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |
| `organization_id` | `organizations` | restrict |

---

## assistant_leadership_backgrounds

**Defined in migrations:**
- `2025_12_02_000002_create_assistant_leadership_backgrounds_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `organization` | string | No | - | No | - |
| `position` | string | No | - | No | - |
| `year` | string | No | - | No | - |
| `order` | integer | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |

---

## attendances

**Defined in migrations:**
- `2025_10_17_000002_create_attendances_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `student_id` | foreignId | No | - | No | - |
| `event_id` | foreignId | No | - | No | - |
| `scan_time` | dateTime | No | - | No | - |
| `status` | enum | No | - | No | Present, Late, Absent |
| `excuse_letter` | string | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `student_id` | `users` | restrict |
| `event_id` | `events` | restrict |

---

## cache

**Defined in migrations:**
- `0001_01_01_000001_create_cache_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `key` | string | No | - | No | - |
| `expiration` | integer | No | - | No | - |
| `owner` | string | No | - | No | - |

---

## cache_locks

**Defined in migrations:**
- `0001_01_01_000001_create_cache_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `key` | string | No | - | No | - |
| `expiration` | integer | No | - | No | - |
| `owner` | string | No | - | No | - |

---

## courses

**Defined in migrations:**
- `2025_10_05_125113_create_courses_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `department_id` | foreignId | No | - | No | - |
| `name` | string | No | - | No | - |

---

## departments

**Defined in migrations:**
- `0001_01_01_000000_create_departments_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |

---

## designations

**Defined in migrations:**
- `2025_10_15_000000_create_designations_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |

---

## document_checklists

**Defined in migrations:**
- `2025_12_15_235159_create_document_checklists_table.php`
- `2025_12_16_000006_create_document_checklists_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `form_137_presented` | boolean | No | - | No | - |
| `tor_presented` | boolean | No | - | No | - |
| `good_moral_cert_presented` | boolean | No | - | No | - |
| `birth_cert_presented` | boolean | No | - | No | - |
| `marriage_cert_presented` | boolean | No | - | No | - |
| `personal_data_sheet_image` | text | No | - | No | - |

---

## educational_backgrounds

**Defined in migrations:**
- `2025_12_15_235142_create_educational_backgrounds_table.php`
- `2025_12_16_000003_create_educational_backgrounds_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `level` | enum | No | - | No | elementary, junior_high, senior_high, college, last_school |
| `school_name` | string | No | - | No | - |
| `address` | text | No | - | No | - |
| `year_graduated` | string | No | - | No | - |
| `year_completed` | string | No | - | No | - |
| `course` | string | No | - | No | - |
| `track_strand` | string | No | - | No | - |
| `lrn` | string | No | - | No | - |
| `honors_awards` | text | No | - | No | - |

---

## emergency_contacts

**Defined in migrations:**
- `2025_12_15_235130_create_emergency_contacts_table.php`
- `2025_12_16_000001_create_emergency_contacts_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `name` | string | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `relation` | string | No | - | No | - |

---

## event_feedback

**Defined in migrations:**
- `2025_11_29_020253_create_event_feedback_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `event_id` | foreignId | No | - | No | - |
| `user_id` | foreignId | No | - | No | - |
| `feedback_text` | text | No | - | No | - |
| `rating` | integer | No | - | No | - |
| `points_awarded` | boolean | No | - | No | - |
| `submitted_at` | timestamp | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `event_id` | `events` | restrict |
| `user_id` | `users` | restrict |

---

## event_files

**Defined in migrations:**
- `2025_11_21_023743_create_event_files_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `event_id` | foreignId | No | - | No | - |
| `uploaded_by` | foreignId | No | - | No | - |
| `file_name` | string | No | - | No | - |
| `file_path` | string | No | - | No | - |
| `file_type` | string | No | - | No | - |
| `file_size` | unsignedBigInteger | No | - | No | - |
| `mime_type` | string | No | - | No | - |
| `description` | text | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `event_id` | `events` | restrict |
| `uploaded_by` | `users` | restrict |

---

## event_participants

**Defined in migrations:**
- `2025_10_05_125244_create_event_participants_table.php`
- `2025_11_28_133159_add_attendance_status_to_event_participants_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `event_id` | foreignId | No | - | No | - |
| `user_id` | foreignId | No | - | No | - |
| `qr_scanned` | boolean | No | - | No | - |
| `scanned_at` | timestamp | No | - | No | - |
| `scanned_by` | foreignId | No | - | No | - |
| `attendance_status` | enum | No | - | No | Attended, Late, Absent |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |

---

## event_requirements

**Defined in migrations:**
- `2025_10_05_125235_create_event_requirements_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `event_id` | foreignId | No | - | No | - |
| `requirement_name` | string | No | - | No | - |
| `is_uploaded` | boolean | No | - | No | - |
| `uploaded_by` | foreignId | No | - | No | - |
| `file_path` | string | No | - | No | - |

---

## events

**Defined in migrations:**
- `2025_01_15_000001_add_indexes_for_performance.php`
- `2025_10_05_125234_create_events_table.php`
- `2025_10_22_230000_add_event_date_to_events_table.php`
- `2025_10_24_120000_add_organization_id_to_events_table.php`
- `2025_10_24_140000_add_location_to_events_table.php`
- `2025_10_25_000001_add_end_date_to_events_table.php`
- `2025_10_25_023535_add_qr_code_path_to_events_table.php`
- `2025_11_01_162115_add_decline_reason_to_events_table.php`
- `2025_11_12_050306_add_coordinator_name_to_events_table.php`
- `2025_11_25_224119_add_required_student_participation_to_events_table.php`
- `2025_11_28_133153_add_participation_monitoring_fields_to_events_table.php`
- `2025_11_29_020247_add_points_to_events_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |
| `description` | text | No | - | No | - |
| `start_time` | dateTime | No | - | No | - |
| `end_time` | dateTime | No | - | No | - |
| `qr_code_path` | string | No | - | No | - |
| `created_by` | foreignId | No | - | No | - |
| `status` | string | No | - | No | - |
| `event_date` | dateTime | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `location` | string | No | - | No | - |
| `end_date` | date | No | - | No | - |
| `decline_reason` | text | No | - | No | - |
| `coordinator_name` | string | No | - | No | - |
| `required_student_participation` | boolean | No | - | No | - |
| `monitoring_started` | boolean | No | - | No | - |
| `monitoring_started_at` | timestamp | No | - | No | - |
| `attended_threshold_minutes` | integer | No | - | No | - |
| `late_threshold_minutes` | integer | No | - | No | - |
| `absent_threshold_minutes` | integer | No | - | No | - |
| `points` | integer | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `created_by` | `users` | restrict |

---

## failed_jobs

**Defined in migrations:**
- `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `queue` | text | No | - | No | - |
| `reserved_at` | unsignedInteger | No | - | No | - |
| `available_at` | unsignedInteger | No | - | No | - |
| `created_at` | integer | No | - | No | - |
| `name` | string | No | - | No | - |
| `total_jobs` | integer | No | - | No | - |
| `pending_jobs` | integer | No | - | No | - |
| `failed_jobs` | integer | No | - | No | - |
| `cancelled_at` | integer | No | - | No | - |
| `finished_at` | integer | No | - | No | - |
| `uuid` | string | No | - | No | - |
| `connection` | text | No | - | No | - |
| `failed_at` | timestamp | No | - | No | - |

---

## family_members

**Defined in migrations:**
- `2025_12_15_235136_create_family_members_table.php`
- `2025_12_16_000002_create_family_members_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `relation` | enum | No | - | No | father, mother, guardian, spouse |
| `name` | string | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `occupation` | string | No | - | No | - |
| `workplace` | string | No | - | No | - |
| `monthly_income` | string | No | - | No | - |
| `relationship` | string | No | - | No | - |

---

## fraternity_members

**Defined in migrations:**
- `2025_12_16_073954_create_fraternity_members_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `fraternity_sorority_name` | string | No | - | No | - |
| `fraternity_sorority_position` | string | No | - | No | - |
| `type` | enum | No | - | No | fraternity, sorority |
| `membership_start_date` | date | No | - | No | - |
| `membership_end_date` | date | No | - | No | - |
| `notes` | text | No | - | No | - |

---

## government_affiliations

**Defined in migrations:**
- `2025_12_16_073948_create_government_affiliations_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `is_government_member` | enum | No | - | No | no, yes |
| `government_level` | enum | No | - | No | barangay, municipal_city, provincial, national |
| `government_role_position` | text | No | - | No | - |
| `government_unit_name` | string | No | - | No | - |
| `start_date` | date | No | - | No | - |
| `end_date` | date | No | - | No | - |
| `notes` | text | No | - | No | - |

---

## indigenous_members

**Defined in migrations:**
- `2025_12_16_073942_create_indigenous_members_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `is_indigenous_group_member` | boolean | No | - | No | - |
| `indigenous_group_specify` | string | No | - | No | - |
| `tribal_affiliation` | text | No | - | No | - |
| `notes` | text | No | - | No | - |

---

## job_batches

**Defined in migrations:**
- `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `queue` | text | No | - | No | - |
| `reserved_at` | unsignedInteger | No | - | No | - |
| `available_at` | unsignedInteger | No | - | No | - |
| `created_at` | integer | No | - | No | - |
| `name` | string | No | - | No | - |
| `total_jobs` | integer | No | - | No | - |
| `pending_jobs` | integer | No | - | No | - |
| `failed_jobs` | integer | No | - | No | - |
| `cancelled_at` | integer | No | - | No | - |
| `finished_at` | integer | No | - | No | - |
| `uuid` | string | No | - | No | - |
| `connection` | text | No | - | No | - |
| `failed_at` | timestamp | No | - | No | - |

---

## jobs

**Defined in migrations:**
- `0001_01_01_000002_create_jobs_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `queue` | text | No | - | No | - |
| `reserved_at` | unsignedInteger | No | - | No | - |
| `available_at` | unsignedInteger | No | - | No | - |
| `created_at` | integer | No | - | No | - |
| `name` | string | No | - | No | - |
| `total_jobs` | integer | No | - | No | - |
| `pending_jobs` | integer | No | - | No | - |
| `failed_jobs` | integer | No | - | No | - |
| `cancelled_at` | integer | No | - | No | - |
| `finished_at` | integer | No | - | No | - |
| `uuid` | string | No | - | No | - |
| `connection` | text | No | - | No | - |
| `failed_at` | timestamp | No | - | No | - |

---

## messages

**Defined in migrations:**
- `2025_10_10_000001_create_messages_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `sender_id` | unsignedBigInteger | No | - | No | - |
| `recipient_id` | unsignedBigInteger | No | - | No | - |
| `content` | text | No | - | No | - |
| `reply_to` | unsignedBigInteger | No | - | No | - |
| `is_read` | boolean | No | - | No | - |

---

## nationalities

**Defined in migrations:**
- `2025_12_16_073928_create_nationalities_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |
| `code` | string | No | - | No | - |
| `is_active` | boolean | No | - | No | - |

---

## notifications

**Defined in migrations:**
- `2025_10_14_112204_create_notifications_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `type` | string | No | - | No | - |
| `notifiable` | morphs | No | - | No | - |
| `data` | text | No | - | No | - |
| `read_at` | timestamp | No | - | No | - |

---

## org_structure_configs

**Defined in migrations:**
- `2025_11_05_071950_create_org_structure_configs_table.php`
- `2025_11_05_073632_modify_org_structure_configs_table_add_staff_selections.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `config_key` | string | No | - | No | - |
| `staff_per_row` | json | No | - | No | - |
| `max_levels` | integer | No | - | No | - |
| `staff_selections` | json | No | - | No | - |

---

## organization_files

**Defined in migrations:**
- `2025_11_25_191417_add_status_to_file_tables.php`
- `2025_12_02_000003_create_organization_files_table.php`
- `2025_12_02_000006_add_file_category_to_organization_files_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `status` | enum | No | - | No | pending, approved, rejected |
| `organization_id` | foreignId | No | - | No | - |
| `uploaded_by` | foreignId | No | - | No | - |
| `file_name` | string | No | - | No | - |
| `file_path` | string | No | - | No | - |
| `file_type` | string | No | - | No | - |
| `description` | text | No | - | No | - |
| `file_size` | unsignedBigInteger | No | - | No | - |
| `mime_type` | string | No | - | No | - |
| `file_category` | string | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `organization_id` | `organizations` | restrict |
| `uploaded_by` | `users` | restrict |

---

## organization_registration_requests

**Defined in migrations:**
- `2025_10_27_000000_create_organization_registration_requests_table.php`
- `2025_11_01_202113_add_details_to_organization_registration_requests_table.php`
- `2025_11_16_233942_add_position_to_organization_registration_requests_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `student_id` | unsignedBigInteger | No | - | No | - |
| `organization_id` | unsignedBigInteger | No | - | No | - |
| `status` | enum | No | - | No | pending, approved, declined |
| `details` | text | No | - | No | - |
| `position` | string | No | - | No | - |

---

## organization_staff

**Defined in migrations:**
- `2025_10_29_000001_create_organization_staff_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `organization_id` | unsignedBigInteger | No | - | No | - |
| `staff_id` | unsignedBigInteger | No | - | No | - |

---

## organization_user

**Defined in migrations:**
- `2025_10_15_140000_create_organization_user_table.php`
- `2025_11_16_233849_modify_organization_user_table_for_multiple_memberships_and_positions.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `position` | string | No | - | No | - |

---

## organizations

**Defined in migrations:**
- `2025_10_05_125144_create_organizations_table.php`
- `2025_10_16_120000_add_department_id_to_organizations_table.php`
- `2025_11_01_163122_add_official_email_to_organizations_table.php`
- `2025_11_01_170559_add_profile_fields_to_organizations_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |
| `is_special` | boolean | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `official_email` | string | No | - | No | - |
| `acronym` | string | No | - | No | - |
| `mailing_address` | text | No | - | No | - |
| `date_established` | date | No | - | No | - |

---

## password_reset_tokens

**Defined in migrations:**
- `2025_10_05_125210_create_users_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `first_name` | string | No | - | No | - |
| `middle_name` | string | No | - | No | - |
| `last_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `gender` | enum | No | - | No | male, female, other |
| `birth_date` | date | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `course_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `year_level` | tinyInteger | No | - | No | - |
| `student_type1` | enum | No | - | No | regular, irregular, transferee |
| `student_type2` | enum | No | - | No | paying, scholar |
| `scholarship_id` | foreignId | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `emergency_contact_name` | string | No | - | No | - |
| `emergency_contact_number` | string | No | - | No | - |
| `emergency_relation` | string | No | - | No | - |
| `role` | tinyInteger | No | - | No | - |
| `email_verified_at` | timestamp | No | - | No | - |
| `password` | string | No | - | No | - |
| `token` | string | No | - | No | - |
| `created_at` | timestamp | No | - | No | - |
| `ip_address` | string | No | - | No | - |
| `user_agent` | text | No | - | No | - |
| `last_activity` | integer | No | - | No | - |

---

## password_resets

**Defined in migrations:**
- `2014_10_12_100000_create_password_resets_table.php`
- `2025_12_10_092129_drop_unnecessary_tables.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `email` | string | No | - | No | - |
| `token` | string | No | - | No | - |
| `created_at` | timestamp | No | - | No | - |

---

## personal_information

**Defined in migrations:**
- `2025_12_15_235153_create_personal_information_table.php`
- `2025_12_16_000005_create_personal_information_table.php`
- `2025_12_16_074819_migrate_data_to_new_normalized_tables.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `age` | integer | No | - | No | - |
| `civil_status` | enum | No | - | No | single, married, divorced, widowed |
| `maiden_name` | string | No | - | No | - |
| `place_of_birth` | string | No | - | No | - |
| `nationality` | string | No | - | No | - |
| `religion` | string | No | - | No | - |
| `sport` | text | No | - | No | - |
| `arts` | text | No | - | No | - |
| `technical` | text | No | - | No | - |
| `is_indigenous_group_member` | boolean | No | - | No | - |
| `indigenous_group_specify` | string | No | - | No | - |
| `is_pwd` | boolean | No | - | No | - |
| `pwd_id_image` | text | No | - | No | - |
| `is_government_member` | enum | No | - | No | no, yes |
| `government_level` | enum | No | - | No | barangay, municipal_city, provincial |
| `government_role_position` | text | No | - | No | - |
| `living_arrangement` | enum | No | - | No | home, boarding_house, relatives, working_student, others |
| `living_arrangement_others_specify` | text | No | - | No | - |
| `is_single_parent` | boolean | No | - | No | - |
| `fraternity_sorority_name` | string | No | - | No | - |
| `fraternity_sorority_position` | string | No | - | No | - |
| `has_criminal_record` | boolean | No | - | No | - |
| `nationality_id` | foreignId | No | - | No | - |

---

## pwd_information

**Defined in migrations:**
- `2025_12_16_073936_create_pwd_information_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `is_pwd` | boolean | No | - | No | - |
| `pwd_id_image` | text | No | - | No | - |
| `pwd_id_number` | string | No | - | No | - |
| `disability_type` | text | No | - | No | - |
| `notes` | text | No | - | No | - |

---

## scholarships

**Defined in migrations:**
- `2025_10_05_125159_create_scholarships_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `name` | string | No | - | No | - |

---

## sessions

**Defined in migrations:**
- `2025_10_05_125210_create_users_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `first_name` | string | No | - | No | - |
| `middle_name` | string | No | - | No | - |
| `last_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `gender` | enum | No | - | No | male, female, other |
| `birth_date` | date | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `course_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `year_level` | tinyInteger | No | - | No | - |
| `student_type1` | enum | No | - | No | regular, irregular, transferee |
| `student_type2` | enum | No | - | No | paying, scholar |
| `scholarship_id` | foreignId | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `emergency_contact_name` | string | No | - | No | - |
| `emergency_contact_number` | string | No | - | No | - |
| `emergency_relation` | string | No | - | No | - |
| `role` | tinyInteger | No | - | No | - |
| `email_verified_at` | timestamp | No | - | No | - |
| `password` | string | No | - | No | - |
| `token` | string | No | - | No | - |
| `created_at` | timestamp | No | - | No | - |
| `ip_address` | string | No | - | No | - |
| `user_agent` | text | No | - | No | - |
| `last_activity` | integer | No | - | No | - |

---

## staff

**Defined in migrations:**
- `2025_01_15_000001_add_indexes_for_performance.php`
- `2025_10_11_140000_create_staff_table.php`
- `2025_10_15_180000_alter_staff_add_contract_fields.php`
- `2025_10_15_190500_add_missing_staff_columns.php`
- `2025_10_15_191000_alter_staff_length_of_service_to_string.php`
- `2025_11_13_235832_add_about_me_to_staff_table.php`
- `2025_12_08_163242_remove_emt_coordinator_designation.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `first_name` | string | No | - | No | - |
| `last_name` | string | No | - | No | - |
| `middle_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `password` | string | No | - | No | - |
| `designation` | string | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `admin_id` | foreignId | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `image` | string | No | - | No | - |
| `birth_date` | date | No | - | No | - |
| `gender` | string | No | - | No | - |
| `age` | unsignedInteger | No | - | No | - |
| `user_id` | string | No | - | No | - |
| `service_order` | string | No | - | No | - |
| `length_of_service` | string | No | - | No | - |
| `contract_end_at` | dateTime | No | - | No | - |
| `employment_status` | string | No | - | No | - |
| `about_me` | text | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `admin_id` | `users` | restrict |

---

## staff_message_attachments

**Defined in migrations:**
- `2025_10_15_160200_create_staff_message_attachments_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `message_id` | foreignId | No | - | No | - |
| `original_name` | string | No | - | No | - |
| `path` | string | No | - | No | - |
| `size` | unsignedBigInteger | No | - | No | - |
| `mime_type` | string | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `message_id` | `staff_messages` | restrict |

---

## staff_message_mentions

**Defined in migrations:**
- `2025_10_15_160300_create_staff_message_mentions_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `message_id` | foreignId | No | - | No | - |
| `mentioned_user_id` | foreignId | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `message_id` | `staff_messages` | restrict |
| `mentioned_user_id` | `users` | restrict |

---

## staff_messages

**Defined in migrations:**
- `2025_10_15_160000_create_staff_messages_table.php`
- `2025_10_15_160100_add_parent_id_to_staff_messages_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `type` | enum | No | - | No | announcement, inquiry |
| `content` | text | No | - | No | - |
| `parent_id` | foreignId | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |

---

## staff_organization_files

**Defined in migrations:**
- `2025_11_25_191417_add_status_to_file_tables.php`
- `2025_12_02_000003_create_staff_organization_files_table.php`
- `2025_12_02_000005_make_organization_id_required_in_staff_organization_files_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `status` | enum | No | - | No | pending, approved, rejected |
| `staff_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `file_name` | string | No | - | No | - |
| `file_path` | string | No | - | No | - |
| `file_type` | string | No | - | No | - |
| `file_category` | string | No | - | No | - |
| `file_size` | integer | No | - | No | - |
| `mime_type` | string | No | - | No | - |
| `description` | text | No | - | No | - |
| `uploaded_by` | foreignId | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `staff_id` | `users` | restrict |
| `uploaded_by` | `users` | restrict |

---

## staff_profiles

**Defined in migrations:**
- `2025_10_11_130000_create_staff_profiles_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `designation` | string | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `image` | string | No | - | No | - |
| `birth_date` | date | No | - | No | - |
| `gender` | string | No | - | No | - |
| `age` | unsignedInteger | No | - | No | - |
| `middle_name` | string | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |

---

## status_changes

**Defined in migrations:**
- `2025_10_29_000001_create_status_changes_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `auditable_type` | string | No | - | No | - |
| `auditable_id` | unsignedBigInteger | No | - | No | - |
| `from_status` | string | No | - | No | - |
| `to_status` | string | No | - | No | - |
| `changed_by` | unsignedBigInteger | No | - | No | - |
| `meta` | json | No | - | No | - |

---

## student_information

**Defined in migrations:**
- `2025_12_15_235147_create_student_information_table.php`
- `2025_12_16_000004_create_student_information_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `year_level` | integer | No | - | No | - |
| `student_type1` | enum | No | - | No | regular, irregular, transferee |
| `student_type2` | enum | No | - | No | paying, scholar |
| `student_type` | enum | No | - | No | new, old |
| `school_year` | string | No | - | No | - |
| `semester` | string | No | - | No | - |
| `academic_year` | string | No | - | No | - |
| `scholarship_id` | foreignId | No | - | No | - |
| `is_active_scholar` | boolean | No | - | No | - |
| `scholarship_grant_name` | string | No | - | No | - |

---

## student_points

**Defined in migrations:**
- `2025_11_29_020255_create_student_points_table.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `event_id` | foreignId | No | - | No | - |
| `feedback_id` | foreignId | No | - | No | - |
| `points` | integer | No | - | No | - |
| `notes` | text | No | - | No | - |
| `awarded_at` | timestamp | No | - | No | - |

### Foreign Keys

| Column | References Table | On Delete |
|--------|------------------|----------|
| `user_id` | `users` | restrict |
| `event_id` | `events` | restrict |

---

## students

**Defined in migrations:**
- `2025_01_15_000001_add_indexes_for_performance.php`
- `2025_10_22_000001_create_students_table.php`
- `2025_10_22_200000_add_user_id_to_students_table.php`
- `2025_10_22_210000_change_student_id_to_user_id_in_students_table.php`
- `2025_10_22_220000_remove_student_id_make_user_id_unique_in_students_table.php`
- `2025_10_31_171252_add_personal_data_sheet_columns_to_users_and_students_tables.php`
- `2025_11_05_015157_add_personal_data_sheet_image_to_students_table.php`
- `2025_11_06_203630_add_student_information_sheet_fields_to_users_and_students_tables.php`
- `2025_11_06_210159_add_additional_fields_to_student_information_sheet.php`
- `2025_12_15_235642_remove_normalized_columns_from_students_table.php`
- `2025_12_16_000008_remove_redundant_columns_from_users_and_students.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `student_id` | string | No | - | No | - |
| `first_name` | string | No | - | No | - |
| `middle_name` | string | No | - | No | - |
| `last_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `gender` | enum | No | - | No | male, female, other |
| `birth_date` | date | No | - | No | - |
| `department_id` | unsignedBigInteger | No | - | No | - |
| `course_id` | unsignedBigInteger | No | - | No | - |
| `organization_id` | unsignedBigInteger | No | - | No | - |
| `scholarship_id` | unsignedBigInteger | No | - | No | - |
| `year_level` | integer | No | - | No | - |
| `student_type1` | enum | No | - | No | regular, irregular, transferee |
| `student_type2` | enum | No | - | No | paying, scholar |
| `contact_number` | string | No | - | No | - |
| `emergency_contact_name` | string | No | - | No | - |
| `emergency_contact_number` | string | No | - | No | - |
| `emergency_relation` | string | No | - | No | - |
| `user_id` | unsignedBigInteger | No | - | No | - |
| `age` | integer | No | - | No | - |
| `civil_status` | enum | No | - | No | single, married, divorced, widowed |
| `maiden_name` | string | No | - | No | - |
| `place_of_birth` | string | No | - | No | - |
| `complete_home_address` | text | No | - | No | - |
| `parent_spouse_guardian` | string | No | - | No | - |
| `parent_spouse_guardian_address` | text | No | - | No | - |
| `elementary_school` | string | No | - | No | - |
| `elementary_address` | string | No | - | No | - |
| `elementary_year_graduated` | string | No | - | No | - |
| `high_school` | string | No | - | No | - |
| `high_school_address` | string | No | - | No | - |
| `high_school_year_graduated` | string | No | - | No | - |
| `college_name` | string | No | - | No | - |
| `college_address` | string | No | - | No | - |
| `college_course` | string | No | - | No | - |
| `college_year` | string | No | - | No | - |
| `school_year` | string | No | - | No | - |
| `semester` | string | No | - | No | - |
| `student_type` | enum | No | - | No | new, old |
| `form_137_presented` | boolean | No | - | No | - |
| `tor_presented` | boolean | No | - | No | - |
| `good_moral_cert_presented` | boolean | No | - | No | - |
| `birth_cert_presented` | boolean | No | - | No | - |
| `marriage_cert_presented` | boolean | No | - | No | - |
| `personal_data_sheet_image` | string | No | - | No | - |
| `ext_name` | string | No | - | No | - |
| `street` | text | No | - | No | - |
| `barangay` | text | No | - | No | - |
| `city_municipality` | text | No | - | No | - |
| `province` | text | No | - | No | - |
| `zip_code` | string | No | - | No | - |
| `nationality` | text | No | - | No | - |
| `religion` | text | No | - | No | - |
| `tel_no` | string | No | - | No | - |
| `spouse_name` | text | No | - | No | - |
| `spouse_contact_no` | string | No | - | No | - |
| `sport` | text | No | - | No | - |
| `arts` | text | No | - | No | - |
| `technical` | text | No | - | No | - |
| `junior_high_school_name` | text | No | - | No | - |
| `junior_high_school_year_completed` | string | No | - | No | - |
| `junior_high_school_address` | text | No | - | No | - |
| `junior_high_school_honors_awards` | text | No | - | No | - |
| `senior_high_school_name` | text | No | - | No | - |
| `senior_high_school_year_graduated` | string | No | - | No | - |
| `senior_high_school_track_strand` | text | No | - | No | - |
| `senior_high_school_lrn` | string | No | - | No | - |
| `senior_high_school_address` | text | No | - | No | - |
| `senior_high_school_honors_awards` | text | No | - | No | - |
| `last_school_attended` | text | No | - | No | - |
| `last_school_course` | text | No | - | No | - |
| `last_school_address` | text | No | - | No | - |
| `last_school_year_attended` | string | No | - | No | - |
| `father_name` | text | No | - | No | - |
| `father_contact_number` | string | No | - | No | - |
| `father_occupation` | text | No | - | No | - |
| `father_workplace` | text | No | - | No | - |
| `father_monthly_income` | text | No | - | No | - |
| `mother_name` | text | No | - | No | - |
| `mother_contact_number` | string | No | - | No | - |
| `mother_occupation` | text | No | - | No | - |
| `mother_workplace` | text | No | - | No | - |
| `mother_monthly_income` | text | No | - | No | - |
| `guardian_name` | text | No | - | No | - |
| `guardian_relationship` | text | No | - | No | - |
| `guardian_contact_number` | string | No | - | No | - |
| `guardian_occupation` | text | No | - | No | - |
| `guardian_workplace` | text | No | - | No | - |
| `guardian_monthly_income` | text | No | - | No | - |
| `is_active_scholar` | boolean | No | - | No | - |
| `scholarship_grant_name` | text | No | - | No | - |
| `is_indigenous_group_member` | boolean | No | - | No | - |
| `indigenous_group_specify` | text | No | - | No | - |
| `is_pwd` | boolean | No | - | No | - |
| `pwd_id_image` | text | No | - | No | - |
| `is_government_member` | enum | No | - | No | no, yes |
| `government_level` | enum | No | - | No | barangay, municipal_city, provincial |
| `government_role_position` | text | No | - | No | - |
| `living_arrangement` | enum | No | - | No | home, boarding_house, relatives, working_student, others |
| `living_arrangement_others_specify` | text | No | - | No | - |
| `is_single_parent` | boolean | No | - | No | - |
| `fraternity_sorority_name` | text | No | - | No | - |
| `fraternity_sorority_position` | text | No | - | No | - |
| `has_criminal_record` | boolean | No | - | No | - |

---

## users

**Defined in migrations:**
- `2025_01_15_000001_add_indexes_for_performance.php`
- `2025_10_05_125210_create_users_table.php`
- `2025_10_06_015154_add_designation_to_users_table.php`
- `2025_10_09_112635_add_name_to_users_table.php`
- `2025_10_10_041719_add_supervisor_id_to_users_table.php`
- `2025_10_11_120000_drop_name_from_users_table.php`
- `2025_10_16_200500_alter_users_add_assistant_optional_fields.php`
- `2025_10_16_210200_alter_users_make_birthdate_gender_nullable.php`
- `2025_10_16_211500_alter_users_add_suspended_flag.php`
- `2025_10_17_000001_add_last_imported_worksheet_to_users_table.php`
- `2025_10_31_171252_add_personal_data_sheet_columns_to_users_and_students_tables.php`
- `2025_11_06_203630_add_student_information_sheet_fields_to_users_and_students_tables.php`
- `2025_11_06_210159_add_additional_fields_to_student_information_sheet.php`
- `2025_11_13_024805_add_suspension_reason_to_users_table.php`
- `2025_11_13_235855_add_about_me_to_users_table.php`
- `2025_12_02_000001_add_academic_year_to_users_table.php`
- `2025_12_15_235636_remove_normalized_columns_from_users_table.php`
- `2025_12_16_000008_remove_redundant_columns_from_users_and_students.php`

### Columns

| Column Name | Data Type | Nullable | Default | Unique | Enum Values |
|------------|-----------|----------|---------|--------|-------------|
| `user_id` | foreignId | No | - | No | - |
| `first_name` | string | No | - | No | - |
| `middle_name` | string | No | - | No | - |
| `last_name` | string | No | - | No | - |
| `email` | string | No | - | No | - |
| `gender` | enum | No | - | No | male, female, other |
| `birth_date` | date | No | - | No | - |
| `department_id` | foreignId | No | - | No | - |
| `course_id` | foreignId | No | - | No | - |
| `organization_id` | foreignId | No | - | No | - |
| `year_level` | tinyInteger | No | - | No | - |
| `student_type1` | enum | No | - | No | regular, irregular, transferee |
| `student_type2` | enum | No | - | No | paying, scholar |
| `scholarship_id` | foreignId | No | - | No | - |
| `contact_number` | string | No | - | No | - |
| `emergency_contact_name` | string | No | - | No | - |
| `emergency_contact_number` | string | No | - | No | - |
| `emergency_relation` | string | No | - | No | - |
| `role` | tinyInteger | No | - | No | - |
| `email_verified_at` | timestamp | No | - | No | - |
| `password` | string | No | - | No | - |
| `token` | string | No | - | No | - |
| `created_at` | timestamp | No | - | No | - |
| `ip_address` | string | No | - | No | - |
| `user_agent` | text | No | - | No | - |
| `last_activity` | integer | No | - | No | - |
| `designation` | string | No | - | No | - |
| `name` | string | No | - | No | - |
| `supervisor_id` | foreignId | No | - | No | - |
| `position` | string | No | - | No | - |
| `image` | string | No | - | No | - |
| `service_order` | string | No | - | No | - |
| `length_of_service` | integer | No | - | No | - |
| `contract_end_at` | date | No | - | No | - |
| `suspended` | boolean | No | - | No | - |
| `last_imported_worksheet` | string | No | - | No | - |
| `age` | integer | No | - | No | - |
| `civil_status` | enum | No | - | No | single, married, divorced, widowed |
| `maiden_name` | string | No | - | No | - |
| `place_of_birth` | string | No | - | No | - |
| `complete_home_address` | text | No | - | No | - |
| `parent_spouse_guardian` | string | No | - | No | - |
| `parent_spouse_guardian_address` | text | No | - | No | - |
| `elementary_school` | string | No | - | No | - |
| `elementary_address` | string | No | - | No | - |
| `elementary_year_graduated` | string | No | - | No | - |
| `high_school` | string | No | - | No | - |
| `high_school_address` | string | No | - | No | - |
| `high_school_year_graduated` | string | No | - | No | - |
| `college_name` | string | No | - | No | - |
| `college_address` | string | No | - | No | - |
| `college_course` | string | No | - | No | - |
| `college_year` | string | No | - | No | - |
| `school_year` | string | No | - | No | - |
| `semester` | string | No | - | No | - |
| `student_type` | enum | No | - | No | new, old |
| `form_137_presented` | boolean | No | - | No | - |
| `tor_presented` | boolean | No | - | No | - |
| `good_moral_cert_presented` | boolean | No | - | No | - |
| `birth_cert_presented` | boolean | No | - | No | - |
| `marriage_cert_presented` | boolean | No | - | No | - |
| `ext_name` | string | No | - | No | - |
| `street` | text | No | - | No | - |
| `barangay` | text | No | - | No | - |
| `city_municipality` | text | No | - | No | - |
| `province` | text | No | - | No | - |
| `zip_code` | string | No | - | No | - |
| `nationality` | text | No | - | No | - |
| `religion` | text | No | - | No | - |
| `tel_no` | string | No | - | No | - |
| `spouse_name` | text | No | - | No | - |
| `spouse_contact_no` | string | No | - | No | - |
| `sport` | text | No | - | No | - |
| `arts` | text | No | - | No | - |
| `technical` | text | No | - | No | - |
| `junior_high_school_name` | text | No | - | No | - |
| `junior_high_school_year_completed` | string | No | - | No | - |
| `junior_high_school_address` | text | No | - | No | - |
| `junior_high_school_honors_awards` | text | No | - | No | - |
| `senior_high_school_name` | text | No | - | No | - |
| `senior_high_school_year_graduated` | string | No | - | No | - |
| `senior_high_school_track_strand` | text | No | - | No | - |
| `senior_high_school_lrn` | string | No | - | No | - |
| `senior_high_school_address` | text | No | - | No | - |
| `senior_high_school_honors_awards` | text | No | - | No | - |
| `last_school_attended` | text | No | - | No | - |
| `last_school_course` | text | No | - | No | - |
| `last_school_address` | text | No | - | No | - |
| `last_school_year_attended` | string | No | - | No | - |
| `father_name` | text | No | - | No | - |
| `father_contact_number` | string | No | - | No | - |
| `father_occupation` | text | No | - | No | - |
| `father_workplace` | text | No | - | No | - |
| `father_monthly_income` | text | No | - | No | - |
| `mother_name` | text | No | - | No | - |
| `mother_contact_number` | string | No | - | No | - |
| `mother_occupation` | text | No | - | No | - |
| `mother_workplace` | text | No | - | No | - |
| `mother_monthly_income` | text | No | - | No | - |
| `guardian_name` | text | No | - | No | - |
| `guardian_relationship` | text | No | - | No | - |
| `guardian_contact_number` | string | No | - | No | - |
| `guardian_occupation` | text | No | - | No | - |
| `guardian_workplace` | text | No | - | No | - |
| `guardian_monthly_income` | text | No | - | No | - |
| `is_active_scholar` | boolean | No | - | No | - |
| `scholarship_grant_name` | text | No | - | No | - |
| `is_indigenous_group_member` | boolean | No | - | No | - |
| `indigenous_group_specify` | text | No | - | No | - |
| `is_pwd` | boolean | No | - | No | - |
| `pwd_id_image` | text | No | - | No | - |
| `is_government_member` | enum | No | - | No | no, yes |
| `government_level` | enum | No | - | No | barangay, municipal_city, provincial |
| `government_role_position` | text | No | - | No | - |
| `living_arrangement` | enum | No | - | No | home, boarding_house, relatives, working_student, others |
| `living_arrangement_others_specify` | text | No | - | No | - |
| `is_single_parent` | boolean | No | - | No | - |
| `fraternity_sorority_name` | text | No | - | No | - |
| `fraternity_sorority_position` | text | No | - | No | - |
| `has_criminal_record` | boolean | No | - | No | - |
| `suspension_reason` | text | No | - | No | - |
| `about_me` | text | No | - | No | - |
| `academic_year` | string | No | - | No | - |

---

## Relationships Summary

### admin_files

- `admin_files`.`uploaded_by` → `users`.id

### appointment_files

- `appointment_files`.`uploaded_by` → `users`.id

### assistant_assignments

- `assistant_assignments`.`user_id` → `users`.id
- `assistant_assignments`.`organization_id` → `organizations`.id

### assistant_leadership_backgrounds

- `assistant_leadership_backgrounds`.`user_id` → `users`.id

### attendances

- `attendances`.`student_id` → `users`.id
- `attendances`.`event_id` → `events`.id

### event_feedback

- `event_feedback`.`event_id` → `events`.id
- `event_feedback`.`user_id` → `users`.id

### event_files

- `event_files`.`event_id` → `events`.id
- `event_files`.`uploaded_by` → `users`.id

### event_participants

- `event_participants`.`user_id` → `users`.id

### events

- `events`.`created_by` → `users`.id

### organization_files

- `organization_files`.`organization_id` → `organizations`.id
- `organization_files`.`uploaded_by` → `users`.id

### staff

- `staff`.`admin_id` → `users`.id

### staff_message_attachments

- `staff_message_attachments`.`message_id` → `staff_messages`.id

### staff_message_mentions

- `staff_message_mentions`.`message_id` → `staff_messages`.id
- `staff_message_mentions`.`mentioned_user_id` → `users`.id

### staff_messages

- `staff_messages`.`user_id` → `users`.id

### staff_organization_files

- `staff_organization_files`.`staff_id` → `users`.id
- `staff_organization_files`.`uploaded_by` → `users`.id

### staff_profiles

- `staff_profiles`.`user_id` → `users`.id

### student_points

- `student_points`.`user_id` → `users`.id
- `student_points`.`event_id` → `events`.id

