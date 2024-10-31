<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\SystemEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PluginUpdateAvailableEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Plugin update available", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return SystemEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a plugin is update is available, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*New version available* for plugin *%plugin_name%* on site <%site_url%|%site_title%>.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        //TODO: make this work.
        add_filter('pre_set_site_transient_update_plugins', function($transient)
        {
            foreach($this->getIntegrations() as $integration)
            {
                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = strtr($this->getMessage($integration), [
                    '%plugin_name%' => "Naam_plugin",
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                ]);

                $this->dispatch($slackMessageModel);
            }

        });
    }
}