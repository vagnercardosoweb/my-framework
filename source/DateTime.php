<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Helper;

/**
 * Class DateTime.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class DateTime extends \DateTime
{
    /**
     * @param string             $time
     * @param \DateTimeZone|null $timezone
     *
     * @throws \Exception
     */
    public function __construct(string $time = 'now', \DateTimeZone $timezone = null)
    {
        parent::__construct(
            Helper::normalizeDateFormat($time),
            $timezone
        );
    }

    /**
     * @param string             $format
     * @param string             $time
     * @param \DateTimeZone|null $timezone
     *
     * @return \DateTime|false
     */
    public static function createFromFormat($format, $time, \DateTimeZone $timezone = null)
    {
        return parent::createFromFormat(
            Helper::normalizeDateFormat($format),
            Helper::normalizeDateFormat($time),
            $timezone
        );
    }

    /**
     * @param int                $timestamp
     * @param \DateTimeZone|null $timezone
     *
     * @throws \Exception
     *
     * @return \DateTime
     */
    public static function createFromTimestamp(int $timestamp, \DateTimeZone $timezone = null): \DateTime
    {
        $date = new self('now', $timezone);

        return $date->setTimestamp((int)$timestamp);
    }

    /**
     * @param string $time
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function createDateDatabase(string $time = 'now'): string
    {
        return (new self($time))->format(
            explode(' ', DATE_DATABASE)[0]
        );
    }

    /**
     * @param string $time
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function createDateTimeDatabase(string $time = 'now'): string
    {
        return (new self($time))->format(DATE_DATABASE);
    }
}
