<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function list_styleYTShortcode($atts, $content = null){
	extract(ytshortcode_atts(array(
		"type" => 'check',
		"color" => ''
	), $atts));
	if($color != '') {
		global $list_color;
		$list_color = $color;
	}
	$id = uniqid('list_style_').rand().time(); 
	$color =(($color != '')? 'color:'.$color : "");
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/list_style/css/list_style.css");
	$css ='';
	$css .= '#'.$id.'.yt-list{'.$color.'}';
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	return '<ul id="'.$id.'" class="yt-list type-' . $type . '">'. parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) . '</ul>';
}
?>