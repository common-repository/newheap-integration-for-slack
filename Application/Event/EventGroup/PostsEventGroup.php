<?php
namespace Slackr\Application\Event\EventGroup;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PostsEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Posts", ConfigService::TEXT_DOMAIN_NAME);
    }
}