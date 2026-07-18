<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function google_fontYTShortcode($atts, $content = null){
	extract(ytshortcode_atts(array(
		"font" => '',
		"size" => '',
		"color" => '',
		"align" => '',
		"font_weight" => '',
		"margin" => '',
	), $atts));
	$css = '';
	$style = '';
	$id = uniqid('google_font_').rand().time(); 
	if($font!="")
		$style .= "font-family:'$font';";

	if($size!="")
		$style .= "font-size:'$size'px;";
	if($color!="")
		$style .= "color:'$color';";
	if($font_weight!="")
		$style .="font-weight:'$font_weight';";
	if($align!="")
		$style .="text-align:'$align';";
	if($margin!="")
		$style .="margin:'$margin';";
	
	$doc = JFactory::getDocument();
	$href = "//fonts.googleapis.com/css?family=".$font;
	$attribs = array('type' => 'text/css', 'title' => 'CSS');
	$doc->addHeadLink( $href, 'stylesheet', 'rel', $attribs );
	$css .= '#'.$id.'{'.$style.'}';
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	$googlefont = "<h3 id='".$id."' class='googlefont'>".$content."</h3>";
	return $googlefont;
}
?>