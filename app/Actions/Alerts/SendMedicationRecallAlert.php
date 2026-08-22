<?php

namespace App\Actions\Alerts;

use App\Mail\MedicationRecallAlert;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SendMedicationRecallAlert
{
    public function handle(Order $order, string $lot): void
    {
        $order->loadMissing(['customer', 'items.medication']);

        $medications = $order->items
            ->pluck('medication')
            ->where('lot_number', $lot)
            ->values();

        if ($medications->isEmpty()) {
            throw ValidationException::withMessages([
                'lot_number' => ['The order does not contain a medication from the specified lot.'],
            ]);
        }

        Mail::to($order->customer->email)->send(
            new MedicationRecallAlert($order, $order->customer, $medications, $lot),
        );
    }
}
