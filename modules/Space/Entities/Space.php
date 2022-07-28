<?php

namespace Modules\Space\Entities;

use App\Models\GlobalOption;
use App\Models\User;
use App\Models\Media;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Space\Entities\Page;

class Space extends Model implements TranslatableContract
{
    use HasFactory;
    use NodeTrait;
    use Translatable;

    public $translatedAttributes = [
        'condition',
        'description',
        'excerpt',
        'surface',
    ];

    protected $fillable = [
        'address',
        'contacts',
        'is_page_enabled',
        'latitude',
        'longitude',
        'name',
        'parent_id',
        'type_id',
    ];

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

    public function Type()
    {
        return $this->belongsTo(GlobalOption::class, 'type_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function logo()
    {
        return $this->hasOne(Media::class, 'id', 'logo_media_id');
    }

    public function cover()
    {
        return $this->hasOne(Media::class, 'id', 'cover_media_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? $this->logo->file_url : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? $this->cover->file_url : null;
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->latitude = $inputs['latitude'];
        $this->longitude = $inputs['longitude'];
        $this->address = $inputs['address'];
        $this->type_id = $inputs['type_id'];
        $this->parent_id = $inputs['parent_id'];
        $this->is_page_enabled = $inputs['is_page_enabled'] ?? false;
        $this->contacts = $inputs['contacts'] ?? [];

        if (!empty($inputs['translations'])) {
            $this->fill($inputs['translations']);
        }

        $this->save();
    }
}
