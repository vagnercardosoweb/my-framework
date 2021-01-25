<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace Core\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Mailer.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Mailer extends Auth
{
    /**
     * @param string $address
     * @param string $name
     *
     * @throws \Exception
     *
     * @return \Core\Mailer\Mailer
     */
    public function from(string $address, ?string $name = ''): Mailer
    {
        $this->mailer->setFrom($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Mailer
     */
    public function reply(string $address, ?string $name = ''): Mailer
    {
        $this->mailer->addReplyTo($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Mailer
     */
    public function addCC(string $address, ?string $name = ''): Mailer
    {
        $this->mailer->addCC($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Mailer
     */
    public function addBCC(string $address, ?string $name = ''): Mailer
    {
        $this->mailer->addBCC($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return \Core\Mailer\Mailer
     */
    public function to(string $address, ?string $name = ''): Mailer
    {
        $this->mailer->addAddress($address, $name);

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return \Core\Mailer\Mailer
     */
    public function subject(string $subject): Mailer
    {
        $this->mailer->Subject = $subject;

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
     * @return \Core\Mailer\Mailer
     */
    public function addFile(
        string $path,
        ?string $name = '',
        ?string $encoding = PHPMailer::ENCODING_BASE64,
        ?string $type = '',
        ?string $disposition = 'attachment'
    ): Mailer {
        $this->mailer->addAttachment($path, $name, $encoding, $type, $disposition);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return \Core\Mailer\Mailer
     */
    public function body(string $message): Mailer
    {
        $this->mailer->msgHTML($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return \Core\Mailer\Mailer
     */
    public function altBody(string $message): Mailer
    {
        $this->mailer->AltBody = $message;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function send(): PHPMailer
    {
        if (!$this->mailer->send()) {
            throw new MailerException($this->mailer->ErrorInfo);
        }

        $this->clear();

        return $this->mailer;
    }

    /**
     * @return void
     */
    protected function clear(): void
    {
        $this->mailer->clearAddresses();
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearBCCs();
        $this->mailer->clearCCs();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();
    }
}
