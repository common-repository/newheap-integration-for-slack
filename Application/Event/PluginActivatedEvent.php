<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\SystemEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackAttachmentModel;
use Slackr\Application\Model\SlackFieldModel;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PluginActivatedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Plugin activated", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return SystemEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a plugin is activated, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("%user_name% *activated plugin* %plugin_name% on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        if(!function_exists('get_plugin_data'))
        {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        add_action('activated_plugin', function($plugin, $networkWide){

            $currentUser = wp_get_current_user();
            $pluginData = get_plugin_data(ABSPATH.'/wp-content/plugins/'.$plugin);

            foreach($this->getIntegrations() as $integration)
            {
                //$eventSettings = $this->getSettings($integration);

                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = strtr($this->getMessage($integration), [
                    '%plugin_name%' => $pluginData['Name'],
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $attachment = new SlackAttachmentModel();
                $attachment->color = 'good';
                $attachment->author_name = $currentUser->display_name;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(TypeHelper::getPropertyValue($pluginData, 'Description',__('No description', ConfigService::TEXT_DOMAIN_NAME)), 30, '...');

                $attachment->fields[] = new SlackFieldModel(
                    __('Plugin name', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.TypeHelper::getPropertyValue($pluginData, 'PluginURI', __('', ConfigService::TEXT_DOMAIN_NAME)).'|'.TypeHelper::getPropertyValue($pluginData, 'Name', __('Unknown', ConfigService::TEXT_DOMAIN_NAME)).'>'
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Plugin author', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.TypeHelper::getPropertyValue($pluginData, 'AuthorURI', __('', ConfigService::TEXT_DOMAIN_NAME)).'|'.TypeHelper::getPropertyValue($pluginData, 'AuthorName', __('Unknown', ConfigService::TEXT_DOMAIN_NAME)).'>'
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Plugin version', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($pluginData, 'Version', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Activated by', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $slackMessageModel->attachments[] = $attachment;

                $this->dispatch($slackMessageModel);
            }

        }, 10, 2);
    }
}