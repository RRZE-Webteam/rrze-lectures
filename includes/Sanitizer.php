<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

class Sanitizer
{
    protected $aMap;


    public function __construct()
    {
        $this->aMap = getSanitizerMap();
    }

    public function sanitizeArray($aIn){
        foreach($aIn as $field => $value){
            if (is_array($value)){
                $aIn[$field] = $this->sanitizeArray($value);
            }else{
                $key = $field;
            }

            if (!empty($this->aMap[$field])){
                $aIn[$field] = $this->sanitizeField($aIn[$field], $this->aMap[$field]);
            }
        }

        return $aIn;
    }

    public function sanitizeField($value, $type = 'string'){
        switch($type){
            case 'date':
                return date("d.m.Y", strtotime($value));
                break;
            case 'time':
                return date("H:i", strtotime($value));
                break;
            default:
                return sanitize_text_field($value);
                break;
        }
    }

}