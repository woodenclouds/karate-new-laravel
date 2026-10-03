<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class UpdateRegistrationAmounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:update-amounts {--event= : Optional specific event ID} {--all : Update both paid and pending registrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate missing amount column in tbl_registration based on belt/event fees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->option('event');
        $includeAll = $this->option('all');

        $query = Registration::where(function ($q) {
                $q->whereNull('amount')->orWhere('amount', '<=', 0);
            })
            ->with('event');

        if (!$includeAll) {
            $query->where('status', 'paid');
        }

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->get();

        if ($registrations->isEmpty()) {
            $this->info('No registrations found with missing amount.');
            return 0;
        }

        $this->info("Found {$registrations->count()} registrations to update.");
        $updated = 0;
        $totalAmountAdded = 0;

        foreach ($registrations as $reg) {
            $submittedData = is_array($reg->submitted_data) 
                ? $reg->submitted_data 
                : json_decode($reg->submitted_data ?? '[]', true);

            $beltField = collect($submittedData)->first(
                fn($item) => str_contains(strtolower($item['label'] ?? ''), 'belt')
            );
            $beltId = is_array($beltField['value'] ?? '')
                ? ($beltField['value'][0] ?? null)
                : ($beltField['value'] ?? null);

            $amount = 0;

            if ($beltId) {
                // Check event-specific belt fee first
                $eventBeltFee = DB::table('event_belt_fees')
                    ->where('event_id', $reg->event_id)
                    ->where('belt_id', $beltId)
                    ->first();
                if ($eventBeltFee && $eventBeltFee->fee > 0) {
                    $amount = (float)$eventBeltFee->fee;
                } else {
                    $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                    if ($belt && $belt->fees > 0) {
                        $amount = (float)$belt->fees;
                    }
                }
            }

            // Fallback to event fee
            if ($amount <= 0 && $reg->event) {
                $amount = (float)($reg->event->fee ?? 0) + (float)($reg->event->additional_fee ?? 0);
            }

            if ($amount > 0) {
                DB::table('tbl_registration')->where('id', $reg->id)->update(['amount' => $amount]);
                $updated++;
                $totalAmountAdded += $amount;
                $this->line("Updated [{$reg->registration_code}] (ID: {$reg->id}) with amount: ₹{$amount}");
            }
        }

        $this->info("Successfully updated {$updated} registrations. Total amount: ₹" . number_format($totalAmountAdded, 2));
        return 0;
    }
}
