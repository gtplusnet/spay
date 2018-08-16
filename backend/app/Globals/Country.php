<?php
namespace App\Globals;
use App\Tbl_country;
use stdClass;
use Carbon\Carbon;

class Country
{
    public static function getCountry()
    {
        return Tbl_country::orderBy("country_name")->get();
    }
    public static function updateCurrencyConversion()
    {
        $_country = Tbl_country::get();

        foreach($_country as $country)
        {
            $_currency[] = $country->country_currency;
        }

        $access_key = "91bdc5f88756ab2366a9040511fad3dd";
        $currency = implode($_currency, ",");
        $source = "USD";
        $format = 1;

        $json = file_get_contents("http://apilayer.net/api/live?access_key=$access_key&currencies=$currency&source=$source&format=$format");
        $raw = json_decode($json);

        foreach($_country as $country)
        {
            $code = "USD" . $country->country_currency;
            $update_country["dollar_conversion"] = $raw->quotes->$code;
            Tbl_country::where("country_id", $country->country_id)->update($update_country);
        }
    }
}