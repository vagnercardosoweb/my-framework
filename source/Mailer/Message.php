<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Message.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Message
{
    /**
     * @var PHPMailer
     */
    protected $mail;

    /**
     * @param PHPMailer $mail
     */
    public function __construct(PHPMailer $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @throws \Exception
     *
     * @return \Core\Mailer\Message
     */
    public function from(string $address, ?string $name = ''): Message
    {
        try {
            $this->mail->setFrom($address, $name);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Message
     */
    public function reply(string $address, ?string $name = ''): Message
    {
        $this->mail->addReplyTo($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Message
     */
    public function addCC(string $address, ?string $name = ''): Message
    {
        $this->mail->addCC($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Message
     */
    public function addBCC(string $address, ?string $name = ''): Message
    {
        $this->mail->addBCC($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Message
     */
    public function to(string $address, ?string $name = ''): Message
    {
        $this->mail->addAddress($address, $name);

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return \Core\Mailer\Message
     */
    public function subject(string $subject): Message
    {
        $this->mail->Subject = $subject;

        return $this;
    }

    /**
     * @param string $path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     *
     * @throws \Exception
     *
     * @return \Core\Mailer\Message
     */
    public function addFile(
        string $path,
        ?string $name = '',
        ?string $encoding = PHPMailer::ENCODING_BASE64,
        ?string $type = '',
        ?string $disposition = 'attachment'
    ): Message {
        try {
            $this->mail->addAttachment($path, $name, $encoding, $type, $disposition);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param string $message
     *
     * @return \Core\Mailer\Message
     */
    public function body(string $message): Message
    {
        $this->mail->Body = $message;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return \Core\Mailer\Message
     */
    public function altBody(string $message): Message
    {
        $this->mail->AltBody = $message;

        return $this;
    }
}
