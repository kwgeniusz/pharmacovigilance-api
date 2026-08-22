<?php

namespace App\Queries\Orders;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchOrders
{
    /**
     * @param  array{lot_number: string, start_date: string, end_date: string}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        return $this->builder($filters)
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array{lot_number: string, start_date: string, end_date: string}  $filters
     * @return Builder<Order>
     */
    public function builder(array $filters): Builder
    {
        $lot = $filters['lot_number'];
        $startDate = CarbonImmutable::createFromFormat('Y-m-d', $filters['start_date'])->startOfDay();
        $endDate = CarbonImmutable::createFromFormat('Y-m-d', $filters['end_date'])->endOfDay();

        return Order::query()
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->whereHas('items.medication', fn ($query) => $query->where('lot_number', $lot))
            ->with([
                'customer',
                'items' => fn ($query) => $query
                    ->whereHas('medication', fn ($medicationQuery) => $medicationQuery->where('lot_number', $lot))
                    ->with('medication'),
            ])
            ->latest('purchase_date')
            ->latest('id');
    }
}
