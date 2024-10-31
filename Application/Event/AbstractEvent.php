<?php
namespace Slackr\Application\Event;

use Slackr\Application\Event\EventCategory\GeneralEventCategory;
use Slackr\Application\Event\EventGroup\GeneralEventGroup;
use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\HttpResponseModel;
use Slackr\Application\Model\IntegrationEventModel;
use Slackr\Application\Model\IntegrationSettingsModel;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\Service\SlackEventService\SlackEventService;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractEvent
{
    /** @var IntegrationSettingsModel[] */
    private $integrations;

    /** @var SlackEventService  */
    protected $slackEventManager;

    /** @var SettingsService */
    protected $settingsManager;

    /** @var ConfigService */
    protected $configManager;

    public function __construct(
        SlackEventService $slackEventManager,
        SettingsService $settingsManager,
        ConfigService $configManager
    )
    {
        $this->slackEventManager = $slackEventManager;
        $this->settingsManager = $settingsManager;
        $this->configManager = $configManager;
        $this->integrations = [];
    }

    /** @return string */
    public abstract function getName();

    /** @return string */
    public abstract function getDescription();

    /** @return string */
    public abstract function getAuthorDisplayName();

    /** @return string */
    public abstract function getAuthorContactUrl();

    /** @return string */
    public abstract function getDefaultMessage();

    public abstract function register();

    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getSettingsUi($index, $eventSettings)
    {
        return '';
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
    }

    /** @return string */
    public function getGroup()
    {
        return GeneralEventGroup::class;
    }

    /** @return string */
    public function getCategory()
    {
        return GeneralEventCategory::class;
    }

    /** @return bool */
    public function canActivate()
    {
        //Checks should be added here. For example, I want to create events for WooCommerce, then i should here return if Woocommerce is installed and active on this site.
        return true;
    }

    /**
     * @param bool $forceReload
     * @return IntegrationSettingsModel[]
     */
    public function getIntegrations($forceReload = false)
    {
        if(empty($this->integrations) || $forceReload)
        {
            $this->integrations = $this->settingsManager->getIntegrationsByActiveEvent(TypeHelper::getCleanClassNameString(get_class($this)));
        }

        return $this->integrations;
    }

    /**
     * @return bool
     */
    public function hasIntegrations()
    {
        return !empty($this->getIntegrations());
    }

    /**
     * @param IntegrationSettingsModel $integrationSettingsModel
     * @return \Slackr\Application\Model\IntegrationEventModel
     */
    public function getSettings(IntegrationSettingsModel $integrationSettingsModel)
    {
        return $this->settingsManager->getIntegrationEvent($integrationSettingsModel->wpPostId, get_class($this));
    }

    /**
     * @param SlackMessageModel $messageRequest
     * @return bool
     */
    protected function isValidMessage(SlackMessageModel $messageRequest)
    {
        //TODO: validate the message
        return true;
    }

    /**
     * @param IntegrationSettingsModel $integrationSettingsModel
     * @return null|string
     */
    public function getMessage($integrationSettingsModel)
    {
        $message = null;

        if($integrationSettingsModel instanceof IntegrationSettingsModel)
        {
            $integrationEvents = $this->settingsManager->getIntegrationEvents($integrationSettingsModel->wpPostId);
            $integrationEvent = null;

            foreach($integrationEvents as $possibleIntegrationEvent)
            {
                if(TypeHelper::getCleanClassNameString($possibleIntegrationEvent->className) === TypeHelper::getCleanClassNameString(get_class($this)))
                {
                    $message = $possibleIntegrationEvent->message;
                }
            }
        }

        if(empty($message))
        {
            $message = $this->getDefaultMessage();
        }

        return $message;
    }
    
    /**
     * @param SlackMessageModel $slackMessageModel
     * @return \Slackr\Application\Model\HttpResponseModel
     */
    public function dispatch(SlackMessageModel $slackMessageModel)
    {
        $defaultResponseModel = new HttpResponseModel();
        $defaultResponseModel->statusCode = 500;
        $defaultResponseModel->response = "can_active_false";

        if(!$this->canActivate())
        {
            return $defaultResponseModel;
        }

        $slackMessageModel->text = apply_filters('slackr_register_event_dispatch_text', $slackMessageModel->text, $this);

        //TODO: validate the message
        //TODO: on error response we might want to build in e-mail notification to site owner to notify failure.
        return $this->slackEventManager->dispatch($slackMessageModel);
    }
}