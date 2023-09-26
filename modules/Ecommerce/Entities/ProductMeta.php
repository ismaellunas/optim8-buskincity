<?php

namespace Modules\Ecommerce\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ecommerce\ModuleService;

class ProductMeta extends BaseModel
{
    use HasFactory;

    protected $table = 'products_meta';
    protected $fillable = ['key', 'value', 'product_id'];

    public function getTable()
    {
        return ModuleService::tablePrefix() . parent::getTable();
    }
}
