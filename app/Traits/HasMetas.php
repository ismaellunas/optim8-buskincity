<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait HasMetas
{
    protected $metaRelation = 'metas';

    protected $metaBuffers = [];

    public function getMetas($keys): Collection
    {
        $metas = $this->{$this->metaRelation};

        $collection = new Collection();

        foreach ($metas as $meta) {
            if (in_array($meta->key, $keys)) {
                $collection->put($meta->key, $meta->value);
            }
        }

        return $collection;
    }

    public function setMeta($key, $value)
    {
        $this->metaBuffers[$key] = $value;
    }

    public function saveMetas(): Collection
    {
        $metas = collect();
        $metaModelClass = get_class($this->{$this->metaRelation}()->getRelated());

        foreach ($this->metaBuffers as $key => $value) {
            $meta = $metaModelClass::where('key', $key)
                ->where($this->getForeignKey(), $this->{$this->getKeyName()})
                ->first();

            if (! $meta) {
                $meta = new $metaModelClass();
                $meta->key = $key;
                $meta->{$this->getForeignKey()} = $this->{$this->getKeyName()};
            }

            $meta->value = $value;
            $meta->save();

            $metas->push($meta);
        }

        return $metas;
    }
}
