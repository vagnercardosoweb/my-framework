<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Mailer;

use Core\App;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Mailer.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Mailer
{
    /**
     * @var PHPMailer
     */
    protected $mail;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->validateOptions($options);
        $this->configureDefaultMailer($options);
    }

    /**
     * @param string   $template
     * @param array    $context
     * @param \Closure $callback
     *
     * @throws \Exception
     *
     * @return \Core\Mailer\Mailer
     */
    public function send(string $template, array $context, \Closure $callback): Mailer
    {
        try {
            $message = new Message($this->mail);
            $message->body(App::getInstance()->resolve('view')->fetch("@mail.{$template}", $context));

            call_user_func_array($callback->bindTo($this->mail), [
                $message,
                $context,
            ]);

            // Send mailer
            if (!$this->mail->send()) {
                throw new \Exception(
                    $this->mail->ErrorInfo, E_USER_ERROR
                );
            }

            // Clear properties
            $this->clearAll();

            return $this;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function clearAll(): void
    {
        $this->mail->clearAddresses();
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();
        $this->mail->clearBCCs();
        $this->mail->clearCCs();
        $this->mail->clearCustomHeaders();
        $this->mail->clearReplyTos();
    }

    /**
     * @param array $options
     */
    protected function validateOptions(array &$options): void
    {
        if (empty($options['host'])) {
            throw new \InvalidArgumentException(
                'Host not configured.', E_USER_ERROR
            );
        }

        if (empty($options['username']) || empty($options['password'])) {
            throw new \InvalidArgumentException(
                'User and password not configured.', E_USER_ERROR
            );
        }
    }

    /**
     * @param array $options
     */
    protected function configureDefaultMailer(array $options): void
    {
        // PHPMailer
        $this->mail = new PHPMailer(
            $options['exception'] ?? true
        );

        // Settings
        $this->mail->SMTPDebug = $options['debug'] ?? 0;
        $this->mail->CharSet = $options['charset'] ?? PHPMailer::CHARSET_UTF8;
        $this->mail->isSMTP();
        $this->mail->isHTML(true);
        $this->mail->setLanguage(
            $options['language']['code'] ?? 'pt_br',
            $options['language']['path'] ?? ''
        );

        // Authentication
        $this->mail->SMTPAuth = $options['auth'] ?? true;

        if (!empty($options['secure'])) {
            $this->mail->SMTPSecure = $options['secure'];
        }

        // Server e-mail
        $this->mail->Host = $this->buildHost($options['host']);
        $this->mail->Port = $options['port'] ?? 587;
        $this->mail->Username = $options['username'];
        $this->mail->Password = $options['password'];

        // Default from
        if (!empty($options['from']['mail'])) {
            $this->mail->From = $options['from']['mail'];

            if (!empty($options['from']['name'])) {
                $this->mail->FromName = $options['from']['name'];
            }
        }
    }

    /**
     * @param string|array $host
     *
     * @return string
     */
    protected function buildHost($host): string
    {
        if (is_array($host)) {
            $host = implode(';', $host);
        }

        return $host;
    }
}
