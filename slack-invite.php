<?php
namespace Grav\Plugin;

use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponse;
use Frlnc\Slack\Http\SlackResponseFactory;
use Grav\Common\Inflector;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class SlackInvitePlugin
 * @package Grav\Plugin
 */
class SlackInvitePlugin extends Plugin
{
    protected $slack;

    protected $total_members;
    protected $active_members;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // invite the user
        require_once __DIR__ . '/vendor/autoload.php';

        $interactor = new CurlInteractor();
        $interactor->setResponseFactory(new SlackResponseFactory);

        /** @var Commander $commander */
        $this->slack = new Commander($this->config['plugins.slack-invite.slack_token'], $interactor);

        $cache = $this->grav['cache'];

        list($this->total_members, $this->active_members) = $cache->fetch('slack_members');

        if (!$this->total_members) {

            /** @var SlackResponse $response */
            $response = $this->slack->execute('users.list', [
                'presence'  => true
            ]);

            $response_body = $response->getBody();

            if ($response_body['ok']) {

                $presences = array_count_values(array_map(function ($foo) {
                    if ($foo['is_bot'] || $foo['name'] == 'slackbot') {
                        return 'bot';
                    }
                    if ($foo['deleted']) {
                        return 'deleted';
                    }
                    if (isset($foo['presence']) && $foo['presence'] == 'active') {
                        return 'active';
                    } else {
                        return 'away';
                    }
                }, $response_body['members']));

                $this->active_members = $presences['active'];
                $this->total_members = $presences['active'] + $presences['away'];

                $cache->save('slack_members', [$this->total_members, $this->active_members], $this->config['plugins.slack-invite.cache_timeout']);
            }

        }


        // Enable the main event we are interested in
        $this->enable([
            'onTwigPageVariables' => ['onTwigVariables', 0],
            'onTwigSiteVariables' => ['onTwigVariables', 0],
            'onFormProcessed' => ['onFormProcessed', 0],
        ]);
    }

    /**
     * get some important information about the team
     */
    public function onTwigVariables()
    {
        $twig = $this->grav['twig'];
        $twig->twig_vars['slack_active_users'] = $this->active_members;
        $twig->twig_vars['slack_total_users'] = $this->total_members;
    }

    /**
     * Process the form and see if there is an slack invite action
     *
     * @param Event $event
     */
    public function onFormProcessed(Event $event)
    {
        /** @var Form $form */
        $form = $event['form'];
        $action = $event['action'];

        switch ($action) {
            case 'slack-invite':
                // make sure we have the email
                $email = $form->value('email');
                if ($email && $this->isValidUser($email)) {

                    /** @var SlackResponse $response */
                    $response = $this->slack->execute('users.admin.invite', [
                        'email'  => $email,
                        'resend' => true,
                    ]);

                    $response_body = $response->getBody();

                    if (!$response_body['ok'])
                    {
                        $inflector = new Inflector();
                        $message = $inflector->humanize($response_body['error']);

                        $form->status = 'error';
                        $form->message_color = 'red';
                        $form->message = $this->grav['language']->translate(['PLUGIN_SLACKINVITE.API_ERROR', $message]);
                    }

                } else {
                    $this->grav->fireEvent('onFormValidationError', new Event([
                        'form'    => $form,
                        'message' => $this->grav['language']->translate('PLUGIN_SLACKINVITE.EMAIL_ERROR')
                    ]));
                    $event->stopPropagation();
                }

                break;
        }
    }

    protected function isValidUser($email)
    {
        $blacklist = (array) $this->config['plugins.slack-invite.blacklist'];

        foreach($blacklist as $var) {
            if (strpos($email, $var) !== false) {
                return false;
            }
        }

        return true;
    }
}
