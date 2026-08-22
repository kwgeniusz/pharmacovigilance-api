<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportOrdersRequest;
use App\Models\Order;
use App\Queries\Orders\SearchOrders;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderExportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ExportOrdersRequest $request, SearchOrders $searchOrders): StreamedResponse
    {
        $filters = $request->validated();
        $lot = Str::slug($filters['lot_number']) ?: 'lot';

        return response()->streamDownload(
            function () use ($filters, $searchOrders): void {
                $stream = fopen('php://output', 'w');

                if ($stream === false) {
                    return;
                }

                fputcsv($stream, [
                    'Order ID',
                    'Customer',
                    'Email',
                    'Phone',
                    'Purchase Date',
                    'Medication',
                    'Lot Number',
                ], ',', '"', '');

                $searchOrders->builder($filters)
                    ->lazy(200)
                    ->each(function (Order $order) use ($stream): void {
                        $medications = $order->items
                            ->pluck('medication.name')
                            ->implode('; ');

                        $cells = [
                            (string) $order->id,
                            $order->customer->name,
                            $order->customer->email,
                            $order->customer->phone,
                            $order->purchase_date->toDateString(),
                            $medications,
                            $order->items->first()?->medication->lot_number ?? '',
                        ];

                        fputcsv(
                            $stream,
                            array_map(fn (string $value): string => $this->safeCell($value), $cells),
                            ',',
                            '"',
                            '',
                        );
                    });

                fclose($stream);
            },
            "affected-orders-{$lot}.csv",
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    private function safeCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
