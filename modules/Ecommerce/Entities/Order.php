<?php

namespace Modules\Ecommerce\Entities;

use App\Models\User;
use GetCandy\Models\Order as GetCandyOrder;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Database\factories\OrderFactory;

class Order extends GetCandyOrder
{
    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\OrderFactory
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
}
