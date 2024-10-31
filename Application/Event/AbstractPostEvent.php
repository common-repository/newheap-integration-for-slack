<?php
namespace Slackr\Application\Event;

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\IntegrationEventModel;
use Slackr\Application\Model\IntegrationSettingsModel;
use Slackr\Application\Model\SlackAttachmentModel;
use Slackr\Application\Model\SlackFieldModel;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractPostEvent extends AbstractEvent
{
    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getSettingsUi($index, $eventSettings)
    {
        ob_start();

        echo $this->getPostTypeSettingsUi($index, $eventSettings);

        $content = ob_get_clean();

        return $content;
    }

    /**
     * @param int $postId
     * @param array $rawPost
     * @param IntegrationSettingsModel $postedSettings
     * @param array $postedEvent
     * @param array &$eventSettings
     */
    public function saveSettings($postId, $rawPost, $postedSettings, $postedEvent, &$eventSettings)
    {
        $this->savePostTypeSettings($postId, $rawPost, $postedSettings, $postedEvent, $eventSettings);
    }

    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getPostTypeSettingsUi($index, $eventSettings)
    {
        $rawData = $eventSettings->rawData;
        ob_start();

        ?>
        <div>
            <div class="property-title"><?=__('Post types', ConfigService::TEXT_DOMAIN_NAME)?></div>
            <div class="slackr-pad-5">
                <?php
                $postTypeSelector = new PostTypeSelectorUISettingElement(SettingsService::INTEGRATION_SETTING_EVENT.'['.$index.'][postTypes]', TypeHelper::getPropertyValue($rawData, 'postTypes', []));
                $postTypeSelector->renderContent();
                ?>
            </div>
            <p class="description">
                <?=__("Choose the post types which this event will fire on.", ConfigService::TEXT_DOMAIN_NAME)?>
            </p>
        </div>
        <?php

        $content = ob_get_clean();

        return $content;
    }

    /**
     * @param int $postId
     * @param array $rawPost
     * @param IntegrationSettingsModel $postedSettings
     * @param array $postedEvent
     * @param array &$eventSettings
     */
    private function savePostTypeSettings($postId, $rawPost, $postedSettings, $postedEvent, &$eventSettings)
    {
        $postedPostTypes = TypeHelper::getPropertyValue($postedEvent, 'postTypes', []);
        $activePostTypes = [];

        foreach($postedPostTypes as $postType)
        {
            if((bool)TypeHelper::getPropertyValue($postType, 'isActive', false))
            {
                $postTypeName = TypeHelper::getPropertyValue($postType, 'name', '');

                if(!empty($postTypeName))
                {
                    $activePostTypes[] = sanitize_text_field($postTypeName);
                }
            }
        }

        $eventSettings['postTypes'] = $activePostTypes;
    }

    public function renderMessage($integration, $postId, $post)
    {
        $currentUser = wp_get_current_user();
        $authorUser = get_user_by('ID', $post->post_author);
        $currentUsername = 'unknown';
        $authorUsername = 'unknown';

        if(is_object($currentUser))
        {
            $currentUsername = $currentUser->user_login;
        }

        if(is_object($authorUser))
        {
            $authorUsername = $authorUser->user_login;
        }

        $postType = get_post_type($postId);

        $message = strtr($this->getMessage($integration), [
            '%user_name%' => $currentUsername,
            '%post_url%' => get_permalink($postId),
            '%post_title%' => $post->post_title,
            '%post_status%' => ucfirst(get_post_status($postId)),
            '%post_type%' => $postType,
            '%site_url%' => network_site_url('/'),
            '%site_title%' => get_bloginfo('name'),
            '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
        ]);

        return $message;
    }

    public function getAttachment($integration, $postId, $post)
    {
        $currentUser = wp_get_current_user();
        $authorUser = get_user_by('ID', $post->post_author);

        $attachment = new SlackAttachmentModel();
        $attachment->title = get_the_title($postId);
        $attachment->title_link = get_permalink($postId);
        $attachment->author_name = $currentUser->display_name;
        $attachment->author_link = get_author_posts_url($currentUser->ID);
        $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
        $attachment->text = wp_trim_words(strip_tags($post->post_content), 30, '...');

        $postThumbUrl = get_the_post_thumbnail_url($postId);

        if(!empty($postThumbUrl))
        {
            $attachment->thumb_url = $postThumbUrl;
        }


        $attachment->fields[] = new SlackFieldModel(
            __('Edited by', ConfigService::TEXT_DOMAIN_NAME),
            TypeHelper::getPropertyValue($currentUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
        );

        $attachment->fields[] = new SlackFieldModel(
            __('Content author', ConfigService::TEXT_DOMAIN_NAME),
            TypeHelper::getPropertyValue($authorUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
        );

        $attachment->fields[] = new SlackFieldModel(
            __('Content type', ConfigService::TEXT_DOMAIN_NAME),
            ucfirst(get_post_type($postId))
        );

        $attachment->fields[] = new SlackFieldModel(
            __('Content status', ConfigService::TEXT_DOMAIN_NAME),
            ucfirst(get_post_status($postId))
        );

        $attachment->fields[] = new SlackFieldModel(
            strtr(__('Edit %post_type%', ConfigService::TEXT_DOMAIN_NAME), ['%post_type%' => get_post_type($postId)]),
            get_edit_post_link($postId)
        );

        return $attachment;
    }
}