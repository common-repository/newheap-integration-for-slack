<?php
namespace Slackr\Application\PostType;

use Slackr\Application\Helper\TypeHelper;
use Slackr\Application\Model\IntegrationEventModel;
use Slackr\Application\Model\IntegrationSettingsModel;
use Slackr\Application\Service\ConfigService\ConfigService;
use Slackr\Application\Service\SettingsService\SettingsService;
use Slackr\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

class SlackIntegrationPostType extends AbstractPostType
{
    protected $name = "slackr_integrations";

    public function getName()
    {
        return $this->name;
    }

    public function init()
    {
        add_action('init', array($this, 'register'));
        add_action('admin_menu', array($this, 'removeSubmitDiv'));
        add_filter(sprintf( 'manage_%s_posts_columns', $this->name), array($this, 'columnsHeader'));
        add_action(sprintf( 'manage_%s_posts_custom_column', $this->name), array($this, 'columnRow'), 10, 2);
        add_filter('post_row_actions', array($this, 'rowActions'), 10, 2);
        add_filter(sprintf( 'bulk_actions-edit-%s', $this->name), array($this, 'bulkActions'));
        add_filter('views_edit-'.$this->name, array($this, 'hideSubSubSub'));
        add_action('before_delete_post', array($this, 'registerOnPostDelete'));
    }

    public function registerOnPostDelete($postId)
    {
        $postType = get_post_type($postId);
        if($postType === $this->name)
        {
            delete_metadata ($this->name, $postId, SettingsService::INTEGRATION_SETTINGS, null, true);
            delete_metadata ($this->name, $postId, SettingsService::INTEGRATION_SETTING_EVENT, null, true);
        }
    }

    public function register()
    {
        $args = [
            'description'         => '',
            'public'              => false,
            'publicly_queryable'  => false,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 75,
            'menu_icon'           => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwMCIgaGVpZ2h0PSIyNTAwIiB2aWV3Qm94PSIwIDAgMjU2IDI1NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCI+PHBhdGggZD0iTTE2NS45NjQgMTUuODM4Yy0zLjg5LTExLjk3NS0xNi43NTItMTguNTI4LTI4LjcyNS0xNC42MzYtMTEuOTc1IDMuODktMTguNTI4IDE2Ljc1Mi0xNC42MzYgMjguNzI1bDU4Ljk0NyAxODEuMzY1YzQuMDQ4IDExLjE4NyAxNi4xMzIgMTcuNDczIDI3LjczMiAxNC4xMzUgMTIuMS0zLjQ4MyAxOS40NzUtMTYuMzM0IDE1LjYxNC0yOC4yMTdMMTY1Ljk2NCAxNS44MzgiIGZpbGw9IiNERkEyMkYiLz48cGF0aCBkPSJNNzQuNjI2IDQ1LjUxNkM3MC43MzQgMzMuNTQyIDU3Ljg3MyAyNi45ODkgNDUuOSAzMC44NzkgMzMuOTI0IDM0Ljc3IDI3LjM3IDQ3LjYzMSAzMS4yNjMgNTkuNjA2bDU4Ljk0OCAxODEuMzY2YzQuMDQ3IDExLjE4NiAxNi4xMzIgMTcuNDczIDI3LjczMiAxNC4xMzIgMTIuMDk5LTMuNDgxIDE5LjQ3NC0xNi4zMzIgMTUuNjEzLTI4LjIxN0w3NC42MjYgNDUuNTE2IiBmaWxsPSIjM0NCMTg3Ii8+PHBhdGggZD0iTTI0MC4xNjIgMTY2LjA0NWMxMS45NzUtMy44OSAxOC41MjYtMTYuNzUgMTQuNjM2LTI4LjcyNi0zLjg5LTExLjk3My0xNi43NTItMTguNTI3LTI4LjcyNS0xNC42MzZMNDQuNzA4IDE4MS42MzJjLTExLjE4NyA0LjA0Ni0xNy40NzMgMTYuMTMtMTQuMTM1IDI3LjczIDMuNDgzIDEyLjA5OSAxNi4zMzQgMTkuNDc1IDI4LjIxNyAxNS42MTRsMTgxLjM3Mi01OC45MyIgZmlsbD0iI0NFMUU1QiIvPjxwYXRoIGQ9Ik04Mi41MDggMjE3LjI3bDQzLjM0Ny0xNC4wODQtMTQuMDg2LTQzLjM1Mi00My4zNSAxNC4wOSAxNC4wODkgNDMuMzQ3IiBmaWxsPSIjMzkyNTM4Ii8+PHBhdGggZD0iTTE3My44NDcgMTg3LjU5MWMxNi4zODgtNS4zMjMgMzEuNjItMTAuMjczIDQzLjM0OC0xNC4wODRsLTE0LjA4OC00My4zNi00My4zNSAxNC4wOSAxNC4wOSA0My4zNTQiIGZpbGw9IiNCQjI0MkEiLz48cGF0aCBkPSJNMjEwLjQ4NCA3NC43MDZjMTEuOTc0LTMuODkgMTguNTI3LTE2Ljc1MSAxNC42MzctMjguNzI3LTMuODktMTEuOTczLTE2Ljc1Mi0xOC41MjYtMjguNzI3LTE0LjYzNkwxNS4wMjggOTAuMjkzQzMuODQyIDk0LjMzNy0yLjQ0NSAxMDYuNDIyLjg5NiAxMTguMDIyYzMuNDgxIDEyLjA5OCAxNi4zMzIgMTkuNDc0IDI4LjIxNyAxNS42MTNsMTgxLjM3MS01OC45MyIgZmlsbD0iIzcyQzVDRCIvPjxwYXRoIGQ9Ik01Mi44MjIgMTI1LjkzM2MxMS44MDUtMy44MzYgMjcuMDI1LTguNzgyIDQzLjM1NC0xNC4wODYtNS4zMjMtMTYuMzktMTAuMjczLTMxLjYyMi0xNC4wODQtNDMuMzUybC00My4zNiAxNC4wOTIgMTQuMDkgNDMuMzQ2IiBmaWxsPSIjMjQ4QzczIi8+PHBhdGggZD0iTTE0NC4xNiA5Ni4yNTZsNDMuMzU2LTE0LjA4OGE1NDYxNzkuMjEgNTQ2MTc5LjIxIDAgMCAwLTE0LjA4OS00My4zNkwxMzAuMDcgNTIuOWwxNC4wOSA0My4zNTYiIGZpbGw9IiM2MjgwM0EiLz48L3N2Zz4=',
            'can_export'          => true,
            'delete_with_user'    => true,
            'hierarchical'        => false,
            'has_archive'         => false,
            'query_var'           => false,
            'map_meta_cap' => false,
            'capabilities' => [
                'edit_post'              => 'manage_options',
                'read_post'              => 'manage_options',
                'delete_post'            => 'manage_options',
                'create_posts'           => 'manage_options',
                'edit_posts'             => 'manage_options',
                'edit_others_posts'      => 'manage_options',
                'publish_posts'          => 'manage_options',
                'read_private_posts'     => 'manage_options',
                'read'                   => 'manage_options',
                'delete_posts'           => 'manage_options',
                'delete_private_posts'   => 'manage_options',
                'delete_published_posts' => 'manage_options',
                'delete_others_posts'    => 'manage_options',
                'edit_private_posts'     => 'manage_options',
                'edit_published_posts'   => 'manage_options',
            ],
            'rewrite' => false,
            'supports' => [''],
            'labels' => [
                'name'               => __('Slack Integration',              ConfigService::TEXT_DOMAIN_NAME),
                'singular_name'      => __('Slack Integration',              ConfigService::TEXT_DOMAIN_NAME),
                'menu_name'          => __('Slackr',                         ConfigService::TEXT_DOMAIN_NAME),
                'name_admin_bar'     => __('Slackr',                         ConfigService::TEXT_DOMAIN_NAME),
                'add_new'            => __('Add New',                        ConfigService::TEXT_DOMAIN_NAME),
                'add_new_item'       => __('Add New Slack Integration',      ConfigService::TEXT_DOMAIN_NAME),
                'edit_item'          => __('Edit Slack Integration',         ConfigService::TEXT_DOMAIN_NAME),
                'new_item'           => __('New Slack Integration',          ConfigService::TEXT_DOMAIN_NAME),
                'view_item'          => __('View Slack Integration',         ConfigService::TEXT_DOMAIN_NAME),
                'search_items'       => __('Search Slack Integration',       ConfigService::TEXT_DOMAIN_NAME),
                'not_found'          => __('No slack integration found',     ConfigService::TEXT_DOMAIN_NAME),
                'not_found_in_trash' => __('No slack integration in trash',  ConfigService::TEXT_DOMAIN_NAME),
                'all_items'          => __('Slack Integrations',             ConfigService::TEXT_DOMAIN_NAME),
            ]
        ];

        register_post_type($this->name, $args);

        add_action('add_meta_boxes_'.$this->name, array($this, 'addSettingsMetaBox'));
        add_action('add_meta_boxes_'.$this->name, array($this, 'addEventsMetaBox'));
        add_action('save_post', array($this, 'saveSettings'));

        add_action('add_meta_boxes', array($this, 'addSubmitMetaBox'));
    }

    public function removeSubmitDiv()
    {
        remove_meta_box('submitdiv', $this->name, 'side');
    }

    public function bulkActions($actions) {
        unset($actions['edit']);

        return $actions;
    }

    public function columnsHeader($columns) {
        unset($columns['title']);
        unset($columns['date']);

        $columns['name'] = __('Name', ConfigService::TEXT_DOMAIN_NAME);
        $columns['endpointUrl'] = __('Service URL', ConfigService::TEXT_DOMAIN_NAME);
        $columns['activeEventCount'] = __('Active events', ConfigService::TEXT_DOMAIN_NAME);
        $columns['isActive'] = __('Is active', ConfigService::TEXT_DOMAIN_NAME);

        return $columns;
    }

    public function columnRow($column, $postId)
    {
        $settings = $this->settingsManager->getIntegrationSettings($postId);
        $events = $this->settingsManager->getIntegrationEvents($postId);

        /** @var IntegrationEventModel[] $activeEvents */
        $activeEvents = [];
        foreach($events as $event)
        {
            if($event->isActive)
            {
                $activeEvents[] = $event;
            }
        }

        switch ($column) {
            case 'name':
                echo TypeHelper::getPropertyValue($settings, 'name', '');
                break;
            case 'endpointUrl':
                echo TypeHelper::getPropertyValue($settings, 'endpointUrl', '');
                break;
            case 'activeEventCount':
                echo count($activeEvents);
                break;
            case 'isActive':
                echo (TypeHelper::getPropertyValue($settings, 'isActive', 0) == 1) ? 'Yes' : 'No';
                break;
        }
    }

    public function rowActions($actions)
    {
        $post = get_post();

        if (get_post_type($post) === $this->name)
        {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }

    public function hideSubSubSub()
    {
        return [];
    }

    public function addSettingsMetaBox() {
        add_meta_box(
            'slackr_integration_settings_meta_box',
            __( 'Integration Settings', ConfigService::TEXT_DOMAIN_NAME),
            function($post){
                $settings = $this->settingsManager->getIntegrationSettings($post->ID);

                wp_nonce_field($this->name.'_nonce', $this->name.'_nonce');
                ?>
                <div class="slackr-integration-settings-container">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[name]"><?php _e('Name', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="<?=SettingsService::INTEGRATION_SETTINGS?>[name]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[name]" value="<?=TypeHelper::getPropertyValue($settings, 'name', '')?>">
                                <p class="description">
                                    <?php _e( 'Provide a name as identification for this webhook.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]"><?php _e('Webhook url', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]" value="<?=TypeHelper::getPropertyValue($settings, 'endpointUrl', '')?>">
                                <p class="description">
                                    <?php _e( 'The incomming webhook URL. It should look like <code>https://hooks.slack.com/services/x/y/z</code>.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                    <br />
                                    <b><?php _e('How do I register a incomming webhook?', ConfigService::TEXT_DOMAIN_NAME) ?></b>
                                    <br />
                                    1. <?php _e('Navigate to <a href="https://your-domain.slack.com/apps" target="_blank">https://your-domain.slack.com/apps</a> and search for "Incomming webhooks".', ConfigService::TEXT_DOMAIN_NAME) ?>
                                    <br />
                                    2. <?php _e('Follow the Incomming webhooks installation, activation and configuration guide.', ConfigService::TEXT_DOMAIN_NAME) ?>
                                    <br />
                                    3. <?php _e('Configure one or more Slackr integrations to communicate with your Slack environment(s)', ConfigService::TEXT_DOMAIN_NAME) ?>
                                    <br />
                                    4. <?php _e('Copy and paste one Incomming webhook URL into the text field above.', ConfigService::TEXT_DOMAIN_NAME) ?>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]"><?php _e( 'Active', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]" <?=checked((bool)TypeHelper::getPropertyValue($settings, 'isActive', false));?>>
                                    <?php _e( 'Activate Notifications.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </label>
                                <p class="description">
                                    <?php _e( 'Notification will not be sent if not checked.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </p>
                            </td>
                        </tr>
                        <!--<tr valign="top">
                            <th scope="row">
                                <label></label>
                            </th>
                            <td>
                                <button class="button-primary slackr-test-connection" data-slackr-interation-id="<?=$post->ID?>"><?=__('Test connection', ConfigService::TEXT_DOMAIN_NAME)?></button>
                            </td>
                        </tr>-->
                        <script type="text/javascript">
                            <?php
                            //TODO: move to JS file and make this functionality work.
                            ?>
                            jQuery(document).ready(function($){
                                $('.slackr-test-connection').click(function(e){
                                    $(this).disabled(true);
                                });
                            });
                        </script>
                        </tbody>
                    </table>
                </div>
                <?php
            },
            $this->name,
            'advanced',
            'high'
        );
    }

    public function addEventsMetaBox()
    {
        add_meta_box(
            'slackr_integration_settings_events_meta_box',
            __( 'Integration events', ConfigService::TEXT_DOMAIN_NAME),
            function($post){
                $settings = $this->settingsManager->getIntegrationSettings($post->ID);
                $eventSettings = $this->settingsManager->getIntegrationEvents($post->ID);
                $eventGroups = $this->slackEventManager->getEventGroups();

                ?>
                <div class="slackr-row">
                    <div class="slackr-col-md-12">
                        <h2 class="nav-tab-wrapper" style="padding:0 14px;">
                            <?php
                            $isActiveTabSet = false;
                            $tabIndex = 0;
                            foreach($eventGroups as $i => $eventGroup)
                            {
                                if (count($eventGroup->getEventCategories()) < 1 || count($eventGroup->getEvents()) < 1)
                                {
                                    continue;
                                }
                                ?>
                                <a href="#slackr-settings-tab-<?=$tabIndex?>" class="slackr-settings-event-group-tab-btn nav-tab<?=($isActiveTabSet ? '': ' nav-tab-active')?>"><?=$eventGroup->getDisplayName()?></a>
                                <?php
                                $isActiveTabSet = true;
                                $tabIndex++;
                            }
                            ?>
                        </h2>
                    </div>
                </div>
                <div class="slackr-row">
                    <div class="slackr-col-md-12">
                        <?php
                        $activeTabSet = false;
                        $eventIndex = 0;
                        $tabIndex = 0;
                        foreach($eventGroups as $eventGroup)
                        {
                            if(count($eventGroup->getEventCategories()) < 1 || count($eventGroup->getEvents()) < 1)
                            {
                                continue;
                            }
                            ?>
                            <div class="slackr-row slackr-integration-settings-event-group-tab slackr-tab<?=(!$activeTabSet ? ' slackr-active' : '')?>" id="slackr-settings-tab-<?=$tabIndex?>">
                                <div class="slackr-col-md-12">
                                    <div class="slackr-row slackr-settings-event-category-crumble">
                                        <div class="slackr-col-md-12">
                                            <ul class="subsubsub">
                                                <?php
                                                $categoryLoopIndex = 0;
                                                $categoryIndex = 0;
                                                $categoryContents = [];
                                                $delimiterPlaceholder = '%PLACEHOLDER__DELIMITER%';

                                                foreach($eventGroup->getEventCategories() as $eventCategory)
                                                {
                                                    $categoryLoopIndex++;

                                                    if(count($eventCategory->getEvents()) < 1)
                                                    {
                                                        continue;
                                                    }

                                                    ob_start();
                                                    ?>
                                                    <li class="all">
                                                        <a data-target-id="slackr-settings-tab-category-<?=$tabIndex.'_'.$categoryIndex?>" class="slackr-settings-event-category-tab-btn <?=($categoryIndex == 0) ? 'current' : ''?>"><?=$eventCategory->getDisplayName()?> <span class="count">(<span class="all-count"><?=count($eventCategory->getEvents())?></span>)</span></a>
                                                        <?=$delimiterPlaceholder?>
                                                    </li>
                                                    <?php
                                                    $categoryContents[] = ob_get_clean();
                                                    $categoryIndex++;
                                                }

                                                foreach($categoryContents as $i => $categoryContent)
                                                {
                                                    $delimiter = ' |';

                                                    if(($i + 1) >= count($categoryContents))
                                                    {
                                                        $delimiter = '';
                                                    }

                                                    echo str_replace($delimiterPlaceholder, $delimiter, $categoryContent);
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                    $activeTabSet = true;
                                    $categoryIndex = 0;

                                    foreach($eventGroup->getEventCategories() as $eventCategory)
                                    {
                                        if(count($eventCategory->getEvents()) < 1)
                                        {
                                            continue;
                                        }

                                        ?>
                                        <div class="slackr-row slackr-integration-settings-event-category-tab slackr-tab<?=($categoryIndex == 0) ? ' slackr-active' : ''?>" id="slackr-settings-tab-category-<?=$tabIndex.'_'.$categoryIndex?>">
                                            <div class="slackr-col-md-12">
                                                <?php
                                                $categoryEventIndex = 1;
                                                foreach($eventCategory->getEvents() as $event)
                                                {
                                                    if(($categoryEventIndex % 4) == 0)
                                                    {
                                                        ?>
                                                        <div class="slackr-row">
                                                        <?php
                                                    }
                                                    ?>
                                                    <div class="slackr-col-md-3">
                                                        <div class="slackr-settings-event-block">
                                                            <?php
                                                            /** @var IntegrationEventModel $eventSetting */
                                                            $eventSetting = null;

                                                            foreach($eventSettings as $possibleEventSetting)
                                                            {
                                                                if(TypeHelper::getCleanClassNameString($possibleEventSetting->className) === TypeHelper::getCleanClassNameString(get_class($event)))
                                                                {
                                                                    $eventSetting = $possibleEventSetting;
                                                                    break;
                                                                }
                                                            }
                                                            ?>
                                                            <div class="slackr-row">
                                                                <div class="slackr-header">
                                                                    <div class="title">
                                                                        <b title="<?=$event->getName()?>"><?=$event->getName()?></b>
                                                                    </div>
                                                                    <label class="active-wrapper">
                                                                        <input class="event-active-action" type="checkbox" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][isActive]" id="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][isActive]" <?=checked((bool)TypeHelper::getPropertyValue($eventSetting, 'isActive', false))?>>
                                                                    </label>
                                                                </div>
                                                                <div class="slackr-body">
                                                                    <div class="content">
                                                                        <input type="hidden" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][className]" value="<?=TypeHelper::getCleanClassNameString(get_class($event))?>" />
                                                                        <p class="description">
                                                                            <?=$event->getDescription()?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="active-content<?=(true === (bool)TypeHelper::getPropertyValue($eventSetting, 'isActive', false)) ? ' slackr-active' : ''?>">
                                                                        <label>
                                                                            <div class="property-title"><?=__('Message', ConfigService::TEXT_DOMAIN_NAME)?></div>
                                                                            <textarea class="large-text" rows="5" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][message]" id="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][message]"><?=$event->getMessage($settings)?></textarea>
                                                                            <p class="description">
                                                                                <?=__('Default message:', ConfigService::TEXT_DOMAIN_NAME)?> <?=$event->getDefaultMessage()?>
                                                                            </p>
                                                                        </label>
                                                                        <?php
                                                                        echo $event->getSettingsUi($eventIndex, $eventSetting);
                                                                        ?>
                                                                    </div>

                                                                </div>
                                                                <div class="slackr-footer">

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    if(($categoryEventIndex % 4) == 0)
                                                    {
                                                        ?>
                                                        </div>
                                                        <br />
                                                        <?php
                                                    }
                                                    $categoryEventIndex++;
                                                    $eventIndex++;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                        $categoryIndex++;
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php
                            $tabIndex++;
                        }
                        ?>
                    </div>
                </div>
                <script type="text/javascript">
                    <?php
                    //TODO: move to js file.
                    ?>
                    jQuery(document).ready(function($){
                        var eventsScopeId = '#slackr_integration_settings_events_meta_box';

                        $(eventsScopeId + ' .slackr-settings-event-group-tab-btn').click(function(e){
                            var self = $(this);

                            $(eventsScopeId + ' .slackr-settings-event-group-tab-btn').removeClass('nav-tab-active');
                            self.addClass('nav-tab-active');

                            var tabSelector = $(this).attr('href');
                            $(eventsScopeId + ' .slackr-integration-settings-event-group-tab').removeClass('slackr-active');
                            $(tabSelector).addClass('slackr-active');

                            var activeCategoryBtnSelector = $(tabSelector).find('.slackr-settings-event-category-tab-btn').first();
                            activeCategoryBtnSelector.trigger('click');
                        });

                        $(eventsScopeId + ' .slackr-settings-event-category-tab-btn').click(function(e){
                            var self = $(this);

                            $(eventsScopeId + ' .slackr-settings-event-category-tab-btn').removeClass('current');
                            self.addClass('current');

                            var tabSelector = '#' + $(this).attr('data-target-id');
                            $(eventsScopeId + ' .slackr-integration-settings-event-category-tab').removeClass('slackr-active');
                            $(tabSelector).addClass('slackr-active');
                        });

                        $(eventsScopeId + ' .slackr-settings-event-block .event-active-action').change(function(e){
                            var elementSelector = $(this).closest('.slackr-settings-event-block').find('.active-content').first();
                            if($(this).is(":checked") && !elementSelector.hasClass('slackr-active'))
                            {
                                elementSelector.addClass('slackr-active');
                            }else{
                                elementSelector.removeClass('slackr-active');
                            }
                        });
                    });
                </script>
                <?php
            },
            $this->name,
            'advanced',
            'high'
        );
    }

    public function saveSettings($postId)
    {
        if (get_post_type($postId) !== $this->name) {
            return;
        }

        $postedWpNonce = TypeHelper::getPropertyValue($_POST, $this->name . '_nonce', null);
        $postedSettings = TypeHelper::getPropertyValue($_POST, SettingsService::INTEGRATION_SETTINGS, []);
        $postedEvents = TypeHelper::getPropertyValue($_POST, SettingsService::INTEGRATION_SETTING_EVENT, []);

        if (empty($postedWpNonce))
        {
            return;
        }

        if (!wp_verify_nonce($postedWpNonce, $this->name.'_nonce')
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can( 'manage_options')
            || empty($postedSettings)
        )
        {
            return;
        }

        $settings = new IntegrationSettingsModel();
        $settings->wpPostId = $postId;
        $settings->name = TypeHelper::getPropertyValue($postedSettings, 'name', '');
        $settings->endpointUrl = TypeHelper::getPropertyValue($postedSettings, 'endpointUrl', '');
        $settings->channelName = TypeHelper::getPropertyValue($postedSettings, 'channelName', '');
        $settings->username = TypeHelper::getPropertyValue($postedSettings, 'username', '');
        $settings->iconEmoji = TypeHelper::getPropertyValue($postedSettings, 'iconEmoji', '');
        $settings->isActive = !empty(TypeHelper::getPropertyValue($postedSettings, 'isActive', null));

        $this->settingsManager->saveIntegrationSettings($settings);

        /** @var array $integrationEvents */
        $integrationEvents = [];
        foreach($postedEvents as $postedEvent)
        {
            $integrationEvent['wpPostId'] = $postId;
            $integrationEvent['className'] = sanitize_text_field(TypeHelper::getPropertyValue($postedEvent, 'className', ''));
            $integrationEvent['message'] = TypeHelper::getPropertyValue($postedEvent, 'message', '');
            $integrationEvent['isActive'] = !empty(TypeHelper::getPropertyValue($postedEvent, 'isActive', null));

            $event = $this->slackEventManager->getRegisteredEvent($integrationEvent['className']);
            if(is_object($event))
            {
                if(strtolower(trim($event->getDefaultMessage())) === strtolower(trim($integrationEvent['message'])))
                {
                    //TODO: make this work with multitple languages
                    $integrationEvent['message'] = ''; //If the message is the default, clear it so that changes in default messages will be applied.
                }

                //Call save for custom event settings. Values should be added to the $integrationEvent array
                $event->saveSettings($postId, $_POST, $postedSettings, $postedEvent, $integrationEvent);
            }

            $integrationEvents[] = $integrationEvent;
        }

        $this->settingsManager->saveIntegrationEvents($postId, $integrationEvents);
    }

    public function addSubmitMetaBox($postType) {
        if ($this->name === $postType) {
            add_meta_box(
                'slackr_submit_meta_box',
                __('Save', 'textdp,aom'),
                function($post){
                    ?>
                    <div class="submitbox" id="submitpost">

                        <div style="display:none;">
                            <?php submit_button( __('Save', ConfigService::TEXT_DOMAIN_NAME), 'button', 'save'); ?>
                        </div>

                        <?php // Always publish. ?>
                        <input type="hidden" name="post_status" id="hidden_post_status" value="publish" />

                        <div id="major-publishing-actions">

                            <div id="delete-action">
                                <?php
                                if ( ! EMPTY_TRASH_DAYS ) {
                                    $delete_text = __('Delete Permanently', ConfigService::TEXT_DOMAIN_NAME);
                                } else {
                                    $delete_text = __('Move to Trash', ConfigService::TEXT_DOMAIN_NAME);
                                }
                                ?>
                                <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a>
                            </div>

                            <div id="publishing-action">
                                <span class="spinner"></span>

                                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save', ConfigService::TEXT_DOMAIN_NAME) ?>" />
                                <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save', ConfigService::TEXT_DOMAIN_NAME); ?>" />
                            </div>
                            <div class="clear"></div>

                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                },
                null,
                'side',
                'core'
            );
        }
    }
}