# Slack Invite Plugin

The **Slack Invite** plugin allows you to create a slack invite form on your Grav website utilizing standard Grav forms. You can see a [demo here](https://getgrav.org/slack).

## Installation

Installing the Slack Invite plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install slack-invite

This will install the Slack Invite plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/slack-invite`.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/slack-invite/slack-invite.yaml` to `user/config/plugins/slack-invite.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
cache_timeout: 3600
slack_token: xoxp-XXXXXXXXXXXXX-XXXXXXXXXXXXX-XXXXXXXXXXXXX-XXXXXXXXXXXXXXXXXXXXX
```

| Note: The `cache_timeout` option configures how long the user counts should remain cached.  This saves continually requesting the values from the API. With cache **off** these will be retreived on every request, and could cause throttling from Slack.

You must generate a Slack token from the [Slack API site](https://api.slack.com/docs/oauth-test-tokens).

## Usage

The plugin provides a new `slack-invite` form processing action.  To use it you simply need to create a form that calls this process.  For example, this is a single page called `default.md` that uses in **in-page** form definition to render an invite form:

    ---
    title: Grav Slack Chat
    never_cache_twig: true
    bodyclass: slack-chat
    process:
        twig: true
    
    forms:
        slack-form:
            fields:
                - name: email
                  label: Email
                  placeholder: Enter your email address
                  type: email
                  validate:
                    required: true
        
            buttons:
                - type: submit
                  value: Get my invite
    
        
            process:
                - message: Check your email to complete the invite process
                - slack-invite: true
                - reset: true
                
    ---
    
    # Grav Chat
    
    Join **{{ slack_total_users }}** other passionate Grav users in the official Grav Slack chat.   
    Right now, there's **{{ slack_active_users }}** active users to chat with.
    
    {% include "forms/form.html.twig" with { form: forms('slack-form') } %}
    
    if you already have an account, [please login to Slack](https://getgrav.slack.com)


You can use `{{ slack_total_users }}` and `{{ slack_active_users }}` to display the number of total and active members in the Slack team. Bots are removed from the count.

