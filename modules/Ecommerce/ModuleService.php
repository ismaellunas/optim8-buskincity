<?php

namespace Modules\Ecommerce;

use App\Services\BaseModuleService;
use Illuminate\Support\Collection;

class ModuleService extends BaseModuleService
{
    public static function permissions(): Collection
    {
        return collect(config('ecommerce.permissions'));
    }

    public static function maxProductMediaNumber(): int
    {
        return 10;
    }

    public static function maxProductFileSize(): int
    {
        return 5 * config('constants.one_megabyte');
    }

    public static function tablePrefix()
    {
        return config('lunar.database.table_prefix');
    }
}
