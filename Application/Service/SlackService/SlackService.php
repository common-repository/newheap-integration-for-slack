<?php

namespace Slackr\Application\Service\SlackService;

if (!defined('ABSPATH'))
{
    exit;
}

use Slackr\Application\Service\IService;
use Slackr\Application\Service\SlackEventService\SlackEventService;

class SlackService implements IService
{
    /** @var  SlackEventService */
    public $eventManager;

    public function __construct(SlackEventService $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}