{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
        {if ($DETAIL_VIEW_WIDGET->getLabel() eq 'Documents') }
            {assign var=DOCUMENT_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
        {elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'ModComments')}
            {assign var=COMMENTS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
        {elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_UPDATES')}
            {assign var=UPDATES_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
        {/if}
    {/foreach}

    <div class="left-block col-lg-4">
        {* Module Summary View*}
        <div class="summaryView">
            <div class="summaryViewHeader">
                <h4 class="display-inline-block">{vtranslate('LBL_KEY_FIELDS', $MODULE_NAME)}</h4>
            </div>
            <div class="summaryViewFields">
                {$MODULE_SUMMARY}
            </div>
        </div>
        {* Module Summary View Ends Here*}

        {* Summary View Documents Widget*}
        {if $DOCUMENT_WIDGET_MODEL}
            <div class="summaryWidgetContainer">
                <div class="widgetContainer_documents" data-url="{$DOCUMENT_WIDGET_MODEL->getUrl()}" data-name="{$DOCUMENT_WIDGET_MODEL->getLabel()}">
                    <div class="widget_header clearfix">
                        <input type="hidden" name="relatedModule" value="{$DOCUMENT_WIDGET_MODEL->get('linkName')}" />
                        <span class="toggleButton pull-left"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;</span>
                        <h4 class="display-inline-block pull-left">{vtranslate($DOCUMENT_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4>

                        {if $DOCUMENT_WIDGET_MODEL->get('action')}
                            {assign var=PARENT_ID value=$RECORD->getId()}
                            <div class="pull-right">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        <span class="fa fa-plus" title="{vtranslate('LBL_NEW_DOCUMENT', $MODULE_NAME)}"></span>&nbsp;{vtranslate('LBL_NEW_DOCUMENT', 'Documents')}&nbsp; <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-header"><i class="fa fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', 'Documents')}</li>
                                        <li id="VtigerAction">
                                            <a href="javascript:Documents_Index_Js.uploadTo('Vtiger',{$PARENT_ID},'{$MODULE_NAME}')">
                                                <i class="fa fa-upload"></i>
                                                {vtranslate('LBL_TO_SERVICE', 'Documents', {vtranslate('LBL_VTIGER', 'Documents')})}
                                            </a>
                                        </li>
                                        <li class="dropdown-header"><i class="fa fa-link"></i> {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', 'Documents')}</li>
                                        <li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E',{$PARENT_ID},'{$MODULE_NAME}')">&nbsp;<i class="fa fa-external-link"></i>&nbsp;&nbsp; {vtranslate('LBL_FROM_SERVICE', 'Documents', {vtranslate('LBL_FILE_URL', 'Documents')})}</a></li>
                                        <li role="separator" class="divider"></li>
                                        <li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W',{$PARENT_ID},'{$MODULE_NAME}')"><i class="fa fa-file-text"></i> {vtranslate('LBL_CREATE_NEW', 'Documents', {vtranslate('SINGLE_Documents', 'Documents')})}</a></li>
                                    </ul>
                                </div>
                            </div>
                        {/if}
                    </div>
                    <div class="widget_contents">

                    </div>
                </div>
            </div>
        {/if}
        {* Summary View Documents Widget Ends Here*}
    </div>
    {if $CAMPAIGN_ANALYTIC_WIDGET}
    <div class="left-block col-lg-8">
        {* Summary View Campaign Activity Widget Starts Here*}
        <div class="summaryView">
            <div class="summaryViewHeader">
                <h4 class="display-inline-block">{vtranslate('LBL_CAMPAIGN_ACTIVITY_ANALYTICS', $MODULE_NAME)}</h4>
            </div>
            {if !empty($CAMPAIGN_ACTIVITY_DATA)}
            <select class="inputElement select2" name="campaign_activity_list" id="campaign_activity_list">
                    {foreach item=ACTIVITY_NAME key=ACTIVITY_ID from=$CAMPAIGN_ACTIVITY_DATA}
                        <option value="{$ACTIVITY_ID}">{$ACTIVITY_NAME}</option>
                    {/foreach}
            </select>
            {else}
                <h5>{vtranslate('NO_CAMPAIGN_ACTIVITY_FOUND', $MODULE_NAME)}</h5>
            {/if}
            <div class="summaryViewFields">
                <div class="campaign_analytic_container">
                    <canvas id="campaignAnalyticChart" style="max-width: 500px;"></canvas>
                </div>
                <input type="hidden" id="campaignAnalyticData" value={$CAMPAIGN_ANALYTIC_DATA_JSON} />
                <input type="hidden" id="campaignId" value={$RECORD->getId()} />
            </div>
        </div>
        <!-- MDB core JavaScript -->
        <script type="text/javascript" src="layouts/v7/modules/Campaigns/resources/mdb.min.js"></script>
        <script>
            {literal}
                jQuery(document).ready(function(){
                    jQuery('#campaign_activity_list').on('change', function(e){
                        var campaignActivityId = jQuery('select[name="campaign_activity_list"] option:selected').val();
                        var campaignId = jQuery('#campaignId').val();
                        var params = {
                            'module' : 'CampaignActivity',
                            'action' : 'GetAnalyticsData',
                            'campaign_id' : campaignId,
                            'campaign_activity_id' : campaignActivityId,
                        };
                        app.request.post({'data' : params}).then(function(error, data) {
                            if(error == null) {
                                if(data.analytics_data !== '')
                                {
                                    jQuery("#campaignAnalyticData").val(data.analytics_data);
                                    jQuery("#campaignAnalyticChart").remove();
                                    var canvasid = 'campaignAnalyticChart'+Math.floor(Math.random() * (41));
                                    var canvasHtml = '<canvas id="'+canvasid+'" style="max-width: 500px;"></canvas>';
                                    jQuery('.campaign_analytic_container').html(canvasHtml);
                                    drawAnalyticChart(canvasid);
                                }
                                else
                                {
                                    var canvasHtml = '<h5>'+app.vtranslate('JS_NO_CAMPAIGN_ACTIVITY_FOUND')+'</h5>';
                                    jQuery('.campaign_analytic_container').html(canvasHtml);
                                }
                            }
                        });
                    });
                    jQuery('#campaign_activity_list').trigger('change');
                });
                function drawAnalyticChart(canvasid = '')
                {
                    if(canvasid !== '')
                    {
                        var ctxP = document.getElementById(canvasid).getContext('2d');
                    }
                    else
                    {
                        var ctxP = document.getElementById("campaignAnalyticChart").getContext('2d');
                    }
                    
                    var campaignAnalyticData = JSON.parse(jQuery("#campaignAnalyticData").val());
                    var campaignAnalyticChart = new Chart(ctxP, {
                        type: 'pie',
                        data: {
                            labels: ["Total Sent", "Email Open", "Clicked", "Email Unopened"],
                            datasets: [{
                                    data: campaignAnalyticData,
                                    backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1"],
                                    hoverBackgroundColor: ["#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5"]
                                }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                }
            {/literal}
        </script>
        {* Summary View Campaign Activity Widget Ends Here*}
    </div>
    {/if}
{/strip}