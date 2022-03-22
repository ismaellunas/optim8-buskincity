<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    protected $dataTypes = [
        'boolean',
        'integer',
        'double',
        'float',
        'string',
        'NULL'
    ];

    public function scopeKeyAndValue($query, string $key, string $value)
    {
        return $query->where('key', $key)->where('value', $value);
    }

    public function setValueAttribute($value)
    {
        $type = gettype($value);

        if (is_array($value)) {
            $this->type = 'array';
            $this->attributes['value'] = json_encode($value);
        } elseif ($value instanceof DateTime) {
            $this->type = 'datetime';
            $this->attributes['value'] = $this->fromDateTime($value);
        } elseif ($value instanceof Model) {
            $this->type = 'model';
            $this->attributes['value'] = get_class($value).(!$value->exists ? '' : '#'.$value->getKey());
        } elseif (is_object($value)) {
            $this->type = 'object';
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->type = in_array($type, $this->dataTypes) ? $type : 'string';
            $this->attributes['value'] = $value;
        }
    }

    public function getValueAttribute($value)
    {
        $type = $this->type ?: 'null';

        switch ($type) {
            case 'array':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            case 'datetime':
                return $this->asDateTime($value);
            case 'model': {
                if (strpos($value, '#') === false) {
                    return new $value();
                }

                list($class, $id) = explode('#', $value);

                return with(new $class())->findOrFail($id);
            }
        }

        if (in_array($type, $this->dataTypes)) {
            settype($value, $type);
        }

        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
