<?php

/**
 * VicMagz override — item de resultado (estilo magazine)
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\String\StringHelper;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */

// String do com_content (não carrega sozinha no Finder)
Factory::getLanguage()->load('com_content', JPATH_SITE, null, true);

$user             = $this->getCurrentUser();
$show_description = $this->params->get('show_description', 1);
$description      = '';

if ($show_description) {
	$term_length = StringHelper::strlen((string) $this->query->input);
	$desc_length = (int) $this->params->get('description_length', 180);
	$pad_length  = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;

	$full_description = (string) $this->result->description;
	if (!empty($this->result->summary) && !empty($this->result->body)) {
		$full_description = Helper::parse($this->result->summary . $this->result->body);
	}

	// Limpa HTML residual / placeholders
	$full_description = trim(strip_tags(html_entity_decode($full_description, ENT_QUOTES, 'UTF-8')));
	$full_description = preg_replace('/\s+/u', ' ', $full_description);

	if ($full_description !== '' && $full_description !== '-') {
		$pos   = $term_length ? StringHelper::strpos(StringHelper::strtolower($full_description), StringHelper::strtolower((string) $this->query->input)) : false;
		$start = ($pos && $pos > $pad_length) ? $pos - $pad_length : 0;
		$space = StringHelper::strpos($full_description, ' ', $start > 0 ? $start - 1 : 0);
		$start = ($space && $pos !== false && $space < $pos) ? $space + 1 : $start;
		$description = HTMLHelper::_('string.truncate', StringHelper::substr($full_description, $start), $desc_length, true);
		$description = trim($description, " \t\n\r\0\x0B-–—");
	}
}

$route = $this->result->route ? Route::_($this->result->route) : '#';
$image = !empty($this->result->imageUrl) ? $this->result->imageUrl : '';

$category = '';
$taxonomies = method_exists($this->result, 'getTaxonomy') ? $this->result->getTaxonomy() : [];
if (!empty($taxonomies['Category'])) {
	foreach ($taxonomies['Category'] as $node) {
		if ((int) $node->state === 1 && in_array($node->access, $user->getAuthorisedViewLevels())) {
			$category = $node->title;
			break;
		}
	}
}

$readMore = Text::_('COM_CONTENT_READ_MORE');
if ($readMore === 'COM_CONTENT_READ_MORE') {
	$readMore = 'Leia mais...';
}
?>
<li class="result__item fcp-finder__item<?php echo $image ? ' has-thumb' : ' no-thumb'; ?>">
	<?php if ($image) : ?>
		<div class="fcp-finder__thumb">
			<a href="<?php echo $route; ?>" tabindex="-1" aria-hidden="true">
				<img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>"
					 alt=""
					 loading="lazy"
					 width="220"
					 height="150">
			</a>
		</div>
	<?php endif; ?>

	<div class="fcp-finder__body">
		<div class="fcp-finder__meta">
			<?php if ($category) : ?>
				<span class="fcp-finder__cat"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
			<?php endif; ?>
			<?php if ($this->result->start_date && $this->params->get('show_date', 1)) : ?>
				<span class="fcp-finder__date">
					<?php echo HTMLHelper::_('date', $this->result->start_date, 'd M Y'); ?>
				</span>
			<?php endif; ?>
		</div>

		<h2 class="result-title result__title">
			<a href="<?php echo $route; ?>" class="result__title-link">
				<?php echo $this->escape($this->result->title); ?>
			</a>
		</h2>

		<?php if ($show_description && $description !== '') : ?>
			<p class="result-text result__description"><?php echo $description; ?></p>
		<?php endif; ?>

		<a class="fcp-finder__more" href="<?php echo $route; ?>">
			<?php echo htmlspecialchars($readMore, ENT_QUOTES, 'UTF-8'); ?>
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</a>
	</div>
</li>
