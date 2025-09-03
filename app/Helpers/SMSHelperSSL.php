<?php             /// NEEDS TO  BE INTEGRATED WITH THIS PROJECT

namespace App\Helpers;

use App\Models\Setting;

class SMSHelperSSL
{
    // const API_TOKEN = "mznqp44c-hooran18-ycrpguea-ki1avmy2-1hxnqgg9"; 
    const API_TOKEN = "hf5ldhb6-lqylljfg-gopriech-r1ooxglz-su0oqrxn"; //put ssl provided api_token here
    // const SID = "HILINKZNONOTP"; 
    const SID = "IMPULSENONOTP"; // put ssl provided sid here
    const DOMAIN = "https://smsplus.sslwireless.com"; //api domain
    const ERROR_CODE = ['200' => 'SMS has been sent successfully', '4025' => 'Invalid Phone Number',];
    const STATUS_TYPE = ['SUCCESS', 'FAILED'];


    public static function singleSms($number, $messageBody)
    {
        $number = self::trim_phone_number($number);
        $params = [
            "api_token" => self::API_TOKEN,
            "sid" => self::SID,
            "msisdn" => $number,
            "sms" => $messageBody,
            "csms_id" => self::generate_csms_id()
        ];
        $url = trim(self::DOMAIN, '/') . "/api/v3/send-sms";

        $response = self::callApi($url, $params);
        return $response;
    }


    public static function bulkSms($numbers, $messageBody)
    {
        $numbers = self::trim_phone_number($numbers);
        $params = [
            "api_token" => self::API_TOKEN,
            "sid" => self::SID,
            "msisdn" => $numbers,
            "sms" => $messageBody,
            "batch_csms_id" => self::generate_csms_id()
        ];
        $url = trim(self::DOMAIN, '/') . "/api/v3/send-sms/bulk";


        $response = self::callApi($url, $params);
        return $response;
    }




    // public static function beautify_response($response)
    // {
    //     $response = json_decode($response);

    //     if(!array_key_exists($response->status_code,self::ERROR_CODE))
    //     {
    //         $formatted_response = [
    //             'status' => self::STATUS_TYPE[1],
    //             'status_message' => 'Cannot send sms'
    //         ];
    //     }

    //     $formatted_response = [
    //         'status' => $response->status,
    //         'status_message' => self::ERROR_CODE[$response->status_code]
    //     ];

    //     return $formatted_response;
    // }

    private static function generate_csms_id()
    {
        return rand();
    }

    public static function callApi($url, $params)
    {
        $params = json_encode($params);

        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params),
            'accept:application/json'
        ));

        $response = curl_exec($ch);

        // Log the full request/response (check storage/logs/laravel.log)
        \Log::info('SMS API Request:', ['url' => $url, 'params' => $params]);
        \Log::info('SMS API Response:', ['response' => $response]);

        curl_close($ch);
        return $response;
        // return self::beautify_response($response);
    }


    public static function trim_phone_number($numbers)
    {
        if (!is_array($numbers)) {
            return preg_replace("/[^0-9]/", "", $numbers);
        } else {
            $filtered_number = [];
            foreach ($numbers as $number) {
                $only_numeric = preg_replace("/[^0-9]/", "", $number);
                array_push($filtered_number, $only_numeric);
            }
            return $filtered_number;
        }
    }
}
