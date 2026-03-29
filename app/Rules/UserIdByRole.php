<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserIdByRole implements ValidationRule
{
    protected $role;
    protected $maxLength;

    /**
     * Create a new rule instance.
     *
     * @param int $role The user role (1=Student, 2=Staff, 3=Student Leader, 4=Admin)
     */
    public function __construct($role = null)
    {
        $this->role = $role ?? request()->input('role', auth()->user()->role ?? 1);
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow nullable user_id
        }

        // Check if value contains only numeric characters
        if (!preg_match('/^\d+$/', $value)) {
            $fail('The :attribute must contain only numeric characters.');
            return;
        }

        // Determine max length based on role
        switch ((int) $this->role) {
            case 1: // Students
                $this->maxLength = 10;
                break;
            case 2: // Staff
                $this->maxLength = 7;
                break;
            case 3: // Student Leaders (treat as students)
                $this->maxLength = 10;
                break;
            case 4: // Admins (can have flexible format, but prefer staff format)
                $this->maxLength = 7;
                break;
            default:
                $this->maxLength = 10; // Default to student format
        }

        if (strlen($value) > $this->maxLength) {
            $roleName = match((int) $this->role) {
                1 => 'students',
                2 => 'staff',
                3 => 'student leaders',
                4 => 'admins',
                default => 'users'
            };
            $fail("The :attribute must be up to {$this->maxLength} digits for {$roleName}.");
        }
    }
}
