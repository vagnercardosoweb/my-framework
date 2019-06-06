<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {
    use Core\App;
    use Core\Helpers\Curl;
    use Core\Helpers\Helper;

    /**
     * Class ErrorSlackProvider
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class ErrorSlackProvider extends Provider
    {
        /**
         * {@inheritdoc}
         */
        public function register()
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
         */
        protected function dispatch(): void
        {
            $this->event->on('eventErrorHandler', function (array $errors) {
                if (!empty($errors['error']) && !empty(env('SLACK_ERROR_URL', ''))) {
                    unset($errors['error']['trace']);

                    // Password
                    $id = hash_hmac('sha1', json_encode($errors['error']), 'slackNotification');

                    // Adiciona um novo evento para gerar o id.
                    $this->event->on('eventErrorHandlerId', function () use ($id) {
                        return $id;
                    });

                    // Envia a notificação
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
            // Time do arquivo
            $filename = $this->createFile($id);
            $filetime = filemtime($filename);
            $time = time();

            // Verifica se pode enviar a notificação
            if ($filetime <= $time) {
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
            // Variáveis
            $path = APP_FOLDER . '/storage/cache/slack';
            $filename = "{$path}/{$id}";

            // Cria a pasta caso não exista
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            // Verifica se tem o arquivo e caso n tenha cria
            if (!file_exists($filename)) {
                file_put_contents($filename, '');
            }

            // Limpa o cache do arquivo
            clearstatcache();

            return $filename;
        }

        /**
         * @param array $error
         */
        protected function notification(array $error): void
        {
            // Deixa prosseguir a aplicação
            if ('true' == env('APP_SESSION', true)) {
                session_write_close();
            }

            // Variáveis
            $ip = Helper::getIpAddress();
            $hostname = gethostbyaddr($ip);
            $text = [];
            $text[] = "*ip:* {$ip}";
            $text[] = "*hostname:* {$hostname}";
            $text[] = '*date:* ' . date('d/m/Y H:i:s', time());

            // Monta payload do erro
            foreach ($error as $key => $value) {
                $text[] = "*error.{$key}:* {$value}";
            }

            // Monta payload do browser
            foreach (Helper::getUserAgent() as $key => $value) {
                $text[] = "*browser.{$key}:* {$value}";
            }

            // Monta text
            $text = implode(PHP_EOL, $text);

            // Envia o payload
            try {
                (new Curl())->post(env('SLACK_ERROR_URL'), json_encode([
                    'text' => $text,
                    'username' => config('client.name'),
                    'mrkdwn' => true,
                ]));
            } catch (\Exception $e) {
                $this->logger->filename('slack')->error(
                    $e->getMessage(), $error
                );
            }
        }
    }
}
