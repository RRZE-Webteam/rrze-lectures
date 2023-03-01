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
            'modules' => [
                // providerValues.modules.modules_name
                'modules_name',

            ],
            'modules_cos' => [
                // providerValues.modules.modules_cos.degree
                // providerValues.modules.modules_cos.subject
                // providerValues.modules.modules_cos.major
                // providerValues.modules.modules_cos.subject_indicator
                // providerValues.modules.modules_cos.subject
                'degree',
                'subject',
                'major',
                'subject_indicator',
                'subject',
            ],
            'stud' => [
                // providerValues.modules.stud.degree
                // providerValues.modules.stud.subject
                // providerValues.modules.stud.major
                'degree',
                'subject',
                'major',
            ],
            'modules_restrictions' => [
                // providerValues.modules.modules_restrictions.requirement_name
                'requirement_name',
            ],
        ];
    }

    private function getTranslation(&$aIn)
    {
        // var_dump($aIn);
        // exit;
        if (!is_array($aIn)){
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

    function getParentIndex($name, $array){
        foreach($array as $key => $value){
            if(is_array($value) && $value['name'] == $name)
                  return $key;
        }
        return null;
    }

    function my_walk_recursive(&$aDataComplete, array $array, &$aAlLangCodes, &$tmp, $path = null) {
        // $tmp = '';
        foreach ($array as $k => $v) {
            $tmp .= ' -> ' . $k . '<br>';
            if (in_array($k, $aAlLangCodes)){
                // echo 'sprache => $path=' . $path . ' # $v=' . $v . '<br>';

                $aParts = explode('/', $path);
                // var_dump($test);
                // echo '<br>';
                array_walk($aParts, function(&$val, $key) use (&$aParts){
                    // if (!empty($val)){
                        if (empty($val) && $val != 0){
                        //     $tmp = (int) $val;
                        //     if ($val == $tmp){
                        //         $val = '[' . $val . ']';
                        //     }else{
                        //         $val = '["' . $val . '"]';
                        //     }
                        // }else{
                            unset($aParts[$key]);
                        }




                });

                // $fieldname = '';
                $field = $aDataComplete;

                // echo '<pre>';
                // var_dump($aParts);
                // exit;

                foreach ($aParts as $part){
                    $field = $field[$part];
                }


                $aDataComplete[$k] = $this->getTranslation($field) . ' TEST';
                // echo '<pre>';
                // var_dump($field);
                // exit;

                // $fieldName = implode($test);

                // echo '<pre>';
                // var_dump($aDataComplete);
                // exit;

                // $bla = '[0]';

                // $test = ${'aDataComplete' . $bla};

                // var_dump($test);
                // exit;

                // ${'aDataComplete' . $fieldName} = 'asdf';
                // echo 'asdfasdf<pre>';
                // var_dump(${'aDataComplete' . $fieldName});
                // echo 'hier<br>';
                // var_dump($aDataComplete);

                // $aDataComplete[0]["name"] = 'TEST TEST';
                // echo 'DANACH<br>';
                // var_dump($aDataComplete);
                // exit;

                // $test = implode('"]["', $test);
                // $test = substr($test, 2);

            }


            if (!is_array($v)) {
                // leaf node (file) -- print link
                $fullpath = $path.$v;
                // now do whatever you want with $fullpath, e.g.:
                // echo "Link to $fullpath<br>";
            }
            else {
                // directory node -- recurse

                if (!isset($tmp)){
                    echo 'nope';
                    exit;
                }

                $this->my_walk_recursive($aDataComplete, $v, $aAlLangCodes, $tmp, $path.'/'.$k);
                // echo '<pre>';
                // var_dump($aDataComplete);
                // echo 'hier<br>';
                // exit;

            }
        }
        echo $tmp;
// exit;

    }

    public function setTranslations(&$aData)
    {

        $aAlLangCodes = \ResourceBundle::getLocales('');
        // exit;

        echo '<pre>';
        $tmp = '';
        $aData = $this->my_walk_recursive($aData, $aData, $aAlLangCodes, $tmp);

        echo $tmp;
// exit;

// exit;
        // $aData = array_walk_recursive($aData, function(&$item, $key) use (&$aAlLangCodes){
        //     echo '<pre>asdf';
        //     var_dump($aAlLangCodes);
        //     exit;

        //     if (in_array($key, $aAlLangCodes)){
        //         // 2-letter digit => might be a language code what DIP delivers as array key
        //         $item = $this->getTranslation();
        //     }
        // });


        // array_walk multidimensional
        // vorher sprachkürzel 2 stellig ermitteln


        /*
af-ZA
am-ET
ar-AE
ar-BH
ar-DZ
ar-EG
ar-IQ
ar-JO
ar-KW
ar-LB
ar-LY
ar-MA
arn-CL
ar-OM
ar-QA
ar-SA
ar-SD
ar-SY
ar-TN
ar-YE
as-IN
az-az
az-Cyrl-AZ
az-Latn-AZ
ba-RU
be-BY
bg-BG
bn-BD
bn-IN
bo-CN
br-FR
bs-Cyrl-BA
bs-Latn-BA
ca-ES
co-FR
cs-CZ
cy-GB
da-DK
de-AT
de-CH
de-DE
de-LI
de-LU
dsb-DE
dv-MV
el-CY
el-GR
en-029
en-AU
en-BZ
en-CA
en-cb
en-GB
en-IE
en-IN
en-JM
en-MT
en-MY
en-NZ
en-PH
en-SG
en-TT
en-US
en-ZA
en-ZW
es-AR
es-BO
es-CL
es-CO
es-CR
es-DO
es-EC
es-ES
es-GT
es-HN
es-MX
es-NI
es-PA
es-PE
es-PR
es-PY
es-SV
es-US
es-UY
es-VE
et-EE
eu-ES
fa-IR
fi-FI
fil-PH
fo-FO
fr-BE
fr-CA
fr-CH
fr-FR
fr-LU
fr-MC
fy-NL
ga-IE
gd-GB
gd-ie
gl-ES
gsw-FR
gu-IN
ha-Latn-NG
he-IL
hi-IN
hr-BA
hr-HR
hsb-DE
hu-HU
hy-AM
id-ID
ig-NG
ii-CN
in-ID
is-IS
it-CH
it-IT
iu-Cans-CA
iu-Latn-CA
iw-IL
ja-JP
ka-GE
kk-KZ
kl-GL
km-KH
kn-IN
kok-IN
ko-KR
ky-KG
lb-LU
lo-LA
lt-LT
lv-LV
mi-NZ
mk-MK
ml-IN
mn-MN
mn-Mong-CN
moh-CA
mr-IN
ms-BN
ms-MY
mt-MT
nb-NO
ne-NP
nl-BE
nl-NL
nn-NO
no-no
nso-ZA
oc-FR
or-IN
pa-IN
pl-PL
prs-AF
ps-AF
pt-BR
pt-PT
qut-GT
quz-BO
quz-EC
quz-PE
rm-CH
ro-mo
ro-RO
ru-mo
ru-RU
rw-RW
sah-RU
sa-IN
se-FI
se-NO
se-SE
si-LK
sk-SK
sl-SI
sma-NO
sma-SE
smj-NO
smj-SE
smn-FI
sms-FI
sq-AL
sr-BA
sr-CS
sr-Cyrl-BA
sr-Cyrl-CS
sr-Cyrl-ME
sr-Cyrl-RS
sr-Latn-BA
sr-Latn-CS
sr-Latn-ME
sr-Latn-RS
sr-ME
sr-RS
sr-sp
sv-FI
sv-SE
sw-KE
syr-SY
ta-IN
te-IN
tg-Cyrl-TJ
th-TH
tk-TM
tlh-QS
tn-ZA
tr-TR
tt-RU
tzm-Latn-DZ
ug-CN
uk-UA
ur-PK
uz-Cyrl-UZ
uz-Latn-UZ
uz-uz
vi-VN
wo-SN
xh-ZA
yo-NG
zh-CN
zh-HK
zh-MO
zh-SG
zh-TW
zu-ZA
Share
Improve
        */

        foreach ($aData as $nr => $aLecture) {
            foreach ($this->multlangFields['main'] as $field) {
                $aData[$nr][$field] = $this->getTranslation($aLecture[$field]);
            }

            // echo '<pre>';
            // var_dump($aLecture['providerValues']['courses']);
            // exit;

            // foreach($aLectures as $lNr => $aLecture){

            foreach ($aLecture['providerValues']['event_orgunit'] as $eNr => $aDetails) {

                foreach ($this->multlangFields['event_orgunit'] as $field) {
                    $aData[$nr]['providerValues']['event_orgunit'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
                }
            }

            // foreach($aLecture['providerValues']['event'] as $eNr => $aDetails){
            //     foreach($this->multlangFields['event'] as $field){
            //         $aData[$nr]['providerValues']['event'][$eNr][$field] = $this->getTranslation($aDetails[$field]);
            //     }
            // }

            foreach ($aLecture['providerValues']['courses'] as $cNr => $aCourses) {
                // echo '<pre>';
                // var_dump($aLecture['providerValues']);
                // exit;


                foreach ($this->multlangFields['courses'] as $field) {
                    // echo '<pre>' . $field . '<br>' . 
                    // // $this->getTranslation($aCourses[$field]) . ' +';
                    // var_dump($aData[$nr]['providerValues']['courses'][$cNr]);
                    // exit;
                    $aData[$nr]['providerValues']['courses'][$cNr][$field] = $this->getTranslation($aCourses[$field]);
                }

                // if (!empty($aCourses['planned_dates'])){
                //     echo '<pre>';
                //     var_dump($aCourses['planned_dates']);
                //     exit;
                // }else{
                //     echo '<pre>';
                //     var_dump($aCourses);
                //     exit;

                // }

                if (!empty($aCourses['planned_dates'])) {
                    foreach ($aCourses['planned_dates'] as $pNr => $aDetails) {
                        foreach ($this->multlangFields['planned_dates'] as $field) {
                            $aData[$nr]['providerValues']['courses'][$cNr]['planned_dates'][$pNr][$field] = $this->getTranslation($aDetails[$field]);
                        }
                    }
                }
            }

            foreach ($aLecture['providerValues']['modules'] as $mNr => $aModule) {
                foreach ($this->multlangFields['modules'] as $field) {
                    $aData[$nr]['providerValues']['modules'][$mNr][$field] = $this->getTranslation($aModule[$field]);
                }
                foreach ($aModule['modules_cos'] as $iNr => $aDetails) {
                    foreach ($this->multlangFields['modules_cos'] as $field) {
                        $aData[$nr]['providerValues']['modules'][$mNr]['modules_cos'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                    }
                }
                foreach ($aModule['stud'] as $iNr => $aDetails) {
                    foreach ($this->multlangFields['stud'] as $field) {
                        $aData[$nr]['providerValues']['modules'][$mNr]['stud'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                    }
                }
                foreach ($aModule['modules_restrictions'] as $iNr => $aDetails) {
                    foreach ($this->multlangFields['stud'] as $field) {
                        $aData[$nr]['providerValues']['modules'][$mNr]['modules_restrictions'][$iNr][$field] = $this->getTranslation($aDetails[$field]);
                    }
                }
            }
            // }
        }
    }
}