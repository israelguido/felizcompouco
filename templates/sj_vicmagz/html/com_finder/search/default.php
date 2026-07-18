<?php

/**
 * VicMagz override — Smart Search
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
$this->getDocument()->getWebAssetManager()
	->useStyle('com_finder.finder')
	->useScript('com_finder.finder');

$this->getDocument()->addStyleSheet(
	Uri::root(true) . '/templates/sj_vicmagz/css/fcp-search.css'
);
?>
<div class="com-finder finder fcp-finder">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php if ($this->escape($this->params->get('page_heading'))) : ?>
					<?php echo $this->escape($this->params->get('page_heading')); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get('page_title')); ?>
				<?php endif; ?>
			</h1>
		</div>
	<?php else : ?>
		<div class="page-header">
			<h1>Busca</h1>
		</div>
	<?php endif; ?>

	<div id="search-form" class="com-finder__form fcp-finder__form">
		<?php echo $this->loadTemplate('form'); ?>
	</div>

	<?php if ($this->query->search === true) : ?>
		<div id="search-results" class="com-finder__results fcp-finder__results">
			<?php echo $this->loadTemplate('results'); ?>
		</div>
	<?php endif; ?>
</div>
