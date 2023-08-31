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
        
        
        $patterns_replacements = array( 
            '/=>[\r\n\s]+/'         => ' => ',            
            "/\s+bool\(true\)/"     => ' <span style="color:green">TRUE</span>,',
            "/\s+bool\(false\)/"    => ' <span style="color:red">FALSE</span>,',
            "/,([\r\n\s]+})/"       => "$1",
            "/\s+string\(\d+\)/"    => '',
            '/\[\"([a-z\-_0-9]+)\"\]/i' => '["<span style="color:#dd8800">$1</span>"]',
            '/\s\[(\d+)\]\s/'         => " <strong>[$1]</strong> ",
            '/\sarray\((\d+)\)\s/'         => " <strong>array($1)</strong> ",
            "/^\s+/"    => '',
        );
        $processed_string = preg_replace(array_keys($patterns_replacements), array_values($patterns_replacements), $out);
        

        if (!empty($processed_string)) {
            $out = $processed_string;
        }
         return '<pre class="var_dump">'.$out.'</pre>';

    }

    public static function get_html_uri_encoded($uri_string) {
         // Extrahiere den Query String aus der URL
        $query_string = parse_url($uri_string, PHP_URL_QUERY);

        // Splitten des Query Strings nach den &-Zeichen und Umwandlung in ein Array
        $params = explode('&', $query_string);
       
        $pattern = '/%[0-9A-Fa-f]{2}/';
        $out = "<code>$uri_string</code>";
        $out .= '<br>=&gt; URI Parts: <ul class="nolist">';
        $first = true;
        foreach ($params as $value) {
            if ($first) {
                $out .= '<li><span style="color:red">?</span>';
                $first = false;
            } else {
                $out .= '<li><span style="color:green">&</span>';
            }
            $rawoutstring = rawurldecode($value);
            $rawoutstring = preg_replace("/&/", '<br>&nbsp;&nbsp;<em style="color: blue;">$0</em>', $rawoutstring);
            $rawoutstring = preg_replace($pattern, '<em style="color: #ff8800;">$0</em>', $rawoutstring);
            $out .= '<code>'.$rawoutstring.'</code>';
            $out .= '</li>';
        }
        $out .= '</ul>';
      

        return $out;
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
        if (!self::isRRZEUser()) {
            return;
        }
        return '<div class="alert clearfix clear alert-info">'.$text.'</div>';

    }
    
    // prints a message on browser console
    public static function console_log(string $msg = '', float $tsStart = 0) {
        if (isset($_GET['debug'])) {
            
            if (!self::isRRZEUser()) {
                return;
            }
            
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
    

    
    private static function isFAUUser() {
         $knownhostsuser = [
            '/\.uni\-erlangen\.de/i'
        ];
        $knownhostadresses = [
            '/^131\.188\./i',
            '/^10\./i'
        ];

        $found = false;	  
        $remip = $_SERVER['REMOTE_ADDR'];

        foreach ($knownhostadresses as $regexp) {
            if (preg_match($regexp,$remip)) {
                $found = true;
                break;
            }
        }

        $remotehost = 	 $_SERVER['REMOTE_HOST']; 

        foreach ($knownhostsuser as $regexp) {
            if (preg_match($regexp,$remotehost)) {
                $found = true;
                break;
            }
        }

        return $found;
    }


    private static function isRRZEUser() {
        $knownhostsuser = [
            '/unrz59\.vpn\.rrze\.uni\-erlangen\.de/i',
            '/zo95zofo\.vpn\.rrze\.uni\-erlangen\.de/i',
            '/unrz228\.vpn\.rrze\.uni\-erlangen\.de/i',
            '/unrz244\.vpn\.rrze\.uni\-erlangen\.de/i',
            '/we53buko\.vpn\.rrze\.uni\-erlangen\.de/i'
        ];
         $knownhostadresses = [
            '/^131\.188\.73/i',
            '/^10\.188\.76/i',
            '/^10\.11\.82/i',
            '/^10\.11\.216/i',
             '/^10\.11\.83\.208/i'
        ];

        $found = false;	  
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remip = $_SERVER['REMOTE_ADDR'];

            foreach ($knownhostadresses as $regexp) {
                if (preg_match($regexp,$remip)) {
                    $found = true;
                    break;
                }
            }
        }
        if (isset($_SERVER['REMOTE_HOST'])) {
            $remotehost = 	 $_SERVER['REMOTE_HOST']; 
            foreach ($knownhostsuser as $regexp) {
                if (preg_match($regexp,$remotehost)) {
                    $found = true;
                    break;
                }
            }
        }
        return $found;
    }

}