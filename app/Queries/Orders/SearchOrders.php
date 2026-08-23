<?php

namespace App\Queries\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchOrders
{
    /**
     * @param  array{lot_number?: string|null, start_date?: string|null, end_date?: string|null}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        return $this->builder($filters)
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array{lot_number?: string|null, start_date?: string|null, end_date?: string|null}  $filters
     * @return Builder<Order>
     */
    public function builder(array $filters): Builder
    {
        $lot = $filters['lot_number'] ?? null;

        $query = Order::query()->with('customer');

        if ($lot) {
            $query
                ->whereHas('items.medication', fn ($medicationQuery) => $medicationQuery->where('lot_number', $lot))
                ->with([
                    'items' => fn ($itemQuery) => $itemQuery
                        ->whereHas('medication', fn ($medicationQuery) => $medicationQuery->where('lot_number', $lot))
                        ->with('medication'),
                ]);
        } else {
            $query->with('items.medication');
        }

        return $query
            ->when(
                $filters['start_date'] ?? null,
                fn (Builder $query, string $startDate) => $query->whereDate('purchase_date', '>=', $startDate),
            )
            ->when(
                $filters['end_date'] ?? null,
                fn (Builder $query, string $endDate) => $query->whereDate('purchase_date', '<=', $endDate),
            )
            ->latest('purchase_date')
            ->latest('id');
    }
}
