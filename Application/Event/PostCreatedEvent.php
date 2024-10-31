<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\PostsEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PostCreatedEvent extends AbstractPostEvent
{
    /** @return string */
    public function getName()
    {
        return __("Post created", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return PostsEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a post is created, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("%user_name% *created %post_type%* <%post_url%|%post_title%> on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('wp_insert_post', function($postId, $post, $update){

            if($update)
            {
                return;
            }
            $postType = get_post_type($postId);

            foreach($this->getIntegrations() as $integration)
            {
                $eventSettings = $this->getSettings($integration);
                $allowedPostTypes = TypeHelper::getPropertyValue($eventSettings->rawData, 'postTypes', []);

                if(!in_array($postType, $allowedPostTypes))
                {
                    return;
                }

                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = $this->renderMessage($integration, $postId, $post);
                $slackMessageModel->attachments[] = $this->getAttachment($integration, $postId, $post);

                $this->dispatch($slackMessageModel);
            }

        }, 10, 3);
    }
}