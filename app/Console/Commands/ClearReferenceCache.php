<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class ClearReferenceCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-reference {--all : Clear all reference data cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached reference data (departments, courses, organizations, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            CacheService::clearReferenceData();
            $this->info('All reference data cache cleared successfully!');
        } else {
            $this->info('Clearing reference data cache...');
            CacheService::clearReferenceData();
            $this->info('Reference data cache cleared successfully!');
        }

        return 0;
    }
}

