<?php

namespace Slackr\Application;

use Slackr\Application\Event\AttachmentDeletedEvent;
use Slackr\Application\Event\AttachmentModifiedEvent;
use Slackr\Application\Event\AttachmentNewEvent;
use Slackr\Application\Event\CommentNewEvent;
use Slackr\Application\Event\CommentStatusChangedEvent;
use Slackr\Application\Event\EventCategory\AuthenticationCategory;
use Slackr\Application\Event\EventCategory\GeneralEventCategory;
use Slackr\Application\Event\EventCategory\MiscEventCategory;
use Slackr\Application\Event\EventGroup\AttachmentEventGroup;
use Slackr\Application\Event\EventGroup\SystemEventGroup;
use Slackr\Application\Event\EventGroup\WooCommerceEventGroup;
use Slackr\Application\Event\PluginActivatedEvent;
use Slackr\Application\Event\PluginDeactivatedEvent;
use Slackr\Application\Event\PluginDeletedEvent;
use Slackr\Application\Event\PluginUpdateAvailableEvent;
use Slackr\Application\Event\PostCreatedEvent;
use Slackr\Application\Event\PostDeletedEvent;
use Slackr\Application\Event\PostThrashedEvent;
use Slackr\Application\Event\PostUpdatedEvent;
use Slackr\Application\Event\UserDeletedEvent;
use Slackr\Application\Event\UserLoginFailedEvent;
use Slackr\Application\Event\UserLoginSuccessfulEvent;
use Slackr\Application\Event\EventGroup\GeneralEventGroup;
use Slackr\Application\Event\EventGroup\PostsEventGroup;
use Slackr\Application\Event\EventGroup\UsersEventGroup;
use Slackr\Application\Event\UserNewEvent;
use Slackr\Application\Event\UserRoleChangedEvent;
use Slackr\Application\Event\UserUpdatedEvent;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\PostType\SlackIntegrationPostType;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\AjaxActionService\AjaxActionService;
use Slackr\Application\Service\HttpService\HttpService;
use Slackr\Application\Service\PostTypeService\PostTypeService;
use Slackr\Application\Service\SlackEventService\SlackEventService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\Service\SlackService\SlackService;

if (!defined('ABSPATH'))
{
    exit;
}

final class SlackerModule
{
    /** @var \Slackr\Application\SlackerModule */
    private static $instance;

    /** @var  ConfigService */
    protected $configManager;

    /** @var  HttpService */
    protected $httpManager;

    /** @var  SlackService */
    protected $slackManager;

    /** @var  PostTypeService */
    protected $postTypeManager;

    /** @var  SettingsService */
    protected $settingsManager;

    /** @var  AjaxActionService */
    protected $ajaxActionManager;

    /**
     * @return \Slackr\Application\SlackerModule
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();

            $instance = static::$instance;

            $instance->loadApplication();
            $instance->loadServices();
            $instance->loadPostTypes();
            $instance->registerEventGroups();
            $instance->registerEventCategories();
            $instance->registerEvents();
            $instance->enqueueStyles();
            $instance->enqueueScripts();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return SlackService
     */
    public function getSlackManager()
    {
        return $this->slackManager;
    }

    private function loadServices()
    {
        $this->configManager = new ConfigService(trailingslashit(plugin_dir_path(__DIR__)));
        $this->httpManager = new HttpService();
        $this->settingsManager = new SettingsService();
        $this->slackManager = new SlackService(new SlackEventService($this->settingsManager, $this->httpManager, $this->configManager));
        $this->postTypeManager = new PostTypeService($this->settingsManager);
        $this->ajaxActionManager = new AjaxActionService($this->slackManager, $this->httpManager, $this->settingsManager, $this->postTypeManager);
    }

    private function loadPostTypes()
    {
        $this->postTypeManager->register(new SlackIntegrationPostType($this->settingsManager, $this->slackManager->eventManager));
    }

    private function registerEventGroups()
    {
        $this->slackManager->eventManager->registerEventGroup(GeneralEventGroup::class);
        $this->slackManager->eventManager->registerEventGroup(PostsEventGroup::class);
        $this->slackManager->eventManager->registerEventGroup(UsersEventGroup::class);
        $this->slackManager->eventManager->registerEventGroup(AttachmentEventGroup::class);
        $this->slackManager->eventManager->registerEventGroup(SystemEventGroup::class);
        $this->slackManager->eventManager->registerEventGroup(WooCommerceEventGroup::class);

        do_action('slackr_register_event_group', $this->slackManager->eventManager);
    }

    private function registerEventCategories()
    {
        $this->slackManager->eventManager->registerEventCategory(GeneralEventCategory::class);
        $this->slackManager->eventManager->registerEventCategory(AuthenticationCategory::class);
        $this->slackManager->eventManager->registerEventCategory(MiscEventCategory::class);

        do_action('slackr_register_event_category', $this->slackManager->eventManager);
    }

    private function registerEvents()
    {
        $this->slackManager->eventManager->registerEvent(PostCreatedEvent::class);
        $this->slackManager->eventManager->registerEvent(PostUpdatedEvent::class);
        $this->slackManager->eventManager->registerEvent(PostDeletedEvent::class);
        $this->slackManager->eventManager->registerEvent(PostThrashedEvent::class);

        $this->slackManager->eventManager->registerEvent(CommentNewEvent::class);
        $this->slackManager->eventManager->registerEvent(CommentStatusChangedEvent::class);

        $this->slackManager->eventManager->registerEvent(UserLoginSuccessfulEvent::class);
        $this->slackManager->eventManager->registerEvent(UserLoginFailedEvent::class);
        $this->slackManager->eventManager->registerEvent(UserNewEvent::class);
        $this->slackManager->eventManager->registerEvent(UserUpdatedEvent::class);
        $this->slackManager->eventManager->registerEvent(UserDeletedEvent::class);
        $this->slackManager->eventManager->registerEvent(UserRoleChangedEvent::class);

        $this->slackManager->eventManager->registerEvent(PluginActivatedEvent::class);
        $this->slackManager->eventManager->registerEvent(PluginDeActivatedEvent::class);
        $this->slackManager->eventManager->registerEvent(PluginDeletedEvent::class);
        //$this->slackManager->eventManager->registerEvent(PluginUpdateAvailableEvent::class);

        $this->slackManager->eventManager->registerEvent(AttachmentNewEvent::class);
        $this->slackManager->eventManager->registerEvent(AttachmentModifiedEvent::class);
        $this->slackManager->eventManager->registerEvent(AttachmentDeletedEvent::class);


        do_action('slackr_register_event', $this->slackManager->eventManager);
    }

    private function loadApplication()
    {
        $pluginDirectory = trailingslashit(plugin_dir_path(__DIR__));
        $applicationDirectory = trailingslashit($pluginDirectory."Application");

        /** @var \SplFileInfo[] $applicationFiles */
        $applicationFiles = $this->locateFilesFromDir($applicationDirectory);

        foreach($applicationFiles as $applicationFile)
        {
            if($applicationFile->getExtension() !== "php")
            {
                continue;
            }

            spl_autoload_register(function($className) use ($pluginDirectory){

                if(!empty($className))
                {
                    if(strpos($className, "Slackr\\Application\\") !== false)
                    {
                        $classNameExplode = explode('\\', $className);
                        unset($classNameExplode[0]);
                        $path = $pluginDirectory.implode('/', $classNameExplode).'.php';

                        require_once($path);
                    }
                }
            });
        }
    }

    /**
     * @param $directory
     * @param bool $includeEmptyDirs
     * @return \SplFileInfo[]
     */
    private function locateFilesFromDir($directory, $includeEmptyDirs = false)
    {
        /** @var \SplFileInfo[] $files */
        $files = [];

        //Scan directory recursive, skip dots and use leaves only.
        $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $itMode = (true === $includeEmptyDirs) ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY;

        /**
         * @var string $filename
         * @var \SplFileInfo $file
         */
        foreach (new \RecursiveIteratorIterator($it, $itMode) as $filename => $file)
        {
            $files[] = $file;
        }

        return $files;
    }

    private function enqueueScripts()
    {
        add_action( 'admin_enqueue_scripts', function($hook){

            wp_enqueue_script($this->configManager->getName().'_backend_js', $this->configManager->getAssetsUrl().'js/backend.js', array('jquery'), false, true);
        }, 100);

        add_action( 'wp_enqueue_scripts', function($hook){

        });
    }

    private function enqueueStyles()
    {
        add_action('admin_print_styles', function(){
            wp_enqueue_style($this->configManager->getName().'_backend_css', $this->configManager->getAssetsUrl().'css/backend.css');
        });
    }
}