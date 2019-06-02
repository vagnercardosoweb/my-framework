<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Mailer {

    use PHPMailer\PHPMailer\PHPMailer;

    /**
     * Class Message
     *
     * @package Core\Mailer
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Message
    {
        /**
         * @var PHPMailer
         */
        protected $mail;

        /**
         * Message constructor.
         *
         * @param PHPMailer $mail
         */
        public function __construct(PHPMailer $mail)
        {
            $this->mail = $mail;
        }

        /**
         * Adicionar quem está enviando o email
         *
         * @param string $address
         * @param string $name
         *
         * @return $this
         * @throws \PHPMailer\PHPMailer\Exception
         */
        public function from($address, $name = '')
        {
            $this->mail->setFrom($address, $name);

            return $this;
        }

        /**
         * Adicionar a quem vai a resposta se for respondido o email
         *
         * @param string $address
         * @param string $name
         *
         * @return $this
         */
        public function reply($address, $name = '')
        {
            $this->mail->addReplyTo($address, $name);

            return $this;
        }

        /**
         * Adiciona para quem vai enviar o email.
         *
         * @param string $address
         * @param string $name
         *
         * @return $this
         */
        public function to($address, $name = '')
        {
            $this->mail->addAddress($address, $name);

            return $this;
        }

        /**
         * Adiciona o titulo do email
         *
         * @param string $subject
         *
         * @return $this
         */
        public function subject($subject)
        {
            $this->mail->Subject = $subject;

            return $this;
        }

        /**
         * Se existir arquivo, adiciona o arquivo.
         * os path dos arquivos devem ser passado como array ou o path direto
         *
         * @param string $path
         * @param string $name
         *
         * @return $this
         * @throws \PHPMailer\PHPMailer\Exception
         */
        public function addFile($path, $name = '')
        {
            $this->mail->addAttachment($path, $name);

            return $this;
        }

        /**
         * Corpo da mensagem
         *
         * @param $body
         */
        public function body($body)
        {
            $this->mail->Body = $body;
        }
    }
}
