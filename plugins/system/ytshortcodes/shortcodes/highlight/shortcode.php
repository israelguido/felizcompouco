<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function highlightYTShortcode($atts = null, $content = null) {
    $atts = ytshortcode_atts(array(
        'background' => '#ddff99',
        'bg'         => null,
        'color'      => '#000000',
        'size'       => '14',
        'class'      => ''
	), $atts, 'highlight');
	$css = '';
	$id = uniqid('highlight_').rand().time(); 
    if ($atts['bg'] !== null)
        $atts['background'] = $atts['bg'];
	
	$css .= '#'.$id.'.yt-highlight{background:' . $atts['background'] . ';color:' . $atts['color'] . '; font-size:'.$atts['size'].'px; line-height:'.$atts['size'].'px}';
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
    JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/highlight/css/highlight.css");
    return '<span id="'.$id.'" class="yt-highlight' . trim($atts['class']) . '">&nbsp;' . parse_shortcode(str_replace(array("<br/>","<br />","<p></p>"), " ", $content)) . '&nbsp;</span>';
}
?>