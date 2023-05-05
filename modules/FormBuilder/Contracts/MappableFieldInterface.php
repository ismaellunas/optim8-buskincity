<?php

namespace Modules\FormBuilder\Contracts;

interface MappableFieldInterface
{
    public static function mappingFieldTypes(): array;

    public function getMappedValue(string $toFieldType): mixed;
}
