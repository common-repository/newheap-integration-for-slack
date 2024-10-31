<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class SlackMessageModel implements IModel
{
    /** @var  string */
    public $text;

    /** @var  string */
    public $endpointUrl;

    /** @var SlackAttachmentModel[]  */
    public $attachments;

    public function __construct($endpointUrl = null, $text = null)
    {
        $this->endpointUrl = $endpointUrl;
        $this->text = $text;
        $this->attachments = [];
    }
}