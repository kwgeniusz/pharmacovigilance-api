<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function show(Customer $customer): CustomerResource
    {
        $customer->load([
            'orders' => fn ($query) => $query->latest('purchase_date'),
            'orders.items.medication',
        ]);

        return new CustomerResource($customer);
    }
}
