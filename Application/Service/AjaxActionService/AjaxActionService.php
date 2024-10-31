<?php

namespace Slackr\Application\Service\AjaxActionService;

if (!defined('ABSPATH'))
{
    exit;
}

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\HttpResponseModel;
use Slackr\Application\Model\SlackMessageModel;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\HttpService\HttpService;
use Slackr\Application\Service\IService;
use Slackr\Application\Service\PostTypeService\PostTypeService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\Service\SlackService\SlackService;

class AjaxActionService implements IService
{
    /** @var  SlackService */
    private $slackManager;

    /** @var  HttpService */
    private $httpManager;

    /** @var  SettingsService */
    private $settingsManager;

    /** @var  PostTypeService */
    private $postTypeManager;

    /** @var string */
    private $actionPrefix;

    /** @var string */
    private $actionPrefixNoPriv;

    public function __construct(
        SlackService $slackManager,
        HttpService $httpManager,
        SettingsService $settingsManager,
        PostTypeService $postTypeManager
    )
    {
        $this->slackManager = $slackManager;
        $this->httpManager = $httpManager;
        $this->settingsManager = $settingsManager;
        $this->postTypeManager = $postTypeManager;

        $this->actionPrefix = 'wp_ajax_slackr_';
        $this->actionPrefixNoPriv = 'wp_ajax_nopriv_slackr_';

        $this->register();
    }

    protected function register()
    {
        $this->registerAction('integration_endpoint_test', false, [$this, 'integrationEndpointTest']);
    }

    public function registerAction($name, $allowNoPriv, callable $function, $priority = 10, $acceptedArgs = 1)
    {
        add_action($this->actionPrefix.$name, $function, $priority, $acceptedArgs);

        if($allowNoPriv)
        {
            add_action($this->actionPrefixNoPriv.$name, $function, $priority, $acceptedArgs);
        }
    }

    public function integrationEndpointTest()
    {
        $response = new HttpResponseModel();
        $response->response = "error";
        $response->statusCode = 500;

        $data = [
            'postId' => TypeHelper::getPropertyValue($_POST, 'postId', null),
        ];

        if(empty($data['postId']))
        {
            echo json_encode($response);
            wp_die();
        }

        $integrationSettings = $this->settingsManager->getIntegrationSettings($data['postId']);

        if(!is_object($integrationSettings) || !(int)$integrationSettings->wpPostId == (int)$data['postId'])
        {
            echo json_encode($response);
            wp_die();
        }

        $message = strtr(__('Testing Slack integration with the name %integration_name%, running on site <%site_url%|%site_name%>.', ConfigService::TEXT_DOMAIN_NAME), [
            '%site_name%' => get_bloginfo('name'),
            '%site_url%' => network_site_url('/'),
            '%integration_name%' => $integrationSettings->name,
        ]);

        $msgModel = new SlackMessageModel($integrationSettings->endpointUrl, $message);
        $response = $this->slackManager->eventManager->dispatch($msgModel);

        echo json_encode($response);
        wp_die();
    }
}