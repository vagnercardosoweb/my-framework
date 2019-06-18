<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 18/06/2019 Vagner Cardoso
 */

use Core\App;
use Core\Date;
use Core\Helpers\Helper;
use Core\Helpers\Str;
use Core\Helpers\Upload;
use Core\Router;
use Slim\Http\StatusCode;

// Constants
if (!defined('E_USER_SUCCESS')) {
    define('E_USER_SUCCESS', 'success');
}

// FUNCTIONS

if (!function_exists('validate_params')) {
    /**
     * @param array|object $params
     * @param array        $rules
     */
    function validate_params($params, array $rules)
    {
        if (is_object($params)) {
            $params = \Core\Helpers\Obj::toArray($params);
        }

        // Percorre os parâmetros
        foreach ($rules as $index => $rule) {
            // Força checagem
            if (!empty($rule['force'])) {
                if (!array_key_exists($index, $params)) {
                    $params[$index] = null;
                }
            }

            // Verifica caso esteja preenchido
            if (!empty($params[$index]) && is_array($params[$index])) {
                $params[$index] = array_filter($params[$index]);
            }

            if (array_key_exists($index, $params) && (empty($params[$index]) && '0' != $params[$index])) {
                if (array_key_exists('force', (array)$rule) && false == $rule['force']) {
                    continue;
                }

                throw new \InvalidArgumentException(
                    (!empty($rule['message']) ? $rule['message'] : (is_string($rule) ? $rule : 'undefined')),
                    (!empty($rule['code']) ? $rule['code'] : E_USER_NOTICE)
                );
            }
        }
    }
}

if (!function_exists('json_trigger')) {
    /**
     * @param string     $message
     * @param string|int $type
     * @param array      $params
     * @param int        $status
     *
     * @return \Slim\Http\Response
     */
    function json_trigger($message, $type = 'success', array $params = [], $status = StatusCode::HTTP_OK)
    {
        return json(array_merge([
            'trigger' => [error_type($type), $message],
        ], $params), $status);
    }
}

if (!function_exists('json_error')) {
    /**
     * @param \Exception $exception
     * @param array      $params
     * @param int        $status
     *
     * @return \Slim\Http\Response
     */
    function json_error($exception, array $params = [], $status = StatusCode::HTTP_BAD_REQUEST)
    {
        return json(array_merge([
            'error' => [
                'code' => $exception->getCode(),
                'status' => $status,
                'type' => error_type($exception->getCode()),
                'file' => str_replace([APP_FOLDER, PUBLIC_FOLDER, RESOURCE_FOLDER], '', $exception->getFile()),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ],
        ], $params), $status);
    }
}

if (!function_exists('json_success')) {
    /**
     * @param string $message
     * @param array  $params
     * @param int    $status
     *
     * @return \Slim\Http\Response
     */
    function json_success($message, array $params = [], $status = StatusCode::HTTP_OK)
    {
        // Caso seja web
        if (in_web()) {
            // Caso a mensagem seja vázia
            // envia apenas os parametros e status
            if (empty($message)) {
                return json($params, $status);
            }

            return json_trigger($message, (!empty($params['messageType']) ? $params['messageType'] : 'success'), $params, $status);
        }

        // Filtra os parametros caso seja da web
        $params = array_filter($params, function ($param) {
            if (!in_array($param, [
                'storage',
                'object',
                'clear',
                'trigger',
                'switch',
                'location',
                'reload',
                'messageType',
            ])) {
                return $param;
            }
        }, ARRAY_FILTER_USE_KEY);

        return json(array_merge([
            'error' => false,
            'message' => $message,
        ], $params), $status);
    }
}

if (!function_exists('error_type')) {
    /**
     * @param string|int $type
     *
     * @return string
     */
    function error_type($type)
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

if (!function_exists('get_month')) {
    /**
     * Retorna o mes do ano em pt-BR.
     *
     * @param string $month
     *
     * @return string
     */
    function get_month($month)
    {
        $months = [
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro',
        ];

        if (array_key_exists($month, $months)) {
            return $months[$month];
        }

        return '';
    }
}

if (!function_exists('get_day')) {
    /**
     * Retorna o dia da semana pt-BR.
     *
     * @param string $day
     *
     * @return string
     */
    function get_day($day)
    {
        $days = [
            '0' => 'Domingo',
            '1' => 'Segunda Feira',
            '2' => 'Terça Feira',
            '3' => 'Quarta Feira',
            '4' => 'Quinta Feira',
            '5' => 'Sexta Feira',
            '6' => 'Sábado',
        ];

        if (array_key_exists($day, $days)) {
            return $days[$day];
        }

        return '';
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
            if (($value['size'] > $maxFilesize = Upload::getMaxFilesize()) || 1 == $value['error']) {
                throw new \Exception(
                    'Opsss, seu upload ultrapassou o limite de tamanho de <b>'.Helper::convertBytesForHuman($maxFilesize).'</b>.',
                    E_USER_ERROR
                );
            }

            // Cria pasta
            if (!file_exists(PUBLIC_FOLDER.$directory)) {
                mkdir(PUBLIC_FOLDER.$directory, 0755, true);
            }

            // Verifica arquivo
            foreach ($extensions as $ext) {
                $deleted = str_replace(".{$extension}", ".{$ext}", $path);

                if (file_exists(PUBLIC_FOLDER."{$deleted}")) {
                    unlink(PUBLIC_FOLDER."{$deleted}");
                }
            }

            // Corrige orientação da imagem
            // Normalmente quando é enviada pelo celular
            if ('jpg' == $extension && in_array($value['type'], ['image/jpeg', 'image/jpg'])) {
                upload_fix_orientation($value['tmp_name'], $extension);
            }

            // Verifica se é arquivo ou imagem para upload
            $uploadError = upload_error($value['error']);

            if (in_array($extension, $extFiles) || 'gif' === $extension) {
                if (!move_uploaded_file($value['tmp_name'], PUBLIC_FOLDER.$path)) {
                    throw new \Exception("<p>Não foi possível enviar seu arquivo no momento!</p><p>{$uploadError}</p>", E_USER_ERROR);
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

                if (!$fnImg($value['tmp_name'], PUBLIC_FOLDER.$path, $width, $height, 90)) {
                    throw new \Exception("<p>Não foi possível enviar sua imagem no momento!</p><p>{$uploadError}</p>", E_USER_ERROR);
                }
            }

            $uploads[] = [
                'name' => $name,
                'path' => $path,
                'extension' => $extension,
                'size' => $value['size'],
                'md5' => md5_file(PUBLIC_FOLDER.$path),
            ];
        }

        return $uploads;
    }
}

if (!function_exists('upload_fix_orientation')) {
    /**
     * Corrige orientação da imagem.
     *
     * @param string $pathImage [Caminho do arquivo ou file enviado pelo formulário]
     * @param string $extension
     */
    function upload_fix_orientation($pathImage, $extension)
    {
        if (file_exists($pathImage) && function_exists('exif_read_data')) {
            // Variáveis
            $exifData = exif_read_data($pathImage);
            $originalImage = null;
            $rotateImage = null;

            // Verifica se existe a orientação na imagem
            if (!empty($exifData['Orientation'])) {
                // Verifica a orientação e ajusta a rotação
                switch ($exifData['Orientation']) {
                    case 3:
                        $rotation = 180;
                        break;

                    case 6:
                        $rotation = -90;
                        break;

                    case 8:
                        $rotation = 90;
                        break;

                    default:
                        $rotation = null;
                }

                // Caso a rotação e extenção seja válida
                if (null !== $rotation && null !== $extension) {
                    // Cria a imagem original dependendo da extenção
                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                            $originalImage = imagecreatefromjpeg($pathImage);
                            break;

                        case 'png':
                            $originalImage = imagecreatefrompng($pathImage);
                            imagealphablending($originalImage, false);
                            imagesavealpha($originalImage, true);
                            break;

                        case 'gif':
                            $originalImage = imagecreatefromgif($pathImage);
                            break;
                    }

                    // Rotaciona a imagem corretamente
                    $rotateImage = imagerotate($originalImage, $rotation, 0);

                    // Cria a imagem
                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($rotateImage, $pathImage, 100);
                            break;

                        case 'png':
                            imagepng($rotateImage, $pathImage, 80);
                            break;

                        case 'gif':
                            imagegif($rotateImage, $pathImage);
                            break;
                    }

                    // Destroi as imagens
                    imagedestroy($originalImage);
                    imagedestroy($rotateImage);
                }
            }
        }
    }
}

if (!function_exists('upload_error')) {
    /**
     * Recupera o tipo do erro do upload.
     *
     * @param int $code
     *
     * @return string
     */
    function upload_error($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'O arquivo enviado excede o limite definido na diretiva `upload_max_filesize` do php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'O arquivo excede o limite definido em `MAX_FILE_SIZE` no formulário HTML.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'O upload do arquivo foi feito parcialmente.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'Nenhum arquivo foi enviado.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Pasta temporária ausênte.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Falha em escrever o arquivo em disco.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'Uma extensão do PHP interrompeu o upload do arquivo.';
                break;

            default:
                $message = '';
                break;
        }

        return $message;
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

if (!function_exists('delete_recursive_directory')) {
    /**
     * Remove os arquivos e os diretórios do path passado.
     *
     * @param string $path
     */
    function delete_recursive_directory($path)
    {
        if (file_exists($path)) {
            $interator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            $interator->rewind();

            while ($interator->valid()) {
                if (!$interator->isDot()) {
                    if ($interator->isFile()) {
                        unlink($interator->getPathname());
                    } else {
                        rmdir($interator->getPathname());
                    }
                }

                $interator->next();
            }

            rmdir($path);
        }
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

if (!function_exists('preg_replace_space')) {
    /**
     * Remove tags e espaços vázios.
     *
     * @param string $string
     *
     * @return string
     */
    function preg_replace_space($string)
    {
        // Remove comentários
        $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);

        // Remove espaço com mais de um espaço
        $string = preg_replace('/\r\n|\r|\n|\t/m', '', $string);
        $string = preg_replace('/^\s+|\s+$|\s+(?=\s)/m', '', $string);

        // Adiciona espaço apos o . (dot)
        $string = preg_replace('/(?<=\.)(?=[a-zA-Z])/m', ' ', $string);

        // Remove tag `p` vázia
        return preg_replace('/<p[^>]*>[\s\s|&nbsp;]*<\/p>/m', '', $string);

        // Remove todas tags vázia
        //$string = preg_replace('/<[\w]*[^>]*>[\s\s|&nbsp;]*<\/[\w]*>/m', '', $string);
    }
}

if (!function_exists('database_format_float')) {
    /**
     * @param string|int|float $value
     *
     * @return mixed
     */
    function database_format_float($value)
    {
        if (false !== strpos($value, ',')) {
            $value = str_replace(',', '.', str_replace('.', '', $value));
        }

        return (float)$value;
    }
}

if (!function_exists('database_format_datetime')) {
    /**
     * @param string|null $dateTime
     * @param string      $type
     *
     * @return string
     */
    function database_format_datetime($dateTime = 'now', $type = 'full')
    {
        $dateFormat = 'Y-m-d';
        $timeFormat = 'H:i:s';
        $dateTimeFormat = "{$dateFormat} {$timeFormat}";

        return (new Date($dateTime))->format(
            ('time' == $type ? $timeFormat : ('date' == $type ? $dateFormat : $dateTimeFormat))
        );
    }
}

if (!function_exists('in_web')) {
    /**
     * Verifica se está no site.
     *
     * return bool
     */
    function in_web()
    {
        /** @var \Slim\Http\Request $request */
        $request = App::getInstance()->resolve('request');

        if (!empty($request->getHeaderLine('X-Csrf-Token')) || !empty(request_params('_csrfToken'))) {
            return true;
        }

        if ((empty($_SERVER['HTTP_REFERER']) && empty($_SERVER['HTTP_ORIGIN'])) && Router::hasCurrent('/api/')) {
            return false;
        }

        return true;
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
