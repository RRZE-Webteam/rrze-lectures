<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

class Translator
{
    protected $display_language;
    protected $display_language_fallback;
    protected $multlangFields = [];


    public function __construct($attTeachingLanguage)
    {
        $aLang = explode(':', $attTeachingLanguage);
        $this->display_language = $aLang[0];
        if (count($aLang) > 1) {
            $this->display_language_fallback = $aLang[1];
        }

        $this->multlangFields = $this->getMultilangFields();
    }

    private function getMultilangFields()
    {

        // siehe: https://matrix.to/#/!pJMQnbUBnkcBiKthwJ:fau.de/$uOsCXKJ-J2JXdJGVyikgeXl9Pm_-_1bxuBty-M7QGPQ?via=fau.de

        // in Campo mit "Turnus des Angebots" bzw "Module frequency" beschriftet / Reiter: "Module / Studiengänge" bzw "Modules and degree programmes" (fehlt in der API)
        // providerValues.courses.course_responsible.orgunit (falls es dazu überhaupt zwei Sprachen gibt)
        // providerValues.event_orgunit.orgunit (falls es dazu überhaupt zwei Sprachen gibt)
        // in Campo mit "Standardtext" bzw "Default text" beschriftet / Reiter: "Module / Studiengänge" bzw "Modules and degree programmes" (fehlt in der API)


        return [
            'main' => [
                'name',
                'description',
            ],
            'event_orgunit' => [
                // providerValues.event_orgunit.orgunit (falls es dazu überhaupt zwei Sprachen gibt)
                'orgunit',
            ],
            'event' => [
                // providerValues.event.eventtype
                // providerValues.event.comment
                'eventtype',
                'comment',
            ],
            'courses' => [
                // providerValues.courses.title
                // providerValues.courses.contents
                // providerValues.courses.literature
                // providerValues.courses.url (falls ein Link mit vorausgewähltem Sprachwechsler-Dropdown auf der Campo-Website möglich ist)
                'title',
                'contents',
                'literature',
                'url',
            ],
            'planned_dates' => [
                // providerValues.courses.planned_dates.rhythm
                // providerValues.courses.planned_dates.comment
                // providerValues.courses.planned_dates.individual_dates.comment
                'rhythm',
                'comment',
                'comment',
            ],
            'module' => [
                // providerValues.module.module_name
                'module_name',

            ],
            'module_cos' => [
                // providerValues.module.module_cos.degree
                // providerValues.module.module_cos.subject
                // providerValues.module.module_cos.major
                // providerValues.module.module_cos.subject_indicator
                // providerValues.module.module_cos.subject
                'degree',
                'subject',
                'major',
                'subject_indicator',
                'subject',
            ],
            'stud' => [
                // providerValues.module.stud.degree
                // providerValues.module.stud.subject
                // providerValues.module.stud.major
                'degree',
                'subject',
                'major',
            ],
            'module_restrictions' => [
                // providerValues.module.module_restrictions.requirement_name
                'requirement_name',
            ],
        ];
    }

    private function getTranslation(&$aIn){
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
            foreach($this->multlangFields['main'] as $field){
                $aData[$nr][$field] = $this->getTranslation($aLecture[$field]);
            }
            foreach($aLecture as $lNr => $lecture){
                foreach($lecture['providerValues']['event_orgunit'] as $eNr => $aDetails){
                    foreach($this->multlangFields['event_orgunit'] as $field){
                        $aData[$nr]['providerValues']['event_orgunit'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
                    }
                }

                foreach($lecture['providerValues']['event'] as $eNr => $aDetails){
                    foreach($this->multlangFields['event'] as $field){
                        $aData[$nr]['providerValues']['event'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
                    }
                }

                foreach($lecture['providerValues']['courses'] as $cNr => $aCourses){
                    foreach($this->multlangFields['courses'] as $field){
                        $aData[$nr]['providerValues']['courses'][$cNr][$field] = $this->getTranslation($aCourses[$field]);
                    }
                    foreach($aCourses['planned_dates'] as $pNr => $aDetails){
                        foreach($this->multlangFields['planned_dates'] as $field){
                            $aData[$nr]['providerValues']['courses'][$cNr]['planned_dates'][$pNr][$field] = $this->getTranslation($aDetails[$field]);
                        }
                    }
                }

                foreach($lecture['providerValues']['module'] as $mNr => $aModule){
                    foreach($this->multlangFields['module'] as $field){
                        $aData[$nr]['providerValues']['module'][$mNr][$field] = $this->getTranslation($aModule[$field]);
                    }
                    foreach($aModule['module_cos'] as $iNr => $aDetails){
                        foreach($this->multlangFields['module_cos'] as $field){
                            $aData[$nr]['providerValues']['module'][$mNr]['module_cos'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                        }
                    }
                    foreach($aModule['stud'] as $iNr => $aDetails){
                        foreach($this->multlangFields['stud'] as $field){
                            $aData[$nr]['providerValues']['module'][$mNr]['stud'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                        }
                    }
                    foreach($aModule['module_restrictions'] as $iNr => $aDetails){
                        foreach($this->multlangFields['stud'] as $field){
                            $aData[$nr]['providerValues']['module'][$mNr]['module_restrictions'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                        }
                    }
                }
            }
        }
    }
}
