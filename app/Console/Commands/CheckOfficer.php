<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Staff;

class CheckOfficer extends Command
{
    protected $signature = 'check:officer';
    protected $description = 'Check for users and staff with designation Admission Services Officer';

    public function handle()
    {
        $this->info('Users with role=2 and designation/staffProfile=Admission Services Officer:');
        $users = User::where('role', 2)
            ->where(function($q){
                $q->where('designation', 'Admission Services Officer')
                  ->orWhereHas('staffProfile', function($qq){ $qq->where('designation','Admission Services Officer'); }); // staffProfile relationship now uses Staff model with staff_information table
            })->get(['id','email','first_name','last_name','role']);

        if ($users->isEmpty()) {
            $this->line('  (none found)');
        } else {
            foreach ($users as $u) {
                $this->line("  {$u->id} - {$u->email} - {$u->first_name} {$u->last_name} (role {$u->role})");
            }
        }

        $this->info('\nLegacy staff table rows with designation Admission Services Officer:');
        $staff = Staff::where('designation', 'Admission Services Officer')->get(['id','email','first_name','last_name','designation','employment_status']);
        if ($staff->isEmpty()) {
            $this->line('  (none found)');
        } else {
            foreach ($staff as $s) {
                $this->line("  {$s->id} - {$s->email} - {$s->first_name} {$s->last_name} - status: {$s->employment_status}");
                $u = \App\Models\User::where('email', $s->email)->first(['id','email','role','first_name','last_name']);
                if ($u) {
                    $this->line("    -> linked User: {$u->id} - {$u->email} - role: {$u->role} - {$u->first_name} {$u->last_name}");
                } else {
                    $this->line("    -> linked User: (none)");
                }
            }
        }

        return 0;
    }
}
