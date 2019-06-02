<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Helpers {

    /**
     * Class Obj
     *
     * @package Core\Helpers
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Obj
    {
        /**
         * @param array $array
         *
         * @return \stdClass
         */
        public static function fromArray(array $array)
        {
            $object = new \stdClass();

            if (empty($array)) {
                return $object;
            }

            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $object->{$key} = self::fromArray($value);
                } else {
                    $object->{$key} = isset($value) ? $value : null;
                }
            }

            return $object;
        }

        /**
         * @param object $object
         * @param string $name
         * @param mixed $default
         *
         * @return mixed
         */
        public static function get($object, ?string $name = null, $default = null)
        {
            if (empty($name)) {
                return $object;
            }

            foreach (explode('.', $name) as $segment) {
                if (is_object($object) || isset($object->{$segment})) {
                    $object = $object->{$segment};
                } else {
                    return $default;
                }
            }

            return $object;
        }

        /**
         * @param object $object
         *
         * @return string
         */
        public static function toJson($object): string
        {
            return json_encode(
                self::toArray($object)
            );
        }

        /**
         * @param object $object
         *
         * @return array
         */
        public static function toArray($object): array
        {
            $array = [];

            foreach ($object as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $array[$key] = self::toArray($value);
                } else {
                    if (isset($key)) {
                        $array[$key] = $value;
                    }
                }
            }

            return $array;
        }
    }
}
