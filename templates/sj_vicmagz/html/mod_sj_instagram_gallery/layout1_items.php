<?php
/**
 * @package Sj Instagram gallery
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2015 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */

defined('_JEXEC') or die;

$users_id = $params->get('users_id');
$access_token = $params->get('access_token');
$limit_image = $params->get('limit_image');
$type_show = $params->get('type_show');
$nb_rows = $params->get('nb_rows');
$show_title = $params->get('show_title');
$title = $params->get('title');
$full_name = $params->get('full_name');
$target = $params->get('target');
$j = 0;
$class_instagram = 'instagram00-' . $params->get('nb-column1', 6) . ' instagram01-' . $params->get('nb-column1', 6) . ' instagram02-' . $params->get('nb-column2', 4) . ' instagram03-' . $params->get('nb-column3', 2) . ' instagram04-' . $params->get('nb-column4', 1);
$json = @file_get_contents('https://api.instagram.com/v1/users/'.$users_id.'/media/recent?access_token='.$access_token.'&count='.$limit_image);
$json_output = json_decode($json, true);
$count  = count($json_output['data']);
if (isset($json_output['data']) && !empty($json_output['data'])){
$_link_title = isset($json_output['data'][0]['user']['username']) ? 'https://instagram.com/'.$json_output['data'][0]['user']['username'] : '#';
$count  = count($json_output['data']);

?>
<div class="instagram-items">
    <?php if($show_title) { ?>
    <h3 class='title_instagram'>
		<a href="<?php echo $_link_title; ?>" target="<?php echo $target ;?>" title="<?php echo $title; ?>">
			<?php echo $title; ?>
		</a>
	</h3>
    <?php } ?>
    <?php if(!empty($count)) { ?>
    <?php if ($type_show == 'slider') { ?>
        <div class="instagram-items-inner owl2-carousel" id="instagram_items_inner_<?php echo $module->id ;?>">
    <?php } else { ?>
        <div class="instagram-items-inner <?php echo $class_instagram;?>">
    <?php } ?>

    <?php for($i=0; $i < $count ; $i++) {
    $j++; ?>
        <?php if($type_show == 'slider' && ($j % $nb_rows == 1 || $nb_rows == 1)) { ?>
            <div class="instagram-item">
        <?php }
            if ($type_show == 'simple'){ ?>
                <div class="instagram-item">
            <?php } ?>
                <div class="instagram_users">
                    <div class="img_users">
                        <a title="<?php echo $json_output['data'][$i]['user']['full_name'] ;?>" target="<?php echo $target ;?>"  data-target="<?php echo $target ;?>" data-href="<?php echo $json_output['data'][$i]['link']?>" href="<?php echo $json_output['data'][$i]['link'] ; ?>" class="instagram_gallery_image gallery_image_<?php echo $module->id ;?>" rel="next">
                            <img class="image_users" src="<?php echo $json_output['data'][$i]['images']['low_resolution']['url'] ?>" title="<?php echo $json_output['data'][$i]['user']['full_name'] ;?>" alt="<?php echo $json_output['data'][$i]['user']['full_name'] ;?>" />
							<span><?php echo $json_output['data'][$i]['user']['full_name'] ;?></span>
						</a>
                    </div>
                </div>
            <?php if($type_show == 'slider' && ($j % $nb_rows == 0 || $j == $count)) { ?>
                </div>
            <?php }
                if ($type_show == 'simple'){ ?>
                </div>
        <?php } ?>
    <?php } ?>
    </div>
            <?php } else { ?>
                <div class="no-images"><?php echo "No Images or  Users ID and Access Token incorrect" ?></div>
            <?php } ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($){
        <?php if ($params->get('type_show') == 'slider') { ?>
        var $tag_id = $('#sj_instagram_gallery_<?php echo $module->id; ?>'),
            total_product = <?php echo round($count/$nb_rows) ;?>,
            tab_active = $('.instagram-items-inner', $tag_id);
            nb_column1 = <?php echo $params->get('nb-column1'); ?>,
            nb_column2 = <?php echo $params->get('nb-column2'); ?>,
            nb_column3 = <?php echo $params->get('nb-column3'); ?>,
            nb_column4 = <?php echo $params->get('nb-column4'); ?>;
        tab_active.owlCarousel2({
            nav: <?php echo $params->get('display_nav') ; ?>,
            dots: false,
            margin: 0,
            loop:  <?php echo $params->get('display_loop') ; ?>,
            autoplay: <?php echo $params->get('autoplay'); ?>,
            autoplayHoverPause: <?php echo $params->get('pausehover') ; ?>,
            autoplayTimeout: <?php if((int)$params->get('autoplay_timeout')){
                    echo ((int)$params->get('autoplay_timeout'));
                    }else{
                        echo "5000";
                    }
                 ?>,
            autoplaySpeed: <?php if((int)$params->get('autoplay_speed')){
                    echo ((int)$params->get('autoplay_speed'));
                }else{
                    echo "2000";
                }
                ?>,
            mouseDrag: <?php echo  $params->get('mousedrag'); ?>,
            touchDrag: <?php echo $params->get('touchdrag'); ?>,
            navRewind: true,
            navText: [ '', '' ],
            responsive: {
                0: {
                    items: nb_column4,
                    nav: total_product <= nb_column4 ? false : ((<?php echo $params->get('display_nav') ; ?>) ? true: false),
                },
                480: {
                    items: nb_column3,
                    nav: total_product <= nb_column3 ? false : ((<?php echo $params->get('display_nav') ; ?>) ? true: false),
                },
                768: {
                    items: nb_column2,
                    nav: total_product <= nb_column2 ? false : ((<?php echo $params->get('display_nav') ; ?>) ? true: false),
                },
                1200: {
                    items: nb_column1,
                    nav: total_product <= nb_column1 ? false : ((<?php echo $params->get('display_nav') ; ?>) ? true: false),
                },
            }
        });
        $("#instagram_items_inner_<?php echo $module->id ;?> .owl2-prev").append('&lsaquo;');
        $("#instagram_items_inner_<?php echo $module->id ;?> .owl2-next").append('&rsaquo;');
        <?php } ?>
        // $(".gallery_image_<?php echo $module->id ;?>").fancybox({
        // prevEffect	 : 'none',
        // nextEffect	 : 'none',
        // helpers: {
            // thumbs	: {
            // width	: 50,
            // height	: 50
            // },
            // <?php if($full_name) { ?>
            // title : false
            // <?php } ?>
        // }
        // });
    });
</script>
<?php }
?>
