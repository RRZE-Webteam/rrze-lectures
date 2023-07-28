{{providerValues.event_orgunit}}
[tab title="<?php echo __('Organizational unit', 'rrze-lectures'); ?>"]   
    <ul>
        {{@providerValues.event_orgunit.orgunit}}
        <li>{{=_val}}</li>
        {{/@providerValues.event_orgunit.orgunit}}
    </ul>    
[/tab]
{{/providerValues.event_orgunit}}