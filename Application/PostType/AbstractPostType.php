<?php
namespace Slackr\Application\PostType;

use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\Service\SlackEventService\SlackEventService;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractPostType
{
    /** @var  SettingsService */
    protected $settingsManager;

    /** @var SlackEventService */
    protected $slackEventManager;

    public function __construct(SettingsService $settingsManager, SlackEventService $slackEventManager)
    {
        $this->settingsManager = $settingsManager;
        $this->slackEventManager = $slackEventManager;
    }

    /** @return string */
    public abstract function getName();
    public abstract function init();
}