<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchMedicationRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicationController extends Controller
{
    public function search(SearchMedicationRequest $request): AnonymousResourceCollection
    {
        $medications = Medication::query()
            ->where('lot_number', $request->validated('lot_number'))
            ->orderBy('name')
            ->get();

        return MedicationResource::collection($medications);
    }
}
