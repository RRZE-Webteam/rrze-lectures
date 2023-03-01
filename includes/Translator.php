<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

// class Translator extends \RecursiveIteratorIterator
class Translator
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

        // set $this->all_language_codes to 2-letters only (example: ['de', 'en', 'fr'])
        $this->all_language_codes = array_map(function ($val) {
            return substr($val, 0, 2);
        }, \ResourceBundle::getLocales(''));

    }

    // public function current()
    // {
    //     $current = parent::current();
    //     switch($this->key()) {
    //         case 'de':
    //             $current = strtolower($current) . ' JUHU';
    //             break;
    //         default:
    //             break;
    //     }
    //     return $current;
    // }


    
    /* returns translations by language (given attribute and/or settings value) or '' */
    private function getTranslation(&$aIn)
    {
        if (!is_array($aIn)) {
            // DIP-Field is not a mulitlang-field (== string (and not array with language codes) (["en" => "english text", "de" => "deutscher Text"])
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


    // private function maybeSetTranslation(&$item, $key)
    // {
    //     $this->aPath .= $key . '/';
    //     // if (in_array($key, $this->all_language_codes)) {
    //     //     $item = 'PHP Rocks'; // Do This!
    //     //     var_dump($this->aPath);
    //     //     exit;
    //     //     // prev($this->mirrorData); 

    //     // }
    //     var_dump($this->aPath);

    // }


    public function setTranslations(&$aData)
    {

        // echo '<pre>';
        // var_dump($aData);

        foreach ($aData as $nr => $aLecture) {
            foreach ($aLecture['main'] as $key => $field) {
                if (in_array($key, $this->all_language_codes)) {
                    $aData[$nr]['main'][$field] = $this->getTranslation($aData[$nr]['main'][$field][$key]);
                    echo '<pre>';
                    echo 'translated! ';
                    var_dump($aData);
                    exit;
                }
            }
        }


            // // echo '<pre>';
            // // var_dump($aLecture['providerValues']['courses']);
            // // exit;

            // // foreach($aLectures as $lNr => $aLecture){

            // foreach ($aLecture['providerValues']['event_orgunit'] as $eNr => $aDetails) {

            //     foreach ($this->multlangFields['event_orgunit'] as $field) {
            //         $aData[$nr]['providerValues']['event_orgunit'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
            //     }
            // }

            // // foreach($aLecture['providerValues']['event'] as $eNr => $aDetails){
            // //     foreach($this->multlangFields['event'] as $field){
            // //         $aData[$nr]['providerValues']['event'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
            // //     }
            // // }

            // foreach ($aLecture['providerValues']['courses'] as $cNr => $aCourses) {
            //     // echo '<pre>';
            //     // var_dump($aLecture['providerValues']);
            //     // exit;


            //     foreach ($this->multlangFields['courses'] as $field) {
            //         // echo '<pre>' . $field . '<br>' . 
            //         // // $this->getTranslation($aCourses[$field]) . ' +';
            //         // var_dump($aData[$nr]['providerValues']['courses'][$cNr]);
            //         // exit;
            //         $aData[$nr]['providerValues']['courses'][$cNr][$field] = $this->getTranslation($aCourses[$field]);
            //     }

            //     // if (!empty($aCourses['planned_dates'])){
            //     //     echo '<pre>';
            //     //     var_dump($aCourses['planned_dates']);
            //     //     exit;
            //     // }else{
            //     //     echo '<pre>';
            //     //     var_dump($aCourses);
            //     //     exit;

            //     // }

            //     if (!empty($aCourses['planned_dates'])) {
            //         foreach ($aCourses['planned_dates'] as $pNr => $aDetails) {
            //             foreach ($this->multlangFields['planned_dates'] as $field) {
            //                 $aData[$nr]['providerValues']['courses'][$cNr]['planned_dates'][$pNr][$field] = $this->getTranslation($aDetails[$field]);
            //             }
            //         }
            //     }
            // }

            // foreach ($aLecture['providerValues']['modules'] as $mNr => $aModule) {
            //     foreach ($this->multlangFields['modules'] as $field) {
            //         $aData[$nr]['providerValues']['modules'][$mNr][$field] = $this->getTranslation($aModule[$field]);
            //     }
            //     foreach ($aModule['modules_cos'] as $iNr => $aDetails) {
            //         foreach ($this->multlangFields['modules_cos'] as $field) {
            //             $aData[$nr]['providerValues']['modules'][$mNr]['modules_cos'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
            //         }
            //     }
            //     foreach ($aModule['stud'] as $iNr => $aDetails) {
            //         foreach ($this->multlangFields['stud'] as $field) {
            //             $aData[$nr]['providerValues']['modules'][$mNr]['stud'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
            //         }
            //     }
            //     foreach ($aModule['modules_restrictions'] as $iNr => $aDetails) {
            //         foreach ($this->multlangFields['stud'] as $field) {
            //             $aData[$nr]['providerValues']['modules'][$mNr]['modules_restrictions'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
            //         }
            //     }
            // }
            // }
        }
    }







        // $test = json_decode(json_encode($aData));
        // $iterator = new Translator(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($test), \RecursiveIteratorIterator::SELF_FIRST));


        // $iterator = new Translator(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($aData), \RecursiveIteratorIterator::SELF_FIRST));
        // foreach ($iterator as $k => $v) {

        //     // // if (is_array($k)){
        //     // //     echo 'key ist ein array';
        //     // //     exit;
        //     // // }
        //     // // if (is_array($v)){
        //     // //     echo 'value ist ein array bei key=' . $k;
        //     // //     exit;
        //     // // }
        //     // // echo "key = " . $k . ' ';
        //     // if (is_array($v)) {
        //     //     // echo '<pre>';
        //     //     // var_dump($v);

        //     //     if (!empty(array_intersect(array_keys($v), $this->all_language_codes))){
        //     //         // echo 'found';
        //     //         // exit;

        //     //         // $k = $this->getTranslation($v);
        //     //         // echo '<pre>';
        //     //         // var_dump($k);
        //     //         // exit;

        //     //         // $iterator->getInnerIterator()->offsetSet($k, 0);
        //     //         $iterator[$k] = 'JUHU';

        //     //         // exit;

        //     //     }
        //     // }

        //     // echo '<hr>';
        // }

        // echo '<pre>test ist ';
        // var_dump($aData);
        // // var_dump($iterator->getArrayCopy());
        // // var_dump($iterator);


        // echo 'setTranslations()<br>';

        // // var_dump($aData);
        // exit;

        // // array_walk_recursive($aData, [$this, 'maybeSetTranslation']);

        // // var_dump($aData);
        // // exit;



    }
}