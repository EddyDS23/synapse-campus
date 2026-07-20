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

    #[Override]
    public function __construct(private AuthVaultServiceClient $authvault, private StudentPortalServiceClient $studenportal) {}
    /**
     * Execute the console command.
     */
    public function handle()
    {

        $fines_pending = Fine::where('debt_notified', false)->get();

        if ($fines_pending->isEmpty()) {
            return;
        }

        $token = $this->authvault->getTokenService();


        foreach ($fines_pending as $fine) {
            try {
                $answer = $this->studenportal->updateDebt($token,$fine->borrower_id,true);
                $fine->update([
                    'debt_notified'=>$answer
                ]);
                
            } catch (\Throwable $th) {
                continue;
            }
        }


        return;
    }
}
