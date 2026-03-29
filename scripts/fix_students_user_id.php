<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Run with: php artisan tinker or as a custom command

// For each student, set student_information.user_id to users.user_id where student_information.user_id matches users.id
DB::table('student_information')
    ->join('users', 'student_information.user_id', '=', 'users.id')
    ->update(['student_information.user_id' => DB::raw('users.user_id')]);

// Output: All students.user_id now matches users.user_id
