<?php
namespace Slackr\Application\Event;

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\IntegrationEventModel;
use Slackr\Application\Model\IntegrationSettingsModel;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractWooCommerceEvent extends AbstractEvent
{
    /** @return bool */
    public function canActivate()
    {
        return (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))));
    }
}