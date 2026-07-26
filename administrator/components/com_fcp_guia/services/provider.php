<?php
defined('_JEXEC') or die;

use FelizComPouco\Component\Fcpguia\Administrator\Extension\FcpguiaComponent;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->registerServiceProvider(new MVCFactory('\\FelizComPouco\\Component\\Fcpguia'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\FelizComPouco\\Component\\Fcpguia'));

		$container->set(
			ComponentInterface::class,
			static function (Container $container) {
				$component = new FcpguiaComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
