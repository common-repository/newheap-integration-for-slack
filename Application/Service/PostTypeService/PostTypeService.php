<?php

namespace Slackr\Application\Service\PostTypeService;

if (!defined('ABSPATH'))
{
    exit;
}

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\PostType\AbstractPostType;
use Slackr\Application\Service\IService;
use Slackr\Application\Service\SettingsService\SettingsService;

class PostTypeService implements IService
{
    /** @var  SettingsService */
    private $settingsManager;

    /** @var AbstractPostType[] */
    private $postTypes;

    public function __construct(SettingsService $settingsManager)
    {
        $this->settingsManager = $settingsManager;
        $this->postTypes = [];
    }

    public function register(AbstractPostType $postType)
    {
        $postTypeClass = TypeHelper::getCleanClassNameString(get_class($postType));

        if(!array_key_exists($postTypeClass, $this->postTypes))
        {
            $this->postTypes[$postTypeClass] = $postType;
            $postType->init();
        }
    }
}