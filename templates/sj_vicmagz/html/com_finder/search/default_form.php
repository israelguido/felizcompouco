<?php

/**
 * VicMagz override — formulário de busca
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
if ($this->params->get('show_autosuggest', 1)) {
	$this->getDocument()->getWebAssetManager()->usePreset('awesomplete');
	$this->getDocument()->addScriptOptions(
		'finder-search',
		['url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component', false)]
	);

	Text::script('COM_FINDER_SEARCH_FORM_LIST_LABEL');
	Text::script('JLIB_JS_AJAX_ERROR_OTHER');
	Text::script('JLIB_JS_AJAX_ERROR_PARSE');
}
?>

<form action="<?php echo Route::_($this->query->toUri()); ?>" method="get" class="js-finder-searchform fcp-finder-searchform" id="finder-search">
	<?php echo $this->getFields(); ?>

	<fieldset class="com-finder__search word fcp-finder__search">
		<label for="q" class="element-invisible"><?php echo Text::_('COM_FINDER_SEARCH_TERMS'); ?></label>
		<div class="fcp-finder__controls">
			<input type="text"
				name="q"
				id="q"
				class="js-finder-search-query inputbox"
				value="<?php echo $this->escape($this->query->input); ?>"
				placeholder="<?php echo $this->escape(Text::_('COM_FINDER_SEARCH_TERMS')); ?>"
				autocomplete="off">
			<button type="submit" class="btn btn-color">
				<i class="fa fa-search" aria-hidden="true"></i>
				Buscar
			</button>
			<?php if ($this->params->get('show_advanced', 1)) : ?>
				<?php HTMLHelper::_('bootstrap.collapse'); ?>
				<button class="btn btn-link fcp-finder__advanced-toggle"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#advancedSearch"
					aria-expanded="<?php echo $this->params->get('expand_advanced', 0) ? 'true' : 'false'; ?>">
					<?php echo Text::_('COM_FINDER_ADVANCED_SEARCH_TOGGLE'); ?>
				</button>
			<?php endif; ?>
		</div>
	</fieldset>

	<?php if ($this->params->get('show_advanced', 1)) : ?>
		<fieldset id="advancedSearch"
			class="com-finder__advanced js-finder-advanced collapse fcp-finder__advanced<?php echo $this->params->get('expand_advanced', 0) ? ' show' : ''; ?>">
			<?php if ($this->params->get('show_advanced_tips', 1)) : ?>
				<div class="fcp-finder__tips">
					<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_INTRO'); ?>
					<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_AND'); ?>
					<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_NOT'); ?>
					<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_OR'); ?>
				</div>
			<?php endif; ?>
			<div id="finder-filter-window" class="com-finder__filter">
				<?php echo HTMLHelper::_('filter.select', $this->query, $this->params); ?>
			</div>
		</fieldset>
	<?php endif; ?>
</form>
