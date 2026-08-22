<?php

namespace App\Http\Controllers\Api;

use App\Actions\Alerts\SendMedicationRecallAlert;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendAlertRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    public function send(SendAlertRequest $request, SendMedicationRecallAlert $sendAlert): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::query()->findOrFail($validated['order_id']);

        $sendAlert->handle($order, $validated['lot_number']);

        return response()->json([
            'message' => 'Alert sent successfully.',
        ]);
    }
}
