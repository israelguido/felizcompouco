<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

if (empty($list)) {
	return;
}

$doc  = Factory::getDocument();
$base = Uri::root(true) . '/modules/mod_fcp_articles/assets';

$doc->addStyleSheet($base . '/css/style.css');
$doc->addStyleSheet($base . '/css/css3.css');
$doc->addStyleSheet($base . '/css/owl.carousel.css');
$fcpSliderCss = JPATH_ROOT . '/modules/mod_fcp_articles/assets/css/fcp-slider.css';
$doc->addStyleSheet($base . '/css/fcp-slider.css', ['version' => is_file($fcpSliderCss) ? filemtime($fcpSliderCss) : null]);

$tag_id = 'sj_extra_slider_' . (int) $module->id;
$theme  = $params->get('theme', 'style1');

$nb0 = (int) $params->get('nb_column0', 3);
$nb1 = (int) $params->get('nb_column1', 3);
$nb2 = (int) $params->get('nb_column2', 2);
$nb3 = 1;
$nb4 = 1;

$class_respl = 'extra-resp00-' . $nb0 . ' extra-resp01-' . $nb1 . ' extra-resp02-' . $nb2;
$showTitle   = (int) $params->get('show_title', 1);
$titleLim    = (int) $params->get('title_limit', 60);
$owlUrl      = $base . '/js/owl.carousel.js';
?>
<div class="moduletable <?php echo htmlspecialchars((string) $params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8'); ?>">
	<div id="<?php echo $tag_id; ?>"
		 class="sj-extra-slider trending button-type1 <?php echo $class_respl; ?> fcp-slider-pending"
		 data-fcp-slider="1"
		 data-mode="trending"
		 data-center="0"
		 data-margin="20"
		 data-dots="1"
		 data-nb0="<?php echo $nb0; ?>"
		 data-nb1="<?php echo $nb1; ?>"
		 data-nb2="<?php echo $nb2; ?>"
		 data-nb3="<?php echo $nb3; ?>"
		 data-nb4="<?php echo $nb4; ?>">
		<div class="extraslider-inner owl-carousel">
			<?php foreach ($list as $item) : ?>
				<div class="item">
					<div class="item-wrap <?php echo htmlspecialchars($theme, ENT_QUOTES, 'UTF-8'); ?>">
						<div class="item-wrap-inner">
							<div class="item-image">
								<a href="<?php echo htmlspecialchars($item->link, ENT_QUOTES, 'UTF-8'); ?>">
									<?php echo ModFcpGuiaHelper::renderImg($item, ['loading' => 'eager']); ?>
								</a>
							</div>
							<div class="item-info">
								<?php if ($showTitle) : ?>
									<div class="item-title">
										<a href="<?php echo htmlspecialchars($item->link, ENT_QUOTES, 'UTF-8'); ?>">
											<?php echo htmlspecialchars($item->displaytitle, ENT_QUOTES, 'UTF-8'); ?>
										</a>
									</div>
								<?php endif; ?>
								<div class="item-content">
									<div class="item-readmore">
										<a href="<?php echo htmlspecialchars($item->link, ENT_QUOTES, 'UTF-8'); ?>">Saiba mais</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<script>
(function () {
	var owlSrc = <?php echo json_encode($owlUrl); ?>;

	function whenReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function loadScript(src, cb) {
		var s = document.createElement('script');
		s.src = src;
		s.onload = cb;
		s.onerror = function () { console.error('FCP guia: failed to load', src); };
		document.head.appendChild(s);
	}

	function initSliders() {
		if (typeof jQuery === 'undefined') {
			return setTimeout(initSliders, 40);
		}
		var $ = jQuery;
		if (typeof $.fn.owlCarousel !== 'function') {
			return loadScript(owlSrc, initSliders);
		}

		$('[data-fcp-slider="1"]').each(function () {
			var $el = $(this);
			var $slider = $el.find('.extraslider-inner');
			if (!$slider.length || $slider.hasClass('owl-loaded')) {
				$el.removeClass('fcp-slider-pending');
				return;
			}

			var margin = parseInt($el.data('margin'), 10) || 0;
			var dots = $el.data('dots') == 1;
			var nb0 = parseInt($el.data('nb0'), 10) || 3;
			var nb1 = parseInt($el.data('nb1'), 10) || 3;
			var nb2 = parseInt($el.data('nb2'), 10) || 2;
			var nb3 = parseInt($el.data('nb3'), 10) || 1;
			var nb4 = parseInt($el.data('nb4'), 10) || 1;

			$slider.owlCarousel({
				margin: margin,
				slideBy: 1,
				autoplay: true,
				autoplayHoverPause: true,
				autoplayTimeout: 5000,
				autoplaySpeed: 600,
				mouseDrag: true,
				touchDrag: true,
				center: false,
				loop: true,
				items: nb0,
				responsive: {
					0: { items: nb4 },
					480: { items: nb3 },
					768: { items: nb2 },
					992: { items: nb1 },
					1200: { items: nb0 }
				},
				dots: dots,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>']
			});

			$el.removeClass('fcp-slider-pending');
		});
	}

	whenReady(initSliders);
})();
</script>
