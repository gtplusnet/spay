<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage ;
use Illuminate\Http\Request;

use App\Tbl_country_codes;

class MainController extends Controller
{
    public function index()
    {
    	header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    }

    public function seed_cc()
    {

    	$string = public_path().'/assets/phone.json';


         
    	$string = json_decode(file_get_contents($string));
        dd(count($string), $string);
        if(!Tbl_country_codes::first())
        {
            foreach ($string as $key => $value) 
            {
                $insert["country_code_abbr"] = $value->code;
                $insert["country_code"] = $value->number;
                $insert["country"] = $value->country;
                Tbl_country_codes::insert($insert);
            }
        }
    }
}
