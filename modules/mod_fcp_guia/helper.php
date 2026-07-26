<?php
/**
 * @package  mod_fcp_guia
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class ModFcpGuiaHelper
{
	public static function getList($params): array
	{
		$db    = Factory::getDbo();
		$count = max(1, (int) $params->get('count', 8));

		$query = $db->getQuery(true)
			->select([
				'a.id',
				'a.title',
				'a.alias',
				'a.image',
				'a.image_alt',
				'a.url',
				'a.introtext',
				'a.ordering',
			])
			->from($db->quoteName('#__fcp_guia', 'a'))
			->where($db->quoteName('a.state') . ' = 1')
			->order($db->quoteName('a.ordering') . ' ASC, ' . $db->quoteName('a.id') . ' DESC');

		$db->setQuery($query, 0, $count);
		$items = $db->loadObjectList() ?: [];

		$titleLimit = (int) $params->get('title_limit', 60);

		foreach ($items as $item) {
			$item->image     = self::cleanImage((string) $item->image);
			$item->image_alt = trim((string) $item->image_alt) !== ''
				? (string) $item->image_alt
				: (string) $item->title;
			$item->link = self::resolveUrl((string) $item->url);
			$item->displaytitle = self::truncate((string) $item->title, $titleLimit);
		}

		return array_values(array_filter($items, static function ($item) {
			return $item->image !== '';
		}));
	}

	public static function cleanImage(string $src): string
	{
		$src = trim($src);

		if ($src === '') {
			return '';
		}

		if (strpos($src, '#') !== false) {
			$cleaned = HTMLHelper::_('cleanImageURL', $src);
			$src = is_object($cleaned) && !empty($cleaned->url) ? (string) $cleaned->url : strtok($src, '#');
		}

		$src = trim((string) $src);

		if ($src === '') {
			return '';
		}

		if (strpos($src, 'http') === 0 || strpos($src, '//') === 0) {
			return $src;
		}

		$rel = ltrim(preg_replace('#^' . preg_quote(Uri::root(true), '#') . '/#', '', $src), '/');

		if ($rel !== '' && !is_file(JPATH_ROOT . '/' . $rel)) {
			return '';
		}

		return Uri::root(true) . '/' . $rel;
	}

	public static function resolveUrl(string $url): string
	{
		$url = trim($url);

		if ($url === '') {
			return '#';
		}

		if (strpos($url, 'http') === 0 || strpos($url, '//') === 0 || strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0) {
			return $url;
		}

		if ($url[0] === '/') {
			return Uri::root(true) . $url;
		}

		return Uri::root(true) . '/' . ltrim($url, '/');
	}

	public static function truncate(string $text, int $limit): string
	{
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

		if ($limit <= 0 || mb_strlen($text) <= $limit) {
			return $text;
		}

		return rtrim(mb_substr($text, 0, $limit)) . '…';
	}

	public static function renderImg(object $item, array $extraAttrs = []): string
	{
		$src = (string) ($item->image ?? '');

		if ($src === '') {
			return '';
		}

		$attrs = array_merge([
			'src' => $src,
			'alt' => (string) ($item->image_alt ?? $item->title ?? ''),
		], $extraAttrs);

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
}
