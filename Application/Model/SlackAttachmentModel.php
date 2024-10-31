<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class SlackAttachmentModel implements IModel
{
    /** @var  string */
    public $fallback;

    /** @var  string */
    public $color;

    /** @var  string */
    public $pretext;

    /** @var  string */
    public $author_name;

    /** @var  string */
    public $author_link;

    /** @var  string */
    public $author_icon;

    /** @var  string */
    public $title;

    /** @var  string */
    public $title_link;

    /** @var  string */
    public $text;

    /** @var  SlackFieldModel[] */
    public $fields;

    /** @var  string */
    public $image_url;

    /** @var  string */
    public $thumb_url;

    /** @var  string */
    public $footer;

    /** @var  string */
    public $footer_icon;

    /** @var  string */
    public $ts;


    public function __construct()
    {
        $this->fields = [];
        $this->footer = 'Slackr';
        $this->footer_icon = 'https://platform.slack-edge.com/img/default_application_icon.png';
        $this->ts = (new \DateTime())->getTimestamp();
    }
}