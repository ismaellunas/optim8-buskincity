<?php

namespace App\Entities\Forms\Fields;

class Textarea extends Text
{
    protected $type = "Textarea";

    public $rows;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->rows = $data['rows'] ?? null;
    }

    public function schema(): array
    {
        $schema = parent::schema();

        $schema['rows'] = $this->rows;

        return $schema;
    }
}
