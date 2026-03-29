# View Update Guide for Database Normalization

## Overview

After normalizing the database, views need to be updated to access data through the new relationships instead of direct column access.

## Common Patterns to Update

### Before (Old Way):
```blade
{{ $student->complete_home_address ?? $student->user->complete_home_address ?? '' }}
{{ $student->user->year_level }}
{{ $student->user->emergency_contact_name }}
{{ $student->user->father_name }}
{{ $student->user->elementary_school }}
```

### After (New Way):
```blade
{{ optional($student->user->homeAddress)->complete_address ?? '' }}
{{ optional($student->user->studentInformation)->year_level }}
{{ optional($student->user->emergencyContacts->first())->name }}
{{ optional($student->user->familyMember('father'))->name }}
{{ optional($student->user->educationalBackground('elementary'))->school_name }}
```

## Field Mapping

### Address Fields
- `$user->complete_home_address` → `optional($user->homeAddress)->complete_address`
- `$user->street` → `optional($user->homeAddress)->street`
- `$user->barangay` → `optional($user->homeAddress)->barangay`
- `$user->city_municipality` → `optional($user->homeAddress)->city_municipality`
- `$user->province` → `optional($user->homeAddress)->province`
- `$user->zip_code` → `optional($user->homeAddress)->zip_code`

### Emergency Contact
- `$user->emergency_contact_name` → `optional($user->emergencyContacts->first())->name`
- `$user->emergency_contact_number` → `optional($user->emergencyContacts->first())->contact_number`
- `$user->emergency_relation` → `optional($user->emergencyContacts->first())->relation`

### Family Members
- `$user->father_name` → `optional($user->familyMember('father'))->name`
- `$user->father_contact_number` → `optional($user->familyMember('father'))->contact_number`
- `$user->father_occupation` → `optional($user->familyMember('father'))->occupation`
- `$user->mother_name` → `optional($user->familyMember('mother'))->name`
- `$user->guardian_name` → `optional($user->familyMember('guardian'))->name`
- `$user->spouse_name` → `optional($user->familyMember('spouse'))->name`

### Educational Background
- `$user->elementary_school` → `optional($user->educationalBackground('elementary'))->school_name`
- `$user->elementary_address` → `optional($user->educationalBackground('elementary'))->address`
- `$user->elementary_year_graduated` → `optional($user->educationalBackground('elementary'))->year_graduated`
- `$user->junior_high_school_name` → `optional($user->educationalBackground('junior_high'))->school_name`
- `$user->senior_high_school_name` → `optional($user->educationalBackground('senior_high'))->school_name`
- `$user->college_name` → `optional($user->educationalBackground('college'))->school_name`
- `$user->last_school_attended` → `optional($user->educationalBackground('last_school'))->school_name`

### Student Information
- `$user->year_level` → `optional($user->studentInformation)->year_level`
- `$user->student_type1` → `optional($user->studentInformation)->student_type1`
- `$user->student_type2` → `optional($user->studentInformation)->student_type2`
- `$user->student_type` → `optional($user->studentInformation)->student_type`
- `$user->school_year` → `optional($user->studentInformation)->school_year`
- `$user->semester` → `optional($user->studentInformation)->semester`
- `$user->academic_year` → `optional($user->studentInformation)->academic_year`
- `$user->is_active_scholar` → `optional($user->studentInformation)->is_active_scholar`
- `$user->scholarship_grant_name` → `optional($user->studentInformation)->scholarship_grant_name`

### Personal Information
- `$user->age` → `optional($user->personalInformation)->age`
- `$user->civil_status` → `optional($user->personalInformation)->civil_status`
- `$user->maiden_name` → `optional($user->personalInformation)->maiden_name`
- `$user->place_of_birth` → `optional($user->personalInformation)->place_of_birth`
- `$user->nationality` → `optional($user->personalInformation)->nationality`
- `$user->religion` → `optional($user->personalInformation)->religion`
- `$user->sport` → `optional($user->personalInformation)->sport`
- `$user->arts` → `optional($user->personalInformation)->arts`
- `$user->technical` → `optional($user->personalInformation)->technical`
- `$user->is_pwd` → `optional($user->personalInformation)->is_pwd`
- `$user->is_indigenous_group_member` → `optional($user->personalInformation)->is_indigenous_group_member`

### Document Checklist
- `$user->form_137_presented` → `optional($user->documentChecklist)->form_137_presented`
- `$user->tor_presented` → `optional($user->documentChecklist)->tor_presented`
- `$user->good_moral_cert_presented` → `optional($user->documentChecklist)->good_moral_cert_presented`
- `$user->birth_cert_presented` → `optional($user->documentChecklist)->birth_cert_presented`
- `$user->marriage_cert_presented` → `optional($user->documentChecklist)->marriage_cert_presented`
- `$user->personal_data_sheet_image` → `optional($user->documentChecklist)->personal_data_sheet_image`

## Files That Need Updating

1. `resources/views/admin/staff/edit-student.blade.php` - ✅ Partially updated
2. `resources/views/admin/staff/dashboard/AdmissionServicesOfficer/student-details.blade.php`
3. `resources/views/admin/staff/dashboard/AdmissionServicesOfficer/student-management.blade.php`
4. `resources/views/student/profile.blade.php`
5. `resources/views/student/register.blade.php`
6. `resources/views/auth/register.blade.php`
7. And 12 more view files...

## Helper Functions

You can create helper functions in your User model or use Laravel's `optional()` helper to safely access relationships:

```php
// In User model
public function getHomeAddressAttribute() {
    return $this->homeAddress;
}

public function getEmergencyContactAttribute() {
    return $this->emergencyContacts->first();
}
```

## Testing

After updating views:
1. Test creating a new student
2. Test editing an existing student
3. Test viewing student details
4. Verify all fields display correctly
5. Check for any null reference errors

