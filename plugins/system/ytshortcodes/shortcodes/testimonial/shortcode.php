<?php
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function testimonialYTShortcode($atts,$content = null){
    global $id_testimonial, $border_testimonial,$column_testimonial,$background_;
    extract (ytshortcode_atts(array(
        "column"    =>'1',
        "border"    =>'',
        "background"=>'',
        "yt_title"     =>'',
        "title_color" => '#ccc',
        "style" => '',
		"display_avatar" => 'false'
    ),$atts));
    $testimonial ='';
    $border_testimonial = $border;
    $column_testimonial = $column;
	$background_ = $background;
    $src_thumb='';
    $css ='';
    if(strpos($background,'http://')!== false){
        $src_thumb = $background;
    }else if( is_file($background) && strpos($background,'http://')!== true){
        $src_thumb = JURI::base().$background;
    }
    $id_testimonial = uniqid('yt_tes').rand().time();
    JHtml::_('jquery.framework');
	if (!defined ('OWL_CAROUSEL')){
		JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/owl.carousel.js");
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/assets/css/owl.carousel.css");
		define( 'OWL_CAROUSEL', 1 );
	}
    JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/testimonial/css/style.css");
	$tag_id = rand().time();
	
	$css .= '#'.$id_testimonial.'{background:url('.$src_thumb.');padding-top:10px;}';
	$css .= '#'.$id_testimonial.' .testimonial-title{color:'.$title_color.'; text-align:center}'; 
	$css .= '#'.$id_testimonial.' .item{border:'.$border_testimonial.'; '.(($background_!= '') ? "background:rgba(255,255,255,0.8)" : "").'}';
	$css .= '#'.$id_testimonial.' .item .item-img-avatar{border-radius:50%; width:auto; margin:0 auto; max-width:150px; max-height:150px}';
	
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
	$testimonial = '<div class="moduletable yt-clearfix '.(($display_avatar == 'true') ? "background" : "").' column-'.$column.'" id="'.$id_testimonial.'">
	<h3 class="testimonial-title">'.$yt_title.'</h3>
		<div id="'.$tag_id.'"
			 class="yt-testimonial button-type2">
			<div class="extraslider-inner" data-effect="fadeIn">';
	$testimonial .= parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
	$testimonial .='	</div>
		</div>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready(function ($) {
			;(function (element) {
				var $element = $(element),
						$extraslider = $(".extraslider-inner", $element),
						_delay = 800,
						_duration = 500,
						_effect = "fadeIn";

				$extraslider.on("initialized.owl.carousel", function () {
					var $item_active = $(".owl-item.active", $element);
					if ($item_active.length > 1 && _effect != "none") {
						_getAnimate($item_active);
					}
					else {
						var $item = $(".owl-item", $element);
						$item.css({"opacity": 1, "filter": "alpha(opacity = 100)"});
					}
					$(".owl-nav", $element).insertBefore($extraslider);
					$(".owl-controls", $element).insertAfter($extraslider);
					
				});

				$extraslider.owlCarousel({
				margin: 5,
				slideBy: 1,
				autoplay: true,
				autoplayHoverPause: true,
				autoplayTimeout: 5000,
				autoplaySpeed: 2000,
				startPosition: 0,
				mouseDrag: true,
				touchDrag: true,
				autoWidth: false,
				responsive: {
					0: 	{ items: 1 } ,
					480: { items: 1 },
					768: { items: '.$column.' },
					992: { items: '.$column.' },
					1200: {items: '.$column.'}
				},
					dotClass: "owl-dot",
					dotsClass: "owl-dots",
					dots: '.(($column == 1) ? "true" : "false").',
					dotsSpeed:5000,
					nav: '.(($column == 1) ? "true" : "false").',
					loop: true,
					navSpeed: 2000,
					navText: ["", ""],
					navClass: ["owl-prev", "owl-next"]

				});
				function _getAnimate($el) {
					if (_effect == "none") return;
					//if ($.browser.msie && parseInt($.browser.version, 10) <= 9) return;
					$extraslider.removeClass("extra-animate");
					$el.each(function (i) {
						var $_el = $(this);
						$(this).css({
							"-webkit-animation": _effect + " " + _duration + "ms ease both",
							"-moz-animation": _effect + " " + _duration + "ms ease both",
							"-o-animation": _effect + " " + _duration + "ms ease both",
							"animation": _effect + " " + _duration + "ms ease both",
							"-webkit-animation-delay": +i * _delay + "ms",
							"-moz-animation-delay": +i * _delay + "ms",
							"-o-animation-delay": +i * _delay + "ms",
							"animation-delay": +i * _delay + "ms",
							"opacity": 1
						}).animate({
							opacity: 1
						});

						if (i == $el.size() - 1) {
							$extraslider.addClass("extra-animate");
						}
					});
				}

				function _UngetAnimate($el) {
					$el.each(function (i) {
						$(this).css({
							"animation": "",
							"-webkit-animation": "",
							"-moz-animation": "",
							"-o-animation": "",
							"opacity": 0
						});
					});
				}

			})("#'.$tag_id.'");
		});
	</script>
</div>';
    return $testimonial;
}
?>
