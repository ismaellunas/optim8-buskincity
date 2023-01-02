<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'file',
        'line',
        'message',
        'trace',
        'total_hit',
    ];

    public function syncErrorLog(array $inputs): void
    {
        $errorLog = $this->getExistsErrorLog($inputs);

        if ($errorLog) {
            $errorLog->total_hit += 1;
            $errorLog->save();
        } else {
            $this->saveFromInputs($inputs);
        }
    }

    private function getExistsErrorLog(array $inputs)
    {
        return self::where('url', $inputs['url'])
            ->where('file', $inputs['file'] ?? null)
            ->where('line', $inputs['line'] ?? null)
            ->where('message', $inputs['message'] ?? null)
            ->where('created_at', 'ILIKE', now()->format('Y-m-d').'%')
            ->first();
    }

    public function saveFromInputs(array $inputs): void
    {
        $this->url = $inputs['url'];

        if (isset($inputs['file'])) {
            $this->file = $inputs['file'];
        }

        if (isset($inputs['line'])) {
            $this->line = $inputs['line'];
        }

        if (isset($inputs['message'])) {
            $this->message = $inputs['message'];
        }

        if (isset($inputs['trace'])) {
            $this->trace = $inputs['trace'];
        }

        $this->save();
    }

    // Scope
    public function scopeDateRange($query, array $dates)
    {
        $dates = array_filter($dates);

        sort($dates);

        if (count($dates) == 1) {
            return $query->whereDate('created_at', $dates[0]);
        }

        return $query
            ->whereDate('created_at', '>=', $dates[0])
            ->whereDate('created_at', '<=', $dates[1]);
    }
}
