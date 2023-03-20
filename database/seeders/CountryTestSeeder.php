<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // @see https://github.com/datasets/country-codes/blob/master/data/country-codes.csv
        // @see https://tableconvert.com/
        //["Dial","ISO3166-1-Alpha-3","ISO3166-1-Alpha-2","ISO4217-currency_name","ISO4217-currency_alphabetic_code","ISO4217-currency_country_name","Continent","TLD","Languages","CLDR display name"]

        $countries = [
            [49,"DEU","DE","Euro","EUR","GERMANY","EU",".de","de","Germany"],
            [62,"IDN","ID","Rupiah","IDR","INDONESIA","AS",".id","id,en,nl,jv","Indonesia"],
            [31,"NLD","NL","Euro","EUR","NETHERLANDS","EU",".nl","nl-NL,fy-NL","Netherlands"],
            [351,"PRT","PT","Euro","EUR","PORTUGAL","EU",".pt","pt-PT,mwl","Portugal"],
            [65,"SGP","SG","Singapore Dollar","SGD","SINGAPORE","AS",".sg","cmn,en-SG,ms-SG,ta-SG,zh-SG","Singapore"],
            [46,"SWE","SE","Swedish Krona","SEK","SWEDEN","EU",".se","sv-SE,se,sma,fi-SE","Sweden"],
            [44,"GBR","GB","Pound Sterling","GBP","UNITED KINGDOM OF GREAT BRITAIN AND NORTHERN IRELAND","EU",".uk","en-GB,cy-GB,gd","UK"],
            [1,"USA","US","US Dollar","USD","UNITED STATES OF AMERICA","NA",".us","en-US,es-US,haw,fr","US"],
        ];

        foreach ($countries as $country) {
            $model = new Country();

            $dials = array_filter(explode(',', $country[0]));

            $dial = null;
            if ($dials) {
                $dial = explode('-', $dials[0])[0];
            }

            $model->dial = $dial;
            $model->dials = json_encode(explode(',', $country[0]));
            $model->alpha3 = $country[1];
            $model->alpha2 = $country[2];
            $model->currency_name = $country[3];
            $model->currency_alphabetic_code = $country[4];
            $model->currency_country_name = $country[5];
            $model->continent = $country[6];
            $model->tld = $country[7];
            $model->languages = json_encode(explode(',', $country[8]));
            $model->display_name = $country[9];
            $model->save();
        }
    }
}
