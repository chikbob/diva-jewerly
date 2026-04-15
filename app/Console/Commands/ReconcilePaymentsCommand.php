<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Console\Command;

class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile
        {--provider= : Reconcile only one payment provider}
        {--reference= : Reconcile only one payment reference}';

    protected $description = 'Reconcile payment transactions with the latest provider state.';

    public function __construct(
        private readonly PaymentReconciliationService $reconciliationService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->reconciliationService->reconcile(
            $this->option('provider'),
            $this->option('reference')
        );

        $this->components->info(sprintf(
            'Reconciliation complete. Checked %d transaction(s), updated %d.',
            $result['checked'],
            $result['updated']
        ));

        return self::SUCCESS;
    }
}
