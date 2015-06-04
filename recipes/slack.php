<?php
/* (c) Tomas Majer <tomasmajer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Notify Slack of successful deployment
 */
task('deploy:slack', function () {
    global $php_errormsg;

    $config = get('slack', []);

    if (!isset($config['message'])) {
        $releasePath = env('release_path');
        $host = env('server.host');
        $stage = env('stages')[0];
        $config['message'] = "Deployment to '{$host}' on *{$stage}* was successful\n($releasePath)";
    }

    $defaultConfig = [
        'channel' => '#general',
        'icon' => ':sunny:',
        'username' => 'Deploy',
        'parse' => '',
    ];

    $config = array_merge($defaultConfig, $config);
    if (!is_array($config) ||
        !isset($config['token']) ||
        !isset($config['team']) ||
        !isset($config['channel']))
    {
        throw new \RuntimeException("Please configure new slack: set('slack', array('token' => 'xoxp...', 'team' => 'team', 'channel' => '#channel', 'messsage' => 'message to send'));");
    }

    $url = 'https://slack.com/api/chat.postMessage?token=' . $config['token'] .
        '&channel=' . urlencode($config['channel']) .
        '&text=' . urlencode($config['message']) .
        '&username=' . urlencode($config['username']) .
        '&icon_emoji=' . urlencode($config['icon']) .
        '&pretty=1';

    $result = @file_get_contents($url);

    if (!$result) {
        throw new \RuntimeException($php_errormsg);
    }
})->desc('Notifying Slack channel of deployment');
