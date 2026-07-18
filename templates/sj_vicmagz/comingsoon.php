<?php
/*
 * ------------------------------------------------------------------------
 * Copyright (C) 2009 - 2013 The YouTech JSC. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: The YouTech JSC
 * Websites: http://www.smartaddons.com - http://www.cmsportal.net
 * ------------------------------------------------------------------------
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
header('X-UA-Compatible: IE=edge');
// Object of class YtTemplate
$doc 	= JFactory::getDocument();
$app 	= JFactory::getApplication();
$option = $app->input->get('option');

// Check yt plugin
if(!defined('YT_FRAMEWORK')) throw new Exception(JText::_('INSTALL_YT_PLUGIN'));
if(!defined('J_TEMPLATEDIR') )define('J_TEMPLATEDIR', JPATH_SITE.J_SEPARATOR.'templates'.J_SEPARATOR.$this->template);

// Include file: frame_inc.php
$comingsoon = true;
 include_once (J_TEMPLATEDIR.J_SEPARATOR.'includes'.J_SEPARATOR.'frame_inc.php');

 $doc->addScript($yt->templateurl().'/js/jquery.countdown.js');
// Check direction for html
$direction = array('rtl' => JFactory::getLanguage()->isRtl());
$dir = ($direction['rtl']) ? ' dir="rtl"' : '';

/** @var YTFramework */
$responsive = $yt->getParam('layouttype');
$favicon 	= $yt->getParam('favicon');
$layoutType	= $yt->getParam('layouttype');

?>
<!DOCTYPE html>
<html <?php echo $dir; ?> lang="<?php echo $this->language; ?>">
<head>
	<jdoc:include type="head" />

    <meta name="HandheldFriendly" content="true"/>
	<meta name="format-detection" content="telephone=no">
	<meta name="apple-mobile-web-app-capable" content="YES" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	
	<!-- META FOR IOS & HANDHELD -->
	<?php if($responsive=='res'): ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
	<?php endif ?>
	
	<!-- LINK FOR FAVICON -->
	<?php if($favicon) : ?>
		<link rel="icon" type="image/x-icon" href="<?php echo $favicon?>" />
    <?php endif; ?>
	
    <?php 
	// Include css, js
	include_once (J_TEMPLATEDIR.J_SEPARATOR.'includes'.J_SEPARATOR.'head.php');
	?>
	
</head>
<?php

	$comingsoon_title = $this->params->get('comingsoon_title');
	$hide_comingsoon_title = $this->params->get('hide_comingsoon_title');
	if( $comingsoon_title ) {
		$doc->setTitle( $comingsoon_title . ' | ' . $app->get('sitename') );
	}

	$comingsoon_date = explode('-', $this->params->get("comingsoon_date"));
	
?>
<body id="bd" class="comingsoon">
	<div id="yt_header" class="hidden"></div>
	<jdoc:include type="modules" name="debug" />
	<div id="yt_comingsoon" >
		<div class="comingsoon-wrap">
			<div class="yt-comingsoon text-center headingsFont">
				<div class="">
					<?php echo $yt->getLogo(); ?>
				</div>
				<?php if( $hide_comingsoon_title ) { ?>
					<h2 class="comingsoon-title">
						<?php echo $comingsoon_title; ?>
					</h2>
				<?php } ?>

				<?php if( $this->params->get('comingsoon_content') ) { ?>
					<div class="comingsoon-content">
						<?php echo $this->params->get('comingsoon_content'); ?>
					</div>
				<?php } ?>

				<div id="comingsoon-countdown" class="comingsoon-countdown ">
					
				</div>
				<?php if($this->countModules('comingsoon')) { ?>
				<div class="position-comingsoon">
					<jdoc:include type="modules" name="comingsoon" style="xhtml" />
				</div>
				<?php } ?>
			
			</div>
		</div>
	</div>
		<script type="text/javascript">
		jQuery(function($) {
			$('#comingsoon-countdown').countdown('<?php echo trim($comingsoon_date[2]); ?>/<?php echo trim($comingsoon_date[1]); ?>/<?php echo trim($comingsoon_date[0]); ?>', function(event) {
			    $(this).html(event.strftime('<div class="days"><span class="number">%-D</span><span class="string">%!D:<?php echo JText::_("COMINGSOON_DAY"); ?>,<?php echo JText::_("COMINGSOON_DAYS"); ?>;</span></div><div class="yt-line"><span>:</span/></div><div class="hours"><span class="number"> %H</span><span class="string">%!H:<?php echo JText::_("COMINGSOON_HOUR"); ?>,<?php echo JText::_("COMINGSOON_HOURS"); ?>;</span></div><div class="yt-line"><span>:</span/></div><div class="minutes"><span class="number">%M</span><span class="string">%!M:<?php echo JText::_("COMINGSOON_MINUTE"); ?>,<?php echo JText::_("COMINGSOON_MINUTES"); ?>;</span></div><div class="yt-line"><span>:</span/></div><div class="seconds"><span class="number">%S</span><span class="string">%!S:<?php echo JText::_("COMINGSOON_SECOND"); ?>,<?php echo JText::_("COMINGSOON_SECONDS"); ?>;</span></div>'));
			});
		});

	</script>
	<?php
	function ytfont($font, $selectors){
		$doc = JFactory::getDocument();
		$font = trim($font);
		$font_boolean = strrpos($font, "'");
		
		if($font !='0'){
			if ($font_boolean ) {
				$doc->addStyleDeclaration($selectors.'{font-family:'.$font.'}');
			}else{
				$doc->addStyleSheet('http://fonts.googleapis.com/css?family='.$font.'&amp;subset=latin,latin-ext');
				$font = str_replace("+"," ",(explode(':',$font)));
				if(trim($selectors)!=""){
					$doc->addStyleDeclaration($selectors.'{font-family:'.$font[0].'}');
				}
			}
		}
	}
	ytfont($bodyFont,$bodySelectors);
	ytfont($menuFont,$menuSelectors);
	ytfont($headingFont,$headingSelectors);
	ytfont($otherFont,$otherSelectors);
	?>
</body>
</html>