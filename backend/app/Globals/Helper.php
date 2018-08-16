<?php
namespace App\Globals;
use App\Tbl_country;
use App\Tbl_faqs;
use stdClass;
use Carbon\Carbon;

class Helper
{
    public static function isTest()
    {
        $domain = $_SERVER['SERVER_NAME'];
        $domain = explode(".", $domain);
        $count = count($domain) - 1;
        $domain = $domain[$count];

        $ip = $_SERVER['REMOTE_ADDR'];

        if($domain == "test" || $ip == "127.0.0.1")
        {

            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getFaqs($search = null)
    {
        $data["all"]             = Tbl_faqs::where("faq_question", "like", "%".$search."%")->get();
        $data["withdraw"]        = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Withdraw")->get();
        $data["buy_coin"]        = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Buy Coin")->get();
        $data["promotion"]       = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Promotion")->get();
        $data["purchase_bonus"]  = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Purchase Bonus")->get();
        $data["affiliate_bonus"] = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Affiliate Bonus")->get();
        $data["others"]          = Tbl_faqs::where("faq_question", "like", "%".$search."%")->where("faq_category", "Others")->get();

        return $data;

    }
}