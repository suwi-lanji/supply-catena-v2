<?php

namespace App\Observers;

use App\Models\BranchUser;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
class BranchUserObserver
{
    /**
     * Handle the BranchUser "created" event.
     */
    public function created(BranchUser $branchUser): void
    {
        // Create the request payload based on the BranchUser attributes
        $payload = [
            'tpin' => $branchUser->tpin,
            'bhfId' => $branchUser->bhfId,
            'userId' => $branchUser->user_id, // Assuming user_id is the User ID
            'userNm' => $branchUser->userNm,
            'adrs' => $branchUser->adrs,
            'useYn' => $branchUser->useYn ? 'Y' : 'N', // Convert boolean to 'Y'/'N'
            'regrNm' => $branchUser->regrNm,
            'regrId' => $branchUser->regr_id, // Registrant ID
            'modrNm' => $branchUser->modrNm,
            'modrId' => $branchUser->modr_id, // Modifier ID
        ];

        // Send the HTTP request to save the branch user
        try {
            $response = Http::post(env('ZRA_API_URL') . 'branches/saveBrancheUser', $payload);

            if ($response->successful()) {
                // Handle success response if needed
                Notification::make()
                ->title('Branch User added successfully' . $response->json()['resultMsg'])
                ->success()
                ->send();
            } else {
                // Handle failure response if needed
                // You can log the error or throw an exception
                \Log::error('Failed to save branch user: ' . $response->body());
                Notification::make()
                ->title('Error adding branch user')
                ->body($response->body())
                ->danger()
                ->send();
                $branchUser->delete();
            }
        } catch (\Exception $e) {
            // Handle exceptions (e.g., connection errors)
            \Log::error('Exception occurred while saving branch user: ' . $e->getMessage());
            Notification::make()
                ->title('Error adding branch user')
                ->body($e->getMessage())
                ->danger()
                ->send();
                $branchUser->delete();
        }
    }

    /**
     * Handle the BranchUser "updated" event.
     */
    public function updated(BranchUser $branchUser): void
    {
        //
    }

    /**
     * Handle the BranchUser "deleted" event.
     */
    public function deleted(BranchUser $branchUser): void
    {
        //
    }

    /**
     * Handle the BranchUser "restored" event.
     */
    public function restored(BranchUser $branchUser): void
    {
        //
    }

    /**
     * Handle the BranchUser "force deleted" event.
     */
    public function forceDeleted(BranchUser $branchUser): void
    {
        //
    }
}
