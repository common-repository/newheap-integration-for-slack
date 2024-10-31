<?php
namespace Slackr\Application\Event\EventCategory;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class MiscEventCategory extends AbstractEventCategory
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Misc", ConfigService::TEXT_DOMAIN_NAME);
    }
}