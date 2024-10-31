<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\UsersEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackAttachmentModel;
use Slackr\Application\Model\SlackFieldModel;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class UserNewEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("New user", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return UsersEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a new user is created, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*New user* created with username %user_name% on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('user_register', function($userId){
            $currentUser = get_userdata($userId);

            foreach($this->getIntegrations() as $integration)
            {
                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = strtr($this->getMessage($integration), [
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $attachment = new SlackAttachmentModel();
                $attachment->author_name = $currentUser->display_name;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(strip_tags(get_the_author_meta('description', $currentUser->ID)), 30, '...');

                if(!empty($attachment->author_icon))
                {
                    $attachment->thumb_url = $attachment->author_icon;
                }

                $attachment->fields[] = new SlackFieldModel(
                    __('Display name', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'display_name',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Username', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_login',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('E-mail address', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_email',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Role(s)', ConfigService::TEXT_DOMAIN_NAME),
                    implode(',', $currentUser->roles)
                );

                $slackMessageModel->attachments[] = $attachment;

                $this->dispatch($slackMessageModel);
            }
        });
    }
}