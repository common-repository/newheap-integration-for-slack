<?php
namespace Slackr\Application\Event\EventCategory;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class AuthenticationCategory extends AbstractEventCategory
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Authentication", ConfigService::TEXT_DOMAIN_NAME);
    }
}