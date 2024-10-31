<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class IntegrationSettingsModel implements IModel
{
    public $wpPostId;
    public $name;
    public $endpointUrl;
    public $channelName;
    public $username;
    public $iconEmoji;
    public $isActive;
    public $rawData;

    public function __construct()
    {
        $this->rawData = null;
    }
}