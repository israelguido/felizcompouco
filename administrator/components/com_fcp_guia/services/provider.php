<?php
defined('_JEXEC') or die;

use FelizComPouco\Component\Fcp_guia\Administrator\Extension\GuiaComponent;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// Fallback if administrator/cache/autoload_psr4.php is stale after deploy
$guiaExtension = dirname(__DIR__) . '/src/Extension/GuiaComponent.php';
if (is_file($guiaExtension)) {
	require_once $guiaExtension;
}

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->registerServiceProvider(new MVCFactory('\\FelizComPouco\\Component\\Fcp_guia'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\FelizComPouco\\Component\\Fcp_guia'));

		$container->set(
			ComponentInterface::class,
			static function (Container $container) {
				$component = new GuiaComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
