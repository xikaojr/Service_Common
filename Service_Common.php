    public static function removeArquivosThumbs($arquivoOriginal, $pasta)
    {
        $thumbs = self::getConfigIni('thumbs');

        $exp = explode('.', $arquivoOriginal);
        $nomeArquivo = current($exp);
        $extensao = end($exp);

        $caminhoPastaThumbs = PUBLIC_PATH . "/arquivos/{$pasta}/thumbs/";

        if (isset($thumbs[$pasta]) && is_array($thumbs[$pasta])) {
            foreach ($thumbs[$pasta] as $tamanhoThumb) {
                $thumb = "{$caminhoPastaThumbs}{$nomeArquivo}.{$tamanhoThumb}.{$extensao}";
                $file = "{$caminhoPastaThumbs}{$nomeArquivo}.{$extensao}";

                if (is_file($thumb)) {
                    unlink($thumb);
                    unlink($file);
                }
            }
        }
    }

    public static function getUploadMaxFileSIze()
    {
        return (int) ini_get('upload_max_filesize') * 1024 * 1024;
    }

    public static function verificaExtensaoImagem($FILES, $indice)
    {
        $ext = false;
        if (isset($FILES, $FILES[$indice]) && !empty($FILES) && $FILES[$indice]['error'] == 0) {
            $fileExp = explode('.', $FILES[$indice]['name']);
            $ext = strtolower(end($fileExp));
            if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                throw new Exception('Tipo de arquivo não permitido, você só pode enviar arquivos do tipo: .gif, .jpg, .png');
            }
        }
        return $ext;
    }

    public static function uploadMultipleArquivo(array $FILES, $arrayKey = null, $caminho = null, $arrayPermExt = array(), $keepName = false)
    {
        $arrayFilesName = array();
        if (empty($arrayPermExt)) {
            $arrayPermExt = array('jpg', 'jpeg', 'png');
        }

        $dir = PUBLIC_PATH . "/{$caminho}/";

        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        if (!empty($FILES) && isset($FILES[$arrayKey]) && !empty($FILES[$arrayKey])) {
            $fileName = 1;
            foreach ($FILES[$arrayKey]['name'] as $key => $f) {
                if ($keepName) {
                    $fileName = substr($FILES[$arrayKey]['name'][$key], 0, strlen($FILES[$arrayKey]['name'][$key]) - 4);
                }

                if ($FILES[$arrayKey]['error'][$key] == 0) {
                    $ext = @strtolower(end(explode('.', $FILES[$arrayKey]['name'][$key])));
                    $index = '';
                    $t = 2;
                    while (file_exists("{$dir}{$fileName}{$index}.{$ext}")) {
                        if ($keepName) {
                            $index = $index == '' ? '_1' : '_' . $t++;
                        } else {
                            $fileName += 1;
                        }
                    }

                    $fileName .= $index;

                    try {
                        if (in_array($ext, $arrayPermExt)) {
                            $temp_file_name = "{$fileName}.{$ext}";

                            @unlink("{$dir}{$temp_file_name}");
                            copy($FILES[$arrayKey]['tmp_name'][$key], "{$dir}{$temp_file_name}");
                        } else {
                            throw new Exception("Tipo de arquivo não permitido!");
                        }
                    } catch (Exception $e) {
                        throw new Exception("Erro ao realizar upload da imagem! [{$e->getMessage()}]");
                    }
                }
            }
        }
        return $arrayFilesName;
    }

    public static function uploadArquivo(array $FILES, $arrayKey, $regId, $caminho)
    {
        if ($FILES['size'] > self::getUploadMaxFileSIze()) {
            throw new Exception("Tamanho da imagem é maior do que o permitido pelo servidor!");
        }

        if ($FILES) {
            if ($FILES[$arrayKey]['error'] == 0) {
                $arrayPermExt = array('jpg', 'jpeg', 'png');
                $fileExp = explode('.', $FILES[$arrayKey]['name']);
                $ext = strtolower(end($fileExp));

                if (in_array($ext, $arrayPermExt)) {
                    try {
                        $dir = PUBLIC_PATH . "/{$caminho}/";
                        if (!is_dir($dir))
                            mkdir($dir, 0777, true);
                        $arquivoFinal = "{$regId}.{$ext}";
                        @unlink("{$dir}{$arquivoFinal}");
                        copy($FILES[$arrayKey]['tmp_name'], "{$dir}{$arquivoFinal}");
                        return true;
                    } catch (Exception $e) {
                        throw $e;
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }