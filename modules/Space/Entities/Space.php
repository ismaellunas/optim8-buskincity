<?php

namespace Modules\Space\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Space\Entities\Page;

class Space extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $fillable = [];

    protected $casts = [
        'contacts' => 'array',
    ];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\SpaceFactory::new();
    }

    public function managers()
    {
        return $this->belongsToMany(User::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->latitude = $inputs['latitude'];
        $this->longitude = $inputs['longitude'];
        $this->address = $inputs['address'];
        $this->type = $inputs['type'];
        $this->parent_id = $inputs['parent_id'];
        $this->is_page_enabled = $inputs['is_page_enabled'] ?? false;
        $this->contacts = $inputs['contacts'] ?? [];

        $this->save();
    }
}
