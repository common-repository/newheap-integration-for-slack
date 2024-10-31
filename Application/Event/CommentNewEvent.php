<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\PostsEventGroup;
use Slackr\Application\Event\EventGroup\UsersEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class CommentNewEvent extends AbstractCommentEvent
{
    /** @return string */
    public function getName()
    {
        return __("New comment", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return PostsEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a new comment is posted, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getAuthorDisplayName()
    {
        return "NewHeap";
    }

    /** @return string */
    public function getAuthorContactUrl()
    {
        return "https://newheap.com";
    }

    /** @return string */
    public function getDefaultMessage()
    {
        return __("*New comment* by %user_name% with status *%comment_status%* on *%post_type%* <%post_url%|%post_title%> on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('wp_insert_comment', function($commentId, $comment){

            $postType = get_post_type($comment->comment_post_ID);

            foreach($this->getIntegrations() as $integration)
            {
                $eventSettings = $this->getSettings($integration);
                $allowedPostTypes = TypeHelper::getPropertyValue($eventSettings->rawData, 'postTypes', []);

                if(!in_array($postType, $allowedPostTypes))
                {
                    return;
                }

                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = strtr($this->renderMessage($integration, $comment), []);
                $slackMessageModel->attachments[] = $this->getAttachment($integration, $comment);

                $this->dispatch($slackMessageModel);
            }
        }, 10, 2);
    }
}