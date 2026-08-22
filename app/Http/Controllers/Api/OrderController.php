<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Queries\Orders\SearchOrders;

class OrderController extends Controller
{
    public function index(SearchOrderRequest $request, SearchOrders $searchOrders): OrderCollection
    {
        return new OrderCollection($searchOrders->execute($request->validated()));
    }
}
