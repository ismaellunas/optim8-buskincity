<?php

namespace Modules\Booking\Services;

use App\Enums\PublishingStatus;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ProductEvent;
use Modules\Ecommerce\Entities\Product;

class ProductEventCrudService
{
    public function getRecords(
        Product $product,
        ?string $term = null,
        int $perPage = 10
    ): AbstractPaginator {
        return ProductEvent::orderBy('started_at', 'ASC')
            ->select([
                'id',
                'title',
                'started_at',
                'ended_at',
                'status',
            ])
            ->where('product_id', $product->id)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->orderBy('started_at')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records)
    {
        $dateFormat = config('constants.format.date_time_minute');

        $records->transform(function ($event) use ($dateFormat) {
            return [
                ...$event->only([
                    'id',
                    'title',
                    'status',
                ]),
                ...[
                    'started_at' => $event->started_at->format($dateFormat),
                    'ended_at' => $event->ended_at->format($dateFormat),
                    'display_status' => $event->displayStatus,
                ]
            ];
        });
    }

    public function getEditableRecord(ProductEvent $event)
    {
        $event->load('translation');

        return [
            ...$event->only([
                'id',
                'address',
                'city',
                'country_code',
                'latitude',
                'longitude',
                'title',
                'timezone',
                'status',
            ]),
            ...[
                'ended_at' => $event->ended_at->toIso8601String(),
                'started_at' => $event->started_at->toIso8601String(),
                'translations' => $event->getTranslationsArray(),
            ]
        ];
    }

    public function createEvent(Product $product, array $inputs): ProductEvent
    {
        $event = new ProductEvent();
        $event->product_id = $product->id;
        $event->author_id = auth()->id();

        $this->updateEvent($event, $inputs);

        if (!$event->schedule) {
            Schedule::factory()->state([
                'schedulable_type' => ProductEvent::class,
                'schedulable_id' => $event->id,
                'timezone' => $event->timezone,
            ])->create();
        }

        return $event;
    }

    public function updateEvent(ProductEvent $event, array $inputs)
    {
        $event->title = Arr::get($inputs, 'title');
        $event->started_at = Arr::get($inputs, 'started_at');
        $event->ended_at = Arr::get($inputs, 'ended_at');
        $event->fill(Arr::get($inputs, 'translations', []));
        $event->timezone = Arr::get($inputs, 'timezone');
        $event->status = Arr::get($inputs, 'status', PublishingStatus::DRAFT->value);
        $event->address = Arr::get($inputs, 'address');
        $event->city = Arr::get($inputs, 'city');
        $event->country_code = Arr::get($inputs, 'country_code');
        $event->latitude = Arr::get($inputs, 'latitude');
        $event->longitude = Arr::get($inputs, 'longitude');

        $saved = $event->save();

        if ($event->schedule && $event->timezone) {
            $event->schedule->timezone = $event->timezone;
            $event->schedule->save();
        }

        return $saved;
    }
}
