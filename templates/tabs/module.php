{{providerValues.modules}}
[tab title="<?php echo __('Modules', 'rrze-lectures'); ?>"]   
    
<table>
    <tr>
        <th><?php echo __('Module', 'rrze-lectures'); ?></th>
        <th><?php echo __('Degrees', 'rrze-lectures'); ?></th>
    </tr>
   
        {{@providerValues.modules}}
        <tr>
            <td>{{=_val.module_name}} (<abbr title="<?php echo __('Number', 'rrze-lectures'); ?>"><?php echo __('No.', 'rrze-lectures'); ?></abbr> {{=_val.module_nr}})</td>
            <td>
                <ul class="kontrastmarker">          
                    {{@_val.module_cos}}<li>{{=_val.degree}} {{=_val.subject}} (<?php echo __('Examination regulations','rrze-lectures'); ?> {{=_val.version}}): {{=_val.subject_indicator}}, {{=_val.major}}</li>{{/@_val.module_cos}}                 
                </ul>
            </td>
            
               
        </tr>
        {{/@providerValues.modules}}
</table>    
[/tab]
{{/providerValues.modules}}