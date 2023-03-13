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
{{type_start}}
    <ul>
{{/type_start}}

        <h2><span itemprop="name">{{=name}}</span></h2>

        [tabs]
        [tab title="<?php echo __('Basic data', 'rrze-lectures'); ?>"]

        <?php echo __('Title', 'rrze-lectures'); ?>: {{=providerValues.event.title}}
        <?php echo __('Short text', 'rrze-lectures'); ?>: {{=providerValues.event.shorttext}}

        {{providerValues.event_orgunit}}
        <?php echo __('Organizational unit', 'rrze-lectures'); ?>:
        <ul>
            {{/providerValues.event_orgunit}}
            {{@providerValues.event_orgunit}}
            <li>{{=_val.orgunit}}</li>
            {{/@providerValues.event_orgunit}}
            {{providerValues.event_orgunit}}
        </ul>
        {{/providerValues.event_orgunit}}

        <p><?php echo __('Course type', 'rrze-lectures'); ?>: {{=providerValues.event.eventtype}}</p>
        <p><?php echo __('Module frequency', 'rrze-lectures'); ?>: Feld FEHLT (Stand: 2023-03-10)</p>
        <p><?php echo __('ECTS credits', 'rrze-lectures'); ?>: fehlt dieses Feld? (Stand: 2023-03-13)</p>
        <p><?php echo __('Link to StudOn course (login)', 'rrze-lectures'); ?>: fehlt dieses Feld? (Stand: 2023-03-13)</p>        

        {{providerValues.event.comment}}[alert style="warning"]<strong><?php echo __('Comment', 'rrze-lectures'); ?></strong><br>{{=providerValues.event.comment}}[/alert]{{/providerValues.event.comment}}


        [/tab]
        [tab title="<?php echo __('Parallel groups / dates', 'rrze-lectures'); ?>"]



        <br>&nbsp;<br>

        {{@providerValues.courses}}

        <a href="{{=_val.url}}" target="rrze-campo" class="rrze-lectures-linklist"><?php echo __('Link to Campo', 'rrze-lectures'); ?></a>

        <p><?php echo __('Semester hours per week', 'rrze-lectures'); ?>: {{=_val.hours_per_week}}</p>

        <p><?php echo __('Teaching language', 'rrze-lectures'); ?>: {{=_val.teaching_language_txt}}</p>

        <?php echo __('Responsible', 'rrze-lectures'); ?>
        <ul>
            {{@_val.event_responsible}}
            <li itemprop="provider" itemscope="" itemtype="http://schema.org/Person">
                <a href="hier fehlt der Link zu IdM oder DIP liefert ID für rrze-contact oder event_responsible hat unter location einen URL">{{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{:_val.surname}}! surname (schema: familyName) ist LEER !{{/_val.surname}}</a>
            </li>
            {{/@_val.event_responsible}}
        </ul>

        <h4><?php echo __('Content', 'rrze-lectures'); ?></h4>
        <p itemprop="description">{{_val.contents}}{{=_val.contents}}{{:_val.contents}}_val.contents ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.contents}}</p>

        <h4><?php echo __('Literature references', 'rrze-lectures'); ?></h4>
        <p>{{_val.literature}}{{=_val.literature}}{{:_val.literature}}_val.literature ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.literature}}</p>

        <h4><?php echo __('Recommended requirements / organizational matters', 'rrze-lectures'); ?></h4>
        <p>{{_val.compulsory_requirement}}{{=_val.compulsory_requirement}}{{:_val.compulsory_requirement}}_val.compulsory_requirement ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.compulsory_requirement}}</p>

        <h4><?php echo __('Maximum number of participants', 'rrze-lectures'); ?></h4>
        <p>{{_val.attendee_maximum}}{{=_val.attendee_maximum}}{{:_val.attendee_maximum}}_val.attendee_maximum ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.attendee_maximum}}</p>
        <h4><?php echo __('Minimum number of participants for the event to take place', 'rrze-lectures'); ?></h4>
        <p>{{_val.attendee_minimum}}{{=_val.attendee_minimum}}{{:_val.attendee_minimum}}Feld FEHLT (Stand: 2023-02-21) (Vorschlag: _val.attendee_minimum){{/_val.attendee_minimum}}</p>

        <table>
            <tr>
                <td><?php echo __('Frequency', 'rrze-lectures'); ?></td>
                <td><?php echo __('Weekday', 'rrze-lectures'); ?></td>
                <td><?php echo __('From - To', 'rrze-lectures'); ?></td>
                <td><?php echo __('Cancellation date', 'rrze-lectures'); ?></td>
                <td><?php echo __('Start date - End date', 'rrze-lectures'); ?></td>
                <td><?php echo __('Exp. Att.', 'rrze-lectures'); ?></td>
                <td><?php echo __('Comment', 'rrze-lectures'); ?></td>
                <td><?php echo __('Lecturer(s)', 'rrze-lectures'); ?></td>
                <td><?php echo __('Room', 'rrze-lectures'); ?></td>
            </tr>
            {{@_val.planned_dates}}
            <tr>
                <td>{{_val.rhythm}}{{=_val.rhythm}}{{/_val.rhythm}}</td>
                <td>{{_val.weekday}}{{=_val.weekday}}{{/_val.weekday}}</td>
                <td>{{_val.starttime}}{{=_val.starttime}}{{/_val.starttime}}
                    -
                    {{_val.endtime}}{{=_val.endtime}}{{/_val.endtime}}
                </td>
                <td>{{@_val.misseddates}}{{=_val}}<br>{{/@_val.misseddates}}</td>
                <td>{{_val.startdate}}{{=_val.startdate}}{{/_val.startdate}}
                    -
                    {{_val.enddate}}{{=_val.enddate}}{{/_val.enddate}}
                </td>
                <td>{{_val.expected_attendees_count}}{{=_val.expected_attendees_count}}{{/_val.expected_attendees_count}}
                </td>
                <td>{{_val.comment}}{{=_val.comment}}{{/_val.comment}}
                </td>
                <td>individual_instructor => falls Array gefüllt ist (2DO: LV finden mit !empty(roviderValues.courses.planned_dates.instructor)</td>
                <td>{{_val.famos_request}}{{=_val.famos_request}}{{/_val.famos_request}} (2DO: famos_request URL FEHLT oder kann Link mit dieser Nr auf Map / Details-Page gesetzt werden?)
                </td>
            </tr>
            {{:@_val.planned_dates}}
                <?php echo __('No planned appointments for this parallel group yet.', 'rrze-lectures'); ?>            
            {{/@_val.planned_dates}}

        </table>
        {{/@providerValues.courses}}

        [/tab]

        {{providerValues.modules}}

        [tab title="<?php echo __('Modules and degree programmes', 'rrze-lectures'); ?>"] 

        <table>
            <tr>
                <td><?php echo __('Default text', 'rrze-lectures'); ?></td>
                <td><?php echo __('Type', 'rrze-lectures'); ?></td>
                <td><?php echo __('Degree', 'rrze-lectures'); ?></td>
                <td><?php echo __('Degree&nbsp;', 'rrze-lectures'); ?></td>
                <td><?php echo __('Major field of study', 'rrze-lectures'); ?></td>
                <td><?php echo __('Course specialization', 'rrze-lectures'); ?></td>
                <td><?php echo __('Subject indicator', 'rrze-lectures'); ?></td>
                <td><?php echo __('Version of examination regulations', 'rrze-lectures'); ?></td>
                <td><?php echo __('Form of study', 'rrze-lectures'); ?></td>
                <td><?php echo __('Place of study', 'rrze-lectures'); ?></td>
                <td><?php echo __('Type of study', 'rrze-lectures'); ?></td>
            </tr>

            {{@providerValues.modules}}
            {{@_val.modules_cos}}

            <tr>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>{{=_val.degree}}</td>
                <td>{{=_val.subject}}</td>
                <td>{{=_val.major}}</td>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>{{=_val.subject_indicator}}</td>
                <td>{{=_val.version}}</td>
                <td>FEHLT (Stand: 2023-02-21)</td>                
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>FEHLT (Stand: 2023-02-21)</td>                
            </tr>
            {{:@_val.modules_cos}}
                <?php echo __('The course has not been assigned to a course of study yet', 'rrze-lectures'); ?>   
            {{/@_val.modules_cos}}
            {{/@providerValues.modules}}
        </table>

        [/tab]

        {{/providerValues.modules}}
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
