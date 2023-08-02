{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <link rel="stylesheet" href="layouts/v7/skins/otp.min.css">
    <style>
        body {
            background: url(test/logo/login-background.jpg);
            background-position: center;
            background-size: cover;
            width: 100%;
            height: 96%;
            background-repeat: no-repeat;
        }
        hr {
            margin-top: 15px;
            background-color: #7C7C7C;
            height: 2px;
            border-width: 0;
        }
        h3, h4 {
            margin-top: 0px;
        }
        hgroup {
            text-align:center;
            margin-top: 4em;
        }
        label {
            font-size: 16px;
            font-weight: normal;
            position: absolute;
            pointer-events: none;
            left: 0px;
            top: 10px;
            transition: all 0.2s ease;
        }
        #page {
            padding-top: 6%;
        }
        .widgetHeight {
            height: 410px;
            margin-top: 20px !important;
        }
        .loginDiv {
            width: 380px;
            margin: 0 auto;
            border-radius: 4px;
            box-shadow: 0 0 10px gray;
            background-color: #FFFFFF;
        }
        .marketingDiv {
            color: #303030;
        }
        .separatorDiv {
            background-color: #7C7C7C;
            width: 2px;
            height: 460px;
            margin-left: 20px;
        }
        .user-logo {
            height: 110px;
            margin: 0 auto;
            padding-top: 40px;
            padding-bottom: 20px;
        }
        .blockLink {
            border: 1px solid #303030;
            padding: 3px 5px;
        }
        .group {
            position: relative;
            margin: 20px 20px 40px;
        }
        .failureMessage {
            color: red;
            display: block;
            text-align: center;
            padding: 0px 0px 10px;
        }
        .successMessage {
            color: green;
            display: block;
            text-align: center;
            padding: 0px 0px 10px;
        }
        .inActiveImgDiv {
            padding: 5px;
            text-align: center;
            margin: 30px 0px;
        }
        .app-footer p {
            margin-top: 0px;
        }
        .footer {
            background-color: #fbfbfb;
            height:26px;
        }
        .bar {
            position: relative;
            display: block;
            width: 100%;
        }
        .bar:before, .bar:after {
            content: '';
            width: 0;
            bottom: 1px;
            position: absolute;
            height: 1px;
            background: #35aa47;
            transition: all 0.2s ease;
        }
        .bar:before {
            left: 50%;
        }
        .bar:after {
            right: 50%;
        }
        .button {
            position: relative;
            display: inline-block;
            padding: 9px;
            margin: .3em 0 1em 0;
            width: 100%;
            vertical-align: middle;
            color: #fff;
            font-size: 16px;
            line-height: 20px;
            -webkit-font-smoothing: antialiased;
            text-align: center;
            letter-spacing: 1px;
            background: transparent;
            border: 0;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .button:focus {
            outline: 0;
        }
        .buttonBlue {
            background-image: linear-gradient(to bottom, #35aa47 0px, #35aa47 100%)
        }
        .ripples {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: transparent;
        }
        
        .otp_container input {
            background-color: #cdcaca;
            width: 45px;
            height: 45px;
            font-size: 24px;
            text-shadow: none !important;
            color: #000 !important;
            border: 1px solid #999;
        }
        a.resend_otp_link,a.resend_otp_link:focus,a.resend_otp_link:active{ color:#23ccef; }
        a.resend_otp_link.disabled{ color:#7a7777; pointer-events: none; cursor: none;}
        .disabled { pointer-events: none; cursor: not-allowed; color: #979393; }
        /* Chrome, Safari, Edge, Opera */
        .otp_container input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {  -webkit-appearance: none;  margin: 0;}
        /* Firefox */
        .otp_container input[type=number] {  -moz-appearance: textfield;}
    </style>

    <span class="app-nav"></span>
    <div class="col-lg-12">
          <div class="col-lg-5">
            <div class="loginDiv widgetHeight">
                <img class="img-responsive user-logo" src="{$SITE_LOGO}">
                <div>
                    <div id="messageBar" class="hide"></div>
                    <span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
                    <span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
                    <span class="successMessage hide" id="successMessage">{$MESSAGE}</span>
                </div>

                <div id="loginFormDiv">
                    <form class="form-horizontal" method="POST" action="index.php">
                        <input type="hidden" name="module" value="Users"/>
                        <input type="hidden" name="action" value="Login"/>
                        <input type="hidden" name="mode" value="verifyOtpProcess"/>
                        <input type="hidden" name="username" value="{$USERNAME}"/>
                        <input type="hidden" name="resend_otp_duration" value="{$RESEND_OTP_DURATION}"/>
                        <input type="hidden" name="otp" value=""/>
                        <div class="group">
                            <label>OTP</label>
                            <div class="flex justify-center otp_container" id="OTPInput"></div>
                        </div>
                        <div class="group">
                            <button class="button buttonBlue" id="verifyOtpSubmitBtn">Verify OTP</button><br>
                            <div class="text-right" style="display:inline-block;float:right;"><a class="forgotPasswordLink" id="resend_otp_link" style="color: #15c;">Resend OTP?</a></div>
                            <div style="display:inline-block;"><a class="forgotPasswordLink" style="color: #15c;" href="index.php"><< Back</a></div>
                            <div class="text-right" style="margin-top: 10px;">Time: <span id="timer"></span></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        jQuery(document).ready(function () {
            var resend_otp_time = jQuery('input[name="resend_otp_duration"]').val();
            timer(resend_otp_time);
            jQuery('#resend_otp_link').addClass('disabled');
            var validationMessage = jQuery('#validationMessage');
            var successMessage = jQuery('#successMessage');
            var loginFormDiv = jQuery('#loginFormDiv');

            $('.otp_container input').on("paste",function(e) {
                e.preventDefault();
            });
            
                jQuery('#verifyOtpSubmitBtn').click(function (e) {
                    e.preventDefault();
                    
                    /*Validations*/
                    var compiledOtpVal = compiledOtp();
                    jQuery('input[name="otp"]').val(compiledOtpVal);
                    var result = true;
                    var errorMessage = '';
                    if (compiledOtpVal === '') {
                        errorMessage = 'Please enter OTP!';
                        result = false;
                    }
                    if (errorMessage) {
                        validationMessage.removeClass('hide').text(errorMessage);
                        setTimeout(function () {
                            validationMessage.addClass('hide');
                        }, 5000);
                        return result;
                    }
                    
                    app.helper.showProgress();
                    jQuery('#verifyOtpSubmitBtn').addClass('disabled');
                    var username = jQuery('input[name="username"]').val();
                    var otp = compiledOtpVal;
                    jQuery.ajax({
                        url: 'index.php?module=Users&action=Login&mode=verifyOtpProcess&username='+username+'&otp='+otp,
                        type: 'GET',
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            jQuery('#resend_otp_link').addClass('disabled');
                        },
                        success: function (response) {
                            app.helper.hideProgress();
                            jQuery('#verifyOtpSubmitBtn').removeClass('disabled');
                            if (response.success)
                            {
                                var url = response.result.url;
                                if(url)
                                {
                                    window.location.href = url;
                                }
                                else
                                {
                                    window.location.href = 'index.php?module=Users&parent=Settings&view=Login&error=login';
                                }
                                return;
                            }
                            else
                            {
                                jQuery("body").removeClass('set_body_opacity');
                                var msg = response.error.message;
                                validationMessage.removeClass('hide').text(msg);
                                setTimeout(function () {
                                    validationMessage.addClass('hide');
                                }, 5000);
                            }
                        }
                    });
                });
                
                jQuery('#resend_otp_link').click(function () {
                    app.helper.showProgress();
                    var username = jQuery('input[name="username"]').val();
                    jQuery.ajax({
                        url: 'index.php?module=Users&action=Login&mode=resendOtpProcess&username='+username,
                        type: 'GET',
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            jQuery('#resend_otp_link').addClass('disabled');
                            jQuery('#OTPInput > *[id]').val('');
                        },
                        complete: function () {
                            jQuery('span#timer').removeClass('stop');
                            timer(resend_otp_time);
                        },
                        success: function (response) {
                            app.helper.hideProgress();
                            if (response.success)
                            {
                                successMessage.removeClass('hide');
                                var msg = '';
                                msg = response.result.message;
                                successMessage.removeClass('hide').text(msg);
                                setTimeout(function () {
                                    successMessage.addClass('hide');
                                }, 5000);
                            }
                            else
                            {
                                var msg = response.error.message;
                                validationMessage.removeClass('hide').text(msg);
                                setTimeout(function () {
                                    validationMessage.addClass('hide');
                                }, 5000);
                            }
                        }
                    });
                });
        });
        
        function compiledOtp()
        {
            const inputs = document.querySelectorAll('#OTPInput > *[id]');
            let compiledOtp = '';
            for (let i = 0; i < inputs.length; i++) {
              compiledOtp += inputs[i].value;
            }
            return compiledOtp;
        }
    </script>
</div>
{/strip}