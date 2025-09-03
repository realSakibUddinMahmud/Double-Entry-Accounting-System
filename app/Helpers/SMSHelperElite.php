<?php            

namespace App\Helpers;

use App\Models\Setting;

class SMSHelperElite
{
    // ElitBuzz API Credentials
    const API_KEY = 'C200809567f1f08d777298.47668741';
    const SENDER_ID = '8809601012653'; // Use approved sender ID (check with ElitBuzz)
    const API_URL = 'https://msg.elitbuzz-bd.com/smsapi';
    const BALANCE_URL = 'https://msg.elitbuzz-bd.com/miscapi/' . self::API_KEY . '/getBalance';

    // Error codes mapping
    const ERROR_CODES = [
        '200'  => 'SMS has been sent successfully',
        '1002' => 'Sender Id/Masking Not Found',
        '1003' => 'API Not Found',
        '1004' => 'SPAM Detected',
        '1005' => 'Internal Error',
        '1006' => 'Internal Error',
        '1007' => 'Balance Insufficient',
        '1008' => 'Message is empty',
        '1009' => 'Message Type Not Set (text/unicode)',
        '1010' => 'Invalid User & Password',
        '1011' => 'Invalid User Id',
        '1012' => 'Invalid Number',
        '1013' => 'API limit error',
        '1014' => 'No matching template',
        '1015' => 'SMS Content Validation Fails',
        '1016' => 'IP address not allowed!!',
        '1019' => 'Sms Purpose Missing',
    ];


    public static function singleSms($number, $messageBody)
    {
        $number = self::formatNumber($number);
        $params = [
            'api_key' => self::API_KEY,
            'type' => 'text',
            'contacts' => $number,
            'senderid' => self::SENDER_ID,
            'msg' => $messageBody // Don't urlencode here - curl will do it
        ];

        return self::callApi($params);
    }

    public static function bulkSms($numbers, $messageBody)
    {
        $contacts = is_array($numbers)
            ? implode('+', array_map([self::class, 'formatNumber'], $numbers))
            : self::formatNumber($numbers);

        $params = [
            'api_key' => self::API_KEY,
            'type' => 'text',
            'contacts' => $contacts,
            'senderid' => self::SENDER_ID,
            'msg' => $messageBody
        ];

        return self::callApi($params);
    }

    public static function getBalance()
    {
        $response = file_get_contents(self::BALANCE_URL);
        return json_decode($response, true);
    }

    public static function callApi($params)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        \Log::info('SMS API Request', ['params' => $params]);
        \Log::info('SMS API Response', ['response' => $response]);

        if (curl_errno($ch)) {
            \Log::error('SMS CURL Error', [
                'error' => curl_error($ch),
                'code' => curl_errno($ch)
            ]);
            return false;
        }

        curl_close($ch);
        return $response;
    }

    private static function formatNumber($number)
    {
        // Remove all non-numeric characters
        $number = preg_replace("/[^0-9]/", "", $number);

        // Handle different number formats:
        if (strlen($number) == 10) {
            return '880' . $number; // 017... → 88017...
        } elseif (strlen($number) == 11 && $number[0] == '0') {
            return '880' . substr($number, 1); // 017... → 88017...
        } elseif (strlen($number) == 13 && substr($number, 0, 3) == '880') {
            return $number; // Already in 880 format
        }

        // Return original if doesn't match expected patterns
        return $number;
    }
}
