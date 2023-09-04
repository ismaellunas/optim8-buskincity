<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Ecommerce\ModuleService;

class ProductMeta extends Model
{
    use HasFactory;

    protected $table = 'products_meta';
    protected $fillable = ['key', 'value', 'product_id'];

    public function getTable()
    {
        return ModuleService::tablePrefix() . parent::getTable();
    }
}
