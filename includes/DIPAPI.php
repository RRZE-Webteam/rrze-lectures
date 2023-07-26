<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIPAPI
{

    protected $api;
    // protected $orgID;
    protected $atts;
    protected $lectureParam;
    protected $sem;
    protected $gast;

    // public function __construct($api, $orgID, $atts)
    public function __construct()
    {
        $this->setAPI();
    }

    private function getKey(){
        $lectureOptions = get_option('rrze-lectures');

        if (!empty($lectureOptions['basic_ApiKey'])){
            return $lectureOptions['basic_ApiKey'];
        }elseif(is_multisite()){
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->dip_apiKey)){
                return $settingsOptions->plugins->dip_apiKey;
            }
        }else{
            return '';
        }
    }

    public function getResponse(string $endpoint = 'educationEvents', string $sParam = NULL): array{
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $aGetArgs = [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->getKey(),
                ]
            ];
            
        if (isset($_GET["debug"])){
           
            $apirequest =  $this->api . $endpoint . '/' . $sParam;
            echo Debug::get_notice('<b>API-Request:</b> <pre>'.$apirequest.'</pre>');

        }
        
        $apiResponse = wp_remote_get($this->api . $endpoint . '/' . $sParam, $aGetArgs);

        if ($apiResponse['response']['code'] != 200){
            $aRet = [
                'valid' => FALSE, 
                'content' => $apiResponse['response']['message'],
                'code' => $apiResponse['response']['code'],
            ];    
        }else{
            $content = json_decode($apiResponse['body'], true);
            $aRet = [
                'valid' => TRUE, 
                'content' => $content,
                'code' => 200,
            ];
        }

        return $aRet;
    }


    private function setAPI()
    {
        $this->api = 'https://api.fau.de/pub/v2/vz/';
    }

    private static function log(string $method, string $logType = 'error', string $msg = '')
    {
        // uses plugin rrze-log
        $pre = __NAMESPACE__ . ' ' . $method . '() : ';
        if ($logType == 'DB') {
            global $wpdb;
            do_action('rrze.log.error', $pre . '$wpdb->last_result= ' . json_encode($wpdb->last_result) . '| $wpdb->last_query= ' . json_encode($wpdb->last_query . '| $wpdb->last_error= ' . json_encode($wpdb->last_error)));
        } else {
            do_action('rrze.log.' . $logType, __NAMESPACE__ . ' ' . $method . '() : ' . $msg);
        }
    }
    
}
