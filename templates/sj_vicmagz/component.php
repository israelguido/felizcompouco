<?php
/**
* @copyright Copyright (C) 2008 JoomlaPraise. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Joomla 6 legacy aliases for YT/SJ
if (defined('JPATH_PLUGINS') && is_file(JPATH_PLUGINS . '/system/j3legacy/j3legacy.php')) {
	require_once JPATH_PLUGINS . '/system/j3legacy/j3legacy.php';
	\PlgSystemJ3legacyBootstrap::register();
}

header('X-UA-Compatible: IE=edge');
// Object of class YtTemplate
$doc 	= JFactory::getDocument();
$app 	= JFactory::getApplication();
$option = $app->input->get('option');

// Check yt plugin
if(!defined('YT_FRAMEWORK')) throw new Exception(JText::_('INSTALL_YT_PLUGIN'));
if(!defined('J_TEMPLATEDIR') )define('J_TEMPLATEDIR', JPATH_SITE.J_SEPARATOR.'templates'.J_SEPARATOR.$this->template);

// Include file: frame_inc.php
 include_once (J_TEMPLATEDIR.J_SEPARATOR.'includes'.J_SEPARATOR.'frame_inc.php');

// Check direction for html
$direction = array('rtl' => JFactory::getLanguage()->isRtl());
$dir = ($direction['rtl']) ? ' dir="rtl"' : '';

/** @var YTFramework */
$responsive = $yt->getParam('layouttype');
$favicon 	= $yt->getParam('favicon');
$layoutType	= $yt->getParam('layouttype');

// Check for the print page
$print = JRequest::getCmd('print');

// Check for the mail page
$mailto = JRequest::getCmd('option') == 'com_mailto';

$params = JFactory::getApplication()->getTemplate(true)->params;

?>
<!DOCTYPE html>
<html <?php echo $dir; ?> lang="<?php echo $this->language; ?>">
<head>
	<jdoc:include type="head" />
	<!-- META FOR IOS & HANDHELD -->
	<?php if($responsive=='res'): ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
	<?php endif ?>
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<?php if($mailto == true) : ?>     
		<?php $this->addStyleSheet(JURI::base() . 'templates/' . $this->template . '/css/mail.css'); ?>
	<?php endif; ?>
	<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/<?php echo $this->template ?>/asset/fonts/awesome/css/font-awesome.min.css" type="text/css"/>	
	<?php if($print == 1) : ?>     
		<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/<?php echo $this->template; ?>/css/print.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo JURI::base(); ?>templates/<?php echo $this->template; ?>/css/template-red.css" type="text/css" />
		
	<?php endif; ?>

</head>
<?php
	//
	$cls_body = '';
	
	//For RTL direction
	$cls_body .= ($direction['rtl']) ? 'rtl' . ' ' : '';
?>
<body class="contentpane <?php echo $cls_body; ?>">
	<div id="yt_header" class="hidden"></div>
	<?php 
		if($print == 1) : 
			$logo_text = $params->get('logoText', '') != '' ? $params->get('logoText', '') : $params->getPageName();
			$logo_slogan = $params->get('sloganText', '');
	?>    
	<div id="print-top">
		<img src="<?php echo JURI::base(); ?>templates/<?php echo $this->template; ?>/images/logo_print.png" alt="<?php echo $logo_text . ' - ' . $logo_slogan; ?>" />
	</div>
	<?php endif; ?>
	
	<jdoc:include type="message" />
	<jdoc:include type="component" />
	
	
	<?php if($print == 1) : ?>     
	<div id="print-bottom">
		<?php if($params->get('ytcopyright', '') == '') : ?>
			&copy; Smartaddons - <a href="https://smartaddons.com/" title="Free Joomla! 3.0 Template">Beautiful Joomla! and WordPress Themes</a> <?php echo date('Y');?>
		<?php else : ?>
			<?php 
				$ytcopyright =  $params->get('ytcopyright', '');
				$ytcopyright = str_replace('{year}', date('Y'), $ytcopyright);
				echo $ytcopyright; 
			?>
		<?php endif; ?> 
	</div>
	<?php endif; ?>
	<?php 
	function ytfont($font, $selectors){
		
		$doc = JFactory::getDocument();
		$font = trim($font);
		$font_boolean = strrpos($font, "'");
		if($font !='0'){
			if ($font_boolean ) {
				$doc->addStyleDeclaration($selectors.'{font-family:'.$font.'}');
				
			}else{
				$doc->addStyleSheet('https://fonts.googleapis.com/css?family='.$font.'&amp;subset=latin,latin-ext');
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
<script type="text/javascript">
		
		//alert(jQuery(window).width());
	</script>
</body>
</html>
