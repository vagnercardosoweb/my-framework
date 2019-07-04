<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Helper.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Helper
{
    /**
     * @return bool
     */
    public static function isMobile(): bool
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isPhpCli(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    /**
     * @return string
     */
    public static function getIpAddress(): string
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $realIp = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $realIp = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $realIp = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $realIp = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $realIp = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $realIp = getenv('REMOTE_ADDR');
        } else {
            $realIp = $_SERVER['REMOTE_ADDR'];
        }

        if (false !== mb_strpos($realIp, ',')) {
            $ip = explode(',', $realIp);

            $realIp = $ip[0];
        }

        return $realIp;
    }

    /**
     * @return array
     */
    public static function getUserAgent(): array
    {
        $httUserAgent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $httUserAgent, $matched)) {
            $browser = 'IE';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Opera/([0-9].[0-9]{1,2})|', $httUserAgent, $matched)) {
            $browser = 'Opera';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Firefox/([0-9\.]+)|', $httUserAgent, $matched)) {
            $browser = 'Firefox';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Chrome/([0-9\.]+)|', $httUserAgent, $matched)) {
            $browser = 'Chrome';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Safari/([0-9\.]+)|', $httUserAgent, $matched)) {
            $browser = 'Safari';
            $browserVersion = $matched[1];
        } else {
            $browser = 'Outro';
            $browserVersion = 0;
        }

        if (preg_match('|Mac|', $httUserAgent, $matched)) {
            $so = 'MAC';
        } elseif (preg_match('|Windows|', $httUserAgent, $matched) || preg_match('|WinNT|', $httUserAgent, $matched) || preg_match('|Win95|', $httUserAgent, $matched)) {
            $so = 'Windows';
        } elseif (preg_match('|Linux|', $httUserAgent, $matched)) {
            $so = 'Linux';
        } else {
            $so = 'Outro';
        }

        return [
            'so' => $so,
            'browser' => $browser,
            'version' => $browserVersion,
            'user_agent' => $httUserAgent,
        ];
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public static function base64Encode(string $data): string
    {
        return str_replace(
            '=', '', strtr(
                base64_encode($data), '+/', '-_'
            )
        );
    }

    /**
     * @param string    $data
     * @param bool|null $strict
     *
     * @return bool|string
     */
    public static function base64Decode(string $data, ?bool $strict = null)
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(
            strtr($data, '-_', '+/'), $strict
        );
    }

    /**
     * @param array  $array
     * @param string $prefix
     *
     * @return string
     */
    public static function httpBuildQuery(array $array, $prefix = null): string
    {
        $build = [];

        foreach ($array as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if ($prefix && $key && !is_int($key)) {
                $key = "{$prefix}[{$key}]";
            } elseif ($prefix) {
                $key = "{$prefix}[]";
            }

            if (is_array($value)) {
                $build[] = self::httpBuildQuery($value, $key);
            } else {
                $build[] = $key.'='.urlencode($value);
            }
        }

        return implode('&', $build);
    }

    /**
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function convertBytesForHuman($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $base = floor(log($bytes) / log(1024));
        $base = min($base, count($units) - 1);
        $bytes = $bytes / pow(1000, $base);

        return number_format(
                round($bytes, $precision), 2, ',', ''
            ).' '.$units[$base];
    }

    /**
     * @param object       $object
     * @param string|array $methods
     *
     * @return bool
     */
    public static function objectMethodExists($object, $methods): bool
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            if (!empty($method) && method_exists($object, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|int|float $value
     *
     * @return mixed
     */
    public static function formatFloat($value)
    {
        if (false !== strpos($value, ',')) {
            $value = str_replace(',', '.', str_replace('.', '', $value));
        }

        return (float)$value;
    }

    /**
     * @param string|array $encodedString
     * @param array|string $result
     */
    public static function parseStr($encodedString, &$result): void
    {
        if (!empty($encodedString)) {
            if (is_string($encodedString)) {
                if (function_exists('mb_parse_str')) {
                    mb_parse_str($encodedString, $result);
                } else {
                    parse_str($encodedString, $result);
                }
            } else {
                $result = $encodedString;
            }

            foreach ($result as $key => $value) {
                $result[$key] = filter_var($value, FILTER_DEFAULT);
            }
        }
    }
}
