<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

namespace Core\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Auth.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Auth
{
    /**
     * @var PHPMailer
     */
    protected $mailer;

    /**
     * Mailer constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->validate($options);
        $this->initialize($options);
    }

    /**
     * @param array $options
     */
    protected function validate(array &$options): void
    {
        if (empty($options['host'])) {
            throw new \InvalidArgumentException(
                'Host not configured.'
            );
        }

        if (empty($options['username']) || empty($options['password'])) {
            throw new \InvalidArgumentException(
                'User and password not configured.'
            );
        }
    }

    /**
     * @param array $options
     */
    protected function initialize(array $options): void
    {
        // PHPMailer
        $this->mailer = new PHPMailer($options['exception'] ?? true);

        // Settings
        $this->mailer->SMTPDebug = $options['debug'] ?? 0;
        $this->mailer->CharSet = $options['charset'] ?? PHPMailer::CHARSET_UTF8;
        $this->mailer->isSMTP();
        $this->mailer->isHTML(true);
        $this->mailer->setLanguage(
            $options['language']['code'] ?? 'pt_br',
            $options['language']['path'] ?? ''
        );

        // Authentication
        $this->mailer->SMTPAuth = $options['auth'] ?? true;

        if (!empty($options['secure'])) {
            $this->mailer->SMTPSecure = $options['secure'];
        }

        // Server e-mail
        $this->mailer->Host = $this->buildHost($options['host']);
        $this->mailer->Port = $options['port'] ?? 587;
        $this->mailer->Username = $options['username'];
        $this->mailer->Password = $options['password'];

        // Default from
        if (!empty($options['from']['mail'])) {
            $this->mailer->From = $options['from']['mail'];

            if (!empty($options['from']['name'])) {
                $this->mailer->FromName = $options['from']['name'];
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
