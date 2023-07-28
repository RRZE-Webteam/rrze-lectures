<?php

/**
 * Debug 
 * 
 * Created on : 24.07.2023, 17:06:22
 */

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

class Debug {
   
   // Gets an Variable, that can be an string, array or object and modifies its output in a more readable form 
   public static function get_html_var_dump($input) {
        $out = Debug::get_var_dump($input);

        $out = preg_replace("/=>[\r\n\s]+/", ' => ', $out);
        $out = preg_replace("/\s+bool\(true\)/", ' <span style="color:green">TRUE</span>,', $out);
        $out = preg_replace("/\s+bool\(false\)/", ' <span style="color:red">FALSE</span>,', $out);
        $out = preg_replace("/,([\r\n\s]+})/", "$1", $out);
        $out = preg_replace("/\s+string\(\d+\)/", '', $out);
        $out = preg_replace("/\[\"([a-z\-_0-9]+)\"\]/i", "[\"<span style=\"color:#dd8800\">$1</span>\"]", $out);

        return '<pre>'.$out.'</pre>';
    }

    // prrints var dump as variable
    public static function get_var_dump($input) {
        ob_start(); 
        var_dump($input);
        return "\n" . ob_get_clean();
    }
    
    // prints a variable as notice string
    public static function get_notice($text) {
        if (!isset($_GET['debug'])) {
            return;
        }
        return '<div class="alert clearfix clear alert-info">'.$text.'</div>';

    }
    
    // prints a message on browser console
    public static function console_log(string $msg = '', float $tsStart = 0) {
        if (isset($_GET['debug'])) {
            $msg .= ' execTime: ' . sprintf('%.2f', microtime(true) - $tsStart) . ' s';
            echo '<script>console.log(' . json_encode($msg, JSON_HEX_TAG) . ');</script>';
        }
    }
    
    // Log to RRZE Error log
    public static function log(string $method, string $logType = 'error', string $msg = '') {
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