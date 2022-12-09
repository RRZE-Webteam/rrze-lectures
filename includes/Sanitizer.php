<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

class Sanitizer
{
    public function __construct()
    {
    }

    public function sanitizeArray($aIn){

        // map: field -> sanitizer => am besten wie rrze-contact in den settings

    }

    public function sanitizeField($value, $type = 'string'){
        switch($type){
            case 'time':
                return date("H:i:s", strtotime($value));
                break;
            default:
                return sanitize_text_field($value);
                break;
        }
    }

}