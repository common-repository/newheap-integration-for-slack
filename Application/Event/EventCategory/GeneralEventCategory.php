<?php
namespace Slackr\Application\Event\EventCategory;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class GeneralEventCategory extends AbstractEventCategory
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("General", ConfigService::TEXT_DOMAIN_NAME);
    }
}