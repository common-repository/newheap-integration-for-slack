<?php
namespace Slackr\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class SlackFieldModel implements IModel
{
    /** @var  string */
    public $title;

    /** @var  string */
    public $value;

    /** @var  string */
    public $short;

    public function __construct($title, $value, $short = true)
    {
        $this->title = $title;
        $this->value = $value;
        $this->short = $short;
    }
}