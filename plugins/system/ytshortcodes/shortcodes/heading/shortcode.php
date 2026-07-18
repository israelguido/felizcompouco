<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function headingYTShortcode($atts,$content = null){
	$atts = ytshortcode_atts(array(
		'style'   => 'default',
		'size'    => 16,
		'align'   => 'center',
		'margin'  => '20',
		'width'   => '',
		'heading' => 'h3',
		'color'   => '#666',
		'class'   => ''
	), $atts, 'heading');
	$css = '';
	$id = uniqid('heading_').rand().time(); 
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/heading/css/heading.css");
	$heading_wrapper_style = '';
	if ($atts["width"]) {
		$heading_wrapper_style .= 'width: ' . intVal($atts['width']) . '%;';
	}

	if ($atts["align"] == 'center') {
		$heading_wrapper_style .= 'margin-left: auto; margin-right: auto;';
	} elseif ($atts["align"] == 'left') {
		$heading_wrapper_style .= 'margin-right: auto;';
	} elseif ($atts["align"] == 'right') {
		$heading_wrapper_style .= 'margin-left: auto;';
	}
		$heading_wrapper_style .= 'margin-bottom: ' . intVal($atts['margin']) . 'px;';


	$heading_style = '';
	$heading_style .= 'font-size: ' . intVal($atts['size']) . 'px;';
	$heading_style .= ($atts['color']) ? ' color: ' . $atts['color'] . ';' : '';
	
	$css .= '#'.$id.'.yt-heading{'.$heading_wrapper_style.'}';
	$css .= '#'.$id.' .yt-heading-inner{'.$heading_style.'}';
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	
	return '<div class="yt-heading yt-heading-style-' . $atts['style'] . ' yt-heading-align-' . $atts['align'] . '" id="'.$id.'"><'.$atts['heading'].' class="yt-heading-inner">' . parse_shortcode(str_replace(array("<br/>","<br />","<p></p>"), " ", $content)) . '</'.$atts['heading'].'></div>';
}
?>