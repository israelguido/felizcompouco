<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function skillsYTShortcode($atts, $content = null){
	global $no_number;
	extract(ytshortcode_atts(array(
		"width"		=> '',
		'no_number'	=> '',
	), $atts));
	$css = '';
	$id = uniqid('skills_').rand().time();
	$css .= '#'.$id.'.yt-skills{width:'.$width.'}';
	$skills = '<div class="yt-skills" id="'.$id.'">';
	$skills .= parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) ;
	$skills .= '</div>';
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/skills/css/skills.css");
	
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	return $skills;
}
?>