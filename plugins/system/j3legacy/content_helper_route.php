<?php
\defined('_JEXEC') or die;

use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

if (!class_exists('ContentHelperRoute', false)) {
	class ContentHelperRoute
	{
		public static function getArticleRoute($id, $catid = 0, $language = null, $layout = null)
		{
			return ContentRouteHelper::getArticleRoute($id, $catid, $language ?? '*', $layout);
		}

		public static function getCategoryRoute($catid, $language = null, $layout = null)
		{
			return ContentRouteHelper::getCategoryRoute($catid, $language ?? '*', $layout);
		}

		public static function getFormRoute($id)
		{
			return ContentRouteHelper::getFormRoute($id);
		}
	}
}
