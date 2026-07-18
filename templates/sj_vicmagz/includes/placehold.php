<?php
/*
 * ------------------------------------------------------------------------
 * Copyright (C) 2009 - 2013 The YouTech JSC. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: The YouTech JSC
 * Websites: http://www.smartaddons.com - http://www.cmsportal.net
 * ------------------------------------------------------------------------
*/
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
global $is_placehold;
global $placehold_size;

// Array param for cookie
$placehold_size = array (
	//default
	'xsmall' => '90x62',
	'small' => '370x260',
	'medium' => '570x400',
	'large' => '770x540',
	'xlarge' => '1170x820',
	'listing' => '370x260',
	'grid' => '770x540',
	'tag_user' => '370x260',
	'article' => '770x540',
	//custom
	'related_items'=>'370x260',
	'slideshow' => '1150x450',
	'popular' => '570x400',
	'popular_video' => '270x180',
	'education' => '285x190',
	'about_k2' => '370x230',
	'events_listing' => '270x200',
	'events_item' => '505x375',
	'k2_style_one' => '390x250',
	'k2_mega' => '330x230',
);

$is_placehold = 1;

if (!function_exists ('yt_placehold') ) {
	function yt_placehold ($size = '100x100',$icon='0xe942', $alt = '', $title = '' ) {
		return '<img src="http://placehold.it/'.$size.'" alt = "'. $alt .'" title = "'. $title .'"/>';
	}
}
?>