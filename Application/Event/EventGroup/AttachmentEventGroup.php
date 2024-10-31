<?php
namespace Slackr\Application\Event\EventGroup;

use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class AttachmentEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Attachment", ConfigService::TEXT_DOMAIN_NAME);
    }
}