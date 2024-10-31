<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class SlackResponseModel implements IModel
{
    /** @var  int */
    public $resultCode;
}