<?php

require_once("../app/base/BaseController.php");
require_once("../app/solicitud/SolicitudService.php");
// require_once("SolicitudService.php");


class SolicitudController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function procesarRespuesta($method, $params)
    {
        try {
            if ($this->getRequestMethod() == "GET" || $this->getRequestMethod() == "DELETE") {
                $response = $this->{$method}();
            } else {
                $response = $this->{$method}($params);
            }
        } catch (Error $e) {
            $response = crearRespuestaSolicitud(404, "error", $e->getMessage());
        }
        return $response;
    }
    private function obtenerSolicitudes()
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $arrSolicitudes = $objService->selectSolicitudes();
            if (!isset($arrSolicitude)) {
                $response = crearRespuestaSolicitud(200, "OK", "Se recuperaron las solicitudes", $arrSolicitudes);
            } else {
                $response = crearRespuestaSolicitud(200, "OK", "No hay solicitudes", $arrSolicitudes);
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    private function obtenerSolicitudPorID($params)
    {
        if ($this->getRequestMethod() == "POST") {
            if (isset($params['id_solicitud'])) {
                $objService = new SolicitudService;
                $arrSolicitudes = $objService->selectSolicitudPorID($params);
                if (isset($arrSolicitudes)) {
                    $arrSolicitudes["fecha_nacimiento"] = date("d-m-Y", strtotime($arrSolicitudes["fecha_nacimiento"]));
                    $arrSolicitudes["fecha_fabricacion"] = date("d-m-Y", strtotime($arrSolicitudes["fecha_fabricacion"]));
                    // $arrContextOptions = array(
                    //     "ssl" => array(
                    //         "verify_peer" => false,
                    //         "verify_peer_name" => false,
                    //     ),
                    // );
                    // $response = file_get_contents($arrSolicitudes["path_declaracion_jurada"], false, stream_context_create($arrContextOptions));
                    foreach ($arrSolicitudes as $key => $value) {
                        if ($key === "path_declaracion_jurada" || $key === "path_titulo" || $key === "path_boleto_compra" || $key === "path_fotografia1" || $key === "path_fotografia2" || $key === "path_fotografia3") {
                            if ($value !== null) {
                                $fileExtension = pathinfo($value, PATHINFO_EXTENSION);

                                // Definimos el tipo de archivo
                                if ($fileExtension == "pdf") {
                                    $fileMimeType = "application/" . $fileExtension;
                                } else {
                                    $fileMimeType = "image/" . $fileExtension;
                                }

                                // Obtenemos el archivo y lo convertimos a base64
                                $fileData = file_get_contents($value);
                                $base64File = "data:$fileMimeType;base64," . base64_encode($fileData);
                                $arrSolicitudes[$key] = $base64File;
                            }
                        }
                    }
                    $response = crearRespuestaSolicitud(200, "OK", "Se recuperaron las solicitudes", $arrSolicitudes);
                } else {
                    $response = crearRespuestaSolicitud(200, "OK", "No hay solicitudes", $arrSolicitudes);
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "error", "Falta especificar parametro id_solicitud.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function aprobarSolicitud($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $exitePatente=$objService->buscarPatente($params);
            if (!isset($exitePatente)) {
                # code...
                $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                if ($estadoSolicitud != 0) {
                    $response = crearRespuestaSolicitud(200, "OK", "Se Aprobó la solicitud correctamente", $estadoSolicitud);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No se pudo actualiar la solicitud");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            }else{
                $response = crearRespuestaSolicitud(400, "error", "Ya existe la patente asignada.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    // private function verificarPreTurnoExistente($params)
    // {
    //     if ($this->getRequestMethod() == "POST") {
    //         $objService = new PreTurnoService;
    //         $objUserService = new VecinoService;
    //         $idUserData = $objUserService->obtenerIdVecino($params['referecia'], $params['documento']);
    //         if (!isset($idUserData["id_vecino"])) {
    //             $response = crearRespuestaSolicitud(200, "OK", "Puede sacar pre-turno.");
    //         } else {
    //             $tienePreturnos = $objService->verificarPreturnoVecino($idUserData['id_vecino']);
    //             if (!isset($tienePreturnos['tienePreturno']) || $tienePreturnos['tienePreturno'] == 0) {
    //                 $response = crearRespuestaSolicitud(200, "OK", "Puede sacar pre-turno.");
    //             } else {
    //                 $resultSet = $objService->buscarPreturnoVecino($idUserData['id_vecino']);
    //                 $response = crearRespuestaSolicitud(400, "error", "Ya dispone de un pre-turno activo", $resultSet);
    //             }
    //         }
    //         $response['headers'] = ['HTTP/1.1 200 OK'];
    //     } else {
    //         $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
    //     }
    //     return $response;
    // }

}
