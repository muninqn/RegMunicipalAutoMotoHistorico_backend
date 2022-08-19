<?php



class FilesService
{

    public function validarSizeArchivos($arrArchivos)
    {
        $response = array();
        foreach ($arrArchivos as $key => $value) {
            ($value['type'] == "application/pdf" ? $maxSizeMB = 5 : $maxSizeMB = 8);
            $maxSizeBytes = $maxSizeMB * 1000 * 1000;

            // print_r($value);
            if ($value['size'] >= $maxSizeBytes) {
                array_push($response, ["error" => "ERROR TAMAÑO SUPERA EL PERMITIDO" . $key]);
                // $response["error"] = "ERROR TAMAÑO SUPERA EL PERMITIDO" . $value['name'];
            }
        }

        return $response;
    }

    public function validarExtensionArchivos($arrArchivos)
    {
        $response = array();
        foreach ($arrArchivos as $key => $value) {
            if (!($value['type'] === "application/pdf" || $value['type'] === "image/jpg" || $value['type'] === "image/jpeg" || $value['type'] === "image/png")) {
                array_push($response, ["error" => "ERROR, EL TIPO DE ARCHIVO NO ESTA PERMITIDO" . $key]);
            }
        }

        return $response;
    }
    public function subirArchivoServidor($tempFile, $fileType, $fileSize, $destinationFilepath)
    {
        if ($fileSize < 1000000) {
            //el peso del archivo no es problema
            $response = copy($tempFile, $destinationFilepath);
        } else {
            //el archivo pesa demasiado, hay que comprimirlo jaja
            if (str_contains($fileType, "image/")) {
                $response = $this->comprimirYSubirArchivoImagen($tempFile, $fileType, $destinationFilepath);
            } elseif (str_contains($fileType, "application/pdf")) {
                //$response = $this->comprimirArchivoPDF($tempFile, $fileType, $destinationFilepath);
                $response = copy($tempFile, $destinationFilepath);
            } else {
                $response = false;
            }
        }
        return $response;
    }
    private function comprimirYSubirArchivoImagen($tempFile, $fileType, $destinationFilepath)
    {
        if ($fileType == 'image/jpg' || $fileType == 'image/jpeg') {
            $image = imagecreatefromjpeg($tempFile);
        } elseif ($fileType == 'image/png') {
            $image = imagecreatefrompng($tempFile);
        } elseif ($fileType == 'image/bmp') {
            $image = imagecreatefrombmp($tempFile);
        }

        return imagejpeg($image, $destinationFilepath, 55);
    }
}
