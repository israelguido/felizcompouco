<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function testimonial_itemYTShortcode($atts, $content = null){
	extract(ytshortcode_atts(array(
		"author" => '',
		"position" => '',
		"avatar" => ''
	), $atts));
    $css='';
	$testimonial_item ='';
	$testimonial_avatar = '';
	if($avatar != '') {
        if(strpos($avatar,'http://')!== false){
            $avatar = $avatar;
        }else if( is_file($avatar) && strpos($avatar,'http://')!== true){
            $avatar = JURI::base().$avatar;
        }
        $testimonial_avatar .='<img class="item-img-avatar" src="' . $avatar . '" alt="'.$atts['author'].'" width="150" height="150"/> ';
    };
	$testimonial_item = 
	'<div class="item">
		<div class="item-wrap">
			<div class="item-wrap-inner">
				<div class="item-image">
					<div class="item-img-info">
						'.$testimonial_avatar.'
					</div>
					<div class="item-info">
						<div class="content">
						'.parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)).'
						</div><h5>'.$author.'</h5>
						<span class="position">'.$position.'</span>
					</div>
				</div>
			</div>
		</div>
	</div>';
	return $testimonial_item;
}
?>
