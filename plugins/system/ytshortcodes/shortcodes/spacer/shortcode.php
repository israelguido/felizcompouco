<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function spacerYTShortcode($atts){
	extract(ytshortcode_atts(array(
		"height" => '20'
	), $atts));
	$css = '';
	$id = uniqid('spacer_').rand().time(); 
	$css = "#".$id."{clear:both; height:" . $height . "px;}";
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	return "<div id='".$id."'></div>";
}
?>