<?php

namespace Modules\Space\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\SpaceFactory::new();
    }

    public function parent()
    {
        return $this->belongsToOne(static::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function updateDepth($newDepth)
    {
        $this->depth = $newDepth;
        $this->save();

        $this->updateChildrenDepth();
    }

    private function updateChildrenDepth()
    {
        $childDepth = $this->depth + 1;

        foreach ($this->children as $child) {
            $child->updateDepth($childDepth);
        }
    }
}
