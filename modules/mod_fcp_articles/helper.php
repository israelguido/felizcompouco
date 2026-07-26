<?php
/**
 * @package  mod_fcp_articles
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\Uri\Uri;

class ModFcpArticlesHelper
{
	public static function getList($params)
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$user  = $app->getIdentity();
		$count = max(1, (int) $params->get('count', 5));
		$now   = Factory::getDate()->toSql();

		$query = $db->getQuery(true)
			->select([
				'a.id', 'a.title', 'a.alias', 'a.introtext', 'a.fulltext', 'a.images',
				'a.catid', 'a.created', 'a.publish_up', 'a.hits', 'a.featured', 'a.created_by',
				'c.title AS category_title', 'c.alias AS category_alias',
			])
			->from($db->quoteName('#__content', 'a'))
			->join('INNER', $db->quoteName('#__categories', 'c'), 'c.id = a.catid')
			->where('a.state = 1')
			->where('c.published = 1')
			->where('(a.publish_up IS NULL OR a.publish_up <= :now1)')
			->where('(a.publish_down IS NULL OR a.publish_down >= :now2)')
			->bind(':now1', $now)
			->bind(':now2', $now);

		$levels = $user->getAuthorisedViewLevels();
		$query->whereIn('a.access', $levels, ParameterType::INTEGER)
			->whereIn('c.access', $levels, ParameterType::INTEGER);

		$catids = (array) $params->get('catid', []);
		$catids = array_values(array_filter(array_map('intval', $catids)));

		if ($catids) {
			if ((int) $params->get('show_child', 1)) {
				$expanded = self::expandCategories($catids);
				$query->whereIn('a.catid', $expanded, ParameterType::INTEGER);
			} else {
				$query->whereIn('a.catid', $catids, ParameterType::INTEGER);
			}
		}

		$featured = (int) $params->get('featured', 0);
		if ($featured === 1) {
			$query->where('a.featured = 1');
		} elseif ($featured === 2) {
			$query->where('a.featured = 0');
		}

		switch ($params->get('ordering', 'latest')) {
			case 'hits':
				$query->order('a.hits DESC');
				break;
			case 'featured':
				$query->order('a.featured DESC, a.publish_up DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			default:
				$query->order('COALESCE(a.publish_up, a.created) DESC');
		}

		$db->setQuery($query, 0, $count);
		$items = $db->loadObjectList() ?: [];

		$titleLimit = (int) $params->get('title_limit', 60);
		$introLimit = (int) $params->get('intro_limit', 100);

		foreach ($items as $item) {
			$item->slug = $item->id . ':' . $item->alias;
			$item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid));
			$item->categoryname = $item->category_title;
			$item->categoryLink = Route::_(RouteHelper::getCategoryRoute($item->catid));
			$item->author = '';
			$item->authorLink = '#';
			$item->numOfComments = 0;
			$item->tags = '';
			$item->fulltext = $item->fulltext ?: '';
			$img = self::getIntroImage($item);
			$item->image       = $img['src'];
			$item->image_alt   = $img['alt'];
			$item->image_class = $img['class'];
			$item->displaytitle = self::truncate($item->title, $titleLimit);
			$item->displayIntrotext = self::truncate(strip_tags($item->introtext), $introLimit);
			$item->introtext = $item->displayIntrotext;
			$item->event = (object) [
				'BeforeDisplay' => '',
				'K2BeforeDisplay' => '',
				'AfterDisplayTitle' => '',
				'K2AfterDisplayTitle' => '',
				'BeforeDisplayContent' => '',
				'K2BeforeDisplayContent' => '',
				'AfterDisplayContent' => '',
				'K2AfterDisplayContent' => '',
				'AfterDisplay' => '',
				'K2AfterDisplay' => '',
				'K2CommentsCounter' => '',
			];
		}

		// Prefer items with images for visual layouts
		$layout = str_replace(['_:', 'sj_vicmagz:'], '', (string) $params->get('layout', 'popular'));
		if (in_array($layout, ['slideshow', 'trending', 'popular'], true)) {
			$withImage = array_values(array_filter($items, static function ($item) {
				return $item->image !== '';
			}));
			if ($withImage) {
				$items = $withImage;
			}
		}

		return $items;
	}

	protected static function expandCategories(array $catids): array
	{
		$db = Factory::getDbo();
		$all = $catids;

		foreach ($catids as $id) {
			$query = $db->getQuery(true)
				->select('c.id')
				->from($db->quoteName('#__categories', 'c'))
				->join('INNER', $db->quoteName('#__categories', 'p'), 'c.lft BETWEEN p.lft AND p.rgt')
				->where('p.id = :pid')
				->where('c.extension = ' . $db->quote('com_content'))
				->where('c.published = 1')
				->bind(':pid', $id, ParameterType::INTEGER);
			$db->setQuery($query);
			$all = array_merge($all, array_map('intval', (array) $db->loadColumn()));
		}

		return array_values(array_unique($all));
	}

	/**
	 * Thumbnail = apenas "Imagem da Introdução" do artigo.
	 * alt = descrição alternativa; class = campo Classe CSS (float_intro no Joomla 6).
	 *
	 * @return array{src:string,alt:string,class:string}
	 */
	public static function getIntroImage($item): array
	{
		$images = json_decode($item->images ?: '{}');
		$empty  = ['src' => '', 'alt' => '', 'class' => ''];

		if (!is_object($images) || empty($images->image_intro)) {
			return $empty;
		}

		$src = (string) $images->image_intro;

		// Joomla 4+/6 media: path#joomlaImage://...
		if (strpos($src, '#') !== false) {
			$cleaned = HTMLHelper::_('cleanImageURL', $src);
			$src = is_object($cleaned) && !empty($cleaned->url) ? (string) $cleaned->url : strtok($src, '#');
		}

		$src = trim($src);
		if ($src === '') {
			return $empty;
		}

		if (strpos($src, 'http') !== 0 && strpos($src, '//') !== 0) {
			$rel = ltrim(preg_replace('#^' . preg_quote(Uri::root(true), '#') . '/#', '', $src), '/');
			if ($rel !== '' && !is_file(JPATH_ROOT . '/' . $rel)) {
				return $empty;
			}
			$src = Uri::root(true) . '/' . $rel;
		}

		$alt = trim((string) ($images->image_intro_alt ?? ''));
		if ($alt === '' && empty($images->image_intro_alt_empty)) {
			$alt = (string) ($item->title ?? '');
		}

		// No Joomla 6 o campo "Classe CSS" grava em float_intro (legado: float_into)
		$class = trim((string) ($images->float_intro ?? $images->float_into ?? ''));
		$class = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $class) ?: '';

		return [
			'src'   => $src,
			'alt'   => $alt,
			'class' => $class,
		];
	}

	/** @deprecated Use getIntroImage() */
	public static function getImage($item): string
	{
		return self::getIntroImage($item)['src'];
	}

	/**
	 * Renderiza <img> da introdução com alt e class do artigo.
	 */
	public static function renderIntroImg($item, array $extraAttrs = []): string
	{
		$src = (string) ($item->image ?? '');
		if ($src === '') {
			return '';
		}

		$attrs = array_merge([
			'src' => $src,
			'alt' => (string) ($item->image_alt ?? $item->title ?? ''),
		], $extraAttrs);

		$class = trim((string) ($item->image_class ?? ''));
		if ($class !== '') {
			$attrs['class'] = trim(($attrs['class'] ?? '') . ' ' . $class);
		}

		$html = '<img';
		foreach ($attrs as $name => $value) {
			if ($value === null || $value === false || $value === '') {
				continue;
			}
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_:.-]*$/', (string) $name)) {
				continue;
			}
			$html .= ' ' . $name . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
		}
		$html .= ' />';

		return $html;
	}

	public static function truncate($text, $limit)
	{
		$text = html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8');

		if ($limit <= 0 || mb_strlen($text) <= $limit) {
			return $text;
		}

		return rtrim(mb_substr($text, 0, $limit)) . '…';
	}

	public static function formatDate($date, $format = 'd M')
	{
		if (empty($date) || $date === '0000-00-00 00:00:00') {
			return '';
		}

		return HTMLHelper::_('date', $date, $format);
	}
}
