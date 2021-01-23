<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Helper;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Class Logger.
 *
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
     * Logger constructor.
     *
     * @param string $name
     * @param string $directory
     */
    public function __construct(string $name, string $directory)
    {
        parent::__construct($name);

        $this->filename = 'default';
        $this->directory = $directory;

        $this->initProcessor();
        $this->initHandler();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->processors = [];
        $this->handlers = [];
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }

        return sprintf('%s/%s', $this->directory, $this->filename);
    }

    /**
     * @param string $filename
     *
     * @return \Core\Logger
     */
    public function filename(string $filename = null): Logger
    {
        if (empty($filename)) {
            return $this;
        }

        $new = clone $this;
        $new->filename = (string)$filename;

        $new->initProcessor();
        $new->initHandler();

        return $new;
    }

    /**
     * @param string      $webhookUrl             Slack Webhook URL
     * @param string|null $channel                Slack channel (encoded ID or name)
     * @param string|null $username               Name of a bot
     * @param bool        $useAttachment          Whether the message should be added to Slack as attachment (plain text otherwise)
     * @param string|null $iconEmoji              The emoji name to use (or null)
     * @param bool        $useShortAttachment     Whether the the context/extra messages added to Slack as attachments are in a short style
     * @param bool        $includeContextAndExtra Whether the attachment should include context and extra data
     */
    public function setSlackWebHookHandler(
        string $webhookUrl,
        ?string $channel = null,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = null,
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false
    ) {
        $this->pushHandler(new SlackWebhookHandler(
            $webhookUrl,
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContextAndExtra
        ));
    }

    /**
     * @return \Core\Logger
     */
    public function setHtmlFormatter(): Logger
    {
        $path = sprintf('%s/%s.html', $this->getDirectory(), date('Ymd'));
        $streamHandle = new StreamHandler($path);
        $streamHandle->setFormatter(new HtmlFormatter('d/m/Y H:i:s'));
        $this->pushHandler($streamHandle);

        return $this;
    }

    /**
     * @return void
     */
    protected function initProcessor(): void
    {
        $this->pushProcessor(new MemoryUsageProcessor());

        $this->pushProcessor(function ($record) {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $record['extra']['ip'] = $ip = Helper::getIpAddress();
                $record['extra']['hostname'] = gethostbyaddr($ip);
            }

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $record['extra']['useragent'] = Helper::getUserAgent();
            }

            return $record;
        });
    }

    /**
     * @return void
     */
    protected function initHandler(): void
    {
        $path = sprintf('%s/%s.log', $this->getDirectory(), date('Ymd'));
        $streamHandle = new StreamHandler($path);
        $separate = str_repeat('=', 150);
        $format = "{$separate}\n[%datetime%] %channel%.%level_name%: %message% \n%context% \n%extra%\n{$separate}\n\n";
        $lineFormatter = new LineFormatter($format);
        $streamHandle->setFormatter($lineFormatter);
        $this->pushHandler($streamHandle);
    }
}
