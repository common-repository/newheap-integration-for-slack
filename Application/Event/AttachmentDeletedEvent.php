<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventGroup\AttachmentEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\SlackAttachmentModel;
use Slackr\Application\Model\SlackFieldModel;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class AttachmentDeletedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Attachment deleted", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return AttachmentEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a attachment is deleted, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*Attachment deleted* <%attachment_url%|%attachment_name%> by %user_name% on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('delete_attachment', function($postId){
            $wpAttachment = get_post($postId);
            $attachmentMimeType = get_post_mime_type($postId);
            $currentUser = wp_get_current_user();

            foreach($this->getIntegrations() as $integration)
            {
                $slackMessageModel = new SlackMessageModel($integration->endpointUrl);
                $slackMessageModel->text = strtr($this->getMessage($integration), [
                    '%attachment_name%' => get_the_title($postId),
                    '%attachment_url%' => wp_get_attachment_url($postId),
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $wpAttachmentImg = wp_get_attachment_image_src($postId, 'medium' );
                $wpAttachmentImgUrl = !empty($wpAttachmentImg[0] ) ? $wpAttachmentImg[0] : '';

                $attachment = new SlackAttachmentModel();
                $attachment->color = 'danger';
                $attachment->title = get_the_title($postId);
                $attachment->title_link = get_permalink($postId);
                $attachment->author_name = $currentUser->user_login;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(strip_tags($wpAttachment->post_content), 30, '...');

                if(!empty($wpAttachmentImgUrl))
                {
                    $attachment->image_url = $wpAttachmentImgUrl;
                }

                $attachment->fields[] = new SlackFieldModel(
                    __('Attachment name', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.wp_get_attachment_url($postId).'|'.get_the_title($postId).'>'
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Attachment type', ConfigService::TEXT_DOMAIN_NAME),
                    $attachmentMimeType
                );

                $attachment->fields[] = new SlackFieldModel(
                    __('Edit attachment', ConfigService::TEXT_DOMAIN_NAME),
                    get_edit_post_link($postId)
                );

                if($wpAttachment->post_parent > 0)
                {
                    $attachment->fields[] = new SlackFieldModel(
                        __('Added to', ConfigService::TEXT_DOMAIN_NAME),
                        get_permalink($wpAttachment->post_parent)
                    );
                }

                if(!empty($wpAttachment->post_excerpt))
                {
                    $attachment->fields[] = new SlackFieldModel(
                        __('Attachment caption', ConfigService::TEXT_DOMAIN_NAME),
                        wp_trim_words(strip_tags($wpAttachment->post_excerpt), 30, '...')
                    );
                }

                $slackMessageModel->attachments[] = $attachment;

                $this->dispatch($slackMessageModel);
            }
        });
    }
}