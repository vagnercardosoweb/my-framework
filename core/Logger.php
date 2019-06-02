<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core {

    use Core\Helpers\Helper;
    use Monolog\Formatter\LineFormatter;
    use Monolog\Handler\StreamHandler;
    use Monolog\Logger as Monolog;

    /**
     * Class Logger
     *
     * @package Core
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Logger extends Monolog
    {
        /**
         * @var string
         */
        protected $filename;

        /**
         * @var string
         */
        protected $directory;

        /**
         * @param string $name
         * @param string $directory
         */
        public function __construct(string $name, string $directory)
        {
            parent::__construct($name);
            $this->filename = 'app';
            $this->directory = $directory;
            $this->initProcessor();
            $this->initHandler();
        }

        /**
         * @inheritdoc
         */
        protected function initProcessor()
        {
            $this->pushProcessor(function ($record) {
                $record['extra']['ip'] = $ip = Helper::getIpAddress();
                $record['extra']['hostname'] = gethostbyaddr($ip);
                $record['extra']['useragent'] = Helper::getUserAgent();

                return $record;
            });
        }

        /**
         * @inheritdoc
         */
        protected function initHandler()
        {
            try {
                // StreamHandler
                $stream = new StreamHandler($this->getDirectory(), self::DEBUG);
                $separate = str_repeat('=', 150);
                $formatter = new LineFormatter(
                    "{$separate}\n[%datetime%] %channel%.%level_name%: %message% \n%context% \n%extra%\n{$separate}\n\n"
                );
                $stream->setFormatter($formatter);
                $this->pushHandler($stream);
            } catch (\Exception $e) {
            }
        }

        /**
         * @return string
         */
        public function getDirectory(): string
        {
            if (!is_dir($this->directory)) {
                mkdir($this->directory, 0755, true);
            }

            return sprintf(
                "%s/%s-%s.log",
                $this->directory,
                $this->filename,
                date('Ymd')
            );
        }

        /**
         * @param string $filename
         *
         * @return \Core\Logger
         */
        public function filename($filename)
        {
            $new = clone $this;
            $new->filename = (string) $filename;
            $new->initProcessor();
            $new->initHandler();

            return $new;
        }

        /**
         * @inheritdoc
         */
        public function __clone()
        {
            $this->processors = [];
            $this->handlers = [];
        }
    }
}
