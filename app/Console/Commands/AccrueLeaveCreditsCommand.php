<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeaveManagementService;

class AccrueLeaveCreditsCommand extends Command
{
    // The name and signature of the console command.
    protected $signature = 'leave:accrue-credits';

    // The console command description.
    protected $description = 'Automatically awards 1.25 VL and SL credits to all employees monthly';

    public function handle(LeaveManagementService $leaveService)
    {
        $this->info('Starting monthly leave credit accrual...');
        
        $leaveService->accrueMonthlyLeaveCredits();
        
        $this->info('Successfully credited 1.25 days to all employees and updated the ledger!');
    }
}