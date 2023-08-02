<tr>
    <td class="fieldLabel col-lg-2">
        <label class="muted pull-right">Trigger Time&nbsp; <span class="redColor">*</span> </label>
    </td>
    <td class="fieldValue col-lg-4" colspan="3">
        <div class="well">
            <div class="form-group">
                <div class="col-sm-10 controls">
                    <select class="select2" id="schtypeid" name="schtypeid" style="min-width: 150px;" tabindex="-1" title="Run Workflow" aria-invalid="false">
                        <option value="1" {if $TRIGGER_TIME_MODEL_OBJ->schtypeid eq 1}selected{/if}>Hourly</option>
                        <option value="2" {if $TRIGGER_TIME_MODEL_OBJ->schtypeid eq 2}selected{/if}>Daily</option>
                        <option value="3" {if $TRIGGER_TIME_MODEL_OBJ->schtypeid eq 3}selected{/if}>Weekly</option>
                        <option value="4" {if $TRIGGER_TIME_MODEL_OBJ->schtypeid eq 4}selected{/if}>On Specific Date</option>
                        <option value="5" {if $TRIGGER_TIME_MODEL_OBJ->schtypeid eq 5}selected{/if}>Monthly by Date</option>
                        <!--option value="6" >Monthly by Weekday</option-->
                        <!--option value="7">Yearly</option-->
                    </select>
                </div>
            </div>
            <div class="form-group  {if $TRIGGER_TIME_MODEL_OBJ->schtypeid neq 3} hide {/if}" id="scheduledWeekDay">
                <label class="col-sm-2 control-label" style="position:relative;top:5px;">On these days<span class="redColor">*</span></label>
                <div class="col-sm-10 controls" style="padding-top: 15px; padding-bottom: 15px;">
                    {assign var=dayOfWeek value=Zend_Json::decode($TRIGGER_TIME_MODEL_OBJ->schdayofweek)}
                    <div class="weekDaySelect ui-selectable">
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("7",$dayOfWeek)}ui-selected{/if}" data-value="7"> Sunday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("1",$dayOfWeek)}ui-selected{/if}" data-value="1"> Monday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("2",$dayOfWeek)}ui-selected{/if}" data-value="2"> Tuesday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("3",$dayOfWeek)}ui-selected{/if}" data-value="3"> Wednesday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("4",$dayOfWeek)}ui-selected{/if}" data-value="4"> Thursday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("5",$dayOfWeek)}ui-selected{/if}" data-value="5"> Friday </span>
                        <span class="ui-state-default  {if is_array($dayOfWeek) && in_array("6",$dayOfWeek)}ui-selected{/if}" data-value="6"> Saturday </span>
                        <input type="hidden" data-rule-required="true" name="schdayofweek" id="schdayofweek" {if is_array($dayOfWeek)} value="{implode(',',$dayOfWeek)}" {else} value=""{/if} aria-required="true" class="ui-selectee" style="">
                    </div>
                </div>
            </div>
            <div class="form-group  {if $TRIGGER_TIME_MODEL_OBJ->schtypeid neq 5} hide {/if}" id="scheduleMonthByDates" style="padding:5px 0px;">
                <label class="col-sm-2 control-label">On these days<span class="redColor">*</span></label>
                <div class="col-sm-4 controls">
                    {assign var=DAYS value=Zend_Json::decode($TRIGGER_TIME_MODEL_OBJ->schdayofmonth)}
                    <select style="width:150px;" multiple="" class="select2" data-rule-required="true" name="schdayofmonth[]" id="schdayofmonth" tabindex="-1" aria-required="true">
                        {section name=foo loop=31}
                            <option value={$smarty.section.foo.iteration} {if is_array($DAYS) && in_array($smarty.section.foo.iteration, $DAYS)}selected{/if}>{$smarty.section.foo.iteration}</option>
                        {/section}
                    </select>
                </div>
            </div>
            <div class="form-group  {if $TRIGGER_TIME_MODEL_OBJ->schtypeid neq 4} hide {/if}" id="scheduleByDate" style="padding:5px 0px;"><label class="col-sm-2 control-label">Choose Date<span class="redColor">*</span></label>
                <div class="col-sm-3 controls">
                    <div class="input-group" style="margin-bottom: 3px">
                        {assign var=specificDate value=Zend_Json::decode($TRIGGER_TIME_MODEL_OBJ->schannualdates)}
                        {if $specificDate[0] neq ''} 
                            {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])} 
                        {/if}
                        <input test="{$specificDate|print_r}" type="text" class="dateField form-control {$specificDate[0]}" name="schdate" value="{$specificDate1}" data-date-format="{$CURRENT_USER->date_format}" data-rule-required="true" aria-required="true">
                        <span class="input-group-addon"><i class="fa fa-calendar "></i></span>
                    </div>
                </div>
            </div>
            {*<div class="form-group  hide" id="scheduleAnually">
                <label class="col-sm-2 control-label"> Select Month and Date <span class="redColor">*</span> </label>
                <div class="col-sm-6 controls">
                    <div id="annualDatePicker" class="hasDatepick">
                        <div class="datepick datepick-multi" style="width: 326px;">
                            <div class="datepick-nav">
                                <a href="javascript:void(0)" title="Show the previous month" class="datepick-cmd datepick-cmd-prev ">&lt;Prev</a>
                                <a href="javascript:void(0)" title="Show today's month" class="datepick-cmd datepick-cmd-today ">Today</a>
                                <a href="javascript:void(0)" title="Show the next month" class="datepick-cmd datepick-cmd-next ">Next&gt;</a>
                            </div>
                            <div class="datepick-month-row">
                                <div class="datepick-month first">
                                    <div class="datepick-month-header">
                                        <select class="datepick-month-year" title="Change the month">
                                            <option value="/2020">January</option><option value="2/2020">February</option><option value="3/2020">March</option><option value="4/2020">April</option><option value="5/2020">May</option><option value="6/2020">June</option><option value="7/2020">July</option><option value="8/2020">August</option><option value="9/2020">September</option><option value="10/2020">October</option><option value="11/2020" selected="selected">November</option><option value="12/2020">December</option></select> <select class="datepick-month-year" title="Change the year" style="display: none;"><option value="11/2020" selected="selected">2020</option>
                                        </select>
                                    </div>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>
                                                    <span class="datepick-dow-0" title="Sunday">Su</span></th><th><span class="datepick-dow-1" title="Monday">Mo</span></th><th><span class="datepick-dow-2" title="Tuesday">Tu</span></th><th><span class="datepick-dow-3" title="Wednesday">We</span></th><th><span class="datepick-dow-4" title="Thursday">Th</span></th><th><span class="datepick-dow-5" title="Friday">Fr</span></th><th><span class="datepick-dow-6" title="Saturday">Sa</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><span class="dp1603607400000  datepick-weekend datepick-other-month">&nbsp;</span></td><td><span class="dp1603693800000  datepick-other-month">&nbsp;</span></td><td><span class="dp1603780200000  datepick-other-month">&nbsp;</span></td><td><span class="dp1603866600000  datepick-other-month">&nbsp;</span></td><td><span class="dp1603953000000  datepick-other-month">&nbsp;</span></td><td><span class="dp1604039400000  datepick-other-month">&nbsp;</span></td><td><span class="dp1604125800000  datepick-weekend datepick-other-month">&nbsp;</span></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1604212200000   datepick-weekend" title="Select Sunday, Nov 1, 2020">1</a></td><td><a href="javascript:void(0)" class="dp1604298600000  " title="Select Monday, Nov 2, 2020">2</a></td><td><a href="javascript:void(0)" class="dp1604385000000  " title="Select Tuesday, Nov 3, 2020">3</a></td><td><a href="javascript:void(0)" class="dp1604471400000  " title="Select Wednesday, Nov 4, 2020">4</a></td><td><a href="javascript:void(0)" class="dp1604557800000  " title="Select Thursday, Nov 5, 2020">5</a></td><td><a href="javascript:void(0)" class="dp1604644200000  " title="Select Friday, Nov 6, 2020">6</a></td><td><a href="javascript:void(0)" class="dp1604730600000   datepick-weekend" title="Select Saturday, Nov 7, 2020">7</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1604817000000   datepick-weekend" title="Select Sunday, Nov 8, 2020">8</a></td><td><a href="javascript:void(0)" class="dp1604903400000  " title="Select Monday, Nov 9, 2020">9</a></td><td><a href="javascript:void(0)" class="dp1604989800000  " title="Select Tuesday, Nov 10, 2020">10</a></td><td><a href="javascript:void(0)" class="dp1605076200000  " title="Select Wednesday, Nov 11, 2020">11</a></td><td><a href="javascript:void(0)" class="dp1605162600000  " title="Select Thursday, Nov 12, 2020">12</a></td><td><a href="javascript:void(0)" class="dp1605249000000  " title="Select Friday, Nov 13, 2020">13</a></td><td><a href="javascript:void(0)" class="dp1605335400000   datepick-weekend" title="Select Saturday, Nov 14, 2020">14</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1605421800000   datepick-weekend" title="Select Sunday, Nov 15, 2020">15</a></td><td><a href="javascript:void(0)" class="dp1605508200000  " title="Select Monday, Nov 16, 2020">16</a></td><td><a href="javascript:void(0)" class="dp1605594600000  " title="Select Tuesday, Nov 17, 2020">17</a></td><td><a href="javascript:void(0)" class="dp1605681000000  " title="Select Wednesday, Nov 18, 2020">18</a></td><td><a href="javascript:void(0)" class="dp1605767400000  " title="Select Thursday, Nov 19, 2020">19</a></td><td><a href="javascript:void(0)" class="dp1605853800000  " title="Select Friday, Nov 20, 2020">20</a></td><td><a href="javascript:void(0)" class="dp1605940200000   datepick-weekend" title="Select Saturday, Nov 21, 2020">21</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1606026600000   datepick-weekend" title="Select Sunday, Nov 22, 2020">22</a></td><td><a href="javascript:void(0)" class="dp1606113000000  " title="Select Monday, Nov 23, 2020">23</a></td><td><a href="javascript:void(0)" class="dp1606199400000  " title="Select Tuesday, Nov 24, 2020">24</a></td><td><a href="javascript:void(0)" class="dp1606285800000   datepick-today datepick-highlight" title="Select Wednesday, Nov 25, 2020">25</a></td><td><a href="javascript:void(0)" class="dp1606372200000  " title="Select Thursday, Nov 26, 2020">26</a></td><td><a href="javascript:void(0)" class="dp1606458600000  " title="Select Friday, Nov 27, 2020">27</a></td><td><a href="javascript:void(0)" class="dp1606545000000   datepick-weekend" title="Select Saturday, Nov 28, 2020">28</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1606631400000   datepick-weekend" title="Select Sunday, Nov 29, 2020">29</a></td><td><a href="javascript:void(0)" class="dp1606717800000  " title="Select Monday, Nov 30, 2020">30</a></td><td><span class="dp1606804200000  datepick-other-month">&nbsp;</span></td><td><span class="dp1606890600000  datepick-other-month">&nbsp;</span></td><td><span class="dp1606977000000  datepick-other-month">&nbsp;</span></td><td><span class="dp1607063400000  datepick-other-month">&nbsp;</span></td><td><span class="dp1607149800000  datepick-weekend datepick-other-month">&nbsp;</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="datepick-month last">
                                    <div class="datepick-month-header">December </div>
                                    <table><thead><tr><th><span class="datepick-dow-0" title="Sunday">Su</span></th><th><span class="datepick-dow-1" title="Monday">Mo</span></th><th><span class="datepick-dow-2" title="Tuesday">Tu</span></th><th><span class="datepick-dow-3" title="Wednesday">We</span></th><th><span class="datepick-dow-4" title="Thursday">Th</span></th><th><span class="datepick-dow-5" title="Friday">Fr</span></th><th><span class="datepick-dow-6" title="Saturday">Sa</span></th></tr></thead><tbody><tr><td><span class="dp1606631400000  datepick-weekend datepick-other-month">&nbsp;</span></td><td><span class="dp1606717800000  datepick-other-month">&nbsp;</span></td><td><a href="javascript:void(0)" class="dp1606804200000  " title="Select Tuesday, Dec 1, 2020">1</a></td><td><a href="javascript:void(0)" class="dp1606890600000  " title="Select Wednesday, Dec 2, 2020">2</a></td><td><a href="javascript:void(0)" class="dp1606977000000  " title="Select Thursday, Dec 3, 2020">3</a></td><td><a href="javascript:void(0)" class="dp1607063400000  " title="Select Friday, Dec 4, 2020">4</a></td><td><a href="javascript:void(0)" class="dp1607149800000   datepick-weekend" title="Select Saturday, Dec 5, 2020">5</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1607236200000   datepick-weekend" title="Select Sunday, Dec 6, 2020">6</a></td><td><a href="javascript:void(0)" class="dp1607322600000  " title="Select Monday, Dec 7, 2020">7</a></td><td><a href="javascript:void(0)" class="dp1607409000000  " title="Select Tuesday, Dec 8, 2020">8</a></td><td><a href="javascript:void(0)" class="dp1607495400000  " title="Select Wednesday, Dec 9, 2020">9</a></td><td><a href="javascript:void(0)" class="dp1607581800000  " title="Select Thursday, Dec 10, 2020">10</a></td><td><a href="javascript:void(0)" class="dp1607668200000  " title="Select Friday, Dec 11, 2020">11</a></td><td><a href="javascript:void(0)" class="dp1607754600000   datepick-weekend" title="Select Saturday, Dec 12, 2020">12</a></td></tr>
                                            <tr><td><a href="javascript:void(0)" class="dp1607841000000   datepick-weekend" title="Select Sunday, Dec 13, 2020">13</a></td><td><a href="javascript:void(0)" class="dp1607927400000  " title="Select Monday, Dec 14, 2020">14</a></td><td><a href="javascript:void(0)" class="dp1608013800000  " title="Select Tuesday, Dec 15, 2020">15</a></td><td><a href="javascript:void(0)" class="dp1608100200000  " title="Select Wednesday, Dec 16, 2020">16</a></td><td><a href="javascript:void(0)" class="dp1608186600000  " title="Select Thursday, Dec 17, 2020">17</a></td><td><a href="javascript:void(0)" class="dp1608273000000  " title="Select Friday, Dec 18, 2020">18</a></td><td><a href="javascript:void(0)" class="dp1608359400000   datepick-weekend" title="Select Saturday, Dec 19, 2020">19</a></td></tr><tr><td><a href="javascript:void(0)" class="dp1608445800000   datepick-weekend" title="Select Sunday, Dec 20, 2020">20</a></td><td><a href="javascript:void(0)" class="dp1608532200000  " title="Select Monday, Dec 21, 2020">21</a></td><td><a href="javascript:void(0)" class="dp1608618600000  " title="Select Tuesday, Dec 22, 2020">22</a></td><td><a href="javascript:void(0)" class="dp1608705000000  " title="Select Wednesday, Dec 23, 2020">23</a></td><td><a href="javascript:void(0)" class="dp1608791400000  " title="Select Thursday, Dec 24, 2020">24</a></td><td><a href="javascript:void(0)" class="dp1608877800000  " title="Select Friday, Dec 25, 2020">25</a></td><td><a href="javascript:void(0)" class="dp1608964200000   datepick-weekend" title="Select Saturday, Dec 26, 2020">26</a></td></tr><tr><td><a href="javascript:void(0)" class="dp1609050600000   datepick-weekend" title="Select Sunday, Dec 27, 2020">27</a></td><td><a href="javascript:void(0)" class="dp1609137000000  " title="Select Monday, Dec 28, 2020">28</a></td><td><a href="javascript:void(0)" class="dp1609223400000  " title="Select Tuesday, Dec 29, 2020">29</a></td><td><a href="javascript:void(0)" class="dp1609309800000  " title="Select Wednesday, Dec 30, 2020">30</a></td><td><a href="javascript:void(0)" class="dp1609396200000  " title="Select Thursday, Dec 31, 2020">31</a></td><td><span class="dp1609482600000  datepick-other-month">&nbsp;</span></td><td><span class="dp1609569000000  datepick-weekend datepick-other-month">&nbsp;</span></td></tr><tr><td><span class="dp1609655400000  datepick-weekend datepick-other-month">&nbsp;</span></td><td><span class="dp1609741800000  datepick-other-month">&nbsp;</span></td><td><span class="dp1609828200000  datepick-other-month">&nbsp;</span></td><td><span class="dp1609914600000  datepick-other-month">&nbsp;</span></td><td><span class="dp1610001000000  datepick-other-month">&nbsp;</span></td><td><span class="dp1610087400000  datepick-other-month">&nbsp;</span></td><td><span class="dp1610173800000  datepick-weekend datepick-other-month">&nbsp;</span></td></tr></tbody></table></div></div><div class="datepick-clear-fix"></div></div></div></div><div class="col-sm-4 controls"><label style="padding-bottom:5px;">Selected Dates</label><div><input type="hidden" id="hiddenAnnualDates" value="">

                        <select multiple="" class="select2" id="annualDates" name="schannualdates[]" data-rule-required="true" style="min-width: 100px;" tabindex="-1" aria-required="true" aria-invalid="false"></select></div></div></div>*}
            <div class="form-group {if $TRIGGER_TIME_MODEL_OBJ->schtypeid < 2} hide {/if}" id="scheduledTime" style="padding:5px 0px 10px 0px;">
                <label for="schtime" class="col-sm-2 control-label">At Time <span class="redColor">*</span></label>
                <div class="col-sm-2 controls" id="schtime">
                    <div class="input-group time">
                        <input type="text" data-format="24" name="schtime" value="{$TRIGGER_TIME_MODEL_OBJ->schtime}" data-rule-required="true" class="timepicker-default inputElement ui-timepicker-input" autocomplete="off" aria-required="true">
                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>