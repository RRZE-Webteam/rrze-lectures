{{first}}
    <div class="rrze-lectures">
        {{atts_do_accordion}}
            [collapsibles hstart="{{=atts_hstart}}"]
        {{/atts_do_accordion}}
{{/first}}
{{atts_do_degree_accordion}}
    {{degree_title}}
        [collapse title="{{=degree_title}}" id="{{=degree_title}}_{{=identifier}}"]
    {{/degree_title}}
{{:atts_do_degree_accordion}}
    {{degree_title}}
        {{atts_show_degree_title}}
            <h{{=degree_hstart}}>{{=degree_title}}</h{{=degree_hstart}}>
        {{:atts_show_degree_title}}
            <br>&nbsp;<br>
        {{/atts_show_degree_title}}
    {{/degree_title}}
{{/atts_do_degree_accordion}}
{{atts_do_type_accordion}}
    {{type_title}}
        {{atts_do_degree_accordion}}
            {{degree_start}}
                [accordion]
            {{/degree_start}}
            [accordion-item title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{:atts_do_degree_accordion}}
            [collapse title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{/atts_do_degree_accordion}}        
    {{/type_title}}
{{:atts_do_type_accordion}}
    {{type_title}}
        <h{{=type_hstart}}>{{=type_title}}</h{{=type_hstart}}>
    {{/type_title}}
{{/atts_do_type_accordion}}

<div itemscope="" itemtype="https://schema.org/Course">
{{atts_hide_lecture_name}}
    <meta itemprop="name" content="{{=name}}">
{{:atts_hide_lecture_name}}
    <h2><span itemprop="name">{{=name}}</span></h2>
{{/atts_hide_lecture_name}}

    [tabs]
        {{=subtemplate_base}}
        {{=subtemplate_termine}}
        {{=subtemplate_module}}
        {{=subtemplate_orgunit}}
    [/tabs]
</div>
{{atts_do_type_accordion}}
    {{type_end}}
        {{atts_do_degree_accordion}}
            [/accordion-item]
            {{degree_end}}
                [/accordion]
            {{/degree_end}}
        {{:atts_do_degree_accordion}}
            [/collapse]
        {{/atts_do_degree_accordion}}        
    {{/type_end}}
{{/atts_do_type_accordion}}
{{atts_do_degree_accordion}}
    {{degree_end}}
    [/collapse]
    {{/degree_end}}
{{/atts_do_degree_accordion}}
{{last}}
    {{atts_do_accordion}}
        [/collapsibles]
    {{/atts_do_accordion}}
        
    </div>
{{/last}}
