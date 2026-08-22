<x-mail::message>
# Important Medication Recall Notice

Hello {{ $customer->name }},

Our records show that order **#{{ $order->id }}**, purchased on **{{ $order->purchase_date->toDateString() }}**, contains medication from lot **{{ $lot }}**. This lot is subject to an important recall notice.

## Affected medication

@foreach ($medications as $medication)
- {{ $medication->name }} — lot {{ $medication->lot_number }}
@endforeach

## Recommended action

Please stop using the affected medication and contact the pharmacy or your healthcare provider as soon as possible for further instructions. Do not discard the medication unless a pharmacy representative instructs you to do so.

If you have already experienced an unexpected reaction, seek appropriate medical care immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
