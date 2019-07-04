<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;
use Core\Curl\Request;
use Core\Helpers\Helper;

/**
 * Class ErrorSlackProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorSlackProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        if (
            !preg_match('/localhost|.dev|.local/i', $_SERVER['HTTP_HOST']) &&
            !Helper::isPhpCli() &&
            App::getInstance()->resolve('event')
        ) {
            $this->dispatch();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function dispatch(): void
    {
        $this->event->on('eventErrorHandler', function (array $errors) {
            if (!empty($errors['error']) && !empty(env('SLACK_ERROR_URL', ''))) {
                unset($errors['error']['trace']);

                $id = hash_hmac('sha1', json_encode($errors['error']), 'slackNotification');

                $this->event->on('eventErrorHandlerId', function () use ($id) {
                    return $id;
                });

                if ($this->timeFile($id)) {
                    $this->notification(array_merge(['id' => $id], $errors['error']));
                }
            }
        });
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    protected function timeFile(string $id): bool
    {
        $filename = $this->createFile($id);
        $filemtime = filemtime($filename);
        $time = time();

        if ($filemtime <= $time) {
            touch($filename, ($time + env('SLACK_ERROR_INTERVAL', 60)));

            return true;
        }

        return false;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function createFile(string $id): string
    {
        $path = APP_FOLDER.'/storage/cache/slack';
        $filename = "{$path}/{$id}";

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        if (!file_exists($filename)) {
            file_put_contents($filename, '');
        }

        clearstatcache();

        return $filename;
    }

    /**
     * @param array $error
     */
    protected function notification(array $error): void
    {
        if ('true' == env('APP_SESSION', true)) {
            session_write_close();
        }

        $ip = Helper::getIpAddress();
        $hostname = gethostbyaddr($ip);
        $text = [];
        $text[] = "*ip:* {$ip}";
        $text[] = "*hostname:* {$hostname}";
        $text[] = '*date:* '.date('d/m/Y H:i:s', time());

        foreach ($error as $key => $value) {
            $text[] = "*error.{$key}:* {$value}";
        }

        foreach (Helper::getUserAgent() as $key => $value) {
            $text[] = "*browser.{$key}:* {$value}";
        }

        $text = implode(PHP_EOL, $text);

        try {
            (new Request())->post(env('SLACK_ERROR_URL'), json_encode([
                'text' => $text,
                'username' => config('client.name'),
                'mrkdwn' => true,
            ]));
        } catch (\Exception $e) {
            $this->logger
                ->filename('slack')
                ->error(
                    $e->getMessage(), $error
                )
            ;
        }
    }
}
