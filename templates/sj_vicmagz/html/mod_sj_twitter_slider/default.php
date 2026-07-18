<?php
/**
 * @package SJ Twitter Slider
 * @version 1.0.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die ();

JHtml::stylesheet('modules/' . $module->module . '/assets/css/styles.css');
if (!defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1") {
	JHtml::script('modules/' . $module->module . '/assets/js/jquery-1.8.2.min.js');
	JHtml::script('modules/' . $module->module . '/assets/js/jquery-noconflict.js');
	define('SMART_JQUERY', 1);
}

//JHtml::script('modules/' . $module->module . '/assets/js/jcarousel.js');
JHtml::script('modules/' . $module->module . '/assets/js/jquery.cj-swipe.js');
JHtml::script('modules/' . $module->module . '/assets/js/twitterFetcher_min.js');
JHtml::script('modules/' . $module->module . '/assets/js/twitterFetcher.js');

$tag_id = 'sj_twitter_slider_' . rand() . time();

$id_user = $params->get('id_user');
$count_item = (int)$params->get('count', 6);
$display_avatars = $params->get('display_avatars',1) ? 'true' : 'false';
$screen_label = $params->get('screenname');


$play = (int)$params->get('play', 1);
$interval = (int)$params->get('interval', 4000);
$interval = ($play) ? $interval : 0;

$start = (int)$params->get('start', 1);
$start =  $start - 1;

$pause_hover = $params->get('pause_hover', 'hover');
$pause_hover = ($pause_hover == 'hover') ? 'hover' : '';

$effect = $params->get('effect', 'slide');
$effect = ($effect == 'slide') ? 'slide' : '';

$slider_id = 'ts_slider_wap' . rand() . time() . $module->id;
if ($params->get('pretext') != '') {
	?>
	<div class="sc-pretext">
		<?php echo $params->get('pretext'); ?>
	</div>
	<?php
}?>

<!--Begin sj-twitter-slider-->
<div id="<?php echo $tag_id; ?>" class="sj-twitter-slider">
	<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
	<!--Begin ts-wrap-->
	<div class="ts-wrap ts-slider-wrap <?php echo $effect; ?>" id="<?php echo $slider_id; ?>"
		 data-interval="<?php echo $interval; ?>" data-pause="<?php echo $pause_hover; ?>">
		<?php if ((int)$params->get('display_direction_button', 1)) { ?>
			<!--<<a class="ts-ctr-prev ts-ctr" href="#<?php echo $slider_id; ?>" data-jslide="prev">&lsaquo;</a>-->
		<?php } ?>
		<div id="content-twiter" class="ts-items"></div>
		<?php if ((int)$params->get('display_direction_button', 1)) { ?>
			<!--<a class="ts-ctr-next ts-ctr" href="#<?php echo $slider_id; ?>" data-jslide="next">&rsaquo;</a>-->
		<?php } ?>
		<?php
		if ((int)$params->get('display_follow_button', 1)) {
			?>
			<!--Begin ts-btn-follow-->
			<div class="ts-btn-follow">
				<a href="https://twitter.com/<?php echo $screen_label; ?>" class="twitter-follow-button"
				   data-show-count="false">Follow @<?php echo $screen_label; ?></a>
				<script>!function (d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (!d.getElementById(id)) {
							js = d.createElement(s);
							js.id = id;
							js.src = "https://platform.twitter.com/widgets.js";
							fjs.parentNode.insertBefore(js, fjs);
						}
					}(document, "script", "twitter-wjs");</script>
			</div>
			<!--End ts-btn-follow-->
		<?php } ?>
	</div>
	<!--End ts-wrap-->
</div>
<?php
if ($params->get('posttext') != '') {
	?>
	<div class="sc-posttext">
		<?php echo $params->get('posttext'); ?>
	</div>
<?php } ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var config = {
			"id": '<?php echo $id_user; ?>',
			"domId": '',
			"maxTweets": <?php echo $count_item; ?>,
			"enableLinks": true,
			"showUser": <?php echo $display_avatars; ?>,
			"customCallback": handleTweets
		};
		function handleTweets(tweets){
			var x = tweets.length;
			var n = 0;
			var j = 0;
			var element = document.getElementById('content-twiter');
			var start = <?php echo $start; ?>;
			var slider_id = <?php echo $slider_id; ?> ;
			var html = '<ul class="list-item">';
			while(n < x) {
				//var active_cls = (n == start) ? 'active' : '';
				var active_cls = (n == start) ? '' : '';
				html += '<li class="ts-item item '+active_cls+'"><i class="fa fa-twitter"></i>' + tweets[n] + '</li>';
				n++;
			}
			html += '</ul>';
			//html += '<ul class="ts-ctr-pages">';
			//for(var k = 0; k < x; k++ ){
				//var sel_class = k == start ? " sel" : "";
				//html += '<li class="ts-ctr-page '+sel_class+'" href="#<?php echo $slider_id; ?>" data-jslide="'+k+'"></li>';
			//}
			html += '</ul>';
			element.innerHTML = html;
			//var this_li = $('li.ts-item').find('.user');
		    //this_li.find('span:eq(1)').css('margin', '6px');
			var this_a = $('.user').find('a');
		        //this_tweet = $('.tweet'),
				//this_img = this_a.find('img'),
				//this_span = this_a.find('span:eq(0)').html(),
				//this_name = this_a.find('span:last-child').clone().appendTo(this_tweet);
				//console.log(this_name);
			//this_tweet.append(this_name);	
		    //this_a.find('span:eq(0)').remove();
			
		    this_a.find('span:first-child').remove();
			this_a.find('img').remove();
			$('.tweet').prepend ($('.user').html());
			$('.ts-wrap').find('.user').remove();
			$('.ts-wrap').find('.interact').remove();
		    //this_a.prepend('' +
			//'<span class="ts-name">'+this_span+'</span>' +
			//'<span class="ts-avatar">' +
			//'<span class="ts-mask">' +
			//'<span class="ts-mask-logo">Open in Twitter</span>' +
			//'</span>' +
			//'<img src="'+this_img.attr('src')+'" />' +
			//'</span>'
		
			//);
			}
			
		twitterFetcher.fetch(config);

		
	});
</script>