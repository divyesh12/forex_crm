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
{*                        <span class="toggleButton pull-left"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;</span>*}
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

    <div class="middle-block col-lg-8">

        {* Wallet Balance Widget Currency Wise Start Here*}
        {if $WALLET_BALANCE_WIDGET}
            <div class="summaryView">
                <div class="summaryViewHeader">
                    <h4 class="display-inline-block">{vtranslate('LBL_WALLET_BALANCE', $MODULE_NAME)}</h4>
                </div>
                <div class="summaryViewFields">
                    <div class="recordDetails">
                        <table class="summary-table no-border">
                            <tbody>
                                {if count($WALLET_BALANCE_DATA) > 0}
                                    {foreach key=INDEX item=BALANCE_DATA from=$WALLET_BALANCE_DATA}
                                        <tr class="summaryViewEntries">
                                            <td class="fieldLabel">
                                                <label class="muted textOverflowEllipsis"><b>{$BALANCE_DATA['currency']}</b></label>
                                            </td>
                                            <td class="fieldValue">
                                                <div class="row">
                                                    <span class="value textOverflowEllipsis">
                                                        {number_format($BALANCE_DATA['total_amount'],2)}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr class="summaryViewEntries">
                                        <td class="fieldLabel" style="text-align: center;">
                                            <label>Wallet balance empty</label>
                                        </td>
                                    </tr>
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}
        {* Wallet Balance Widget Currency Wise Start Here*}
        
        
        {* IB Summary widget Start Here*}
        {if $IB_SUMMARY_WIDGET}
        <div class="summaryView ib_comm_summary_widget summary-table">
            <div class="summaryViewHeader">
                <h4 class="display-inline-block">{vtranslate('LBL_IB_SUMMARY_WIDGET', $MODULE_NAME)}</h4>
            </div>
            <div class="summaryViewFields">
                <div class="recordDetails">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Total Commission Earned</th>
                                <th>Earned Commission Lots</th>
                                <th>Total Commission Withdraw</th>
                                <th>Available Commission Amount</th>
                                <th>Total Lots</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="total_comm_earned"></td>
                                <td id="earned_comm_lots"></td>
                                <td id="total_comm_withdraw"></td>
                                <td id="available_comm_amount"></td>
                                <td id="total_lots"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {/if}
        {* IB Summary widget end Here*}

        {* Top 5 Tickets Widget start here *}
        {if $TOP_5_TICKETS_WIDGET}
            <div class="summaryView top_5_tickets_widget summary-table">
                <div class="summaryViewHeader">
                    <h4 class="display-inline-block">{vtranslate('LBL_TOP_5_TICKETS_WIDGET', $MODULE_NAME)}</h4>
                    <div class="ticket-filter pull-right">
                        <input type="text" id="summarydaterange" name="summarydaterange" class="dateRangeFilter" />
                        <select name="ticket_status_filter" id="ticket_status_filter" class="ticket_status_filter summary-dropdown">
                            <option value="">All</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Wait For Response">Wait For Response</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="summaryViewFields">
                    <div class="recordDetails">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Ticket Number</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Department</th>
                                    <th>Created Date</th>
                                    <th>Closed Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="ticket_number"></td>
                                    <td id="subject"></td>
                                    <td id="priority"></td>
                                    <td id="department"></td>
                                    <td id="created_date"></td>
                                    <td id="closed_date"></td>
                                    <td id="status"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="more-btn-block text-right">
                            <a class="btn-more-summary" data-module="HelpDesk">More</a>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {* Top 5 Tickets Widget end here *}        
        
        {* Contact Summary widget Start Here*}
        {if $CONTACT_SUMMARY_WIDGET}
            <div class="summaryView contact_summary_widget summary-table">
                <div class="summaryViewHeader">
                    <h4 class="display-inline-block">{vtranslate('LBL_CONTACT_SUMMARY_WIDGET', $MODULE_NAME)}</h4>
                    {if count($CURRENCY_CODES) > 0}
                        <select name="contact_currency" id="contact_currency" class="pull-right contact_currency summary-dropdown">
                            {foreach key=INDEX item=CURRENCY from=$CURRENCY_CODES}
                                <option value="{$CURRENCY}">{$CURRENCY}</option>
                            {/foreach}
                        </select>
                    {/if}
                </div>
                <div class="summaryViewFields">
                    <div class="recordDetails">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Total Deposit</th>
                                    <th>Total Withdrawal</th>
                                    <th>Total Live Account</th>
                                    <th>Total Demo Account</th>
                                    <th>Total Lots</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="total_deposit"></td>
                                    <td id="total_withdrawal"></td>
                                    <td id="total_live_account"></td>
                                    <td id="total_demo_account"></td>
                                    <td id="total_lots_contact"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}
        {* Contact Summary widget end Here*}

        {* Top 5 Transaction Widget start here *}
        {if $TOP_5_TRANSACTIONS_WIDGET}
            <div class="summaryView top_5_transactions_widget summary-table">
                <div class="summaryViewHeader">
                    <h4 class="display-inline-block">{vtranslate('LBL_TOP_5_TRANSACTIONS_WIDGET', $MODULE_NAME)}</h4>
                    <select name="status_filter" id="status_filter" class="pull-right status_filter summary-dropdown">
                        <option value="">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Failed">Failed</option>
                        <option value="Rejected">Rejected</option>                        
                    </select>
                </div>
                <div class="summaryViewFields">
                    <div class="recordDetails">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Payment From</th>
                                    <th>Payment To</th>
                                    <th>Payment Operation</th>
                                    <th>Amount</th>
                                    {* <th>Currency</th> *}
                                    <th>Status</th>
                                    <th>Reject Reason</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="payment_from"></td>
                                    <td id="payment_to"></td>
                                    <td id="payment_operation"></td>
                                    <td id="amount"></td>
                                    {* <td id="payment_currency"></td> *}
                                    <td id="payment_status"></td>
                                    <td id="failure_reason"></td>
                                    <td id="created_time"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="more-btn-block text-right">
                            <a class="btn-more-summary" data-module="Payments">More</a>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {* Top 5 Transaction Widget end here *}

        {* Top 5 Live Accounts Widget start here *}
        {if $TOP_5_LIVEACCOUNTS_WIDGET}
            <div class="summaryView top_5_liveaccounts_widget summary-table">
                <div class="summaryViewHeader">
                    <h4 class="display-inline-block">{vtranslate('LBL_TOP_5_LIVEACCOUNTS_WIDGET', $MODULE_NAME)}</h4>
                    <select name="live_status_filter" id="live_status_filter" class="pull-right live_status_filter summary-dropdown">
                        <option value="">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Disapproved">Disapproved</option>                        
                    </select>
                </div>
                <div class="summaryViewFields">
                    <div class="recordDetails">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Account Number</th>
                                    <th>Account Type</th>
                                    <th>Currency</th>
                                    <th>Leverage</th>
                                    <th>Meta Trader Type</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="account_no"></td>
                                    <td id="live_label_account_type"></td>
                                    <td id="live_currency_code"></td>
                                    <td id="leverage"></td>
                                    <td id="live_metatrader_type"></td>
                                    <td id="record_status"></td>
                                    <td id="created_date"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="more-btn-block text-right">
                            <a class="btn-more-summary" data-module="LiveAccount">More</a>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {* Top 5 Live Accounts Widget end here *}   

        {* Payment Summary widget Start Here*}
        <div class="summaryView payment_summary_widget summary-table">
            <div class="summaryViewHeader">
                <h4 class="display-inline-block">{vtranslate('LBL_PAYMENT_SUMMARY_WIDGET', $MODULE_NAME)}</h4>
            </div>
            <div class="summaryViewFields">
                <div class="recordDetails">
                    <table class="table table-bordered" style="margin-bottom: -1px;">
                            <thead>
                                {if COUNT($PAYMENT_SUMMARY_DATA) <= 5 }
                                    <tr>
                                        <th style="text-align: center;width: 25.3%;">{vtranslate('LBL_PAYMENT_GETWAY_NAME', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25.6%;">{vtranslate('LBL_DEPOSIT', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25.6%;">{vtranslate('LBL_WITHDRAW', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25%;">{vtranslate('LBL_DIFFERENTIATION', $MODULE_NAME)}</th>
                                    </tr>
                                {else}
                                    <tr>
                                        <th style="text-align: center;width: 25%;">{vtranslate('LBL_PAYMENT_GETWAY_NAME', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25%;">{vtranslate('LBL_DEPOSIT', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25%;">{vtranslate('LBL_WITHDRAW', $MODULE_NAME)}</th>
                                        <th style="text-align: center;width: 25%;">{vtranslate('LBL_DIFFERENTIATION', $MODULE_NAME)}</th>
                                    </tr>
                                {/if}
                            </thead>
                    </table>
                </div>
                <div class="recordDetails" style="height: 200px;overflow-y: auto;overflow-x: hidden;">   
                    <table class="table table-bordered">
                        <tbody>
                            {if COUNT($PAYMENT_SUMMARY_DATA) }
                                {foreach item=PAYMENT_AMOUNT key=PAYMENT_NAME from=$PAYMENT_SUMMARY_DATA}
                                    {assign var=DIFFERENTIATION_AMOUNT value=($PAYMENT_AMOUNT['total_deposit'] - $PAYMENT_AMOUNT['total_withdrw'])}
                                    <tr>
                                        <td  style="width: 25.3%;">{$PAYMENT_NAME}</td>
                                        <td  style="width: 25.6%;">{number_format($PAYMENT_AMOUNT['total_deposit'],2)}</td>
                                        <td  style="width: 25.5%;">{number_format($PAYMENT_AMOUNT['total_withdrw'],2)}</td>
                                        <td  style="width: 25.3%;">{number_format($DIFFERENTIATION_AMOUNT,2)}</td>
                                    </tr>
                                {/foreach}
                            {else}
                            <tr>
                                <td colspan=4 style="text-align: center;">{vtranslate('LBL_TRANSACTION_NOT_FOUND', $MODULE_NAME)}</td>
                            </tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="more-btn-block text-right" style="margin-top: 25px;"><a class="btn-more-summary" data-module="Payments">More</a></div>
        </div>
        {* Payment Summary widget end Here*}

        {* Summary View Related Activities Widget*}
        <div id="relatedActivities">
            {$RELATED_ACTIVITIES}
        </div>
        {* Summary View Related Activities Widget Ends Here*}

        {* Summary View Comments Widget*}
        {if $COMMENTS_WIDGET_MODEL}
            <div class="summaryWidgetContainer">
                <div class="widgetContainer_comments" data-url="{$COMMENTS_WIDGET_MODEL->getUrl()}" data-name="{$COMMENTS_WIDGET_MODEL->getLabel()}">
                    <div class="widget_header">
                        <input type="hidden" name="relatedModule" value="{$COMMENTS_WIDGET_MODEL->get('linkName')}" />
                        <h4 class="display-inline-block">{vtranslate($COMMENTS_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4>
                    </div>
                    <div class="widget_contents">
                    </div>
                </div>
            </div>
        {/if}
        {* Summary View Comments Widget Ends Here*}
    </div>
{/strip}