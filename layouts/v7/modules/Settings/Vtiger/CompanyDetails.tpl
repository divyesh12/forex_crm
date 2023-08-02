{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Vtiger/views/CompanyDetails.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}

{strip}

	<div class=" col-lg-12 col-md-12 col-sm-12">
		<input type="hidden" id="supportedImageFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}' />
		<input type="hidden" id="bannerSupportedFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$bannerSupportedFormats)}' />
		<input type="hidden" id="faviconSupportedFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$faviconSupportedFormats)}' />

		{*<div class="blockData" >
		<h3>{vtranslate('LBL_COMPANY_DETAILS', $QUALIFIED_MODULE)}</h3>
		{if $DESCRIPTION}<span style="font-size:12px;color: black;"> - &nbsp;{vtranslate({$DESCRIPTION}, $QUALIFIED_MODULE)}</span>{/if}
		</div>
		<hr>*}
		<div class="clearfix">
			<div class="btn-group pull-right editbutton-container">
				<button id="updateCompanyDetails" class="btn btn-default ">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
			</div>
		</div>
		{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
		<div id="CompanyDetailsContainer" class=" detailViewContainer {if !empty($ERROR_MESSAGE)}hide{/if}" >
			<div class="block">
				<div>
					<h4>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyLogo">
										{if $MODULE_MODEL->getLogoPath()}
											<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" style="width:150px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div>
					<h4>{vtranslate('LBL_CABINET_COMPANY_LOGO',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyLogo">
										{if $MODULE_MODEL->getLogoPathCabinet()}
											<img src="{$MODULE_MODEL->getLogoPathCabinet()}" class="alignMiddle" style="width:150px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div>
					<h4>{vtranslate('LBL_COMPANY_BANNER',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
	                <table class="table detailview-table no-border">
	                	<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyBanner">
										{if $MODULE_MODEL->getBannerPath()}
											<img src="{$MODULE_MODEL->getBannerPath()}" class="alignMiddle" style="width:150px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
	                </table>
	            </div>

	            <div>
					<h4>{vtranslate('LBL_CABINET_COMPANY_BANNER',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
	                <table class="table detailview-table no-border">
	                	<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyBanner">
										{if $MODULE_MODEL->getBannerPathCabinet()}
											<img src="{$MODULE_MODEL->getBannerPathCabinet()}" class="alignMiddle" style="width:150px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
	                </table>
	            </div>

	            <div>
					<h4>{vtranslate('LBL_COMPANY_FAVICON',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
	                <table class="table table-bordered no-border">
	                	<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyBanner">
										{if $MODULE_MODEL->getFaviconPath()}
											<img src="{$MODULE_MODEL->getFaviconPath()}" class="alignMiddle" style="width:20px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
	                </table>
				</div>

				<div>
					<h4>{vtranslate('LBL_CABINET_COMPANY_FAVICON',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
	                <table class="table table-bordered no-border">
	                	<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyBanner">
										{if $MODULE_MODEL->getFaviconPathCabinet()}
											<img src="{$MODULE_MODEL->getFaviconPathCabinet()}" class="alignMiddle" style="width:20px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
	                </table>
				</div>
			</div>
			<br>
			<div class="block">
				<div>
					<h4>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
								{if $FIELD neq 'logoname' && $FIELD neq 'logo' && $FIELD neq 'sidebar_color' }
									<tr>
										<td class="{$WIDTHTYPE} fieldLabel" style="width:25%"><label >{vtranslate($FIELD,$QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE}" style="word-wrap:break-word;">
											{if $FIELD eq 'address'} {decode_html($MODULE_MODEL->get($FIELD))|nl2br} {else} {decode_html($MODULE_MODEL->get($FIELD))} {/if}
										</td>
									</tr>
								{/if}
							{/foreach}
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel" style="width:25%"><label >{vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'sidebar_color'}{/if}</label></td>
								<td class="{$WIDTHTYPE}" style="word-wrap:break-word;">
									{if $FIELD eq 'sidebar_color'} 
										<div class="sidebar-list-color-box {decode_html($MODULE_MODEL->get($FIELD))} ">
										</div>
									{/if}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>


		</div>


		<div class="editViewContainer">
			<form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}" id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="Vtiger" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="CompanyDetailsSave" />
				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" style="width:150px;"/>
							<br><hr>
							<input type="file" name="logo" id="logoFile" />
							<input type="hidden"  id="logoNameWidth" value=""  />
                    		<input type="hidden"  id="logoNameHeight" value=""  />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
					</div>
				</div>

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_CABINET_COMPANY_LOGO',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getLogoPathCabinet()}" class="alignMiddle" style="width:150px;"/>
							<br><hr>
							<input type="file" name="logocabinet" id="logoFileCabinet" />
							<input type="hidden"  id="logoNameWidthCabinet" value=""  />
                    		<input type="hidden"  id="logoNameHeightCabinet" value=""  />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
					</div>
				</div>

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_COMPANY_BANNER',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getBannerPath()}" class="alignMiddle" style="width:150px;"/>
							<br><hr>
							<input type="file" name="banner" id="bannerFile" />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_WALL_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
						{if !empty($ERROR_MESSAGE)}
	                        <div class="marginLeftZero span9 alert alert-error">
	                            {vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
	                        </div>
	                    {/if}
					</div>
				</div>

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_CABINET_COMPANY_BANNER',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getBannerPathCabinet()}" class="alignMiddle" style="width:150px;"/>
							<br><hr>
							<input type="file" name="bannercabinet" id="bannerFileCabinet" />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_WALL_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
						{if !empty($ERROR_MESSAGE)}
	                        <div class="marginLeftZero span9 alert alert-error">
	                            {vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
	                        </div>
	                    {/if}
					</div>
				</div>

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_COMPANY_FAVICON',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getFaviconPath()}" class="alignMiddle" style="width:20px;"/>
							<br><hr>
							<input type="file" name="company_favicon" id="faviconFile" width="16" height="16"/>
							<input type="hidden"  id="faviconNameWidth" value=""  />
                    		<input type="hidden"  id="faviconNameHeight" value=""  />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_FAVICON_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
						{if !empty($ERROR_MESSAGE)}
	                        <div class="marginLeftZero span9 alert alert-error">
	                            {vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
	                        </div>
	                    {/if}
					</div>
				</div>

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_CABINET_COMPANY_FAVICON',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getFaviconPathCabinet()}" class="alignMiddle" style="width:20px;"/>
							<br><hr>
							<input type="file" name="company_faviconcabinet" id="faviconFileCabinet" width="16" height="16"/>
							<input type="hidden"  id="faviconNameWidthCabinet" value=""  />
                    		<input type="hidden"  id="faviconNameHeightCabinet" value=""  />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_FAVICON_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
						{if !empty($ERROR_MESSAGE)}
	                        <div class="marginLeftZero span9 alert alert-error">
	                            {vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
	                        </div>
	                    {/if}
					</div>
				</div>


				{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
					{if $FIELD neq 'logoname' && $FIELD neq 'logo' && $FIELD neq 'sidebar_color'}
						<div class="form-group companydetailsedit">
							<label class="col-sm-2 fieldLabel control-label ">
								{vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'organizationname'}&nbsp;<span class="redColor">*</span>{/if}
							</label>
							<div class="fieldValue col-sm-5">
								{if $FIELD eq 'address'}
									<textarea class="form-control col-sm-6 resize-vertical" rows="2" name="{$FIELD}">{$MODULE_MODEL->get($FIELD)}</textarea>
								{else if $FIELD eq 'website'}
									<input type="text" class="inputElement" data-rule-url="true" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
								{else}
									<input type="text" {if $FIELD eq 'organizationname'} data-rule-required="true" {/if} class="inputElement" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
								{/if}
							</div>
						</div>
					{/if}
				{/foreach}

				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label ">
						{vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'sidebar_color'}{/if}
					</label>
					<div class="fieldValue col-sm-5 cab-sidebar-color-block">
						<div class="sidebar-color-box">
							{if $MODULE_MODEL->get($FIELD) eq 'color-727272'}
								<input type="radio" checked class="grey" name="sidebar_color" value="color-727272">
							{else}
								<input type="radio" class="grey" name="sidebar_color" value="color-727272">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-1560BD'}
								<input type="radio" checked class="azure" name="sidebar_color" value="color-1560BD">
							{else}
								<input type="radio" class="azure" name="sidebar_color" value="color-1560BD">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-204E81'}
								<input type="radio" checked class="darkblue" name="sidebar_color" value="color-204E81">
							{else}
								<input type="radio" class="darkblue" name="sidebar_color" value="color-204E81">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-008D4C'}
					  			<input type="radio" checked class="green" name="sidebar_color" value="color-008D4C">
							{else}
								<input type="radio" class="green" name="sidebar_color" value="color-008D4C">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-C19803'}
					  			<input type="radio" checked class="yellow" name="sidebar_color" value="color-C19803">
							{else}
								<input type="radio" class="yellow" name="sidebar_color" value="color-C19803">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-C65479'}
					  			<input type="radio" checked class="pink" name="sidebar_color" value="color-C65479">
							{else}
								<input type="radio" class="pink" name="sidebar_color" value="color-C65479">
							{/if}

					  		{if $MODULE_MODEL->get($FIELD) eq 'color-E51400'}
					  			<input type="radio" checked class="red" name="sidebar_color" value="color-E51400">
					  		{else}
					  			<input type="radio" class="red" name="sidebar_color" value="color-E51400">
					  		{/if}

					  		{if $MODULE_MODEL->get($FIELD) eq 'color-404952'}
				  				<input type="radio" checked class="darkgrey" name="sidebar_color" value="color-404952">
					  		{else}
					  			<input type="radio" class="darkgrey" name="sidebar_color" value="color-404952">
					  		{/if}

					  		{if $MODULE_MODEL->get($FIELD) eq 'color-894400'}
				  				<input type="radio" checked class="almond" name="sidebar_color" value="color-894400">
					  		{else}
					  			<input type="radio" class="almond" name="sidebar_color" value="color-894400">
					  		{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-013CEB'}
								<input type="radio" checked class="blue" name="sidebar_color" value="color-013CEB">
							{else}
								<input type="radio" class="blue" name="sidebar_color" value="color-013CEB">
							{/if}

							{if $MODULE_MODEL->get($FIELD) eq 'color-101828'}
								<input type="radio" checked class="russian_black" name="sidebar_color" value="color-101828">
							{else}
								<input type="radio" class="russian_black" name="sidebar_color" value="color-101828">
							{/if}
						</div>
					</div>
				</div>

				<div class="modal-overlay-footer clearfix">
					<div class="row clearfix">
						<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
							<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
							<a class="cancelLink" data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
					</div>
				</div>
			</form>
		</div>
</div>
</div>
{/strip}
