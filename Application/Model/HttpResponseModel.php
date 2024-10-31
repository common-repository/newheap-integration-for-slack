<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class HttpResponseModel implements IModel
{
    public $statusCode;
    public $response;
}