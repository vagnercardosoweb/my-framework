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
 * Class Upload.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Upload
{
    /**
     * @param array $files
     *
     * @return array
     */
    public static function organizeMultipleFiles(array $files)
    {
        $newFiles = [];

        foreach ($files as $key => $file) {
            if (!is_array($file['name'])) {
                $newFiles[$key] = $file;
            } else {
                $totalFiles = count($file['name']);
                $fileKeys = array_keys($file);

                for ($i = 0; $i < $totalFiles; $i++) {
                    foreach ($fileKeys as $fileKey) {
                        $newFiles[$key][$i][$fileKey] = $file[$fileKey][$i];
                    }
                }
            }
        }

        return $newFiles;
    }

    /**
     * @param string   $imagePath
     * @param resource $image
     *
     * @return false|resource
     */
    public static function fixImageRotate(string $imagePath, $image)
    {
        if (file_exists($imagePath) && function_exists('exif_read_data') && is_resource($image)) {
            $exif = @exif_read_data($imagePath);
            $orientation = !empty($exif['Orientation']) ? $exif['Orientation'] : null;

            switch ($orientation) {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;

                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;

                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
            }
        }

        return $image;
    }

    /**
     * @param int  $code
     * @param bool $english
     *
     * @return string
     */
    public static function getStringError($code, bool $english = true): ?string
    {
        $message = null;

        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = [
                    'The uploaded file exceeds the limit defined in the `upload_max_filesize` directive of php.ini',
                    'O arquivo enviado excede o limite definido na diretiva `upload_max_filesize` do php.ini',
                ];
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = [
                    'The file exceeds the limit defined in `MAX_FILE_SIZE` in the HTML form.',
                    'O arquivo excede o limite definido em `MAX_FILE_SIZE` no formulário HTML.',
                ];
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = [
                    'File upload was partially done.',
                    'O upload do arquivo foi feito parcialmente.',
                ];
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = [
                    'No files have been uploaded.',
                    'Nenhum arquivo foi enviado.',
                ];
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = [
                    'Missing temporary folder.',
                    'Pasta temporária ausênte.',
                ];
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = [
                    'Failed to write file to disk.',
                    'Falha em escrever o arquivo em disco.',
                ];
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = [
                    'An extension of PHP will stop uploading the file.',
                    'Uma extensão do PHP interrompeu o upload do arquivo.',
                ];
                break;

            default:
                return $message;
        }

        return $english
            ? $message[0]
            : $message[1];
    }

    /**
     * @return float|int
     */
    public static function getPhpMaxFilesize()
    {
        $config = ini_get('upload_max_filesize');
        $newSize = 0;

        if (preg_match('/([0-9]+)+([a-zA-Z]+)/', $config, $matches)) {
            switch ($matches[2]) {
                case 'K':
                case 'KB':
                    $newSize = ($matches[1] * pow(1024, 1));
                    break;

                case 'M':
                case 'MB':
                    $newSize = ($matches[1] * pow(1024, 2));
                    break;

                case 'G':
                case 'GB':
                    $newSize = ($matches[1] * pow(1024, 3));
                    break;

                case 'T':
                case 'TB':
                    $newSize = ($matches[1] * pow(1024, 4));
                    break;

                case 'P':
                case 'PB':
                    $newSize = ($matches[1] * pow(1024, 5));
                    break;
            }
        }

        return $newSize;
    }
}
