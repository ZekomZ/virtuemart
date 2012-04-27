<?php  defined('_JEXEC') or die(); 
$code2 = $viewData['setup']['countryCode'] ;
$sType = $viewData['sType'];
// missing house_extension,ysalary,companyName
?>
<!-- KLARNA BOX -->
<?php echo $viewData['checkout']; ?>
<script type="text/javascript">
     <!--
            klarna.countryCode = '<?php echo $viewData['setup']['countryCode'] ; ?>';
            klarna.language = '<?php echo $viewData['setup']['langISO'] ; ?>';
            klarna.sum = '<?php echo $viewData['setup']['sum'] ; ?>';
            klarna.eid = '<?php echo $viewData['setup']['eid'] ; ?>';
            klarna.flag = '<?php echo $viewData['setup']['flag'] ; ?>';
            klarna.unary_checkout = '<?php echo @$viewData['setup']['unary_checkout'] ; ?>';
            klarna.type = '<?php echo $sType ?>';
            klarna.lang_companyNotAllowed = '<?php echo JText::_('VMPAYMENT_KLARNA_COMPANY_NOT_ALLOWED'); ?>';
            klarna.pid = '<?php echo $viewData['setup']['payment_id'] ; ?>';
            if (typeof klarna.red_baloon_content == "undefined" || klarna.red_baloon_content == "") {
                klarna.red_baloon_content = '<?php echo @$viewData['setup']['red_baloon_content'] ; ?>';
                klarna.red_baloon_box = '<?php echo @$viewData['setup']['red_baloon_paymentBox'] ; ?>';
            }

            klarna.lang_personNum    = '<?php echo JText::_('VMPAYMENT_KLARNA_PERSON_NUMBER'); ?>';
            klarna.lang_orgNum        = '<?php echo JText::_('VMPAYMENT_KLARNA_ORGANISATION_NUMBER'); ?>';

            klarna.select_bday    = '<?php echo @$viewData['value']['birth_day'] ; ?>';
            klarna.select_bmonth    = '<?php echo @$viewData['value']['birth_month'] ; ?>';
            klarna.select_byear    = '<?php echo @$viewData['value']['birth_year'] ; ?>';
            klarna.gender                = '<?php echo @$viewData['value']['gender'] ; ?>';

            klarna.invoice_ITId        = 'klarna_invoice_type';
            // Mapping to the real field names which may be prefixed
            klarna.params = {
				birth_day: 'klarna_birth_day',
				birth_month: 'klarna_birth_month',
				birth_year: 'klarna_birth_year',
				companyName: 'klarna_company_name',
                socialNumber: 'klarna_socialNumber',
                firstName: 'klarna_firstName',
                lastName: 'klarna_lastName',
                gender: 'klarna_gender',
                street: 'klarna_street',
                homenumber: 'klarna_homenumber',
                house_extension: 'house_extension',
                city: 'klarna_city',
                zipcode: 'klarna_zip',
                reference: 'klarna_reference',
                phoneNumber: 'klarna_phone',
                emailAddress: 'klarna_email',
                invoiceType: 'klarna_invoice_type',
                shipmentAddressInput: 'klarna_shipment_address',
				consent: 'klarna_consent'
				

              }




    //-->
</script>
<?php if ($sType =='spec') { ?>
	<script src="https://static.klarna.com/external/js/klarnaspecial.js" type="text/javascript"></script>
<?php } ?>
<script type="text/javascript">
jQuery(function (){
    klarna.methodReady('<?php echo $sType ?>');
});
</script>
<div class="klarna_baloon" id="klarna_baloon" style="display: none">
    <div class="klarna_baloon_top"></div>
    <div class="klarna_baloon_middle" id="klarna_baloon_content">
        <div></div>
    </div>
    <div class="klarna_baloon_bottom"></div>
</div>
<div class="klarna_blue_baloon" id="klarna_blue_baloon"
    style="display: none">
    <div class="klarna_blue_baloon_top"></div>
    <div class="klarna_blue_baloon_middle" id="klarna_blue_baloon_content">
        <div></div>
    </div>
    <div class="klarna_blue_baloon_bottom"></div>
</div>
<div class="klarna_box_container">
    <div class="klarna_box" id="klarna_box_<?php echo $sType ?>">
        <script type="text/javascript">
            openAgreement('<?php echo $viewData['setup']['countryCode'] ; ?>');
        </script>
        <div class="klarna_box_top">
            <div id="klarna_box_<?php echo $sType ?>_top_right" class="klarna_box_top_right">
                <div class="klarna_box_top_flag">
                    <div class="box_active_language">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/<?php echo $viewData['setup']['langISO'] ; ?>.png"
                            alt="<?php echo $viewData['setup']['langISO'] ; ?>" /> <img
                            src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/arrow_down.gif" alt=""
                            style="float: right; padding: 6px 2px 0 0; margin: 0" />
                    </div>
                    <div class="klarna_box_top_flag_list">
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/en.png" alt="en" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/da.png" alt="da" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/de.png" alt="de" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/fi.png" alt="fi" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/nl.png" alt="nl" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/nb.png" alt="nb" />
                        <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/flags/sv.png" alt="sv" />
                    </div>
                </div>
                <div class="klarna_box_top_agreement">
				<?php if ( $sType=='spec') { ?>
					<!-- Special payment External js(SPEC) -->
                    <a id="specialCampaignPopupLink" href="javascript:ShowKlarnaSpecialPaymentPopup()"></a>
				<?php } else  { 
						$popupTotal = ($sType=='part') ? $viewData['setup']['sum'] : $viewData['setup']['fee'] ;
					?>
					<!-- Part/invoice payment External js -->
                    <a href="javascript:ShowKlarnaPopup('<?php echo $viewData['setup']['eid'] ; ?>', '<?php echo $popupTotal ; ?>','<?php echo $sType ; ?>')">
                        <?php echo JText::_('VMPAYMENT_KLARNA_KLARNA_ACCOUNT_AGREEMENT'); ?> </a>
					<!-- payment External js END -->
				<?php } ?>		
                </div>
                <div class="klarna_box_bottom_languageInfo">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/notice.png"
                        alt="<?php echo JText::_('VMPAYMENT_KLARNA_LANGUAGESETTING_NOTE_'.$code2); ?>" />
                </div>
            </div>
            <img class="klarna_logo" src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>logo/klarna_<?php echo $sType.'_'.$code2; ?>.png"
                alt="<?php echo JText::_('VMPAYMENT_KLARNA_IMG_LOGO_ACCOUNT'); ?>" />
        </div>
        <div class="klarna_box_bottom">
            <div class="klarna_box_bottom_contents">
				<?php if ( $sType!=='invoice') { ?>
                <div class="klarna_box_bottom_left">
                    <div class="klarna_box_bottom_content">
                        <div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_'.$sType.'_PAYMENT'); ?></div>
                        <ol id="paymentPlan"><?php echo $this->renderPClasses(); ?>
                        </ol>
                        <input type="hidden" name="klarna_paymentPlan"
                            value="<?php echo @$viewData['value']['paymentPlan'] ; ?>" class="paymentPlan" />
                        <div class="klarna_box_bottom_content_listPriceInfo">
                            <?php // echo JText::_('VMPAYMENT_KLARNA_PRICES_ARE_IN_SEK'); ?></div>
                    </div>
                </div>
				<?php } ?>
                <div class="klarna_box_bottom_right">
                    <div class="klarna_box_bottom_content">
					<?php 
					if ($code2 !=='se' ) { ?>
						<div class="klarna_box_bottom_input_combo">
							<div class="klarna_left" style="width: 60%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_FIRST_NAME'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_FIRSTNAME'); ?>" type="text"
									name="klarna_firstName" value="<?php echo @$viewData['value']['firstName'] ; ?>"
									style="width: 98%" />
							</div>
							<div class="klarna_right" style="width: 40%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_LAST_NAME'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_LASTNAME'); ?>" type="text"
									name="klarna_lastName" value="<?php echo @$viewData['value']['lastName'] ; ?>"
									style="width: 100%" />
							</div>
						</div>
						<?php if ($code2 =='de' || $code2 =='nl') { ?>
							<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SEX'); ?></div>
							<input type="radio" name="klarna_gender" value="1" id="<?php echo $sType ?>_male"
								class="Klarna_radio gender" />
							<div class="klarna_box_bottom_radio_title" style="float: left">
								<label for="<?php echo $sType ?>_male"><?php echo JText::_('VMPAYMENT_KLARNA_SEX_MALE'); ?></label>
							</div>
							<input type="radio" name="klarna_gender" value="0"
								id="<?php echo $sType ?>_female" class="Klarna_radio gender" />
							<div class="klarna_box_bottom_radio_title" style="float: none">
								<label for="<?php echo $sType ?>_female"><?php echo JText::_('VMPAYMENT_KLARNA_SEX_FEMALE'); ?></label>
							</div>
						<?php } ?>
						<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
						<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_'.$code2); ?>" type="text" 
						name="klarna_phone" value="<?php echo @$viewData['value']['phoneNumber'] ; ?>"
							class="Klarna_fullwidth" />
						<div class="klarna_box_bottom_input_combo">
							<div class="klarna_left" style="width: 60%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_STREET'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_STREETADDRESS'); ?>" type="text"
									name="klarna_street" value="<?php echo @$viewData['value']['street'] ; ?>"
									style="width: 98%" />
							</div>
							<div class="klarna_right" style="width: 40%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_HOMENUMBER'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_HOUSENUMBER'); ?>" type="text"
									name="klarna_homenumber" value="<?php echo @$viewData['value']['homenumber'] ; ?>"
									style="width: 100%" />
							</div>
						</div>
						<div class="klarna_box_bottom_input_combo" style="width: 100%">
							<div class="klarna_left" style="width: 60%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_ZIP'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_ZIP'); ?>" type="text"
									name="klarna_zipcode" value="<?php echo @$viewData['value']['zipcode'] ; ?>"
									style="width: 98%" />
							</div>
							<div class="klarna_right" style="width: 40%">
								<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_ADDRESS_CITY'); ?></div>
								<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_CITY'); ?>" type="text"
									name="klarna_city" value="<?php echo @$viewData['value']['city'] ; ?>"
									style="width: 100%" />
							</div>
						</div>
					<?php 
					} else
					{ ?>
						<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_SOCIALSECURITYNUMBER'); ?></div>
						<div class="klarna_box_bottom_content_loader">
							<img src="<?php echo VMKLARNAPLUGINWEBASSETS.'/images/' ?>share/loader1.gif" alt="" />
						</div>
						<input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_SOCIALNUMBER_SE'); ?>"
							name="klarna_socialNumber" value="<?php echo @$viewData['value']['socialNumber'] ; ?>"
							class="Klarna_pnoInputField" />

						<div class="referenceDiv" style="display: none">
							<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_REFERENCE'); ?></div>
							<input type="text" alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_REFERENCE'); ?>"
								name="klarna_reference" value="<?php echo @$viewData['value']['reference'] ; ?>"
								class="Klarna_fullwidth" />
						</div>
						<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_EMAIL'); ?></div>
						<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_SE'); ?>" type="text"
							name="klarna_emailAddress" value="<?php echo @$viewData['value']['emailAddress'] ; ?>"
							class="Klarna_fullwidth" /> <br /> <br />
						<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_PHONE_NUMBER'); ?></div>
						<input alt="<?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_PHONENUMBER_SE'); ?>" type="text"
							name="klarna_phoneNumber" value="<?php echo @$viewData['value']['phoneNumber'] ; ?>"
							class="Klarna_fullwidth" /> <br /> <br />
						<div class="klarna_box_bottom_address" style="display: none">
							<div class="klarna_box_bottom_address_title"><?php echo JText::_('VMPAYMENT_KLARNA_DELIVERY_ADDRESS'); ?></div>
							<div class="klarna_box_bottom_address_content"></div>
						</div>
						<div class="klarna_additional_information">
							<?php echo @$viewData['setup']['additional_information'] ; ?>
						</div>				
					
					<?php 
					}
					if ($code2 =='de' || $code2 =='nl') { ?>
						<div class="klarna_box_bottom_title"><?php echo JText::_('VMPAYMENT_KLARNA_BIRTHDAY'); ?></div>
						<div class="klarna_box_bottom_input_combo" style="width: 100%">
							<div class="klarna_left" style="width: 30%">
								<select style="width: 98%" name="klarna_birth_day"
									class="selectBox_bday">
									<option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_DAY'); ?></option>
									<option value="01">01</option>
									<option value="02">02</option>
									<option value="03">03</option>
									<option value="04">04</option>
									<option value="05">05</option>
									<option value="06">06</option>
									<option value="07">07</option>
									<option value="08">08</option>
									<option value="09">09</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
									<option value="13">13</option>
									<option value="14">14</option>
									<option value="15">15</option>
									<option value="16">16</option>
									<option value="17">17</option>
									<option value="18">18</option>
									<option value="19">19</option>
									<option value="20">20</option>
									<option value="21">21</option>
									<option value="22">22</option>
									<option value="23">23</option>
									<option value="24">24</option>
									<option value="25">25</option>
									<option value="26">26</option>
									<option value="27">27</option>
									<option value="28">28</option>
									<option value="29">29</option>
									<option value="30">30</option>
									<option value="31">31</option>
								</select>
							</div>
							<div class="klarna_left" style="width: 40%">
								<select style="width: 98%" name="klarna_birth_month"
									class="selectBox_bmonth">
									<option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_MONTH'); ?></option>
									<option value="01"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_1'); ?></option>
									<option value="02"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_2'); ?></option>
									<option value="03"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_3'); ?></option>
									<option value="04"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_4'); ?></option>
									<option value="05"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_5'); ?></option>
									<option value="06"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_6'); ?></option>
									<option value="07"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_7'); ?></option>
									<option value="08"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_8'); ?></option>
									<option value="09"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_9'); ?></option>
									<option value="10"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_10'); ?></option>
									<option value="11"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_11'); ?></option>
									<option value="12"><?php echo JText::_('VMPAYMENT_KLARNA_MONTH_12'); ?></option>
								</select>
							</div>
							<div class="klarna_right" style="width: 30%">
								<select style="width: 100%" name="klarna_birth_year>"
									class="selectBox_year">
									<option selected="selected"><?php echo JText::_('VMPAYMENT_KLARNA_DATE_YEAR'); ?></option>
								</select>
							</div>
						</div>
					
						<div class="klarna_box_bottom_input_combo" style="width: 100%">
							<div class="klarna_box_bottom_title" style="width: 90%; margin-top: 3px">
								<em><?php echo JText::_('VMPAYMENT_KLARNA_NOTICE_BILLING_SAME_AS_SHIPPING'); ?></em>
							</div>
						</div>
					<?php } 
					if ($code2 =='de') { ?>
						<div class="klarna_box_bottom_input_combo" style="width: 100%">
							<input type="checkbox" name="klarna_consent"
								id="box_klarna_consent'] ; ?>"
								style="float: left; margin-right: 3px" />
								<div class="klarna_box_bottom_title" style="width: 80%; margin-top: 3px">Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufes und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an Klarna bin ich einverstanden. Meine <a href="javascript:ShowKlarnaConsentPopup('<?php echo $viewData['setup']['eid'] ; ?>','<?php echo $sType ?>')">Einwilligung</a> kann ich jederzeit mit Wirkung f&uuml;r die Zukunft widerrufen. Es gelten die <a href="<?php echo $viewData['setup']['agb_link'] ; ?>">AGB</a> des H&auml;ndlers.</div>
						</div>
					<?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="klarna_box_ilt">
            <div class="klarna_box_ilt_title"><?php echo JText::_('VMPAYMENT_KLARNA_ILT_TITLE'); ?></div>
            <div class="klarna_box_iltContents"></div>
        </div>
    </div>
</div>
<input type="hidden" name="klarna_emailAddress"
    value="<?php echo @$viewData['value']['emailAddress'] ; ?>" />
<!-- END KLARNA BOX -->