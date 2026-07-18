<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function flip_backYTShortcode($atts = null, $content = null) {
	$atts = ytshortcode_atts(array(
		'background' => '#4e9e41',
		'color'      => '#fff',
		'border'     => 'none',
		'shadow'     => 'none',
		'radius'     => '0px',
		'text_align' => 'center',
		'padding'    => '15px',
		'class'      => ''
	), $atts, 'flip_back');
	$css = '';
	$id = uniqid('flip_back_').rand().time(); 
	$css .= '#'.$id.'.back-flip_box{background-color:' . $atts['background']. ';color:' . $atts['color'] .';border:' . $atts['border'] .';box-shadow:'.$atts['shadow'].';border-radius:'.$atts['radius'].';text-align:'.$atts['text_align'].';padding:'.$atts['padding'].';}';
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
    return '<div id="'.$id.'" class="back-flip_box ' . trim($atts['class']) . '">' . parse_shortcode(str_replace(array("<br/>","<br />"), " ", $content)) . '</div>';
}
?>