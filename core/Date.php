<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 02/06/19 Vagner Cardoso
 */

namespace App\Core {

    use DateTimeZone;

    /**
     * Class Date
     *
     * @package App\Core
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Date extends \DateTime
    {
        const DATE_BR = 'd/m/Y';

        const DATE_TIME_BR = 'd/m/Y H:i:s';

        const DATE_DATABASE = 'Y-m-d H:i:s';

        /***
         * @param string|int $time
         * @param \DateTimeZone|null $timezone
         */
        public function __construct($time = 'now', DateTimeZone $timezone = null)
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
         * @param int $timestamp
         * @param \DateTimeZone|null $timezone
         *
         * @return \App\Core\Date
         */
        public static function createFromTimestamp(int $timestamp, DateTimeZone $timezone = null)
        {
            try {
                $time = new self('now', $timezone);

                return $time->setTimestamp((int) $timestamp);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    $e->getMessage(), $e->getCode(), $e->getPrevious()
                );
            }
        }
    }
}
