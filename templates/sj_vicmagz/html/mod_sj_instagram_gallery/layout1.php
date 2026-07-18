<?php
/**
 * @package Sj Vm Listing Tabs
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */

defined('_JEXEC') or die;

    JHtml::stylesheet('modules/' . $module->module . '/assets/css/sj-instagram-gallery.css');
    JHtml::stylesheet('modules/' . $module->module . '/assets/css/animate.css');
    JHtml::stylesheet('modules/' . $module->module . '/assets/css/owl.carousel.css');
    JHtml::stylesheet('modules/' . $module->module . '/assets/css/jquery.fancybox.css');
    if (!defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1") {
        JHtml::script('modules/' . $module->module . '/assets/js/jquery-2.1.3.min.js');
        JHtml::script('modules/' . $module->module . '/assets/js/jquery-noconflict.js');
        define('SMART_JQUERY', 1);
    }
    JHtml::script('modules/' . $module->module . '/assets/js/owl.carousel.js');
    JHtml::script('modules/' . $module->module . '/assets/js/jquery.fancybox.js');
    instagramGalleryImageHelper::setDefault($params);

    $instance = rand() . time();
    $tag_id = 'sj_instagram_gallery_' . $module->id;
    $options = $params->toObject();
    ?>
    <!--[if lt IE 9]>
    <div id="<?php echo $tag_id; ?>" class="sj-instagram-gallery instagram-layout1 msie lt-ie9 first-load"><![endif]-->
    <!--[if IE 9]>
    <div id="<?php echo $tag_id; ?>" class="sj-instagram-gallery instagram-layout1 msie first-load"><![endif]-->
    <!--[if gt IE 9]><!-->
    <div id="<?php echo $tag_id; ?>" class="sj-instagram-gallery instagram-layout1 first-load"><!--<![endif]-->
        <?php if (!empty($options->pretext)) { ?>
            <div class="pre-text"><?php echo $options->pretext; ?></div>
        <?php } ?>
            <div class="instagram-wrap">
            <!-- End Tabs-->
            <div class="instagram-items-container" ><!--Begin Items-->
                <?php require JModuleHelper::getLayoutPath($module->module, $layout . '_items'); ?>
            </div>
            <!--End Items-->
        </div>
    <?php if (!empty($options->posttext)) { ?>
        <div class="post-text"><?php echo $options->posttext; ?></div>
    <?php } ?>
    </div>




