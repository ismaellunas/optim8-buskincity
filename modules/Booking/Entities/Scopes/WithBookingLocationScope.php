<?php

namespace Modules\Booking\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\OrderLine;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\OrderLineType;

class WithBookingLocationScope implements Scope
{
    private $orderTable;
    private $orderLineTable;
    private $productMetaTable;
    private $productVariantTable;

    public function __construct(
        public string $columnName = 'location',
        public bool $isSelectAll = false,
    ) {
        $this->orderTable = (new Order())->getTable();
        $this->orderLineTable = (new OrderLine())->getTable();
        $this->productMetaTable = (new Product())->getTable().'_meta';
        $this->productVariantTable = (new ProductVariant())->getTable();
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
                ->select(DB::raw("value::json->0"))
                ->from($this->productMetaTable)
                ->join($this->productVariantTable,
                    $this->productMetaTable.'.product_id', '=', $this->productVariantTable.'.product_id'
                )
                ->join($this->orderLineTable, function ($join) {
                    $join->on($this->productVariantTable.'.id', '=', $this->orderLineTable.'.purchasable_id');
                })
                ->where('key', 'locations')
                ->whereRaw("{$this->orderTable}.id = {$this->orderLineTable}.order_id")
                ->where($this->orderLineTable.'.purchasable_type', '=', ProductVariant::class)
                ->where($this->orderLineTable.'.type', '=', OrderLineType::EVENT->value)
                ->limit(1);
        }, $this->isSelectAll);
    }
}
