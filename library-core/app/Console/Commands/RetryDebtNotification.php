<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Fine;
use Illuminate\Support\Facades\Http;

use App\Services\AuthVaultServiceClient;
use App\Services\StudentPortalServiceClient;
use Override;

#[Signature('app:retry-debt-notification')]
#[Description('Command description')]
class RetryDebtNotification extends Command
{

    public function __construct(private AuthVaultServiceClient $authvault, private StudentPortalServiceClient $studenportal)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $fines_pending = Fine::where('debt_notified', false)->get();
        $fines_paid = Fine::where('paid_notified', false)->where('status','paid')->get();

        if ($fines_pending->isEmpty() && $fines_paid->isEmpty()) {
            return;
        }

        $token = $this->authvault->getTokenService();

        if (!$fines_pending->isEmpty()) {
            foreach ($fines_pending as $fine) {
                try {
                    $answer = $this->studenportal->updateDebt($token, $fine->borrower_id, true);
                    $fine->update([
                        'debt_notified' => $answer
                    ]);
                } catch (\Throwable $th) {
                    continue;
                }
            }
        }

        if (!$fines_paid->isEmpty()) {
            foreach ($fines_paid as $fine) {
                try {
                    $answer = $this->studenportal->updateDebt($token, $fine->borrower_id, false);
                    $fine->update([
                        'paid_notified' => $answer
                    ]);
                } catch (\Throwable $th) {
                    continue;
                }
            }
        }
    }
}
