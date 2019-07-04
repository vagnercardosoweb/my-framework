<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\Helpers\Arr;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;

// Constants
if (!defined('E_USER_SUCCESS')) {
    define('E_USER_SUCCESS', 'success');
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        static $variables;

        if (empty($variables)) {
            $variables = (new DotenvFactory([
                new EnvConstAdapter(),
                new PutenvAdapter(),
                new ServerConstAdapter(),
            ]))->createImmutable();
        }

        if (!$value = $variables->get($key)) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
                break;
            case 'false':
            case '(false)':
                return false;
                break;
            case 'empty':
            case '(empty)':
                return '';
                break;
            case 'null':
            case '(null)':
                return null;
                break;
        }

        return trim($value);
    }
}

if (!function_exists('asset')) {
    /**
     * @param string $file
     * @param bool   $baseUrl
     * @param bool   $version
     *
     * @return bool|string
     */
    function asset(string $file, bool $baseUrl = false, bool $version = false)
    {
        $file = ((!empty($file[0]) && '/' !== $file[0]) ? "/{$file}" : $file);
        $path = PUBLIC_FOLDER."{$file}";
        $baseUrl = ($baseUrl ? BASE_URL : '');

        if (file_exists($path)) {
            $version = ($version ? '?v='.substr(md5_file($path), 0, 15) : '');

            return "{$baseUrl}{$file}{$version}";
        }

        return false;
    }
}

if (!function_exists('asset_source')) {
    /**
     * @param string|array $files
     *
     * @return bool|string
     */
    function asset_source($files)
    {
        $contents = [];

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $file = ((!empty($file[0]) && '/' !== $file[0]) ? "/{$file}" : $file);
            $filePath = PUBLIC_FOLDER."{$file}";

            if (file_exists($filePath)) {
                $contents[] = file_get_contents(
                    $filePath
                );
            }
        }

        return implode('', $contents);
    }
}

if (!function_exists('config')) {
    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    function config(string $name = null, $default = null)
    {
        static $config = [];

        if (empty($config)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    APP_FOLDER.'/config', FilesystemIterator::SKIP_DOTS
                )
            );

            $iterator->rewind();

            while ($iterator->valid()) {
                $basename = $iterator->getBasename('.php');
                $content = include "{$iterator->getRealPath()}";
                $config[$basename] = $content;
                $iterator->next();
            }
        }

        return Arr::get(
            $config, $name, $default
        );
    }
}

if (!function_exists('__')) {
    /**
     * @param string $value
     *
     * @return string
     */
    function __($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('htmlentities_recursive')) {
    /**
     * @param mixed $values
     *
     * @return array
     */
    function htmlentities_recursive($values)
    {
        $data = [];

        foreach ((array)$values as $key => $value) {
            if (is_array($value)) {
                $data[$key] = htmlentities_recursive($value);
            } else {
                if (is_string($value)) {
                    $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }
}

if (!function_exists('filter_values')) {
    /**
     * @param mixed $values
     *
     * @return array
     */
    function filter_values($values)
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = filter_values($value);
            } else {
                if (is_int($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_INT;
                } elseif (is_float($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                } elseif (is_string($value)) {
                    $filter = FILTER_SANITIZE_STRING;
                } else {
                    $filter = FILTER_DEFAULT;
                }

                $result[$key] = addslashes(strip_tags(
                    trim(filter_var($value, $filter))
                ));
            }
        }

        return $result;
    }
}

if (!function_exists('preg_replace_space')) {
    /**
     * @param string $string
     * @param bool   $removeEmptyTagParagraph
     * @param bool   $removeAllEmptyTags
     *
     * @return string
     */
    function preg_replace_space(string $string, bool $removeEmptyTagParagraph = false, bool $removeAllEmptyTags = false): string
    {
        // Remove comments
        $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);

        // Remove space with more than one space
        $string = preg_replace('/\r\n|\r|\n|\t/m', '', $string);
        $string = preg_replace('/^\s+|\s+$|\s+(?=\s)/m', '', $string);

        // Adds space after. (dot)
        $string = preg_replace('/(?<=\.)(?=[a-zA-Z])/m', ' ', $string);

        // Remove empty tag paragraph
        if ($removeEmptyTagParagraph) {
            $string = preg_replace('/<p[^>]*>[\s\s|&nbsp;]*<\/p>/m', '', $string);
        }

        // Remove all empty tags
        if ($removeAllEmptyTags) {
            $string = preg_replace('/<[\w]*[^>]*>[\s\s|&nbsp;]*<\/[\w]*>/m', '', $string);
        }

        return $string;
    }
}

if (!function_exists('delete_recursive_directory')) {
    /**
     * @param string $path
     * @param int    $mode
     *
     * @return void
     */
    function delete_recursive_directory(string $path, int $mode = \RecursiveIteratorIterator::CHILD_FIRST): void
    {
        if (file_exists($path)) {
            $interator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                $mode
            );

            $interator->rewind();

            while ($interator->valid()) {
                if (!$interator->isDot()) {
                    if ($interator->isFile()) {
                        @unlink($interator->getPathname());
                    } else {
                        @rmdir($interator->getPathname());
                    }
                }

                $interator->next();
            }

            @rmdir($path);
        }
    }
}

if (!function_exists('get_month_string')) {
    /**
     * @param string $month
     * @param bool   $english
     *
     * @return string
     */
    function get_month_string($month, bool $english = false)
    {
        $months = [
            '01' => $english ? 'January' : 'Janeiro',
            '02' => $english ? 'February' : 'Fevereiro',
            '03' => $english ? 'March' : 'Março',
            '04' => $english ? 'April' : 'Abril',
            '05' => $english ? 'May' : 'Maio',
            '06' => $english ? 'June' : 'Junho',
            '07' => $english ? 'July' : 'Julho',
            '08' => $english ? 'August' : 'Agosto',
            '09' => $english ? 'September' : 'Setembro',
            '10' => $english ? 'October' : 'Outubro',
            '11' => $english ? 'November' : 'Novembro',
            '12' => $english ? 'December' : 'Dezembro',
        ];

        if (array_key_exists($month, $months)) {
            return $months[$month];
        }

        return '';
    }
}

if (!function_exists('get_day_string')) {
    /**
     * @param string $day
     * @param bool   $english
     *
     * @return string
     */
    function get_day_string($day, bool $english = false)
    {
        $days = [
            '0' => $english ? 'Sunday' : 'Domingo',
            '1' => $english ? 'Second Fair' : 'Segunda Feira',
            '2' => $english ? 'Tuesday' : 'Terça Feira',
            '3' => $english ? 'Wednesday Fair' : 'Quarta Feira',
            '4' => $english ? 'Thursday Fair' : 'Quinta Feira',
            '5' => $english ? 'Friday Fair' : 'Sexta Feira',
            '6' => $english ? 'Saturday' : 'Sábado',
        ];

        if (array_key_exists($day, $days)) {
            return $days[$day];
        }

        return '';
    }
}
