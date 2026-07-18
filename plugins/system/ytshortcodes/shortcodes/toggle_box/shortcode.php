<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function toggle_boxYTShortcode($atts, $content = null){
	extract(ytshortcode_atts(array(
		"width"  => '',
		"align"  => '',
		"height" =>'',
	), $atts));
	$css = '';
	$id = uniqid('toggle_box_').rand().time(); 
	$css .= "#".$id.".yt-toggle-box{width:".$width."}";
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	$toggle_box = "<ul id='".$id."' class='yt-toggle-box pull-".$align."'>";
	$toggle_box = $toggle_box . parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
	$toggle_box = $toggle_box . "</ul>";
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/toggle_box/css/toggle_box.css");
	return $toggle_box;
}
?>