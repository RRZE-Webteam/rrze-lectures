[tab title="<?php echo __('Parallel groups / dates', 'rrze-lectures'); ?>"]

        {{@providerValues.courses}}

        {{_val.contents}}
        <p itemprop="description" class="course-contents">{{=_val.contents}}</p>
        {{/_val.contents}}
        
        [columns]
		[column]
       <table class="nobackground">
           {{_val.parallelgroup}}<tr><th colspan="2">{{=_val.parallelgroup}}</th></tr>{{/_val.parallelgroup}}     
        
           {{_val.hours_per_week}}<tr><th><?php echo __('Semester hours per week', 'rrze-lectures'); ?></th><td>{{=_val.hours_per_week}}</td></tr>{{/_val.hours_per_week}}
           {{_val.teaching_language_txt}}<tr><th><?php echo __('Teaching language', 'rrze-lectures'); ?></th><td>{{=_val.teaching_language_txt}}</td></tr>{{/_val.teaching_language_txt}}
         {{_val.course_responsible}}
         <tr><th><?php echo __('Responsible', 'rrze-lectures'); ?></th>
             <td>
            {{@_val.course_responsible}}
            <span itemprop="provider" itemscope="" itemtype="http://schema.org/Person">{{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{/_val.surname}}</span>
            {{/@_val.course_responsible}}

         </td></tr>
        {{/_val.course_responsible}}

        </table>

       
        [/column]
		[column]
       

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


        {{_val.url}}[button link="{{=_val.url}}" ]<?php echo __('Link to Campo', 'rrze-lectures'); ?>[/button]{{/_val.url}}
        {{studon_url}}[button link="{{=studon_url}}" ]<?php echo __('Link to StudOn', 'rrze-lectures'); ?>[/button]{{/studon_url}}
        [/column]
		[/columns]
        


        {{_val.planned_dates}}
        <table>
            <tr>
                <th><?php echo __('Date and Time', 'rrze-lectures'); ?></th>
                <th><?php echo __('Cancellation date', 'rrze-lectures'); ?></th>
                <th><?php echo __('Start date - End date', 'rrze-lectures'); ?></th>
                <th><?php echo __('Lecturer(s)', 'rrze-lectures'); ?></th>
                <th><?php echo __('Comment', 'rrze-lectures'); ?></th>
                <th><?php echo __('Room', 'rrze-lectures'); ?></th>
            </tr>
            {{@_val.planned_dates}}
            <tr>              
                <td>
                    {{_val.rhythm}}{{=_val.rhythm}} {{/_val.rhythm}}{{_val.weekday}}{{=_val.weekday}}{{/_val.weekday}}{{_val.starttime}}, {{=_val.starttime}}{{/_val.starttime}} - {{_val.endtime}}{{=_val.endtime}}{{/_val.endtime}}
                </td>
                <td>{{@_val.misseddates}}{{=_val}}<br>{{/@_val.misseddates}}</td>
                <td>{{_val.startdate}}{{=_val.startdate}}{{/_val.startdate}} - {{_val.enddate}}{{=_val.enddate}}{{/_val.enddate}}</td>
                <td>
                {{_val.instructor}}
                <ul>
                {{@_val.instructor}}<li itemprop="provider" itemscope="" itemtype="http://schema.org/Person">{{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{/_val.surname}}</li>{{/@_val.instructor}}    
                </ul>
                {{/_val.instructor}}
                </td>
                <td>{{_val.comment}}{{=_val.comment}}{{/_val.comment}}</td>
                <td>{{_val.famos_code}}{{=_val.famos_code}}{{/_val.famos_code}}</td>
            </tr>
            {{/@_val.planned_dates}}
        </table>
        {{:_val.planned_dates}}
            <?php echo __('No planned appointments for this parallel group yet.', 'rrze-lectures'); ?>
        {{/_val.planned_dates}}

        {{/@providerValues.courses}}
        
[/tab]