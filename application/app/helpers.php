<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\App;
use Core\Date;
use Core\Helpers\Helper;
use Core\Helpers\Str;
use Core\Helpers\Upload;
use Slim\Http\StatusCode;

if (!function_exists('error_code_type')) {
    /**
     * @param string|int $type
     *
     * @return string
     */
    function error_code_type($type)
    {
        if (is_string($type) && E_USER_SUCCESS !== $type) {
            $type = E_USER_ERROR;
        }

        switch ($type) {
            case E_USER_NOTICE:
            case E_NOTICE:
                $result = 'info';
                break;

            case E_USER_WARNING:
            case E_WARNING:
                $result = 'warning';
                break;

            case E_USER_ERROR:
            case E_ERROR:
            case '0':
                $result = 'danger';
                break;

            case E_USER_SUCCESS:
                $result = 'success';
                break;

            default:
                $result = 'danger';
        }

        return $result;
    }
}

if (!function_exists('json')) {
    /**
     * @param mixed $data
     * @param int   $status
     * @param int   $options
     *
     * @return \Slim\Http\Response
     */
    function json($data, int $status = StatusCode::HTTP_OK, int $options = 0)
    {
        return App::getInstance()
            ->resolve('response')
            ->withJson(
                $data, $status, $options
            )
        ;
    }
}

if (!function_exists('json_error')) {
    /**
     * @param \Exception|\Throwable $exception
     * @param array                 $data
     * @param int                   $status
     *
     * @return \Slim\Http\Response
     */
    function json_error($exception, array $data = [], $status = StatusCode::HTTP_BAD_REQUEST)
    {
        return json(array_merge([
            'error' => [
                'code' => $exception->getCode(),
                'status' => $status,
                'type' => error_code_type($exception->getCode()),
                'file' => str_replace([
                    APP_FOLDER,
                    PUBLIC_FOLDER,
                    RESOURCE_FOLDER,
                ], '', $exception->getFile()),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ],
        ], $data), $status);
    }
}

if (!function_exists('json_trigger')) {
    /**
     * @param string     $message
     * @param string|int $type
     * @param array      $data
     * @param int        $status
     *
     * @return \Slim\Http\Response
     */
    function json_trigger(string $message, $type = E_USER_SUCCESS, array $data = [], int $status = StatusCode::HTTP_OK): Slim\Http\Response
    {
        return json(array_merge([
            'trigger' => [
                'type' => error_code_type($type),
                'message' => $message,
            ],
        ], $data), $status);
    }
}

if (!function_exists('json_success')) {
    /**
     * @param string $message
     * @param array  $data
     * @param int    $status
     *
     * @return \Slim\Http\Response
     */
    function json_success(?string $message = null, array $data = [], int $status = StatusCode::HTTP_OK): Slim\Http\Response
    {
        $inApi = !empty($data['api']);
        unset($data['api']);

        if (!$inApi) {
            if (empty($message)) {
                return json($data, $status);
            }

            $type = !empty($data['type']) ? $data['type'] : E_USER_SUCCESS;
            unset($data['type']);

            return json_trigger(
                nl2br($message),
                $type,
                $data,
                $status
            );
        }

        foreach ([
            'storage',
            'object',
            'clear',
            'trigger',
            'switch',
            'location',
            'reload',
        ] as $excluded) {
            if (isset($data[$excluded])) {
                unset($data[$excluded]);
            }
        }

        return json(array_merge([
            'error' => false,
            'message' => $message,
        ], $data), $status);
    }
}

if (!function_exists('get_image')) {
    /**
     * Recupera a imagem do asset.
     *
     * @param string     $table
     * @param int|string $id
     * @param string     $name
     * @param bool       $baseUrl
     * @param bool       $version
     * @param string     $extension
     *
     * @return bool|string
     */
    function get_image($table, $id, $name, $baseUrl = true, $version = true, $extension = 'jpg')
    {
        if (!empty($id) && '0' != $id) {
            $name = mb_strtoupper($name, 'UTF-8');
            $path = "/fotos/{$table}/{$id}/{$name}";

            foreach ([$extension, strtoupper($extension)] as $ext) {
                if ($asset = asset("{$path}.{$ext}", $baseUrl, $version)) {
                    return $asset;
                }
            }
        }

        return '';
    }
}

if (!function_exists('get_galeria')) {
    /**
     * Recupera a imagem do asset.
     *
     * @param string     $table
     * @param int|string $id
     * @param string     $name
     *
     * @return array
     */
    function get_galeria($table, $id, $name)
    {
        $name = mb_strtoupper($name, 'UTF-8');
        $path = ["fotos/{$table}/{$id}/galeria_{$name}", "fotos/fotos_album/{$id}"];
        $array = [];
        $images = [];

        // Imagens antigas
        if (file_exists(PUBLIC_FOLDER."/{$path[1]}")) {
            $images = array_values(array_diff(scandir(PUBLIC_FOLDER."/{$path[1]}"), ['.', '..']));
            $path = $path[1];
        } else {
            // Imagens novas
            if (file_exists(PUBLIC_FOLDER."/{$path[0]}")) {
                $images = array_values(array_diff(scandir(PUBLIC_FOLDER."/{$path[0]}/0"), ['.', '..']));
                $path = "{$path[0]}/";
            }
        }

        // Percore as imagens
        foreach ($images as $key => $image) {
            if (preg_match('/(\.jpg|\.jpeg|\.png|\.gif)/i', $image)) {
                $array[] = "/{$path}%s/{$image}";
            }
        }

        return $array;
    }
}

if (!function_exists('upload')) {
    /**
     * Upload de arquivos/images.
     *
     * @param array  $file
     * @param string $directory
     * @param string $name
     * @param int    $width
     * @param int    $height
     * @param bool   $forceJpg
     * @param bool   $whExact
     *
     * @throws \Exception
     *
     * @return array
     */
    function upload(array $file, $directory, $name = null, $width = 500, $height = 500, $forceJpg = false, $whExact = false)
    {
        $extFiles = ['zip', 'rar', 'pdf', 'docx', 'mp4'];
        $extImages = ['jpg', 'jpeg', 'png', 'gif'];
        $extensions = array_merge($extFiles, $extImages);
        $uploads = [];

        // Percore os arquivos
        foreach ($file as $key => $value) {
            $extension = mb_strtolower(substr(strrchr($value['name'], '.'), 1), 'UTF-8');
            $name = (empty($name) ? Str::slug(substr($value['name'], 0, strrpos($value['name'], '.'))) : $name);

            // Muda extenção caso seja JPEG
            if ('jpeg' == $extension || (true === $forceJpg && in_array($extension, $extImages))) {
                $extension = 'jpg';
            }

            // Path do arquivo
            $path = "{$directory}/{$name}.{$extension}";

            // Checa extension
            if (in_array($extension, $extImages)) {
                if (!in_array($extension, $extImages)) {
                    throw new \Exception(
                        'Opsss, apenas as extenções <b>'.strtoupper(implode(', ', $extImages)).'</b> são aceita para enviar sua imagem.',
                        E_USER_ERROR
                    );
                }
            } else {
                if (!in_array($extension, $extensions)) {
                    throw new \Exception(
                        'Opsss, apenas as extenções <b>'.strtoupper(implode(', ', $extensions)).'</b> são aceita para enviar seu arquivo.',
                        E_USER_ERROR
                    );
                }
            }

            // Checa tamanho
            if (($value['size'] > $maxFilesize = Upload::getPhpMaxFilesize()) || 1 == $value['error']) {
                throw new \Exception(
                    'Opsss, seu upload ultrapassou o limite de tamanho de <b>'.Helper::convertBytesForHuman($maxFilesize).'</b>.',
                    E_USER_ERROR
                );
            }

            // Cria pasta
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Verifica arquivo
            foreach ($extensions as $ext) {
                $deleted = str_replace(".{$extension}", ".{$ext}", $path);

                if (file_exists($deleted)) {
                    unlink($deleted);
                }
            }

            // Verifica se é arquivo ou imagem para upload
            $uploadError = Upload::getStringError($value['error'], false);

            if (in_array($extension, $extFiles) || 'gif' === $extension) {
                if (!move_uploaded_file($value['tmp_name'], $path)) {
                    throw new \Exception(
                        "<p>Não foi possível enviar seu arquivo no momento!</p><p>{$uploadError}</p>", E_USER_ERROR
                    );
                }
            } else {
                // Verifica se é o tamanho exato da imagem
                if (true === $whExact) {
                    $fnImg = 'imagemTamExato';
                } else {
                    $fnImg = 'imagem';

                    // Calcula o tamanho com base no original
                    list($widthOri, $heightOri) = getimagesize($value['tmp_name']);
                    $width = ($width > $widthOri ? $widthOri : $width);
                    $height = ($height > $heightOri ? $heightOri : $height);
                }

                if (!$fnImg($value['tmp_name'], $path, $width, $height, 90)) {
                    throw new \Exception(
                        "<p>Não foi possível enviar sua imagem no momento!</p><p>{$uploadError}</p>", E_USER_ERROR
                    );
                }
            }

            $uploads[] = [
                'name' => $name,
                'path' => str_replace([
                    PUBLIC_FOLDER,
                    APP_FOLDER,
                    RESOURCE_FOLDER,
                ], '', $path),
                'extension' => $extension,
                'size' => $value['size'],
                'md5' => md5_file($path),
            ];
        }

        return $uploads;
    }
}

if (!function_exists('upload_image')) {
    /**
     * Upload de imagem.
     *
     * @param array  $file
     * @param string $directory
     * @param string $name
     * @param int    $width
     * @param int    $height
     * @param bool   $forceJpg
     * @param bool   $whExact
     *
     * @throws \Exception
     *
     * @return array
     */
    function upload_image($file, $directory, $name = null, $width = 500, $height = 500, $forceJpg = false, $whExact = false)
    {
        return upload($file, $directory, $name, $width, $height, $forceJpg, $whExact);
    }
}

if (!function_exists('upload_archive')) {
    /**
     * Upload de arquivo.
     *
     * @param array  $file
     * @param string $directory
     * @param string $name
     *
     * @throws \Exception
     *
     * @return array
     */
    function upload_archive($file, $directory, $name = null)
    {
        return upload($file, $directory, $name);
    }
}

if (!function_exists('date_for_human')) {
    /**
     * @param string $dateTime
     * @param int    $precision
     *
     * @return string
     */
    function date_for_human($dateTime, $precision = 2)
    {
        if (empty($dateTime)) {
            return '-';
        }

        // Variáveis
        $minute = 60;
        $hour = 3600;
        $day = 86400;
        $week = 604800;
        $month = 2629743;
        $year = 31556926;
        $century = $year * 10;
        $decade = $century * 10;

        // Tempos
        $periods = [
            $decade => ['decada', 'decadas'],
            $century => ['seculo', 'seculos'],
            $year => ['ano', 'anos'],
            $month => ['mês', 'mêses'],
            $week => ['semana', 'semanas'],
            $day => ['dia', 'dias'],
            $hour => ['hora', 'horas'],
            $minute => ['minuto', 'minutos'],
            1 => ['segundo', 'segundos'],
        ];

        // Time atual
        $currentTime = (new Date())->getTimestamp();
        $dateTime = (new Date($dateTime))->getTimestamp();

        // Quanto tempo já passou da data atual - a data passada
        if ($dateTime > $currentTime) {
            $passed = $dateTime - $currentTime;
        } else {
            $passed = $currentTime - $dateTime;
        }

        // Monta o resultado
        if ($passed < 5) {
            $output = '5 segundos';
        } else {
            $output = [];
            $exit = 0;

            foreach ($periods as $period => $name) {
                if ($exit >= $precision || $exit > 0 && $period < 1) {
                    break;
                }

                $result = floor($passed / $period);

                if ($result > 0) {
                    $output[] = $result.' '.(1 == $result ? $name[0] : $name[1]);
                    $passed -= $result * $period;
                    $exit++;
                }
            }

            $output = implode(' e ', $output);
        }

        return $output;
    }
}

if (!function_exists('get_code_video_youtube')) {
    /**
     * @param string $url
     *
     * @return string|bool
     */
    function get_code_video_youtube(string $url)
    {
        if (strpos($url, 'youtu.be/')) {
            preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $matches);

            return $matches[4];
        }
        if (strstr($url, '/v/')) {
            $aux = explode('v/', $url);
            $aux2 = explode('?', $aux[1]);

            return $aux2[0];
        }
        if (strstr($url, 'v=')) {
            $aux = explode('v=', $url);
            $aux2 = explode('&', $aux[1]);

            return $aux2[0];
        }
        if (strstr($url, '/embed/')) {
            $aux = explode('/embed/', $url);

            return $aux[1];
        }
        if (strstr($url, 'be/')) {
            $aux = explode('be/', $url);

            return $aux[1];
        }

        return false;
    }
}

if (!function_exists('imagem')) {
    /**
     * @param $src
     * @param $dest
     * @param $maxWidth
     * @param $maxHeight
     * @param $quality
     *
     * @return bool
     */
    function imagem($src, $dest, $maxWidth, $maxHeight, $quality)
    {
        $png = false;
        if (file_exists($src) && isset($dest)) {
            // Retorna informação sobre o path do um arquivo
            $destInfo = pathinfo($dest);

            // Retorna o tamanho da imagem
            $srcSize = getimagesize($src);

            // tamanho de destino $destSize[0] = width, $destSize[1] = height
            $srcRatio = $srcSize[0] / $srcSize[1]; // width/height média
            $destRatio = $maxWidth / $maxHeight;

            if ($destRatio > $srcRatio) {
                $destSize[1] = $maxHeight;
                $destSize[0] = $maxHeight * $srcRatio;
            } else {
                $destSize[0] = $maxWidth;
                $destSize[1] = $maxWidth / $srcRatio;
            }

            // retifica o arquivo
            if ('gif' == $destInfo['extension']) {
                $dest = substr_replace($dest, 'jpg', -3);
            }

            // cria uma imagem com a extensão original
            switch ($srcSize[2]) {
                case 1: //GIF
                    $srcImage = imagecreatefromgif($src);
                    break;
                case 2: //JPEG
                    $srcImage = imagecreatefromjpeg($src);
                    break;
                case 3: //PNG
                    $srcImage = imagecreatefrompng($src);
                    imagesavealpha($srcImage, true);
                    $png = true;
                    break;
                default:
                    return false;
                    break;
            }

            // Fix rotate
            $srcImage = Upload::fixImageRotate($src, $srcImage);

            // ajusta a cor
            if (function_exists('imagecreatetruecolor')) {
                $destImage = imagecreatetruecolor($destSize[0], $destSize[1]);
            } else {
                $destImage = imagecreate($destSize[0], $destSize[1]);
            }

            if (function_exists('imageantialias')) {
                imageantialias($destImage, true);
            }

            if ($png) {
                if ('png' == substr($dest, -3)) {
                    imagealphablending($destImage, false);
                    imagesavealpha($destImage, true);
                    $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
                } else {
                    $white = imagecolorallocate($destImage, 255, 255, 255);
                    imagefilledrectangle($destImage, 0, 0, $destSize[0], $destSize[1], $white);
                }
            }

            // copia a figura redimencionando o seu tamanho
            if (function_exists('imagecopyresampled')) {
                imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $destSize[0], $destSize[1], $srcSize[0], $srcSize[1]);
            } else {
                imagecopyresized($destImage, $srcImage, 0, 0, 0, 0, $destSize[0], $destSize[1], $srcSize[0], $srcSize[1]);
            }

            if ('png' == substr($dest, -3)) {
                imagepng($destImage, $dest);
            } else {
                imagejpeg($destImage, $dest, $quality);
            }

            return true;
        }

        return false;
    }
}

if (!function_exists('imagemTamExato')) {
    /**
     * @param $imgSrc
     * @param $dest
     * @param $thumbnail_width
     * @param $thumbnail_height
     * @param $quality
     *
     * @return bool
     */
    function imagemTamExato($imgSrc, $dest, $thumbnail_width, $thumbnail_height, $quality)
    {
        if (file_exists($imgSrc) && isset($dest)) {
            $srcSize = getimagesize($imgSrc);
            $destInfo = pathinfo($dest);

            // retifica o arquivo
            if ('gif' == $destInfo['extension']) {
                $dest = substr_replace($dest, 'jpg', -3);
            }

            list($width_orig, $height_orig) = getimagesize($imgSrc);

            $png = false;

            switch ($srcSize[2]) {
                case 1: //GIF
                    $myImage = imagecreatefromgif($imgSrc);
                    break;
                case 2: //JPEG
                    $myImage = imagecreatefromjpeg($imgSrc);
                    break;
                case 3: //PNG
                    $myImage = imagecreatefrompng($imgSrc);
                    $png = true;
                    break;
                default:
                    return false;
                    break;
            }

            // Fix rotate
            Upload::fixImageRotate($imgSrc, $myImage);

            $ratio_orig = $width_orig / $height_orig;

            if (($thumbnail_width / $thumbnail_height) > $ratio_orig) {
                $new_height = $thumbnail_width / $ratio_orig;
                $new_width = $thumbnail_width;
            } else {
                $new_width = $thumbnail_height * $ratio_orig;
                $new_height = $thumbnail_height;
            }

            $x_mid = $new_width / 2;  //horizontal middle
            $y_mid = $new_height / 2; //vertical middle

            $process = imagecreatetruecolor(round($new_width), round($new_height));
            $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

            if ($png) {
                if ('png' == substr($dest, -3)) {
                    imagesavealpha($myImage, true);
                    imagealphablending($process, false);
                    imagesavealpha($process, true);
                    $transparent = imagecolorallocatealpha($process, 255, 255, 255, 127);
                    imagefilledrectangle($process, 0, 0, $new_width, $new_height, $transparent);
                    imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
                    $thumb = $process;
                } else {
                    $white = imagecolorallocate($thumb, 255, 255, 255);
                    imagefilledrectangle($thumb, 0, 0, $thumbnail_width, $thumbnail_width, $white);
                    imagecopyresampled($thumb, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
                }
            } else {
                imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
                imagecopyresampled($thumb, $process, 0, 0, ($x_mid - ($thumbnail_width / 2)), ($y_mid - ($thumbnail_height / 2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
            }

            if ('png' == substr($dest, -3)) {
                imagepng($thumb, $dest);
            } else {
                imagejpeg($thumb, $dest, $quality);
            }

            return true;
        }

        return false;
    }
}

if (!function_exists('placeholder')) {
    /**
     * @param string $uri
     *
     * @return string
     */
    function placeholder(string $uri = '500x500')
    {
        return "https://via.placeholder.com/{$uri}";
    }
}
