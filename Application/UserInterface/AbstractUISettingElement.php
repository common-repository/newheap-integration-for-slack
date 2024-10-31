<?php
namespace Slackr\Application\UserInterface;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractUISettingElement extends AbstractUIElement
{
    /** @var string */
    protected $settingKey;

    /**
     * AbstractUISettingElement constructor.
     * @param $settingKey
     */
    public function __construct($settingKey)
    {
        $this->settingKey = $settingKey;
    }

    public function getSettingsKey()
    {
        return $this->settingKey;
    }
}