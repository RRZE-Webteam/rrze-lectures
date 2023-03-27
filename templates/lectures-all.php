<?php

namespace RRZE\Lectures;

$ret = '<div class="rrze-lectures">';
if ($data) {
    $lang = get_locale();
    $options = get_option('rrze-lectures');
    $ssstart = (!empty($options['basic_ssStart']) ? $options['basic_ssStart'] : 0);
    $ssend = (!empty($options['basic_ssEnd']) ? $options['basic_ssEnd'] : 0);
    $wsstart = (!empty($options['basic_wsStart']) ? $options['basic_wsStart'] : 0);
    $wsend = (!empty($options['basic_wsEnd']) ? $options['basic_wsEnd'] : 0);

    if (in_array('accordion', $this->show) || in_array('accordion_courses', $this->show)) {
        $ret .= '[collapsibles hstart="' . $this->atts['hstart'] . '"]';
    }

    foreach ($data as $type => $lectures) {

        // what if we used one template for single and all?
        // if (count($data) > 1){
        if (in_array('accordion', $this->show)) {
            $ret .= '[collapse title="' . $type . '" name="' . urlencode($type) . '" color="' . $this->atts['color'] . '"]';
        } else {
            $ret .= '<h' . $this->atts['hstart'] . '>' . $type . '</h' . $this->atts['hstart'] . '>';
        }
        // }

        $ret .= '<ul>';
        foreach ($lectures as $lecture) {
            $courseDates = '';
            $url = get_permalink() . 'lv_id/' . $lecture['lecture_id'];
            $ret .= '<li>';
            $ret .= '<h' . ($this->atts['hstart'] + 1) . '><a href="' . $url . '">';
            if ($lang != 'de_DE' && $lang != 'de_DE_formal' && !empty($lecture['ects_name'])) {
                $lecture['title'] = $lecture['ects_name'];
            } else {
                $lecture['title'] = $lecture['name'];
            }
            $ret .= $lecture['title'];
            $ret .= '</a></h' . ($this->atts['hstart'] + 1) . '>';
            if (empty('hide_comment') && !empty($lecture['comment'])) {
                $ret .= '<p>' . make_clickable($lecture['comment']) . '</p>';
            }
            if (empty('hide_organizational') && !empty($lecture['organizational'])) {
                $ret .= '<p>' . make_clickable($lecture['organizational']) . '</p>';
            }

            if (empty('hide_lecturers') && !empty($lecture['lecturers'])) {
                $ret .= '<h' . ($this->atts['hstart'] + 1) . '>' . __('Lecturers', 'rrze-lectures') . '</h' . ($this->atts['hstart'] + 1) . '>';
                $ret .= '<ul>';
                foreach ($lecture['lecturers'] as $doz) {
                    $name = array();
                    if (!empty($doz['title'])) {
                        $name['title'] = '<span itemprop="honorificPrefix">' . $doz['title'] . '</span>';
                    }
                    if (!empty($doz['firstname'])) {
                        $name['firstname'] = '<span itemprop="givenName">' . $doz['firstname'] . '</span>';
                    }
                    if (!empty($doz['lastname'])) {
                        $name['lastname'] = '<span itemprop="familyName">' . $doz['lastname'] . '</span>';
                    }
                    $fullname = implode(' ', $name);
                    if (!empty($doz['person_id'])) {
                        $url = '<a href="' . get_permalink() . 'lectureid/' . $doz['person_id'] . '">' . $fullname . '</a>';
                    } else {
                        $url = $fullname;
                    }
                    $ret .= '<li itemprop="provider" itemscope itemtype="http://schema.org/Person">' . $url . '</li>';
                }
                $ret .= '</ul>';
            }

            $ret .= '<ul class="terminmeta">';
            $ret .= '<li>';
            $infos = '';
            if (empty('hide_sws') && !empty($lecture['sws'])) {
                $infos .= '<span>' . $lecture['sws'] . '</span>';
            }
            if (empty('hide_maxturnout') && !empty($lecture['maxturnout'])) {
                if (!empty($infos)) {
                    $infos .= '; ';
                }
                $infos .= '<span>' . __('Expected participants', 'rrze-lectures') . ': ' . $lecture['maxturnout'] . '</span>';
            }
            if (empty('hide_earlystudy') && !empty($lecture['earlystudy'])) {
                if (!empty($infos)) {
                    $infos .= '; ';
                }
                $infos .= '<span>' . $lecture['earlystudy'] . '</span>';
            }
            if (empty('hide_guest') && !empty($lecture['guest'])) {
                if (!empty($infos)) {
                    $infos .= '; ';
                }
                $infos .= '<span>' . $lecture['guest'] . '</span>';
            }
            if (empty('hide_cerificate') && !empty($lecture['cerificate'])) {
                if (!empty($infos)) {
                    $infos .= '; ';
                }
                $infos .= '<span>' . $lecture['cerificate'] . '</span>';
            }
            if (empty('hide_ects') && !empty($lecture['ects'])) {
                if (!empty($infos)) {
                    $infos .= '; ';
                }
                $infos .= '<span>' . $lecture['ects'] . '</span>';
                if (!empty($lecture['ects_cred'])) {
                    $infos .= ' (' . $lecture['ects_cred'] . ')';
                }
                $infos .= '</span>';
            }
            if (empty('hide_language') && !empty($lecture['leclanguage_long']) && ($lecture['leclanguage_long'] != __('Lecture\'s language German', 'rrze-lectures'))) {
                if (!empty($infos)) {
                    $infos .= ', ';
                }
                $infos .= '<span>' . $lecture['leclanguage_long'] . '</span>';
            }
            $ret .= $infos . '</li>';

            $courseDates = '';
            if (empty('hide_courses')) {
                if (in_array('accordion_courses', $this->show)) {
                    if (in_array('accordion', $this->show)) {
                        if (empty($courseDates)) {
                            $courseDates = '[accordion hstart="' . ($this->atts['hstart'] + 1) . '"]';
                        }
                        $courseDates .= '[accordion-item title="' . __('Date', 'rrze-lectures') . '" name="' . __('Date', 'rrze-lectures') . '_' . urlencode($lecture['title']) . '" color="' . $this->atts['color_courses'] . '"]';
                    } else {
                        $courseDates = '[collapse title="' . __('Date', 'rrze-lectures') . '" name="' . __('Date', 'rrze-lectures') . '_' . urlencode($lecture['title']) . '" color="' . $this->atts['color_courses'] . '"]';
                    }
                } else {
                    $courseDates = '<li class="termindaten">' . __('Date', 'rrze-lectures') . ':';
                }
                $courseDates .= '<ul>';

                if (isset($lecture['courses'])) {
                    foreach ($lecture['courses'] as $course) {
                        if ((empty($lecture['lecturer_key']) || empty($course['doz'])) || (!empty($lecture['lecturer_key']) && !empty($course['doz']) && (in_array($lecture['lecturer_key'], $course['doz'])))) {
                            foreach ($course['term'] as $term) {
                                $t = array();
                                $time = array();
                                if (!empty($term['repeat'])) {
                                    $t['repeat'] = $term['repeat'];
                                }
                                if (!empty($term['startdate'])) {
                                    if (!empty($term['enddate']) && $term['startdate'] != $term['enddate']) {
                                        $t['date'] = date("d.m.Y", strtotime($term['startdate'])) . '-' . date("d.m.Y", strtotime($term['enddate']));
                                    } else {
                                        $t['date'] = date("d.m.Y", strtotime($term['startdate']));
                                    }
                                }
                                if (!empty($term['starttime'])) {
                                    $time['starttime'] = $term['starttime'];
                                }
                                if (!empty($term['endtime'])) {
                                    $time['endtime'] = $term['endtime'];
                                }
                                if (!empty($time)) {
                                    $t['time'] = $time['starttime'] . '-' . $time['endtime'];
                                } else {
                                    $t['time'] = __('Time on appointment', 'rrze-lectures');
                                }
                                if (!empty($term['room']['short'])) {
                                    $t['room'] = __('Room', 'rrze-lectures') . ' ' . $term['room']['short'];
                                }
                                if (!empty($term['exclude'])) {
                                    $t['exclude'] = '(' . __('exclude', 'rrze-lectures') . ' ' . $term['exclude'] . ')';
                                }
                                if (!empty($course['coursename'])) {
                                    $t['coursename'] = '(' . __('Course', 'rrze-lectures') . ' ' . $course['coursename'] . ')';
                                }
                                // ICS
                                if (!in_array('ics', $this->hide)) {
                                    $aIcsLink = Functions::makeLinkToICS($type, $lecture, $term, $t);
                                    $t['ics'] = '<span class="lecture-info-ics" itemprop="ics"><a href="' . $aIcsLink['link'] . '" aria-label="' . $aIcsLink['linkTxt'] . '">' . __('ICS', 'rrze-lectures') . '</a></span>';
                                }
                                $t['time'] .= ',';
                                $term_formatted = implode(' ', $t);
                                $courseDates .= '<li>' . $term_formatted . '</li>';
                            }
                        }
                    }
                    if (in_array('accordion_courses', $this->show)) {
                        if (in_array('accordion', $this->show)) {
                            $courseDates .= '[/accordion-item]';
                            $courseDates .= '[/accordion]';
                        } else {
                            $courseDates .= '[/collapse]';
                        }
                    }
                } else {
                    $courseDates .= '<li>' . __('Time and place on appointment', 'rrze-lectures') . '</li>';
                    if (in_array('accordion_courses', $this->show)) {
                        if (in_array('accordion', $this->show)) {
                            $courseDates .= '[/accordion-item]';
                            $courseDates .= '[/accordion]';
                        } else {
                            $courseDates .= '[/collapse]';
                        }
                    }
                }
                $courseDates .= '</ul>';
                $courseDates .= '</li>';
            }
            $ret .= $courseDates . '</li>';
        }
        $ret .= '</ul>';

        if (in_array('accordion', $this->show)) {
            $ret .= '[/collapse]';
        }
    }

    if (in_array('accordion', $this->show) || in_array('accordion_courses', $this->show)) {
        $ret .= '[/collapsibles]';
        $ret = do_shortcode($ret);
    }
}

$ret .= '</div>';

echo $ret;
