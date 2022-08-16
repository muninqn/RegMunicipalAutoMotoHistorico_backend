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

                    foreach ($arrSolicitudes as $key => $value) {

                        if ($key === "path_declaracion_jurada" || $key === "path_titulo" || $key === "path_boleto_compra" || $key === "path_fotografia1" || $key === "path_fotografia2" || $key === "path_fotografia3" || $key === "pathEmpresaDocumento" || $key === 'pathFotoVehiculoAdmin') {
                            if ($value !== null) {

                                $base64File = obtenerArchivo($value);
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

    private function aprobarDocumentacion($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $objBaseService = new BaseService;
            $params["id_solicitud"] = $params["solicitud"];
            $datosSolicitud = $objService->selectSolicitudPorID($params);
            if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                if ($estadoSolicitud != 0) {
                    $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Solicitud Cancelada");
                    $response = crearRespuestaSolicitud(200, "OK", "La solicitud ha cancelado correctamente.", $estadoSolicitud);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No se ha podido rechazar la solicitud.");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function aprobarSolicitud($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objFileService = new FilesService;
            $tamaño = $objFileService->validarSizeArchivos($_FILES);
            $extension = $objFileService->validarExtensionArchivos($_FILES);
            if (isset($tamaño)) {
                if (isset($extension)) {
                    $objService = new SolicitudService;

                    if (isset($params["patente"])) {
                        $exitePatente = $objService->buscarPatente($params);
                        if (!isset($exitePatente)) {
                            $insertSolicitudHistorico = 0;
                            $params["id_solicitud"] = $params["solicitud"];
                            if (array_key_exists('edicionPatente', $params)) {
                                $solicitudHistorial = $objService->selectSolicitudParaHistorico($params);
                                $solicitudHistorial["accion"] = "EDICION_PATENTE";
                                $insertSolicitudHistorico = $objService->insertSolicitudHistorico($solicitudHistorial);
                                $params["estado"] = "EDICION_PATENTE";
                            }
                            $objBaseService = new BaseService();
                            $datosSolicitud = $objService->selectSolicitudPorID($params);

                            $params["unaPath"] = null;
                            if (isset($_FILES)) {
                                foreach ($_FILES as $key => $value) {
                                    $nombreArchivo = "solicitud_" . $params["id_solicitud"] . "-" . $key . obtenerExtensionArchivo($value['type']);

                                    $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $params["id_solicitud"]);
                                    $objFileService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                    $params["unaPath"] = $filePathSolicitud;

                                    //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                }
                            }
                            if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                                if ($insertSolicitudHistorico !== -1) {
                                    $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                                    if ($estadoSolicitud != 0) {
                                        if (array_key_exists('edicionPatente', $params)) {
                                            $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Se modifico la Patente.");
                                            $response = crearRespuestaSolicitud(200, "OK", "Se Modifico la solicitud correctamente", $estadoSolicitud);
                                        } else {
                                            $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Solicitud aprobada");
                                            $response = crearRespuestaSolicitud(200, "OK", "Se Aprobó la solicitud correctamente", $estadoSolicitud);
                                        }
                                    } else {
                                        $response = crearRespuestaSolicitud(400, "Error", "No se pudo actualiar la solicitud");
                                    }
                                    $response['headers'] = ['HTTP/1.1 200 OK'];
                                } else {
                                    $response = crearRespuestaSolicitud(400, "Error", "Fallo el registro del historico de modificacion");
                                }
                            } else {
                                $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
                            }
                        } else {
                            $response = crearRespuestaSolicitud(400, "error", "Ya existe la patente asignada.");
                        }
                    } else {
                        $response = crearRespuestaSolicitud(400, "error", "Debe indicar una patente.");
                    }
                } else {
                    $response = crearRespuestaSolicitud(400, "error", "El archivo tiene una extencion valida.");
                }
            } else {
                $response = crearRespuestaSolicitud(400, "error", "El archivo supera el tamaño permitido.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function revisarSolicitud($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $objBaseService = new BaseService;
            $params["id_solicitud"] = $params["solicitud"];
            $solicitudHistorial = $objService->selectSolicitudParaHistorico($params);
            $solicitudHistorial["accion"] = "ENVIO_OBSERVACION";
            $insertSolicitudHistorico = $objService->insertSolicitudHistorico($solicitudHistorial);
            $datosSolicitud = $objService->selectSolicitudPorID($params);
            if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                if ($insertSolicitudHistorico !== -1) {
                    $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                    if ($estadoSolicitud != 0) {
                        $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Solicitud enviada para correccion");
                        $response = crearRespuestaSolicitud(200, "OK", "La solicitud se ha enviado para su revision correctamente.", $estadoSolicitud);
                    } else {
                        $response = crearRespuestaSolicitud(400, "Error", "No se ha podido enviar la solicitud para su revision.");
                    }
                    $response['headers'] = ['HTTP/1.1 200 OK'];
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "Fallo el registro del historico de modificacion");
                }
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function rechazarSolicitud($params)
    {

        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $objBaseService = new BaseService;
            $params["id_solicitud"] = $params["solicitud"];
            $estadoSolicitud = $objService->updateEstadoSolcitud($params);
            $datosSolicitud = $objService->selectSolicitudPorID($params);
            if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                if ($estadoSolicitud != 0) {
                    $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Solicitud Rechazada");
                    $response = crearRespuestaSolicitud(200, "OK", "La solicitud ha rechazado correctamente.", $estadoSolicitud);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No se ha podido rechazar la solicitud.");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    private function cancelarSolicitud($params)
    {

        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $objBaseService = new BaseService;
            $params["id_solicitud"] = $params["solicitud"];
            $datosSolicitud = $objService->selectSolicitudPorID($params);
            if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                if ($estadoSolicitud != 0) {
                    $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), "Solicitud Cancelada");
                    $response = crearRespuestaSolicitud(200, "OK", "La solicitud ha cancelado correctamente.", $estadoSolicitud);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No se ha podido rechazar la solicitud.");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    private function buscarSolicitudPorUsuario($params)
    {
        if ($this->getRequestMethod() == "POST") {
            if ($params["documento"]) {
                $objService = new SolicitudService;
                $unaSolicitud = $objService->verificarSolicitudUsuario($params);
                if (isset($unaSolicitud)) {
                    $unaSolicitud["fecha_nacimiento"] = date("d-m-Y", strtotime($unaSolicitud["fecha_nacimiento"]));
                    // $arrContextOptions = array(
                    //     "ssl" => array(
                    //         "verify_peer" => false,
                    //         "verify_peer_name" => false,
                    //     ),
                    // );
                    // $response = file_get_contents($arrSolicitudes["path_declaracion_jurada"], false, stream_context_create($arrContextOptions));
                    foreach ($unaSolicitud as $key => $value) {
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
                                $unaSolicitud[$key] = $base64File;
                            }
                        }
                    }
                    $response = crearRespuestaSolicitud(200, "OK", "Existe solicitud vigente", $unaSolicitud);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No existe solicitud vigente");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "error", "Falta especificar parametros.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    private function buscarSolicitudesDelUsuario($params)
    {
        if ($this->getRequestMethod() == "POST") {
            if ($params["documento"]) {
                $objService = new SolicitudService;
                $solicitudes = $objService->verificarSolicitudesUsuario($params);
                if (isset($solicitudes)) {
                    $response = crearRespuestaSolicitud(200, "OK", "Existe solicitud vigente", $solicitudes);
                } else {
                    $response = crearRespuestaSolicitud(400, "Error", "No existe solicitud vigente");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "error", "Falta especificar parametros.");
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
