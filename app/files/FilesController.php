<?php

//
require_once("../app/base/BaseController.php");
require_once("../app/files/FilesService.php");
require_once("../app/vecino/VecinoService.php");
require_once("../app/solicitud/SolicitudService.php");


class FilesController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function procesarRespuesta($method, $params)
    {
        try {
            if ($this->getRequestMethod() == "DELETE") {
                $response = $this->{$method}();
            } else {
                $response = $this->{$method}($params);
            }
        } catch (Error $e) {
            $response = crearRespuestaSolicitud(404, "error", $e->getMessage());
            //$response['headers'] = ['HTTP/1.1 404 Not Found'];
        }
        return $response;
    }

    private function subirSolicitud($params)
    {
        if ($this->getRequestMethod() == "POST") {
            // var_dump($_FILES);
            // var_dump($params);
            // die();
            $objService = new FilesService;
            $objServiceSolicitud = new SolicitudService;
            if (array_key_exists("esEdicion", $params)) {
                $solicitudHistorial = $objServiceSolicitud->selectSolicitudParaHistorico($params);

                $insertSolicitudHistorico = $objServiceSolicitud->insertSolicitudHistorico($solicitudHistorial);
                // var_dump($sqlQuery);

                if (isset($insertSolicitudHistorico)) {
                    if (count($_FILES) > 0) {
                        $tamaño = $objService->validarSizeArchivos($_FILES);
                        $extension = $objService->validarExtensionArchivos($_FILES);
                        if (isset($tamaño)) {
                            if (isset($extension)) {
                                $insertSolicitud = $objServiceSolicitud->updateRevisionSolicitud($params, $solicitudHistorial);
                                if (isset($insertSolicitud)) {
                                    $idSolicitud = $params['id_solicitud'];
                                    $insertSolicitud = $objServiceSolicitud->insertOperacion($idSolicitud, $params["wap_persona"], "Envio de Correccion de Solicitud");

                                    $arrPath = [];
                                    foreach ($_FILES as $key => $value) {
                                        $nombreArchivo = "solicitud_" . $idSolicitud . "-" . $key . obtenerExtensionArchivo($value['type']);
                                        //$nombreArchivo = "licencia_" . $this->getIdTramite() . "_" . $params['descripcionArchivo'];
                                        $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $idSolicitud);
                                        if (file_exists($filePathSolicitud . $nombreArchivo)) {
                                            unlink($filePathSolicitud . $nombreArchivo);
                                        }
                                        $objService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                        $arrPath[$key] = $filePathSolicitud;
                                        //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                    }
                                    $data = $objServiceSolicitud->updatePathSolcituModificacion($idSolicitud, $arrPath);
                                    $response = crearRespuestaSolicitud(200, "OK", "Solicitud Subida", $data);
                                } else {
                                    $response = crearRespuestaSolicitud(400, "error", "no se puedo registrar la solicitud");
                                }
                            } else {
                                $response = crearRespuestaSolicitud(400, "error", $extension);
                            }
                        } else {
                            $response = crearRespuestaSolicitud(400, "error", $tamaño);
                        }
                    } else {
                        // var_dump($solicitudHistorial);
                        // var_dump($params);
                        // die();
                        // $params[""]
                        $insertSolicitud = $objServiceSolicitud->updateRevisionSolicitud($params, $solicitudHistorial);
                        if ($insertSolicitud != 0) {
                            $response = crearRespuestaSolicitud(200, "OK", "Solicitud Subida correctamente.");
                        } else {
                            $response = crearRespuestaSolicitud(400, "Error", "No se ha podido enviar la solicitud.");
                        }
                        $response['headers'] = ['HTTP/1.1 200 OK'];
                        #../../../projects_files/RMAMH/
                        // $objServiceSolicitud->updatePathSolcitud($insertSolicitud, $arrPath);
                    }
                } else {
                    $response = crearRespuestaSolicitud(400, "error", "No se puedo registrar historial de solicitud");
                }
            } else {

                $tamaño = $objService->validarSizeArchivos($_FILES);
                $extension = $objService->validarExtensionArchivos($_FILES);
                if (isset($tamaño)) {
                    if (isset($extension)) {
                        $objServiceVecino = new VecinoService;
                        $insertVecino = $objServiceVecino->obtenerIdVecino($params);
                        if (!isset($insertVecino)) {
                            $insertVecino = $objServiceVecino->insertVecino($params);
                        } else {
                            $insertVecino = $insertVecino["id_vecino"];
                        }
                        if ($insertVecino != -1) {
                            $params['vecino_id'] = $insertVecino;
                            $objServiceSolicitud = new SolicitudService;
                            $insertSolicitud = $objServiceSolicitud->insertSolicitud($params);
                            if ($insertSolicitud != -1) {
                                $idSolicitud = $insertSolicitud;
                                $insertSolicitud = $objServiceSolicitud->insertOperacion($idSolicitud, $params["wap_persona"], "Envio de Solicitud");
                                $arrPath = [];
                                foreach ($_FILES as $key => $value) {
                                    $nombreArchivo = "solicitud_" . $idSolicitud . "-" . $key . obtenerExtensionArchivo($value['type']);
                                    //$nombreArchivo = "licencia_" . $this->getIdTramite() . "_" . $params['descripcionArchivo'];
                                    $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $idSolicitud);
                                    $objService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                    $arrPath[$key] = $filePathSolicitud;
                                    //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                }
                                $data = $objServiceSolicitud->updatePathSolcitud($idSolicitud, $arrPath);
                                $response = crearRespuestaSolicitud(200, "OK", "Solicitud Subida", $data);
                            } else {
                                $response = crearRespuestaSolicitud(400, "error", "no se puedo registrar la solicitud");
                            }
                        } else {
                            $response = crearRespuestaSolicitud(400, "error", "no se puedo registrar vecino");
                        }
                    } else {
                        $response = crearRespuestaSolicitud(400, "error", $extension);
                    }
                } else {

                    $response = crearRespuestaSolicitud(400, "error", $tamaño);
                }
            }
        } else {
            //No se utilizo el metodo HTTP correcto
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado");
            //$response['headers'] = ['HTTP/1.1 400 Bad Request'];
        }
        return $response;
    }
    
}
