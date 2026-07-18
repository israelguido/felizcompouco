<?php

/**
 * VicMagz override — lista de resultados
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
?>
<?php if (($this->suggested && $this->params->get('show_suggested_query', 1)) || ($this->explained && $this->params->get('show_explained_query', 1))) : ?>
	<div id="search-query-explained" class="com-finder__explained fcp-finder__explained">
		<?php if ($this->suggested && $this->params->get('show_suggested_query', 1)) : ?>
			<?php $uri = Uri::getInstance($this->query->toUri()); ?>
			<?php $uri->setVar('q', $this->suggested); ?>
			<?php $linkUrl = Route::_($uri->toString(['path', 'query'])); ?>
			<?php $link = '<a href="' . $linkUrl . '">' . $this->escape($this->suggested) . '</a>'; ?>
			<?php echo Text::sprintf('COM_FINDER_SEARCH_SIMILAR', $link); ?>
		<?php elseif ($this->explained && $this->params->get('show_explained_query', 1)) : ?>
			<p><?php echo Text::plural('COM_FINDER_QUERY_RESULTS', $this->total, $this->explained); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if (($this->total === 0) || ($this->total === null)) : ?>
	<div id="search-result-empty" class="com-finder__empty fcp-finder__empty">
		<h2><?php echo Text::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php $multilang = Factory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
		<p><?php echo Text::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->query->input)); ?></p>
	</div>
	<?php return; ?>
<?php endif; ?>

<?php if ($this->params->get('show_sort_order', 0) && !empty($this->sortOrderFields) && !empty($this->results)) : ?>
	<div id="search-sorting" class="com-finder__sorting">
		<?php echo $this->loadTemplate('sorting'); ?>
	</div>
<?php endif; ?>

<?php if (!empty($this->query->highlight) && $this->params->get('highlight_terms', 1)) : ?>
	<?php
	$this->getDocument()->getWebAssetManager()->useScript('highlight');
	$this->getDocument()->addScriptOptions(
		'highlight',
		[[
			'class'     => 'js-highlight',
			'highLight' => array_slice($this->query->highlight, 0, 10),
		]]
	);
	?>
<?php endif; ?>

<ul id="search-result-list" class="js-highlight com-finder__results-list search-results list-striped fcp-finder__list" start="<?php echo (int) $this->pagination->limitstart + 1; ?>">
	<?php $this->baseUrl = Uri::getInstance()->toString(['scheme', 'host', 'port']); ?>
	<?php foreach ($this->results as $i => $result) : ?>
		<?php $this->result = &$result; ?>
		<?php $this->result->counter = $i + 1; ?>
		<?php $layout = $this->getLayoutFile($this->result->layout); ?>
		<?php echo $this->loadTemplate($layout); ?>
	<?php endforeach; ?>
</ul>

<div class="com-finder__navigation search-pagination fcp-finder__pagination">
	<?php if ($this->params->get('show_pagination', 1) > 0) : ?>
		<div class="com-finder__pages">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_pagination_results', 1) > 0) : ?>
		<div class="com-finder__counter search-pages-counter">
			<?php $start = (int) $this->pagination->limitstart + 1; ?>
			<?php $total = (int) $this->pagination->total; ?>
			<?php $limit = (int) $this->pagination->limit * $this->pagination->pagesCurrent; ?>
			<?php $limit = (int) min($limit, $total); ?>
			<?php echo Text::sprintf('COM_FINDER_SEARCH_RESULTS_OF', $start, $limit, $total); ?>
		</div>
	<?php endif; ?>
</div>
