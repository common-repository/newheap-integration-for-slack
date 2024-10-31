<?php

namespace Slackr\Application\Service\SettingsService;

if (!defined('ABSPATH'))
{
    exit;
}

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\IntegrationEventModel;
use Slackr\Application\Model\IntegrationSettingsModel;
use Slackr\Application\PostType\SlackIntegrationPostType;
use Slackr\Application\Service\IService;

class SettingsService implements IService
{
    const INTEGRATION_SETTINGS = "slackr_intgr_st";
    const INTEGRATION_SETTING_EVENT = "slackr_intgr_stevent";

    public function __construct()
    {
    }

    public function saveIntegrationSettings(IntegrationSettingsModel $settingsModel)
    {
        $settings = [
            'name' => sanitize_text_field($settingsModel->name),
            'endpointUrl' => esc_url($settingsModel->endpointUrl),
            'channelName' => sanitize_text_field($settingsModel->channelName),
            'iconEmoji' => sanitize_text_field($settingsModel->iconEmoji),
            'username' => sanitize_text_field($settingsModel->username),
            'isActive' => sanitize_text_field($settingsModel->isActive),
        ];

        update_post_meta($settingsModel->wpPostId, self::INTEGRATION_SETTINGS, $settings);
    }

    /**
     * @param $postId
     * @return null|IntegrationSettingsModel
     */
    public function getIntegrationSettings($postId)
    {
        $settingsModel = null;
        $settings = get_post_meta($postId, self::INTEGRATION_SETTINGS, true);

        if(!empty($settings))
        {
            $settingsModel = new IntegrationSettingsModel();

            $settingsModel->rawData = $settings;
            $settingsModel->wpPostId = $postId;
            $settingsModel->name = TypeHelper::getPropertyValue($settings, 'name', '');
            $settingsModel->endpointUrl = TypeHelper::getPropertyValue($settings, 'endpointUrl', '');
            $settingsModel->channelName = TypeHelper::getPropertyValue($settings, 'channelName', '');
            $settingsModel->iconEmoji = TypeHelper::getPropertyValue($settings, 'iconEmoji', '');
            $settingsModel->username = TypeHelper::getPropertyValue($settings, 'username', '');
            $settingsModel->isActive = (bool)TypeHelper::getPropertyValue($settings, 'isActive', false);
        }

        return $settingsModel;
    }

    /**
     * @param $postId
     * @param array $events
     */
    public function saveIntegrationEvents($postId, $events)
    {
        $eventsSettings = [];

        foreach($events as $event)
        {
            $eventsSettings[] = (array)$event;
        }

        update_post_meta($postId, self::INTEGRATION_SETTING_EVENT, $eventsSettings);
    }

    /**
     * @param $postId
     * @param $event
     */
    public function saveIntegrationEvent($postId, $event)
    {
        $eventModels = $this->getIntegrationEvents($postId);

        foreach($eventModels as &$possibleEventModel)
        {
            if($possibleEventModel->className === TypeHelper::getCleanClassNameString($event->className))
            {
                $possibleEventModel = $event;
                break;
            }
        }

        update_post_meta($postId, self::INTEGRATION_SETTING_EVENT, $eventModels);
    }

    /**
     * @param $postId
     * @return IntegrationEventModel[]
     */
    public function getIntegrationEvents($postId)
    {
        /** @var array $events */
        $events = get_post_meta($postId, self::INTEGRATION_SETTING_EVENT, true);

        /** @var IntegrationEventModel[] $events */
        $eventModels = [];

        if(is_array($events))
        {
            foreach($events as $event)
            {
                $eventModel = new IntegrationEventModel();
                $eventModel->rawData = $event;
                $eventModel->wpPostId = $postId;
                $eventModel->className = TypeHelper::getPropertyValue($event, 'className', '');
                $eventModel->isActive = TypeHelper::getPropertyValue($event, 'isActive', false);
                $eventModel->message = TypeHelper::getPropertyValue($event, 'message', '');

                $eventModels[] = $eventModel;
            }
        }

        return $eventModels;
    }

    /**
     * @param $postId
     * @param string $eventClassName
     * @return IntegrationEventModel
     */
    public function getIntegrationEvent($postId, $eventClassName)
    {
        /** @var IntegrationEventModel $eventModel */
        $eventModel = null;
        $eventClassName = TypeHelper::getCleanClassNameString($eventClassName);
        $eventModels = $this->getIntegrationEvents($postId);

        foreach($eventModels as $possibleEventModel)
        {
            if($possibleEventModel->className === $eventClassName)
            {
                $eventModel = $possibleEventModel;
                break;
            }
        }

        return $eventModel;
    }

    /**
     * @param $eventClassName
     * @return IntegrationSettingsModel[]
     */
    public function getIntegrationsByActiveEvent($eventClassName)
    {
        /** @var IntegrationSettingsModel[] $settingsCollection */
        $settingsCollection = [];

        $posts = get_posts([
            'post_type' => ['slackr_integrations'],
            'meta_query' => [
                [
                    'key' => SettingsService::INTEGRATION_SETTING_EVENT,
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        foreach($posts as $post)
        {
            $postId = $post->ID;

            $eventSettingsCollection = $this->getIntegrationEvents($postId);
            if(empty($eventSettingsCollection))
            {
                continue;
            }

            foreach($eventSettingsCollection as $eventSettings)
            {
                if($eventSettings->className === $eventClassName && $eventSettings->isActive)
                {
                    $settings = $this->getIntegrationSettings($postId);

                    if(empty($settings) || !$settings->isActive)
                    {
                        continue;
                    }

                    $settingsCollection[] = $settings;
                }
            }
        }

        return $settingsCollection;
    }
}