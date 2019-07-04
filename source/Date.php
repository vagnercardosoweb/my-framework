<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core;

/**
 * Class Date.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Date extends \DateTime
{
    const FORMAT_DATE_BR = 'd/m/Y';

    const FORMAT_DATE_TIME_BR = 'd/m/Y H:i:s';

    const FORMAT_DATE_DATABASE = 'Y-m-d H:i:s';

    /**
     * @param string             $time
     * @param \DateTimeZone|null $timezone
     */
    public function __construct(string $time = 'now', \DateTimeZone $timezone = null)
    {
        try {
            parent::__construct(str_replace('/', '-', $time), $timezone);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                $e->getMessage(), $e->getCode(), $e->getPrevious()
            );
        }
    }

    /**
     * @param int                $timestamp
     * @param \DateTimeZone|null $timezone
     *
     * @return \Core\Date
     */
    public static function createFromTimestamp(int $timestamp, \DateTimeZone $timezone = null): Date
    {
        try {
            $date = new self('now', $timezone);

            return $date->setTimestamp((int)$timestamp);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                $e->getMessage(), $e->getCode(), $e->getPrevious()
            );
        }
    }

    /**
     * @param string $time
     *
     * @return string
     */
    public static function formatFromDateDatabase(string $time = 'now'): string
    {
        return (new self($time))->format(
            explode(' ', self::FORMAT_DATE_DATABASE)[0]
        );
    }

    /**
     * @param string $time
     *
     * @return string
     */
    public static function formatFromDateTimeDatabase(string $time = 'now'): string
    {
        return (new self($time))->format(self::FORMAT_DATE_DATABASE);
    }
}
