<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            'Guidance Counselor',
            'Prefect of Discipline',
            'Librarian',
            'Nurse',
            'OSA Staff',
            'Student Org. Moderator',
            'Admission Services Officer',
            'Carriers Management Officer',
        ];
        foreach ($designations as $name) {
            Designation::firstOrCreate(['name' => $name]);
        }
    }
}
