<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

class Translator extends \RecursiveIteratorIterator
{
    protected $display_language;
    protected $display_language_fallback;
    protected $all_language_codes = [];
    // protected $mirrorData;
    protected $aPath = '';

    // protected $varFunc;


    public function __construct($attTeachingLanguage) // , $aData

    {
        // $this->mirrorData = $aData;

        $aLang = explode(':', $attTeachingLanguage);
        $this->display_language = $aLang[0];
        if (count($aLang) > 1) {
            $this->display_language_fallback = $aLang[1];
        }

        $this->all_language_codes = array_map(function ($val) {
            return substr($val, 0, 2);
        }, \ResourceBundle::getLocales(''));

    }

    public function current()
    {
        $current = parent::current();
        switch($this->key()) {
            case 'de':
                $current = strtolower($current) . ' JUHU';
                break;
            default:
                break;
        }
        return $current;
    }


    private function getTranslation(&$aIn)
    {
        if (!is_array($aIn)) {
            // DIP-Field is string (and not array with language codes) = DIP-field is not a multilang field (["en" => "english text", "de" => "deutscher Text"])
            return $aIn;
        }

        if (!empty($aIn[$this->display_language])) {
            return $aIn[$this->display_language];
        } elseif (!empty($this->display_language_fallback) && !empty($aIn[$this->display_language_fallback])) {
            return $aIn[$this->display_language_fallback];
        } else {
            return '';
        }
    }


    private function maybeSetTranslation(&$item, $key)
    {
        $this->aPath .= $key . '/';
        // if (in_array($key, $this->all_language_codes)) {
        //     $item = 'PHP Rocks'; // Do This!
        //     var_dump($this->aPath);
        //     exit;
        //     // prev($this->mirrorData); 

        // }
        var_dump($this->aPath);

    }


    public function setTranslations(&$aData)
    {

        // echo '<pre>';
        // var_dump($aData);
        // $test = json_decode(json_encode($aData));


        $iterator = new Translator(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($aData), \RecursiveIteratorIterator::SELF_FIRST));
        foreach ($iterator as $k => $v) {

            // // if (is_array($k)){
            // //     echo 'key ist ein array';
            // //     exit;
            // // }
            // // if (is_array($v)){
            // //     echo 'value ist ein array bei key=' . $k;
            // //     exit;
            // // }
            // // echo "key = " . $k . ' ';
            // if (is_array($v)) {
            //     // echo '<pre>';
            //     // var_dump($v);

            //     if (!empty(array_intersect(array_keys($v), $this->all_language_codes))){
            //         // echo 'found';
            //         // exit;

            //         // $k = $this->getTranslation($v);
            //         // echo '<pre>';
            //         // var_dump($k);
            //         // exit;

            //         // $iterator->getInnerIterator()->offsetSet($k, 0);
            //         $iterator[$k] = 'JUHU';

            //         // exit;

            //     }
            // }

            // echo '<hr>';
        }

        echo '<pre>test ist ';
        var_dump($aData);
        // var_dump($iterator->getArrayCopy());
        // var_dump($iterator);


        echo 'setTranslations()<br>';

        // var_dump($aData);
        exit;

        // array_walk_recursive($aData, [$this, 'maybeSetTranslation']);

        // var_dump($aData);
        // exit;



    }
}