<?php 

namespace RRZE\DIP;

$ret = '<div class="rrze-lectures">';

if ($lecture){
    $lang = get_locale();
    $options = get_option('rrze-lectures');
    $ssstart = (!empty($options['basic_ssStart']) ? $options['basic_ssStart'] : 0);
    $ssend = (!empty($options['basic_ssEnd']) ? $options['basic_ssEnd'] : 0);
    $wsstart = (!empty($options['basic_wsStart']) ? $options['basic_wsStart'] : 0);
    $wsend = (!empty($options['basic_wsEnd']) ? $options['basic_wsEnd'] : 0);

    $ret .= '<div itemscope itemtype="https://schema.org/Course">';

    $ret .= '<h' . $this->atts['hstart'] . '>';
    if ($lang != 'de_DE' && $lang != 'de_DE_formal' && !empty($lecture['ects_name'])) {
        $lecture['title'] = $lecture['ects_name'];
    } else {
        $lecture['title'] = $lecture['name'];
    }
    $ret .= '<span itemprop="name">' . $lecture['title'] . '</span>';

    // $ret .= '<span itemprop="provider" itemscope itemtype="http://schema.org/EducationalOrganization">;

    $ret .= '</h' . $this->atts['hstart'] . '>';
    if (!empty($lecture['lecturers'])){
        $ret .= '<h' . ($this->atts['hstart'] + 1) . '>' . __('Lecturers', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 1) . '>';
		$ret .= '<ul>';
        foreach ($lecture['lecturers'] as $doz){
            $name = array();
            if (!empty($doz['title'])){
                $name['title'] = '<span itemprop="honorificPrefix">' . $doz['title'] . '</span>';
            }
            if (!empty($doz['firstname'])){
                $name['firstname'] = '<span itemprop="givenName">' . $doz['firstname'] . '</span>';
            }
            if (!empty($doz['lastname'])){
                $name['lastname'] = '<span itemprop="familyName">' . $doz['lastname'] . '</span>';
            }
            $fullname = implode(' ', $name);
            if (!empty($doz['person_id'])){
                $url = '<a href="' . get_permalink() . 'lectureid/' . $doz['person_id'] . '">' . $fullname . '</a>';
            }else{
                $url = $fullname;
            }
			$ret .= '<li itemprop="provider" itemscope itemtype="http://schema.org/Person">' . $url . '</li>';
        }
        $ret .= '</ul>';
    }
    $ret .= '<h' . ($this->atts['hstart'] + 1) . '>' . __('Details', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 1) . '>';

    if (!empty($lecture['angaben'])){
        $ret .= '<p>' . make_clickable($lecture['angaben']) . '</p>';
    }

    $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('Time and place', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
    if (array_key_exists('comment', $lecture)){
        $ret .= '<p>' . make_clickable($lecture['comment']) . '</p>';
    }
    $ret .= '<ul>';
    if (isset($lecture['courses'])){
        foreach ($lecture['courses'] as $course){
            foreach ($course['term'] as $term){
                $t = array();
                $time = array();
                if (!empty($term['repeat'])){
                    $t['repeat'] = $term['repeat'];
                }
                if (!empty($term['startdate'])){
                    if (!empty($term['enddate']) && $term['startdate'] != $term['enddate']){
                        $t['date'] = date("d.m.Y", strtotime($term['startdate'])) . '-' . date("d.m.Y", strtotime($term['enddate']));
                    }else{
                        $t['date'] = date("d.m.Y", strtotime($term['startdate']));
                    }
                }
                if (!empty($term['starttime'])){
                    $time['starttime'] = $term['starttime'];
                }
                if (!empty($term['endtime'])){
                    $time['endtime'] = $term['endtime'];
                }
                if (!empty($time)){
                    $t['time'] = $time['starttime'] . '-' . $time['endtime'];
                }else{
                    $t['time'] = __('Time on appointment', 'rrze-lectures');
                }
                if (!empty($term['room']['short'])){
                    $t['room'] = __('Room', 'rrze-lectures') . ' ' . $term['room']['short'];
                }
                if (!empty($term['exclude'])){
                    $t['exclude'] = '(' . __('exclude', 'rrze-lectures') . ' ' . $term['exclude'] . ')';
                }
                if (!empty($course['coursename'])){
                    $t['coursename'] = '(' . __('Course', 'rrze-lectures') . ' ' . $course['coursename'] . ')';
                }
                // ICS
                if (!in_array('ics', $this->hide)) {
                    if (!in_array('ics', $this->hide)) {
                        $aIcsLink = Functions::makeLinkToICS($lecture['lecture_type_long'], $lecture, $term, $t);
                        $t['ics'] = '<span class="lecture-info-ics" itemprop="ics"><a href="' . $aIcsLink['link'] . '" aria-label="' . $aIcsLink['linkTxt'] . '">' . __('ICS', 'rrze-univis') . '</a></span>';
                    }
                }
                $t['time'] .= ',';
                $term_formatted = implode(' ', $t);
                $ret .= '<li>' . $term_formatted . '</li>';
            }
        }
    }else{
        $ret .= '<li>' . __('Time and place on appointment', 'rrze-lectures') . '</li>';
    }
    $ret .= '</ul>';

    if (array_key_exists('studs', $lecture) && array_key_exists('stud', $lecture['studs'][0])){
        $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('Fields of study', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
        $ret .= '<ul>';
        foreach ($lecture['studs'][0]['stud'] as $stud){
            $s = array();
            if (!empty($stud['pflicht'])){
                $s['pflicht'] = $stud['pflicht'];
            }
            if (!empty($stud['richt'])){
                $s['richt'] = $stud['richt'];
            }
            if (!empty($stud['sem'][0]) && absint($stud['sem'][0])){
                $s['sem'] = sprintf('%s %d', __('from SEM', 'rrze-lectures'), absint($stud['sem'][0]));
            }
            $studinfo = implode(' ', $s);
            $ret .= '<li>' . $studinfo . '</li>';
        }
        $ret .= '</ul>';
    }

    if (!empty($lecture['organizational'])){
        $ret .= '<h4>' . __('Prerequisites / Organizational information', 'rrze-lectures') . '</h4>';
        $ret .= '<p>' . $lecture['organizational'] . '</p>';
    }
    if (!empty($lecture['summary'])) {
        $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('Content', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
        $ret .= '<p itemprop="description">' . make_clickable($lecture['summary']) . '</p>';
    }
    if (!empty($lecture['literature'])) {
        $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('Recommended Literature', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
        $ret .= '<p>' . make_clickable($lecture['literature']) . '</p>';
    }
    if (!empty($lecture['ects_infos'])) {
        $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('ECTS information', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
        if (!empty($lecture['ects_name'])) {
            $ret .= '<h' . ($this->atts['hstart'] + 3) . '>' . __('Title', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 3) . '>';
            $ret .= '<p>' . $lecture['ects_name'] . '</p>';
        }
        if (!empty($lecture['ects_cred'])) {
            $ret .= '<h' . ($this->atts['hstart'] + 3) . '>' . __('Credits', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 3) . '>';
            $ret .= '<p>' . $lecture['ects_cred'] . '</p>';
        }
        if (!empty($lecture['ects_summary'])) {
            $ret .= '<h' . ($this->atts['hstart'] + 3) . '>' . __('Content', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 3) . '>';
            $ret .= '<p>' . $lecture['ects_summary'] . '</p>';
        }
        if (!empty($lecture['ects_literature'])) {
            $ret .= '<h' . ($this->atts['hstart'] + 3) . '>' . __('Literature', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 3) . '>';
            $ret .= '<p>' . $lecture['ects_literature'] . '</p>';
        }
    }
    if (!empty($lecture['keywords']) || !empty($lecture['maxturnout']) || !empty($lecture['url_description'])) {
        $ret .= '<h' . ($this->atts['hstart'] + 2) . '>' . __('Additional information', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 2) . '>';
        if (!empty($lecture['keywords'])) {
            $ret .= '<p>' . __('Keywords', 'rrze-lectures') . ': ' . $lecture['keywords'] . '</p>';
        }
        if (!empty($lecture['maxturnout'])) {
            $ret .= '<p>' . __('Expected participants', 'rrze-lectures') . ': ' . $lecture['maxturnout'] . '</p>';
        }
        if (!empty($lecture['url_description'])) {
            $ret .= '<p>www: <a href="' . $lecture['url_description'] . '">' . $lecture['url_description'] . '</a></p>';
        }
    }

// $ret .= '<div itemprop="provider" itemscope itemtype="https://schema.org/provider">';
// $ret .= '<span itemprop="name">FAU</span>';
// $ret .= '<span itemprop="url">https://www.fau.de</span>';
// $ret .= '</div>';

    $ret .= '</div>'; // schema
}
$ret .= '</div>';

echo $ret;