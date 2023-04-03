<?php

namespace Modules\Ecommerce\Entities;

use App\Models\User;
use Lunar\Models\Order as LunarOrder;
use Illuminate\Database\Eloquent\Builder;
use Modules\Booking\Entities\Event as BookingEvent;
use Modules\Booking\Entities\OrderCheckIn;
use Modules\Ecommerce\Database\factories\OrderFactory;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\ModuleService;

class Order extends LunarOrder
{
    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\OrderFactory
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function firstEventLine()
    {
        return $this
            ->hasOne(OrderLine::class)
            ->where('type', OrderLineType::EVENT->value);
    }

    public function user()
    {
        $relation = parent::user();

        return $relation->withTrashed();
    }

    public function getUserFullNameAttribute(): ?string
    {
        return $this->user->fullName ?? null;
    }

    public function getFormattedPlacedAtAttribute(): ?string
    {
        return $this->placed_at
            ? $this->placed_at->format(config('constants.format.date_time'))
            : null;
    }

    public function getEmailRecipientAttribute(): object
    {
        return (object) [
            'name' => $this->user->fullName,
            'email' => $this->user->email,
        ];
    }

    public function getFirstProductAttribute(): ?Product
    {
        return $this->firstEventLine->purchasable->product ?? null;
    }

    public function isPlacedByUser(User $user): bool
    {
        return $this->user_id == $user->id;
    }

    public function scopeOrderByColumn($query, array $orderConfig)
    {
        $column = $orderConfig['column'] ?? null;
        $order = $orderConfig['order'] ?? 'asc';

        switch ($column) {
            case 'status':
                return $query->orderByStatus($order);
                break;

            case 'name':
                return $query->orderByName($order);
                break;

            case 'date':
                return $query->orderByDate($order);
                break;

            case 'time':
                return $query->orderByTime($order);
                break;

            case 'checkin':
                return $query->orderByCheckIn($order);
                break;

            case 'location':
                return $query->orderByLocation($order);
                break;

            default:
                return $query->orderBy('reference', 'DESC');
                break;
        }
    }

    public function scopeOrderByStatus($query, string $order)
    {
        return $query->orderBy(
            $this->getBuilderJoinEventToOrder('status')
        , $order);
    }

    public function scopeOrderByName($query, string $order)
    {
        $locale = config('app.locale');
        $tablePrefix = ModuleService::tablePrefix();
        $moduleName = ModuleService::getName();

        return $query->orderBy(
            Product::select("{$tablePrefix}products.attribute_data->name->value->{$locale}")
                ->join("{$tablePrefix}product_variants", "{$tablePrefix}product_variants.product_id", "=", "{$tablePrefix}products.id")
                ->join("{$tablePrefix}order_lines", function ($join) use ($tablePrefix, $moduleName) {
                    $purchasableType = "Modules\\{$moduleName}\\Entities\\ProductVariant";

                    $join->on("{$tablePrefix}order_lines.purchasable_id", "=", "{$tablePrefix}product_variants.product_id")
                        ->where("purchasable_type", $purchasableType);
                })
                ->whereColumn("{$tablePrefix}order_lines.order_id", "{$tablePrefix}orders.id")
        , $order);
    }

    public function scopeOrderByDate($query, string $order)
    {
        return $query->orderBy(
            $this->getBuilderJoinEventToOrder("to_char(events.booked_at, 'YYMMDD')")
        , $order);
    }

    public function scopeOrderByTime($query, string $order)
    {
        return $query->orderBy(
            $this->getBuilderJoinEventToOrder("to_char(events.booked_at, 'HHMI')")
        , $order);
    }

    public function scopeOrderByLocation($query, string $order)
    {
        $tablePrefix = ModuleService::tablePrefix();
        $moduleName = ModuleService::getName();

        return $query->orderBy(
            Product::selectRaw("concat(prod_meta.value::json->0->>'city', ', ', prod_meta.value::json->0->>'country_code')")
                ->join("{$tablePrefix}products_meta as prod_meta", function ($join) use ($tablePrefix) {
                    $join
                        ->on("prod_meta.product_id", "=", "{$tablePrefix}products.id")
                        ->where('prod_meta.key', '=', 'locations');
                })
                ->join("{$tablePrefix}product_variants", "{$tablePrefix}product_variants.product_id", "=", "{$tablePrefix}products.id")
                ->join("{$tablePrefix}order_lines", function ($join) use ($tablePrefix, $moduleName) {
                    $purchasableType = "Modules\\{$moduleName}\\Entities\\ProductVariant";

                    $join->on("{$tablePrefix}order_lines.purchasable_id", "=", "{$tablePrefix}product_variants.product_id")
                        ->where("purchasable_type", $purchasableType);
                })
                ->whereColumn("{$tablePrefix}order_lines.order_id", "{$tablePrefix}orders.id")
        , $order);
    }

    public function scopeOrderByCheckIn($query, string $order)
    {
        $tablePrefix = ModuleService::tablePrefix();

        return $query->orderBy(
            OrderCheckIn::selectRaw("to_char(order_check_ins.checked_in_at, 'HHMI')")
                ->whereColumn('order_check_ins.order_id', "{$tablePrefix}orders.id")
        , $order);
    }

    private function getBuilderJoinEventToOrder(string $selectRaw): Builder
    {
        $tablePrefix = ModuleService::tablePrefix();

        return BookingEvent::selectRaw($selectRaw)
            ->join("{$tablePrefix}order_lines", "{$tablePrefix}order_lines.id", "=", "events.id")
            ->whereColumn("{$tablePrefix}order_lines.order_id", "{$tablePrefix}orders.id");
    }

    public function scopeProductManager($query, int $userId)
    {
        return $query->whereHas(
            'firstEventLine.purchasable.product.managers',
            function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        );
    }
}
