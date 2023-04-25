{{first}}
    <div class="rrze-lectures">
        <div itemscope="" itemtype="https://schema.org/Course">

        {{do_accordion}}
            [collapsibles hstart="{{=hstart}}"]
        {{/do_accordion}}
{{/first}}
{{do_degree_accordion}}
    {{degree_title}}
        [collapse title="{{=degree_title}}" id="{{=degree_title}}_{{=identifier}}"]
    {{/degree_title}}
{{:do_degree_accordion}}
    {{degree_title}}
        {{show_degree_title}}
            <H{{=degree_hstart}}>{{=degree_title}}</H{{=degree_hstart}}>
        {{:show_degree_title}}
            <br>&nbsp;<br>
        {{/show_degree_title}}
    {{/degree_title}}
{{/do_degree_accordion}}
{{do_type_accordion}}
    {{type_title}}
        {{do_degree_accordion}}
            {{degree_start}}
                [accordion]
            {{/degree_start}}
            [accordion-item title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{:do_degree_accordion}}
            [collapse title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{/do_degree_accordion}}        
    {{/type_title}}
{{:do_type_accordion}}
    {{type_title}}
        <H{{=type_hstart}}>{{=type_title}}</H{{=type_hstart}}>
    {{/type_title}}
{{/do_type_accordion}}


{{hide_lecture_name}}
{{:hide_lecture_name}}
        <h2><span itemprop="name">{{=name}}</span></h2>
{{/hide_lecture_name}}

        [tabs]
        [tab title="<?php echo __('Basic data', 'rrze-lectures'); ?>"]

        {{providerValues.event.title}}<?php echo __('Title', 'rrze-lectures'); ?>: {{=providerValues.event.title}}{{/providerValues.event.title}}
        {{providerValues.event.shorttext}}<?php echo __('Short text', 'rrze-lectures'); ?>: {{=providerValues.event.shorttext}}{{/providerValues.event.shorttext}}

        {{providerValues.event_orgunit}}
        <?php echo __('Organizational unit', 'rrze-lectures'); ?>:
        <ul>
            {{/providerValues.event_orgunit}}
            {{@providerValues.event_orgunit.orgunit}}
            <li>{{=_val}}</li>
            {{/@providerValues.event_orgunit.orgunit}}
            {{providerValues.event_orgunit}}
        </ul>
        {{/providerValues.event_orgunit}}

        {{providerValues.event.eventtype}}<p><?php echo __('Course type', 'rrze-lectures'); ?>: {{=providerValues.event.eventtype}}</p>{{/providerValues.event.eventtype}}
        {{providerValues.event.frequency}}<p><?php echo __('Module frequency', 'rrze-lectures'); ?>: {{=providerValues.event.frequency}}</p>{{/providerValues.event.frequency}}
        {{providerValues.event.semester_hours_per_week}}<p><?php echo __('Semester hours per week', 'rrze-lectures'); ?>: {{=providerValues.event.semester_hours_per_week}}</p>{{/providerValues.event.semester_hours_per_week}}
        <!-- <p><?php echo __('ECTS credits', 'rrze-lectures'); ?>: Feld FEHLT  (Stand: 2023-03-22)</p> -->
        <!-- <p><?php echo __('Link to StudOn course (login)', 'rrze-lectures'); ?>: Feld FEHLT  (Stand: 2023-03-22)</p> -->

        {{providerValues.event.comment}}[alert style="warning"]<strong><?php echo __('Comment', 'rrze-lectures'); ?></strong><br>{{=providerValues.event.comment}}[/alert]{{/providerValues.event.comment}}

        [/tab]
        [tab title="<?php echo __('Parallel groups / dates', 'rrze-lectures'); ?>"]



        <br>&nbsp;<br>

        {{@providerValues.courses}}

        {{_val.parallelgroup}}<strong>{{=_val.parallelgroup}}</strong>{{/_val.parallelgroup}}

        {{_val.url}}<a href="{{=_val.url}}" target="rrze-campo" class="rrze-lectures-linklist"><?php echo __('Link to Campo', 'rrze-lectures'); ?></a>{{/_val.url}}

        {{_val.hours_per_week}}<p><?php echo __('Semester hours per week', 'rrze-lectures'); ?>: {{=_val.hours_per_week}}</p>{{/_val.hours_per_week}}

        {{_val.teaching_language_txt}}<p><?php echo __('Teaching language', 'rrze-lectures'); ?>: {{=_val.teaching_language_txt}}</p>{{/_val.teaching_language_txt}}

        {{_val.course_responsible}}
        <?php echo __('Responsible', 'rrze-lectures'); ?>:
        <ul>
            {{@_val.course_responsible}}
            <li itemprop="provider" itemscope="" itemtype="http://schema.org/Person">
                {{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{/_val.surname}}
            </li>
            {{/@_val.course_responsible}}
        </ul>
        {{/_val.course_responsible}}
        </br>
        {{_val.contents}}
        <strong><?php echo __('Content', 'rrze-lectures'); ?></strong>
        <p itemprop="description">{{=_val.contents}}</p>
        {{/_val.contents}}

        {{_val.literature}}
        <p><?php echo __('Literature references', 'rrze-lectures'); ?>: {{=_val.literature}}</p>
        {{/_val.literature}}

        {{_val.compulsory_requirement}}
        <p><?php echo __('Recommended requirements / organizational matters', 'rrze-lectures'); ?>: {{=_val.compulsory_requirement}}</p>
        {{/_val.compulsory_requirement}}

        {{_val.attendee_maximum}}
        <p><?php echo __('Maximum number of participants', 'rrze-lectures'); ?>: {{=_val.attendee_maximum}}</p>
        {{/_val.attendee_maximum}}

        {{_val.attendee_minimum}}
        <p><?php echo __('Minimum number of participants for the event to take place', 'rrze-lectures'); ?>: {{=_val.attendee_minimum}}</p>
        {{/_val.attendee_minimum}}

        {{_val.planned_dates}}
        <table>
            <tr>
                <th><?php echo __('Frequency', 'rrze-lectures'); ?></th>
                <th><?php echo __('Weekday', 'rrze-lectures'); ?></th>
                <th><?php echo __('From - To', 'rrze-lectures'); ?></th>
                <th><?php echo __('Cancellation date', 'rrze-lectures'); ?></th>
                <th><?php echo __('Start date - End date', 'rrze-lectures'); ?></th>
                <th><?php echo __('Exp. Att.', 'rrze-lectures'); ?></th>
                <th><?php echo __('Comment', 'rrze-lectures'); ?></th>
                <th><?php echo __('Lecturer(s)', 'rrze-lectures'); ?></th>
                <th><?php echo __('Room', 'rrze-lectures'); ?></th>
            </tr>
            {{@_val.planned_dates}}
            <tr>
                <td>{{_val.rhythm}}{{=_val.rhythm}}{{/_val.rhythm}}</td>
                <td>{{_val.weekday}}{{=_val.weekday}}{{/_val.weekday}}</td>
                <td>{{_val.starttime}}{{=_val.starttime}}{{/_val.starttime}} - {{_val.endtime}}{{=_val.endtime}}{{/_val.endtime}}</td>
                <td>{{@_val.misseddates}}{{=_val}}<br>{{/@_val.misseddates}}</td>
                <td>{{_val.startdate}}{{=_val.startdate}}{{/_val.startdate}} - {{_val.enddate}}{{=_val.enddate}}{{/_val.enddate}}</td>
                <td>{{_val.expected_attendees_count}}{{=_val.expected_attendees_count}}{{/_val.expected_attendees_count}}
                </td>
                <td>{{_val.comment}}{{=_val.comment}}{{/_val.comment}}
                </td>
                <td>
                {{_val.instructor}}
                <ul>
                {{@_val.instructor}}
                    <li itemprop="provider" itemscope="" itemtype="http://schema.org/Person">{{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{/_val.surname}}</li>
                {{/@_val.instructor}}    
                </ul>
                {{/_val.instructor}}
                </td>
                <td>{{_val.famos_code}}{{=_val.famos_code}}{{/_val.famos_code}}
                </td>
            </tr>
            {{/@_val.planned_dates}}
        </table>
        {{:_val.planned_dates}}
            <?php echo __('No planned appointments for this parallel group yet.', 'rrze-lectures'); ?>
        {{/_val.planned_dates}}

        {{/@providerValues.courses}}

        [/tab]

    [/tabs]

{{do_type_accordion}}
    {{type_end}}
        {{do_degree_accordion}}
            [/accordion-item]
            {{degree_end}}
                [/accordion]
            {{/degree_end}}
        {{:do_degree_accordion}}
            [/collapse]
        {{/do_degree_accordion}}        
    {{/type_end}}
{{/do_type_accordion}}
{{do_degree_accordion}}
    {{degree_end}}
    [/collapse]
    {{/degree_end}}
{{/do_degree_accordion}}
{{last}}
    {{do_accordion}}
        [/collapsibles]
    {{/do_accordion}}
    </div>
{{/last}}
