=== Slackr ===
Contributors: newheap
Donate link: https://newheap.com
Tags: Slack, Slackr, Events, Notifications, Integration, webhook, chat
Requires at least: 4.6
Tested up to: 4.8
Stable tag: 1.0.0
Minimum PHP: 5.6

Slackr keeps you in the loop of everything that is happening on your site by sending customizable Slack notifications.

== Description ==
Slackr allows you to easily add Slack Incomming Webhook integrations to your Wordpress environment. Incomming webhooks is a plugin for your Slack environment which enables you to generate a URL which will allow 3rd party application's to send notifications to your Slack environment.

Slackr contains mulitple, easy to configure, events. When an event occurs, a personally configurable notification will be send to your Slack environment via the Incomming webhook. This plugin also allow's you to setup custom events which you can create in your own theme or plugin.

**The following events are supported out of the box:**
* Add custom events
* Filter on post types
* Post created
* Post updated
* Post thrashed
* Post deleted
* Comment created
* Comment status change
* User login successful
* User login failed
* User created
* User updated
* User deleted
* User role changed
* Plugin activated
* Plugin deactivated
* Plugin deleted
* Attachment created
* Attachment updated
* Attachment deleted

== Installation ==

=== From within WordPress ===
1. Visit 'Plugins > Add New'
1. Search for 'Slackr'
1. Activate Slackr from your Plugins page.
1. Go to "after activation" below.

=== Manually ===
1. Upload the `slackr` folder to the `/wp-content/plugins/` directory
1. Activate the Slackr plugin through the 'Plugins' menu in WordPress
1. Go to "after activation" below.

=== After activation ===
1. Slackr should now be available in the main menu
1. Navigate to https://your-domain.slack.com/apps and search for "Incomming webhooks"
1. Follow the Incomming webhooks installation, activation and configuration guide.
1. Configure one or more Slackr integrations to communicate with your Slack environment(s)
1. You're done!

== Screenshots ==
1. How Slackr looks in slack
2. Support for multiple customizable integrations
3. Posts hooks with settings
4. Support for system hooks
5. Support for user and authentication hooks

== Changelog ==

= 1.0.0 =
* Initial release.