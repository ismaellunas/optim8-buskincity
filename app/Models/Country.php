<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        "dial",
        "alpha3",
        "alpha2",
        "currency_name",
        "currency_alphabetic_code",
        "currency_country_name",
        "continent",
        "tld",
        "languages",
        "display_name",
    ];
}
