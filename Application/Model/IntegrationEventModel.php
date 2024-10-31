<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class IntegrationEventModel implements IModel
{
    /** @var  int */
    public $wpPostId;
    /** @var  bool */
    public $isActive;
    /** @var  string */
    public $className;
    /** @var  string */
    public $message;
    /** @var  mixed */
    public $rawData;

    public function __construct()
    {
        $this->rawData = null;
    }
}