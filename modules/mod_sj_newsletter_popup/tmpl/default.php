<?php
/**
 * @package Sj Newletter Popup
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;
$cookie_name = "sj_newletter_popup";
if(!isset($_COOKIE[$cookie_name])) {
JHtml::stylesheet('modules/'.$module->module.'/assets/css/style.css');
$title 		= $params->get('title');
$width 		= $params->get('width');
$title_display = $params->get('title_display');
$newsletter_promo = $params->get('newsletter_promo');
$image_bg_display = $params->get('image_bg_display');
$color_bg = $params->get('color_bg');
$image = $params->get('imageurl');
$class_sfx= $params->get('moduleclass_sfx');
$expired= $params->get('expired');
$tag_id = 'sj_newletter_popup_'.rand().time();
$width_popup = $width ? $width : '50%';
if($image_bg_display){
	$bg = 'background: url(\''.JURI::base().$image.'\')';
}else{
	$bg = 'background-color: '.$color_bg.'';
}
?>
<div class="sj-popup-container">
<div class="sj_newletter_popup_bg "></div>
<div class="sj_newletter_popup <?php echo $class_sfx; ?>" id="<?php echo $tag_id; ?>">
	<div class="sj-custom-popup sj-custom-oca-popup sj-popup-767" data-ajaxurl="<?php echo JURI::base() ?>" style="width: <?php echo $width_popup; ?>; <?php echo $bg; ?>;  background-size: 100%; background-size: cover; background-repeat: no-repeat; ">
		<div class="oca_popup" id="test-popup">
			<div class="popup-content">
				<div class="popup-title">
					<?php if($title_display)
					{
						echo $title;
					}
					?>
				</div>
				<p class="newsletter_promo"><?php echo $newsletter_promo ;?></p>
				<div id="signup" class="form-inline signup">
					<div class="input-control">
						<div class="email">
							<input type="email" placeholder="<?php echo JText::_('ENTER_YOUR_EMAIL_ADDRESS'); ?>" value="" class="form-control txtemail" name="txtemail">
						</div>
						<div class="btn-button">
							<button class="btn-cool send-mail sendmail-<?php echo $module->id;?>" type="button">
								<?php echo JText::_('Subscribe'); ?>
							</button>
						</div>
					</div>
				</div>
				<label class="hidden-popup">
					<input type="checkbox" value="1" name="hidden-popup">
					<span class="inline">&nbsp;&nbsp;<?php echo JText::_("Don't show this popup again"); ?></span>
				</label>
			</div>
		</div>
		<button title="Close" type="button" class="popup-close">&times;</button>
	</div>
</div>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			var $element = $("#<?php echo $tag_id; ?>");
			$('.sj_newletter_popup_bg').addClass('popup_bg');
			$('input[name=\'hidden-popup\']').on('change', function(){
				if ($(this).is(':checked')) {
					checkCookie();
				} else {
					unsetCookie("sj_newletter_popup");
				}
			});
			function setCookie(cname, cvalue, exdays) {
				var d = new Date();
				console.log(d.getTime());
				d.setTime(d.getTime() + (exdays*24*60*60*1000));
				var expires = "expires="+d.toUTCString();
				document.cookie = cname + "=" + cvalue + "; " + expires;
			}
			function getCookie(cname) {
				var name = cname + "=";
				var ca = document.cookie.split(';');
				for(var i=0; i<ca.length; i++) {
					var c = ca[i];
					while (c.charAt(0)==' ') c = c.substring(1);
					if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
				}
				return "";
			}
			function checkCookie() {
				var check_cookie = getCookie("sj_newletter_popup");
				if(check_cookie == ""){
					setCookie("sj_newletter_popup", "Newletter Popup", <?php echo $expired ?> );
				}
			}
			function unsetCookie( name ) {
				document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
			}
			$('.popup-close').click(function(){
				var this_close = $('.popup-close');
				this_close.parents().find('.sj-popup-container').remove();
			});
			$('.sendmail-<?php echo $module->id;?>').click(function(e){
				e.preventDefault();
				var emailpattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
				var email = $('.txtemail',$element).val();
				var ajax_url = $('#<?php echo $tag_id;?>').attr('data-ajaxurl');
				if(email != "")
				{
					if(!emailpattern.test(email))
					{
						$('.show-error').remove();
						$('.btn-button').after('<span class="show-error" style="color: red;margin-left: 10px"> Incorrect email format </span>')
						return false;
					}
					else
					{
						$.ajax({
							type: 'POST',
							url: ajax_url,
							data: {
								is_newletter: 1,
								email : email
							},
							success: function(data) {
								$('.show-error').remove();
								$('.btn-button').after('<span class="show-error" style="color: #003bb3;margin-left: 10px"> Registered successfully </span>');
								$(".txtemail",$element).val("");
							}
						});
						return false;
					}
				}
				else
				{
					$('.show-error').remove();
					$('.btn-button').after('<span class="show-error" style="color: red;margin-left: 10px"> Email is required </span>')
					$(email).focus();
					return false;
				}
				return false;
			});
		});
	</script>
</div>
<?php
} else {
	echo "";
}?>