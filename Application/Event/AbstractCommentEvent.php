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

abstract class AbstractCommentEvent extends AbstractEvent
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

    public function renderMessage($integration, $comment)
    {
        $post = get_post($comment->comment_post_ID);
        $commentUserId = TypeHelper::getPropertyValue($comment, 'user_id', 0);
        $commentUser = ($commentUserId > 0) ? get_userdata($commentUserId) : null;
        $username  = (is_object($commentUser)) ? $commentUser->login_name : $comment->comment_author;

        $message = strtr($this->getMessage($integration), [
            '%comment_status%' => ucfirst(wp_get_comment_status($comment->comment_ID)),
            '%user_name%' => $username,
            '%post_url%' => get_permalink($post->ID),
            '%post_title%' => $post->post_title,
            '%post_type%' => get_post_type($post->ID),
            '%site_url%' => network_site_url('/'),
            '%site_title%' => get_bloginfo('name'),
            '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
        ]);

        return $message;
    }

    public function getAttachment($integration, $comment)
    {
        $post = get_post($comment->comment_post_ID);
        $commentUserId = TypeHelper::getPropertyValue($comment, 'user_id', 0);
        $commentUser = ($commentUserId > 0) ? get_userdata($commentUserId) : null;
        $username  = (is_object($commentUser)) ? $commentUser->login_name : $comment->comment_author;

        $attachment = new SlackAttachmentModel();
        $attachment->author_name = !empty($username) ? $username : __('Unknown', ConfigService::TEXT_DOMAIN_NAME);
        $attachment->author_link = get_author_posts_url($commentUserId);
        $attachment->author_icon = get_avatar_url($commentUserId, 32);
        $attachment->title = get_the_title($post->ID);
        $attachment->title_link = get_comments_link($post->ID);
        $attachment->text = wp_trim_words(strip_tags(TypeHelper::getPropertyValue($comment, 'comment_content',__('No comment', ConfigService::TEXT_DOMAIN_NAME))), 30, '...');

        $attachment->fields[] = new SlackFieldModel(
            __('Comment status', ConfigService::TEXT_DOMAIN_NAME),
            ucfirst(wp_get_comment_status($comment->comment_ID))
        );

        $attachment->fields[] = new SlackFieldModel(
            __('Comment type', ConfigService::TEXT_DOMAIN_NAME),
            ucwords(TypeHelper::getPropertyValue($comment, 'comment_type', __('Undefined', ConfigService::TEXT_DOMAIN_NAME)))
        );

        $attachment->fields[] = new SlackFieldModel(
            __('Edit comment', ConfigService::TEXT_DOMAIN_NAME),
            add_query_arg(array('action' => 'editcomment', 'c' => $comment->comment_ID), admin_url( 'comment.php' ))
        );

        if($comment->approved == 1)
        {
            $attachment->fields[] = new SlackFieldModel(
                __('View comment', ConfigService::TEXT_DOMAIN_NAME),
                trailingslashit(get_permalink($post->ID))."#comment-{$comment->comment_ID}"
            );
        }

        return $attachment;
    }
}