<?php
/*
    Plugin Name: NewHeap integration for Slack
    Plugin URI: https://www.newheap.com/
    Version: 1.0.0
    Author: NewHeap
    Description: Slack integration for Wordpress.
*/

if (!defined('ABSPATH'))
{
    exit;
}

require_once (__DIR__) . '/Application/SlackerModule.php';

use Slackr\Application\SlackerModule;

SlackerModule::getInstance();




