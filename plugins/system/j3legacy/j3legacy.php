<?php

/**
 * Early Joomla 3 class aliases for legacy templates/plugins (e.g. YT Framework / SJ VicMagz).
 *
 * @package  PlgSystemJ3legacy
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\User;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * JVersion with legacy $RELEASE / $DEV_LEVEL (Version is final in Joomla 4+).
 */
if (!class_exists('JVersionCompat', false)) {
	class JVersionCompat
	{
		/** @var string Major.minor (e.g. "6.1") */
		public $RELEASE;

		/** @var string Patch level */
		public $DEV_LEVEL;

		/** @var Version */
		private $inner;

		public function __construct()
		{
			$this->inner = new Version();
			$parts = explode('.', $this->inner->getShortVersion());
			$this->RELEASE   = ($parts[0] ?? '0') . '.' . ($parts[1] ?? '0');
			$this->DEV_LEVEL = $parts[2] ?? '0';
		}

		public function __call($name, $arguments)
		{
			return $this->inner->$name(...$arguments);
		}

		public function __get($name)
		{
			return $this->inner->$name;
		}
	}
}

/**
 * Joomla 3 filesystem stubs (modern package removed several JFile APIs).
 */
if (!class_exists('JFile', false)) {
	class JFile
	{
		public static function exists($file)
		{
			return is_file($file);
		}

		public static function read($file)
		{
			return is_file($file) ? file_get_contents($file) : false;
		}

		public static function write($file, $buffer)
		{
			$dir = \dirname($file);

			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}

			return file_put_contents($file, $buffer) !== false;
		}

		public static function delete($file)
		{
			return is_file($file) ? unlink($file) : false;
		}

		public static function stripExt($file)
		{
			return preg_replace('#\.[^.]*$#', '', $file);
		}

		public static function getExt($file)
		{
			$parts = explode('.', $file);

			return (string) end($parts);
		}

		public static function getName($file)
		{
			return basename($file);
		}

		public static function copy($src, $dest, $path = null, $use_streams = false)
		{
			if ($path) {
				$src  = rtrim($path, '/\\') . '/' . $src;
				$dest = rtrim($path, '/\\') . '/' . $dest;
			}

			return copy($src, $dest);
		}

		public static function move($src, $dest, $path = '', $use_streams = false)
		{
			if ($path) {
				$src  = rtrim($path, '/\\') . '/' . $src;
				$dest = rtrim($path, '/\\') . '/' . $dest;
			}

			return rename($src, $dest);
		}

		public static function makeSafe($file)
		{
			return preg_replace('/[^A-Za-z0-9_\-\.]/', '', $file);
		}
	}
}

if (!class_exists('JFolder', false)) {
	class JFolder
	{
		public static function exists($path)
		{
			return is_dir($path);
		}

		public static function create($path, $mode = 0755)
		{
			return is_dir($path) ? true : mkdir($path, $mode, true);
		}

		public static function delete($path)
		{
			if (!is_dir($path)) {
				return false;
			}

			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($files as $file) {
				$file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
			}

			return rmdir($path);
		}

		public static function files($path, $filter = '.', $recurse = false, $full = false, $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'])
		{
			if (!is_dir($path)) {
				return [];
			}

			$flags = \FilesystemIterator::SKIP_DOTS;
			$it = $recurse
				? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags))
				: new \FilesystemIterator($path, $flags);
			$out = [];

			foreach ($it as $file) {
				if (!$file->isFile()) {
					continue;
				}

				$name = $file->getFilename();

				if (in_array($name, (array) $exclude, true)) {
					continue;
				}

				if (!preg_match('/' . $filter . '/', $name)) {
					continue;
				}

				$out[] = $full ? $file->getPathname() : $name;
			}

			return $out;
		}

		public static function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'])
		{
			if (!is_dir($path)) {
				return [];
			}

			$out = [];
			$it = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);

			foreach ($it as $file) {
				if (!$file->isDir()) {
					continue;
				}

				$name = $file->getFilename();

				if (in_array($name, (array) $exclude, true)) {
					continue;
				}

				if (!preg_match('/' . $filter . '/', $name)) {
					continue;
				}

				$out[] = $full ? $file->getPathname() : $name;

				if ($recurse) {
					foreach (self::folders($file->getPathname(), $filter, true, $full, $exclude) as $child) {
						$out[] = $child;
					}
				}
			}

			return $out;
		}

		public static function listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
		{
			$dirs = [];

			if ($level >= $maxLevel || !is_dir($path)) {
				return $dirs;
			}

			foreach (self::folders($path, $filter) as $i => $name) {
				$id = $parent * 1000 + $i + 1;
				$dirs[] = [
					'id' => $id,
					'parent' => $parent,
					'name' => $name,
					'fullname' => '',
					'fullnamepath' => $path . '/' . $name,
				];
				$dirs = array_merge($dirs, self::listFolderTree($path . '/' . $name, $filter, $maxLevel, $level + 1, $id));
			}

			return $dirs;
		}
	}
}

if (!class_exists('JPath', false)) {
	class JPath
	{
		public static function clean($path, $ds = DIRECTORY_SEPARATOR)
		{
			$path = trim($path);

			if (empty($path)) {
				return $ds === '\\' ? '\\\\' : '/';
			}

			$path = preg_replace('#[/\\\\]+#', $ds, $path);

			return $path;
		}

		public static function check($path)
		{
			return self::clean($path);
		}

		public static function find($paths, $file)
		{
			foreach ((array) $paths as $path) {
				$fullname = self::clean($path . '/' . $file);

				if (is_file($fullname)) {
					return $fullname;
				}
			}

			return false;
		}
	}
}

/**
 * Minimal stubs for removed Joomla 3 APIs.
 */
if (!class_exists('JRequest', false)) {
	class JRequest
	{
		public static function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
		{
			$input = Factory::getApplication()->getInput();

			return $input->get($name, $default, $type === 'none' ? 'raw' : $type);
		}

		public static function get($name = null, $default = null)
		{
			if ($name === null) {
				$input = Factory::getApplication()->getInput();

				return array_merge(
					(array) $input->getArray(),
					[
						'option' => $input->getCmd('option', ''),
						'view'   => $input->getCmd('view', ''),
						'task'   => $input->getCmd('task', ''),
						'layout' => $input->getCmd('layout', ''),
					]
				);
			}

			return self::getVar($name, $default);
		}

		public static function getInt($name, $default = 0, $hash = 'default')
		{
			return (int) self::getVar($name, $default, $hash, 'int');
		}

		public static function getCmd($name, $default = '', $hash = 'default')
		{
			return (string) self::getVar($name, $default, $hash, 'cmd');
		}

		public static function getString($name, $default = '', $hash = 'default')
		{
			return (string) self::getVar($name, $default, $hash, 'string');
		}

		public static function getWord($name, $default = '', $hash = 'default')
		{
			return (string) self::getVar($name, $default, $hash, 'word');
		}

		public static function getBool($name, $default = false, $hash = 'default')
		{
			return (bool) self::getVar($name, $default, $hash, 'bool');
		}

		public static function getFloat($name, $default = 0.0, $hash = 'default')
		{
			return (float) self::getVar($name, $default, $hash, 'float');
		}

		public static function getMethod()
		{
			return Factory::getApplication()->getInput()->getMethod();
		}

		public static function setVar($name, $value = null)
		{
			Factory::getApplication()->getInput()->set($name, $value);

			return $value;
		}
	}
}

if (!class_exists('JError', false)) {
	class JError
	{
		public static function raiseWarning($code, $msg)
		{
			Factory::getApplication()->enqueueMessage($msg, 'warning');

			return false;
		}

		public static function raiseError($code, $msg)
		{
			Factory::getApplication()->enqueueMessage($msg, 'error');

			return false;
		}

		public static function raiseNotice($code, $msg)
		{
			Factory::getApplication()->enqueueMessage($msg, 'notice');

			return false;
		}
	}
}

if (!class_exists('JResponse', false)) {
	class JResponse
	{
		protected static $body = '';

		public static function setHeader($name, $value, $replace = false)
		{
			Factory::getApplication()->setHeader($name, $value, $replace);
		}

		public static function allowCache($allow = null)
		{
			return false;
		}

		public static function getBody($asArray = false)
		{
			$app = Factory::getApplication();

			if (method_exists($app, 'getBody')) {
				$body = $app->getBody($asArray);
			} else {
				$body = self::$body;
			}

			return $asArray ? (array) $body : (string) $body;
		}

		public static function setBody($content)
		{
			self::$body = (string) $content;
			$app = Factory::getApplication();

			if (method_exists($app, 'setBody')) {
				$app->setBody($content);
			}

			return true;
		}

		public static function appendBody($content)
		{
			return self::setBody(self::getBody() . $content);
		}

		public static function prependBody($content)
		{
			return self::setBody($content . self::getBody());
		}
	}
}

if (!class_exists('JDispatcher', false)) {
	class JDispatcher
	{
		public static function getInstance()
		{
			return Factory::getApplication()->getDispatcher();
		}
	}
}


if (!class_exists('JSite', false)) {
	class JSite
	{
		public static function getRouter()
		{
			return \Joomla\CMS\Factory::getApplication()->getRouter();
		}

		public static function getMenu()
		{
			return \Joomla\CMS\Factory::getApplication()->getMenu();
		}
	}
}

if (!\defined('JROUTER_MODE_SEF')) {
	\define('JROUTER_MODE_SEF', 1);
}

if (!\defined('JROUTER_MODE_RAW')) {
	\define('JROUTER_MODE_RAW', 0);
}

/**
 * Register legacy class aliases once.
 */
final class PlgSystemJ3legacyBootstrap
{
	public static function register(): void
	{
		static $done = false;

		if ($done) {
			return;
		}

		$done = true;

		$map = [
			'JFactory'         => Factory::class,
			'JHtml'            => HTMLHelper::class,
			'JHTML'            => HTMLHelper::class,
			'JURI'             => Uri::class,
			'JUri'             => Uri::class,
			'JText'            => Text::class,
			'JTEXT'            => Text::class,
			'JModuleHelper'    => ModuleHelper::class,
			'JDocument'        => Document::class,
			'JRoute'           => Route::class,
			'JSession'         => Session::class,
			'JUser'            => User::class,
			'JDate'            => Date::class,
			'JLanguage'        => Language::class,
			'JPluginHelper'    => PluginHelper::class,
			'JComponentHelper' => ComponentHelper::class,
			'JTable'           => Table::class,
			'JForm'            => Form::class,
			'JFormHelper'      => FormHelper::class,
			'JFormField'       => FormField::class,
			'JLayoutHelper'    => LayoutHelper::class,
			'JVersion'         => \JVersionCompat::class,
			'JInstaller'       => Installer::class,
			'JRegistry'        => Registry::class,
			'JString'          => StringHelper::class,
			'JPlugin'          => CMSPlugin::class,
			'JObject'          => \Joomla\CMS\Object\CMSObject::class,
			'JPagination'      => \Joomla\CMS\Pagination\Pagination::class,
			'JInput'           => \Joomla\Input\Input::class,
			'JFilterInput'     => \Joomla\CMS\Filter\InputFilter::class,
			'JFilterOutput'    => \Joomla\CMS\Filter\OutputFilter::class,
			'JLog'             => \Joomla\CMS\Log\Log::class,
			'JMail'            => \Joomla\CMS\Mail\Mail::class,
			'JCache'           => \Joomla\CMS\Cache\Cache::class,
			'JHttp'            => \Joomla\CMS\Http\Http::class,
			'JApplicationCms'  => \Joomla\CMS\Application\CMSApplication::class,
		];

		foreach ($map as $legacy => $modern) {
			if (!class_exists($legacy, false) && class_exists($modern)) {
				class_alias($modern, $legacy);
			}
		}



		// No-op stubs for removed MooTools behaviors
		if (is_file(__DIR__ . '/content_helper_route.php')) {
			require_once __DIR__ . '/content_helper_route.php';
		}

		foreach ([
			'behavior.framework',
			'behavior.caption',
			'behavior.tooltip',
			'behavior.modal',
			'behavior.keepalive',
			'behavior.formvalidation',
			'behavior.calendar',
			'behavior.colorpicker',
			'behavior.combobox',
			'behavior.highlighter',
			'behavior.multiselect',
			'behavior.nofloat',
			'behavior.switcher',
			'behavior.tabstate',
			'behavior.tree',
			'formbehavior.chosen',
		] as $key) {
			HTMLHelper::register($key, static function (...$args) {
				return;
			});
		}

	}
}

PlgSystemJ3legacyBootstrap::register();

if (is_file(__DIR__ . '/content_helper_route.php')) {
	require_once __DIR__ . '/content_helper_route.php';
}

/**
 * System plugin so aliases are available early for site templates.
 */
class PlgSystemJ3legacy extends CMSPlugin
{
	public function __construct(&$subject, $config = [])
	{
		PlgSystemJ3legacyBootstrap::register();
		parent::__construct($subject, $config);
	}

	public function onAfterInitialise()
	{
		PlgSystemJ3legacyBootstrap::register();
	}
}
