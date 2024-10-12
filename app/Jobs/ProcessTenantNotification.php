<?php
namespace App\Jobs;

use App\Notifications\TenantNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessTenantNotification implements ShouldQueue
{
    use Dispatchable, Queueable; // Ensure Dispatchable trait is included

    protected $tenantId;
    protected $message;

    public function __construct($tenantId, $message)
    {
        $this->tenantId = $tenantId;
        $this->message = $message;
    }

    public function handle()
    {
        // Set tenant context
        app()->make('tenant')->setTenant($this->tenantId);

        // Notify the user
        $user = auth()->user();
        if ($user) {
            $user->notify(new TenantNotification([
                'tenantId' => $this->tenantId,
                'message' => $this->message,
            ]));
        }
    }
}
