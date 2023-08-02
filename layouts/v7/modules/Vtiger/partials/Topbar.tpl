{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{include file="modules/Vtiger/Header.tpl"}	
	{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
	<nav class="navbar navbar-default navbar-fixed-top app-fixed-navbar">
		<div class="container-fluid global-nav">
			<div class="row">
				<div class="col-lg-2 col-md-3 col-sm-3 app-navigator-container">
					<div class="row">
						<div id="appnavigator" class="col-sm-2 col-xs-2 cursorPointer app-switcher-container" data-app-class="{if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}">
							<div class="row app-navigator">
								<span class="app-icon fa fa-bars"></span>
							</div>
						</div>
						<div class="logo-container col-lg-9 col-md-9 col-sm-9 col-xs-9">
							<div class="row">
								<a href="index.php" class="company-logo">
									<img src="{$COMPANY_LOGO->get('imagepath')}" alt="{$COMPANY_LOGO->get('alt')}"/>
								</a>
							</div>
						</div>  
					</div>
				</div>
				<div class="search-links-container col-md-3 col-lg-2 col-sm-3">
					<div class="search-link hidden-xs">
						<span class="fa fa-search" aria-hidden="true"></span>
						<input class="keyword-input" type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}">
						<span id="adv-search" class="adv-search fa fa-chevron-circle-down pull-right cursorPointer" aria-hidden="true"></span>
					</div>
				</div>
				<!-- Add By Divyesh 
					Comment:- Contact Creation restriction base on subscription package contact Annousment Error Messege -->
				{assign var=contactCreationResult value=contactCreationRestrictBaseOnPkg()}
				{assign var=userCreationResult value=usersCreationRestrictBaseOnPkg()}
				{* {assign var=paymentGetwayCreationResult value=paymentGetwayCreationRestrictBaseOnPkg()}
				{assign var=tradingPlatformCreationResult value=tradingPlatformCreationRestrictBaseOnPkg()} *}
				<div class="col-md-3 col-lg-6 col-sm-3" style="color: red;font-family: system-ui;font-size: 17px;margin-top: 10px;">
					<marquee width="100%" direction="left" height="68%"  scrollamount="7">
						{if $contactCreationResult['success'] neq 1 }<span><i class="fa fa-circle" style="font-size: 7px;margin-right: 2px;position: relative;top: -3px;margin-left: 20px;"></i>&nbsp;{$contactCreationResult['message']}&nbsp;</span>
						{/if}
						{* {if $paymentGetwayCreationResult['success'] neq 1 }<span><i class="fa fa-circle" style="font-size: 7px;margin-right: 2px;position: relative;top: -3px;margin-left: 20px;"></i>&nbsp;{$paymentGetwayCreationResult['message']}&nbsp;</span>
						{/if}
						{if $tradingPlatformCreationResult['success'] neq 1 }<span><i class="fa fa-circle" style="font-size: 7px;margin-right: 2px;position: relative;top: -3px;margin-left: 20px;"></i>&nbsp;{$tradingPlatformCreationResult['message']}&nbsp;</span>
						{/if} *}
						{if $userCreationResult['success'] neq 1 }<span><i class="fa fa-circle" style="font-size: 7px;margin-right: 2px;position: relative;top: -3px;margin-left: 20px;"></i>&nbsp;{$userCreationResult['message']}&nbsp;</span>
						{/if}
				</marquee>
				</div>
				<!-- end -->
				<div id="navbar" class="col-sm-6 col-md-3 col-lg-3 collapse navbar-collapse navbar-right global-actions">
					<ul class="nav navbar-nav">
                                            {if vtlib_isModuleActive('Notifications')}
                                                {assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
						{assign var=NOTIFICATIONS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Notifications')}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($NOTIFICATIONS_MODULE_MODEL->getId())}
                                                    {assign var=IS_CRM_NOTIFICATION_ALLOW value=configvar('is_crm_notification_allow')}
                                                    {if $IS_CRM_NOTIFICATION_ALLOW}
                                                        {assign var=NOTIFICATION_SETTINGS value=Notifications_Module_Model::getSettings()}
                                                        <li class="dropdown notification-container">
                                                        <audio class="notification_sound"><source src="layouts/v7/modules/Notifications/resources/sound/beep.mp3"></source><source src="layouts/v7/modules/Notifications/resources/sound/beep.ogg"></source></audio>
                                                        <div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                                <a href="#" id="menubar_quickCreate" class="qc-button fa fa-bell" title="{vtranslate('LBL_NOTIFICATIONS',$MODULE)}" aria-hidden="true"><span class="badge badge-light notification-badge">0</span></a>
                                                        </div>
                                                        <ul class="dropdown-menu notify-drop">
                                                            <div class="notify-drop-title">
                                                                <div class="row">
                                                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                                                            <strong>{vtranslate('LBL_NOTIFICATIONS',$MODULE)}</strong>
                                                                        </div>
                                                                        <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                                                            <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{vtranslate('LBL_NOTIFICATIONS_TOPBAR_INFO',$MODULE)}"></i>
                                                                            <a href="javascript:void(0);" class="rIcon clear_all_notifications" data-tooltip="tooltip" data-placement="bottom" title="" data-original-title="Clear all">Clear all</a>
                                                                        </div>
                                                                </div>
                                                            </div>
                                                          <!-- end notify title -->
                                                          <!-- notify content -->
                                                            <div class="drop-content notification-drop-content">

                                                            </div>
                                                            <div class="notify-drop-footer text-center">
                                                                <span class="pull-left">
                                                                    <span class="notification-user-setting notification-sound-setting m-r-5">
                                                                        <span class="m-r-5">Sound</span>
                                                                        <label class="switch">
                                                                            <input type="checkbox" name="sound_setting" {if $NOTIFICATION_SETTINGS['sound'] eq 1}checked{/if}>
                                                                            <span class="slider round"></span>
                                                                        </label>
                                                                    </span>
                                                                    <span class="notification-user-setting notification-message-setting">
                                                                        <span class="m-r-5">Message</span>
                                                                        <label class="switch">
                                                                            <input type="checkbox" name="message_setting" {if $NOTIFICATION_SETTINGS['message'] eq 1}checked{/if}>
                                                                            <span class="slider round"></span>
                                                                        </label>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </ul>
                                                    </li>
                                                    {/if}
                                                {/if}
                                            {/if}
						<li>
							<div class="dropdown">
								<div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
									<a href="#" id="menubar_quickCreate" class="qc-button fa fa-plus-circle" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true"></a>
								</div>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" style="width:500px;">
									<li class="title" style="padding: 5px 0 0 15px;">
										<strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
									</li>
									<hr/>
									<li id="quickCreateModules" style="padding: 0 5px;">
										<div class="col-lg-12" style="padding-bottom:15px;">
											{foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
												{if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
													{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
													{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
													{assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
													{if $quickCreateModule == '1'}
														{if $count % 3 == 0}
															<div class="row">
															{/if}
															{* Adding two links,Event and Task if module is Calendar *}
															{if $singularLabel == 'SINGLE_Calendar'}
																{assign var='singularLabel' value='LBL_TASK'}
																<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
																	<a id="menubar_quickCreate_Events" class="quickCreateModule" data-name="Events"
																	   data-url="index.php?module=Events&view=QuickCreateAjax" href="javascript:void(0)">{$moduleModel->getModuleIcon('Event')}<span class="quick-create-module">{vtranslate('LBL_EVENT',$moduleName)}</span></a>
																</div>
																{if $count % 3 == 2}
																	</div>
																	<br>
																	<div class="row">
																{/if}
																<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
																	<a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
																	   data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">{$moduleModel->getModuleIcon('Task')}<span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span></a>
																</div>
																{if !$hideDiv}
																	{assign var='count' value=$count+1}
																{/if}
															{else if $singularLabel == 'SINGLE_Documents'}
																<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if} dropdown">
																	<a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModuleSubmenu dropdown-toggle" data-name="{$moduleModel->getName()}" data-toggle="dropdown" 
																	   data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
																		{$moduleModel->getModuleIcon(true)}
																		<span class="quick-create-module">
																			{vtranslate($singularLabel,$moduleName)}
																			<i class="fa fa-caret-down quickcreateMoreDropdownAction"></i>
																		</span>
																	</a>
																	<ul class="dropdown-menu quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
																		<li class="dropdown-header"><i class="fa fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', $moduleName)}</li>
																		<li id="VtigerAction">
																			<a href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
																				<img style="  margin-top: -3px;margin-right: 4%;" title="Vtiger" alt="Vtiger" src="layouts/v7/skins//images/Vtiger.png">
																				{vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_VTIGER', $moduleName)})}
																			</a>
																		</li>
																		<li class="dropdown-header"><i class="fa fa-link"></i> {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</li>
																		<li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E')">&nbsp;<i class="fa fa-external-link"></i>&nbsp;&nbsp; {vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</a></li>
																		<li role="separator" class="divider"></li>
																		<li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W')"><i class="fa fa-file-text"></i> {vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</a></li>
																	</ul>
																</div>
															{else}
																<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4{/if}">
																	<a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
																	   data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
																		{$moduleModel->getModuleIcon(true)}
																		<span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
																	</a>
																</div>
															{/if}
															{if $count % 3 == 2}
																</div>
																<br>
															{/if}
														{if !$hideDiv}
															{assign var='count' value=$count+1}
														{/if}
													{/if}
												{/if}
											{/foreach}
										</div>
									</li>
								</ul>
							</div>
						</li>
						{assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
						{assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Calendar')}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
							<li><div><a href="index.php?module=Calendar&view={$CALENDAR_MODULE_MODEL->getDefaultViewName()}" class="fa fa-calendar" title="{vtranslate('Calendar','Calendar')}" aria-hidden="true"></a></div></li>
						{/if}
						{assign var=REPORTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Reports')}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($REPORTS_MODULE_MODEL->getId())}
							<li><div><a href="index.php?module=Reports&view=List" class="fa fa-bar-chart" title="{vtranslate('Reports','Reports')}" aria-hidden="true"></a></div></li>
						{/if}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
							<li><div><a href="#" class="taskManagement vicon vicon-task" title="{vtranslate('Tasks','Vtiger')}" aria-hidden="true"></a></div></li>
						{/if}
						<li class="dropdown">
							<div style="margin-top: 15px;">
								<a href="#" class="userName dropdown-toggle" data-toggle="dropdown" role="button">
									<span class="fa fa-user" aria-hidden="true" title="{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}
										  ({$USER_MODEL->get('user_name')})"></span>
									<span class="link-text-xs-only hidden-lg hidden-md hidden-sm">{$USER_MODEL->getName()}</span>
								</a>
								<div class="dropdown-menu logout-content" role="menu">
									<div class="row">
										<div class="col-lg-4 col-sm-4">
											<div class="profile-img-container">
												{assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
												{if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
													<i class='vicon-vtigeruser' style="font-size:90px"></i>
												{else}
													{foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
														{if !empty($IMAGE_INFO.url)}
															<img src="{$IMAGE_INFO.url}" width="100px" height="100px">
														{/if}
													{/foreach}
												{/if}
											</div>
										</div>
										<div class="col-lg-8 col-sm-8">
											<div class="profile-container">
												<h4>{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}</h4>
												<h5 class="textOverflowEllipsis" title='{$USER_MODEL->get('user_name')}'>{$USER_MODEL->get('user_name')}</h5>
												<p>{$USER_MODEL->getUserRoleName()}</p>
											</div>
										</div>
									</div>
									<div class="logout-footer clearfix">
										<hr style="margin: 10px 0 !important">
										<div class="">
											<span class="pull-left">
												<span class="fa fa-cogs"></span>
												<a id="menubar_item_right_LBL_MY_PREFERENCES" href="{$USER_MODEL->getPreferenceDetailViewUrl()}">{vtranslate('LBL_MY_PREFERENCES')}</a>
											</span>
											<span class="pull-right">
												<span class="fa fa-power-off"></span>
												<a id="menubar_item_right_LBL_SIGN_OUT" href="index.php?module=Users&action=Logout">{vtranslate('LBL_SIGN_OUT')}</a>
											</span>
										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>	
		</div>
{/strip}


