<?php
namespace Slackr\Application\UserInterface;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractUIElement
{
    public function renderContent()
    {
        echo $this->getContent();
    }

    /** @return string */
    public abstract function getContent();
}