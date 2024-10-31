<?php
namespace Slackr\Application\Event\EventGroup;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class SystemEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("System", ConfigService::TEXT_DOMAIN_NAME);
    }
}