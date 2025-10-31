<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Discount;

class DeactivateExpiredDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate expired discounts';

    /**
     * Execute the console command.
     */
     public function handle()
    {
        $expired = Discount::withoutGlobalScope('hideExpired')
            ->where('end_date', '<', Carbon::now())
            ->where('is_active', true) // Changed from 'status' to 'is_active'
            ->update(['is_active' => false]); // Changed from 'status' to 'is_active'

        $this->info("Deactivated {$expired} expired discounts.");
    
        return 0;
    }
}
