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
            if (isset($arrSolicitudes)) {
                foreach ($arrSolicitudes as $key => $value) {
                    $arrSolicitudes[$key]["created_at"] = date("d-m-Y", strtotime($value["created_at"]));
                }
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
                    $arrSolicitudes["FechaNacimiento"] = date("d-m-Y", strtotime($arrSolicitudes["FechaNacimiento"]));

                    foreach ($arrSolicitudes as $key => $value) {

                        if ($key === "path_declaracion_jurada" || $key === "path_titulo" || $key === "path_boleto_compra" || $key === "path_fotografia1" || $key === "path_fotografia2" || $key === "path_fotografia3" || $key === "pathEmpresaDocumento" || $key === 'pathFotoVehiculoAdmin' || $key === 'pathFotoVehiculoAdmin2') {
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
            $datosSolicitud["nombre"] = $datosSolicitud["Nombre"];
            $datosSolicitud["email"] = $datosSolicitud["CorreoElectronico"];
            // if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
            $estadoSolicitud = $objService->updateEstadoSolcitud($params);
            if ($estadoSolicitud != 0) {
                $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 6);
                $response = crearRespuestaSolicitud(200, "OK", "Se Aprobó la Documentacion Correctamente.", $estadoSolicitud);
                $objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"]);
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se ha podido aprobar la Documentacion de la solicitud.");
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
            // } else {
            //     $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            // }
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
                        if (array_key_exists('edicionPatente', $params)) {
                            $exitePatente = null;
                        } else {
                            $exitePatente = $objService->buscarPatente($params);
                        }
                        if (!isset($exitePatente)) {
                            $insertSolicitudHistorico = 0;
                            $params["id_solicitud"] = $params["solicitud"];
                            $objBaseService = new BaseService();
                            $datosSolicitud = $objService->selectSolicitudPorID($params);
                            $datosSolicitud["nombre"] = $datosSolicitud["Nombre"];
                            $datosSolicitud["email"] = $datosSolicitud["CorreoElectronico"];
                            if (isset($_FILES)) {
                                foreach ($_FILES as $key => $value) {
                                    $nombreArchivo = "solicitud_" . $params["id_solicitud"] . "-" . $key . obtenerExtensionArchivo($value['type']);

                                    $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $params["id_solicitud"]);
                                    $objFileService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                    $params[$key] = $filePathSolicitud;
                                    //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                }
                            }

                            if (array_key_exists('edicionPatente', $params)) {
                                $solicitudHistorial = $objService->selectSolicitudParaHistorico($params);
                                $params["estado"] = "EDICION_PATENTE";
                                $insertSolicitudHistorico = $objService->insertSolicitudHistorico($solicitudHistorial, $params);
                            }

                            if ($insertSolicitudHistorico !== -1) {
                                $estadoSolicitud = $objService->updateEstadoSolcitud($params);
                                if ($estadoSolicitud != 0) {
                                    // if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
                                    if (array_key_exists('edicionPatente', $params)) {
                                        $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 2);
                                        $response = crearRespuestaSolicitud(200, "OK", "Se Modifico la solicitud correctamente", $estadoSolicitud);
                                    } else {
                                        $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 2);
                                        $response = crearRespuestaSolicitud(200, "OK", "Se Aprobó la solicitud correctamente", $estadoSolicitud);
                                    }
                                    $objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"]);
                                    // } else {
                                    //     $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
                                    // }
                                } else {
                                    $response = crearRespuestaSolicitud(400, "Error", "No se pudo actualiar la solicitud");
                                }
                                $response['headers'] = ['HTTP/1.1 200 OK'];
                            } else {
                                $response = crearRespuestaSolicitud(400, "Error", "Fallo el registro del historico de modificacion");
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

    private function verificarPatente($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new SolicitudService;
            $exitePatente = $objService->buscarPatente($params);
            if (!isset($exitePatente)) {
                $response = crearRespuestaSolicitud(200, "OK", "Patente Aceptada");
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response = crearRespuestaSolicitud(400, "error", "Ya existe la patente asignada.");
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
            $datosSolicitud = $objService->selectSolicitudPorID($params);
            $datosSolicitud["nombre"] = $datosSolicitud["Nombre"];
            $datosSolicitud["email"] = $datosSolicitud["CorreoElectronico"];
            // if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {

            $estadoSolicitud = $objService->updateEstadoSolcitud($params);
            if ($estadoSolicitud != 0) {
                $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 5);
                $response = crearRespuestaSolicitud(200, "OK", "La solicitud se ha enviado para su revision correctamente.", $estadoSolicitud);
                $objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"]);
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se ha podido enviar la solicitud para su revision.");
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];

            // } else {
            //     $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            // }
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
            $datosSolicitud["nombre"] = $datosSolicitud["Nombre"];
            $datosSolicitud["email"] = $datosSolicitud["CorreoElectronico"];
            // if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
            if ($estadoSolicitud != 0) {
                $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 3);
                $response = crearRespuestaSolicitud(200, "OK", "La solicitud ha rechazado correctamente.", $estadoSolicitud);
                $objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"]);
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se ha podido rechazar la solicitud.");
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
            // } else {
            //     $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            // }
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
            $datosSolicitud["nombre"] = $datosSolicitud["Nombre"];
            $datosSolicitud["email"] = $datosSolicitud["CorreoElectronico"];
            // if ($objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"])) {
            $estadoSolicitud = $objService->updateEstadoSolcitud($params);
            if ($estadoSolicitud != 0) {
                $objService->insertOperacion($params["solicitud"], $this->getIdWapPersona(), 4);
                $response = crearRespuestaSolicitud(200, "OK", "La solicitud ha cancelado correctamente.", $estadoSolicitud);
                $objBaseService->gestionarEnvioMail($datosSolicitud, $params["estado"]);
            } else {
                $response = crearRespuestaSolicitud(400, "Error", "No se ha podido rechazar la solicitud.");
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
            // } else {
            //     $response = crearRespuestaSolicitud(400, "Error", "No se pudo enviar email");
            // }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function buscarSolicitudPorUsuario($params)
    {
        if ($this->getRequestMethod() == "POST") {
            if ($params["usuario"]) {
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
            if ($params["usuario"]) {
                $objService = new SolicitudService;
                $solicitudes = $objService->verificarSolicitudesUsuario($params);
                if (isset($solicitudes)) {
                    foreach ($solicitudes as $key => $value) {
                        $solicitudes[$key]["created_at"] = date("d-m-Y", strtotime($value["created_at"]));
                    }
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
}
