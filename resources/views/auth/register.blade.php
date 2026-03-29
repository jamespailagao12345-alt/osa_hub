@extends('layouts.app')

@section('content')
<!-- Registration disabled message and button hidden for welcome screen. -->
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                        </div>

                        <!-- Birth Date + Auto Age -->
                        <div class="row mb-3">
                            <label for="birth_date" class="col-md-4 col-form-label text-md-end">{{ __('Birth Date') }}</label>
                            <div class="col-md-6" data-birth-age-pair>
                                @include('components.birthdate_with_age', ['name' => 'birth_date', 'value' => old('birth_date'), 'ageName' => 'age', 'required' => true])
                            </div>
                        </div>

                        <!-- Department -->
                        <select name="department_id" id="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>

                        <!-- Course -->
                        <select id="course_id" name="course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                        </select>

                        <!-- Organization -->
                        <select id="organization_id" name="organization_id" class="form-control" required>
                            <option value="">-- Select Organization --</option>
                        </select>

                        <!-- Year Level -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Year Level') }}</label>
                            <div class="col-md-6">
                                <select name="year_level" class="form-control" required>
                                    <option value="">-- Select --</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('year_level') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Student Type 1 -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Student Type') }}</label>
                            <div class="col-md-6">
                                <select name="student_type1" class="form-control" required>
                                    <option value="regular" {{ old('student_type1') == 'regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="irregular" {{ old('student_type1') == 'irregular' ? 'selected' : '' }}>Irregular</option>
                                    <option value="transferee" {{ old('student_type1') == 'transferee' ? 'selected' : '' }}>Transferee</option>
                                </select>
                            </div>
                        </div>

                        <!-- Student Type 2 -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Payment Type') }}</label>
                            <div class="col-md-6">
                                <select id="student_type2" name="student_type2" class="form-control" required>
                                    <option value="paying" {{ old('student_type2') == 'paying' ? 'selected' : '' }}>Paying</option>
                                    <option value="scholar" {{ old('student_type2') == 'scholar' ? 'selected' : '' }}>Scholar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Scholarship (appears only if scholar) -->
                        <div class="row mb-3" id="scholarship-field" style="display:none;">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Scholarship') }}</label>
                            <div class="col-md-6">
                                <select name="scholarship_id" class="form-control">
                                    <option value="">Select Scholarship (Optional)</option>
                                    @foreach($scholarships as $scholarship)
                                        <option value="{{ $scholarship->id }}">{{ $scholarship->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="row mb-3">
                            <label for="contact_number" class="col-md-4 col-form-label text-md-end">{{ __('Contact Number') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="contact_number" value="{{ old('contact_number') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="emergency_contact_name" class="col-md-4 col-form-label text-md-end">{{ __('Emergency Contact Person') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="emergency_contact_number" class="col-md-4 col-form-label text-md-end">{{ __('Emergency Contact Number') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="emergency_contact_number" value="{{ old('emergency_contact_number') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Relation to contact Person') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="emergency_relation" value="{{ old('emergency_relation') }}" required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                                <div class="col-md-6">
                                    <input id="password" 
                                        type="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        name="password" 
                                        required
                                        autocomplete="new-password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" 
                                    type="password" 
                                    class="form-control" 
                                    name="password_confirmation" 
                                    required
                                    autocomplete="new-password">
                            </div>
                        </div>
                        <!-- Privacy Agreement -->
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="agreement" id="agreement" required>
                                    <label class="form-check-label" for="agreement">
                                        I agree to the Data Privacy Agreement
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Get elements
    const deptSelect = document.getElementById('department_id');
    const courseSelect = document.getElementById('course_id');
    const orgSelect = document.getElementById('organization_id');

    if (!deptSelect || !courseSelect || !orgSelect) {
        console.error('Dropdown elements not found!');
        return;
    }

    // Use Laravel's URL helper to avoid path issues
    const baseUrl = "{{ url('/') }}";

    // Load organizations
    fetch(`${baseUrl}/api/organizations`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(orgs => {
            orgs.forEach(org => {
                const opt = document.createElement('option');
                opt.value = org.id;
                opt.textContent = org.name + (org.is_special ? ' (Special)' : '');
                orgSelect.appendChild(opt);
            });
        })
        .catch(error => console.error('Error loading organizations:', error));

    // Load courses when department changes
    deptSelect.addEventListener('change', function () {
        const deptId = this.value;
        courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
        if (deptId) {
            fetch(`${baseUrl}/api/courses/${deptId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(courses => {
                    courses.forEach(course => {
                        const opt = document.createElement('option');
                        opt.value = course.id;
                        opt.textContent = course.name;
                        courseSelect.appendChild(opt);
                    });
                })
                .catch(error => console.error('Error loading courses:', error));
        }
    });

    // Show scholarship field if needed
    const type2 = document.getElementById('student_type2');
    const scholarshipField = document.getElementById('scholarship-field');
    if (type2 && scholarshipField) {
        type2.addEventListener('change', () => {
            scholarshipField.style.display = type2.value === 'scholar' ? 'block' : 'none';
        });
        if (type2.value === 'scholar') scholarshipField.style.display = 'block';
    }
});
</script>
@endpush