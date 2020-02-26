<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Events;

use Core\Curl\Curl;
use Core\Helpers\Helper;
use Core\Helpers\Path;
use Core\Session\Session;

/**
 * Class ErrorSlackEvent.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorSlackEvent extends Event
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'eventErrorHandler';
    }

    /**
     * @param mixed $errors
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function register($errors = null)
    {
        if (!$this->event || 'production' !== env('APP_ENV')) {
            return;
        }

        if (!empty($errors['error']) && !empty(env('SLACK_ERROR_URL', ''))) {
            unset($errors['error']['trace']);

            if (!empty($errors['error']['sha1'])) {
                $id = $errors['error']['sha1'];
            } else {
                $id = sha1(json_encode($errors['error']));
            }

            $this->event->on('eventErrorHandlerId', function () use ($id) {
                return $id;
            });

            if ($this->timeFile($id)) {
                $this->notification(array_merge(['id' => $id], $errors['error']));
            }
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    protected function timeFile(string $id): bool
    {
        $filename = $this->createFile($id);
        $fileTime = filemtime($filename);
        $time = time();

        if ($fileTime <= $time) {
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
        $path = Path::app('/storage/cache/slack');
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
        if (Session::active()) {
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
            (new Curl())->post(env('SLACK_ERROR_URL'), json_encode([
                'text' => $text,
                'username' => config('client.name'),
                'mrkdwn' => true,
            ]));
        } catch (\Exception $e) {
            $this->logger->filename('slack')->error($e->getMessage(), $error);
        }
    }
}
