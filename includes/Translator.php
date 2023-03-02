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


    public function setTranslations(&$aData)
    {
        foreach ($aData as $nr => $aLecture) {
            foreach ($aLecture as $fieldName => $field) {
                // main part
                if (is_array($field)) {
                    foreach ($field as $fKey => $val) {
                        if (in_array($fKey, $this->all_language_codes)) {
                            $translated = $this->getTranslation($aData[$nr][$fieldName]);
                            unset($aData[$nr][$fieldName]); // drop array with all languages
                            $aData[$nr][$fieldName] = $translated;
                        }
                    }
                }
            }

            foreach ($aLecture['providerValues']['event'] as $fieldName => $field) {
                // event part
                if (is_array($field)) {
                    foreach ($field as $fKey => $val) {
                        if (in_array($fKey, $this->all_language_codes)) {
                            $translated = $this->getTranslation($aData[$nr]['providerValues']['event'][$fieldName]);
                            unset($aData[$nr]['providerValues']['event'][$fieldName]); // drop array with all languages
                            $aData[$nr]['providerValues']['event'][$fieldName] = $translated;
                        }
                    }
                }
            }

            $aSubs = [
                'courses',
                'modules', // depending on $format not all DIP fields are avaliable
            ];

            foreach ($aSubs as $subName) {
                if (!empty($aLecture['providerValues'][$subName])) { // depending on $format not all DIP fields are avaliable
                    foreach ($aLecture['providerValues'][$subName] as $cNr => $aCourse) {
                        foreach ($aCourse as $coursefieldName => $aTranslatable) {
                            if (is_array($aTranslatable)) {
                                foreach ($aTranslatable as $lang => $val) {
                                    if (in_array($lang, $this->all_language_codes)) {
                                        $translated = $this->getTranslation($aLecture['providerValues'][$subName][$coursefieldName]);
                                        unset($aData[$nr]['providerValues'][$subName][$cNr][$coursefieldName]); // drop all other languages
                                        $aData[$nr]['providerValues'][$subName][$cNr][$coursefieldName] = $translated;
                                    }
                                }
                            }

                            if ($coursefieldName == 'planned_dates') {
                                foreach ($aTranslatable as $planed_datesNr => $aPlanedDatesFields) {
                                    foreach ($aPlanedDatesFields as $fieldName => $aVals) {
                                        if (is_array($aVals)) {
                                            foreach ($aVals as $lang => $val) {
                                                if (in_array($lang, $this->all_language_codes)) {
                                                    $translated = $this->getTranslation($aLecture['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName]);
                                                    unset($aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName]); // drop all other languages
                                                    $aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName] = $translated;
                                                }
                                            }
                                        }
                                        if ($fieldName == 'individual_dates') {
                                            foreach ($aVals as $dateNr => $aDateDetails) {
                                                foreach ($aDateDetails as $fN => $aV) {
                                                    if (is_array($aV)) {
                                                        foreach ($aV as $lang => $val) {
                                                            if (in_array($lang, $this->all_language_codes)) {
                                                                $translated = $this->getTranslation($aLecture['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN]);
                                                                unset($aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN]); // drop all other languages
                                                                $aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN] = $translated;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}