<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.CacheControl
 * @license     GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

use BlackBrix\Plugin\System\CacheControl\Extension\CacheControl;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): PluginInterface {
                $plugin = new CacheControl(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'cachecontrol')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
