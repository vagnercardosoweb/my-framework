<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Mailer {

    use Core\App;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;

    /**
     * Class Mailer
     *
     * @package Core\Mailer
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Mailer
    {
        /**
         * @var PHPMailer
         */
        protected $mail;

        /**
         * @var string|bool
         */
        protected $error;

        /**
         * Mailer constructor.
         */
        public function __construct()
        {
            $this->mail = new PHPMailer(true);

            // Inicia configurações
            $this->mail->SMTPDebug = config('mail.debug');
            $this->mail->CharSet = config('mail.charset');
            $this->mail->isSMTP();
            $this->mail->setLanguage('pt_br');
            $this->mail->isHTML(true);

            // SMTP Segurança
            $this->mail->SMTPAuth = config('mail.auth');

            if (!empty(config('mail.secure'))) {
                $this->mail->SMTPSecure = config('mail.secure');
            }

            // Servidor de e-mail
            $this->mail->Host = (is_array(config('mail.host')) ? implode(';', config('mail.host')) : config('mail.host'));
            $this->mail->Port = config('mail.port');
            $this->mail->Username = config('mail.username');
            $this->mail->Password = config('mail.password');

            // Quem está enviando
            if (config('mail.from.mail')) {
                $this->mail->From = config('mail.from.mail');

                if (!empty(config('mail.from.name'))) {
                    $this->mail->FromName = config('mail.from.name');
                }
            }
        }

        /**
         * Monta e envia o e-mail
         *
         * @param string $view
         * @param array $params
         * @param callable $callback
         * @param bool $SMTPKeepAlive
         *
         * @return $this
         * @throws \Exception
         */
        public function send($view, array $params, callable $callback, $SMTPKeepAlive = false)
        {
            $message = new Message($this->mail);
            $message->body(App::getInstance()->resolve('view')->fetch("@mail.{$view}", $params));

            call_user_func_array($callback, [$message, $params]);

            // Conexão SMTP não fechará após cada email enviado, reduz a sobrecarga de SMTP
            if ($SMTPKeepAlive) {
                $this->mail->SMTPKeepAlive = true;
            }

            try {
                if (!$this->mail->send()) {
                    throw new Exception($this->mail->ErrorInfo, E_USER_ERROR);
                }

                // Limpa as propriedade do email
                $this->mail->clearAddresses();
                $this->mail->clearAllRecipients();
                $this->mail->clearAttachments();
                $this->mail->clearBCCs();
                $this->mail->clearCCs();
                $this->mail->clearCustomHeaders();
                $this->mail->clearReplyTos();
                $this->error = false;

                return $this;
            } catch (Exception $e) {
                $this->error = $e->getMessage();

                throw $e;
            }
        }

        /**
         * Verifica se existe erros
         *
         * @return bool|string
         */
        public function failed()
        {
            return $this->error;
        }
    }
}
