<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Queries\Orders\SearchOrders;

class OrderController extends Controller
{
    public function index(SearchOrderRequest $request, SearchOrders $searchOrders): OrderCollection
    {
        return new OrderCollection($searchOrders->execute($request->validated()));
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items.medication']);

        return new OrderResource($order);
    }
}
