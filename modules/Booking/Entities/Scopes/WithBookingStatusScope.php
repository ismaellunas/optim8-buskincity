<?php

namespace Modules\Booking\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Booking\Entities\Event;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\OrderLine;

class WithBookingStatusScope implements Scope
{
    private $orderTable;
    private $orderLineTable;
    private $eventTable;

    public function __construct(
        public string $columnName = 'booking_status',
        public bool $isSelectAll = false
    ) {
        $this->orderTable = (new Order())->getTable();
        $this->orderLineTable = (new OrderLine())->getTable();
        $this->eventTable = Event::getTableName();
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->addSubSelect($this->columnName, function ($builder) {
            return $builder
                ->select($this->eventTable.'.status')
                ->from($this->eventTable)
                ->whereExists(function ($builder) {
                    $builder->selectRaw('1')
                        ->from($this->orderLineTable)
                        ->whereRaw("$this->orderLineTable.id = $this->eventTable.order_line_id")
                        ->whereRaw("{$this->orderLineTable}.order_id = {$this->orderTable}.id")
                        ->latest($this->orderLineTable.'.id');
                })
                ->limit(1);
        }, $this->isSelectAll);
    }
}
