[tab title="<?php echo __('Basic data', 'rrze-lectures'); ?>"]

[columns]
[column]
<table class="nobackground">
    {{providerValues.event.title}}
    <tr>
        <th><?php echo __('Title', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.title}}</td>
    </tr>
    {{/providerValues.event.title}}
    {{providerValues.event.shorttext}}
    <tr>
        <th><?php echo __('Short text', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.shorttext}}</td>
    </tr>
    {{/providerValues.event.shorttext}}
    {{providerValues.event.eventtype}}
    <tr>
        <th><?php echo __('Course type', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.eventtype}}</td>
    </tr>
    {{/providerValues.event.eventtype}}
    {{providerValues.event.frequency}}
    <tr>
        <th><?php echo __('Module frequency', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.frequency}}</td>
    </tr>
    {{/providerValues.event.frequency}}
    {{providerValues.event.semester_hours_per_week}}
    <tr>
        <th><?php echo __('Semester hours per week', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.semester_hours_per_week}}</td>
    </tr>
    {{/providerValues.event.semester_hours_per_week}}  
    {{providerValues.event.ects}}
    <tr>
        <th><?php echo __('ECTS credits', 'rrze-lectures'); ?></th>
        <td>{{=providerValues.event.ects}}</td>
    </tr>
    {{/providerValues.event.ects}}
   
    
</table>
[/column]
[column]
{{campo_url}}[button link="{{=campo_url}}"]<?php echo __('Link to Campo', 'rrze-lectures'); ?>[/button]{{/campo_url}} 
{{studon_url}}[button link="{{=studon_url}}"]<?php echo __('Link to StudOn', 'rrze-lectures'); ?>[/button]{{/studon_url}}
[/column]
[/columns]
  {{providerValues.event.comment}}[alert style="info"]<strong><?php echo __('Comment', 'rrze-lectures'); ?></strong><br>{{=providerValues.event.comment}}[/alert]{{/providerValues.event.comment}}

[/tab]