<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 01/06/19 Vagner Cardoso
 */

namespace Core\Helpers {
    /**
     * Class Upload.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Upload
    {
        public static function organizeMultipleFiles(array $files)
        {
            $newFiles = [];

            foreach ($files as $key => $file) {
                if (!is_array($file['name'])) {
                    $newFiles[$key] = $file;
                } else {
                    $totalFiles = count($file['name']);
                    $fileKeys = array_keys($file);

                    for ($i = 0; $i < $totalFiles; ++$i) {
                        foreach ($fileKeys as $fileKey) {
                            $newFiles[$key][$i][$fileKey] = $file[$fileKey][$i];
                        }
                    }
                }
            }

            return $newFiles;
        }

        /**
         * @return float|int
         */
        public static function getMaxFilesize()
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
}
