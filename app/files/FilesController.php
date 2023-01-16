<?php

//
require_once("../app/base/BaseController.php");
require_once("../app/files/FilesService.php");
require_once("../app/vecino/VecinoService.php");
require_once("../app/solicitud/SolicitudService.php");
require_once("../app/empresa/EmpresaService.php");


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
        // var_dump($params);
        // var_dump($_FILES);
        // die;
        if ($this->getRequestMethod() == "POST") {

            $objService = new FilesService;
            $objServiceSolicitud = new SolicitudService;
            $objServiceVecino = new VecinoService;
            $empresaService= new EmpresaService;
            $objBaseService = new BaseService;
            $datosVecino = $objServiceVecino->obtenerIdVecino($params);
            if (!isset($datosVecino)) {
                // $insertVecino = $objServiceVecino->insertVecino($params);
                $params["nombre"] = $datosVecino["Nombre"];
                $params["email"] = $datosVecino["CorreoElectronico"];
            } else {
                $params["id_vecino"] = $datosVecino["id_vecino"];
                $params["nombre"] = $datosVecino["Nombre"];
                $params["email"] = $datosVecino["CorreoElectronico"];
                $insertVecino = $datosVecino["id_vecino"];
            }
            if (array_key_exists("esEdicion", $params)) {
                // $datosVecino = $objServiceVecino->obtenerIdVecino($params);
                if (isset($datosVecino)) {
                    $updateVecino = $objServiceVecino->updateVecino($params);
                    if ($updateVecino != 0) {
                        $solicitudHistorial = $objServiceSolicitud->selectSolicitudParaHistorico($params);
                        $adjuntosSolicitudHistorico = $objServiceSolicitud->selectAdjuntosSolicitudPorID($params);
                        if (count($adjuntosSolicitudHistorico) > 0) {
                            foreach ($adjuntosSolicitudHistorico as $clave => $archivo) {
                                $keyAdjunto= explode(".",explode("-",$archivo["nombre_archivo"])[1])[0];
                                $solicitudHistorial[$keyAdjunto]=$archivo["nombre_archivo"];
                            }
                        }
                        // if (isset($insertSolicitudHistorico)) {
                            $archivos=false;
                            $sinArchivos=false;
                            if (count($_FILES) > 0) {
                                $tamaño = $objService->validarSizeArchivos($_FILES);
                                $extension = $objService->validarExtensionArchivos($_FILES);
                                if (isset($tamaño)) {
                                    if (isset($extension)) {
                                        $idSolicitud = $params['id_solicitud'];
                                        $arrPath = [];
                                        foreach ($_FILES as $key => $value) {
                                            $nombreArchivo = "solicitud_" . $idSolicitud . "-" . $key . obtenerExtensionArchivo($value['type']);
                                            //$nombreArchivo = "licencia_" . $this->getIdTramite() . "_" . $params['descripcionArchivo'];
                                            $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $idSolicitud);
                                            if (file_exists($filePathSolicitud . $nombreArchivo)) {
                                                unlink($filePathSolicitud . $nombreArchivo);
                                            }
                                            $archivos = $objService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                            $arrPath[$key] = $nombreArchivo;
                                            $params[$key] = $nombreArchivo;
                                            if ($archivos) {
                                                if ($key === "path_sellado") {
                                                    if (array_key_exists("id_$key",$params)) {
                                                        //update archivo adjundo sellado
                                                        $objServiceSolicitud->updatePathAdjuntos($params,$key,$nombreArchivo);
                                                    } else {
                                                        //insert archivo adjunto
                                                        $objServiceSolicitud->insertPathAdjuntos($params,$key,$nombreArchivo);
                                                    }
                                                    
                                                }   
                                            }else{
                                                break;
                                            }
                                            //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                        }
                                        // var_dump("se rompe?");
                                        // die;
                                        $objServiceSolicitud->updatePathSolcitud($idSolicitud, $arrPath);
                                        // var_dump("se rompe?");
                                        // die;
                                    } else {
                                        $response = crearRespuestaSolicitud(400, "error extencion no valida de algun archivo", $extension);
                                    }
                                } else {
                                    $response = crearRespuestaSolicitud(400, "error algun archivo supera el tamaño permitido", $tamaño);
                                }
                            }else{
                                $sinArchivos=true;
                            }

                            if ($sinArchivos || $archivos) {

                                $insertSolicitud = $objServiceSolicitud->updateRevisionSolicitud($params, $solicitudHistorial);
                                if ($insertSolicitud != 0) {
                                    $objServiceSolicitud->insertSolicitudHistorico($solicitudHistorial,$params);
                                    $insertSolicitud = $objServiceSolicitud->insertOperacion($params['id_solicitud'], $params["wap_persona"],1);
                                    if ($params["esEmpresa"] !== 'false') {
                                        $buscarEmpresa= $empresaService->verificarExisteCuitEmpresa($params["empresaCuit"]);
                                        if (isset($buscarEmpresa)) {
                                            $params["empresa_id"]=$buscarEmpresa["id_empresa"];
                                            //update solicitud con id empresa
                                            $empresaService->updateEmpresa($params);
                                            $objServiceSolicitud->updateEmpresaSolicitud($params);
                                        }else{
                                            $params["empresa_id"]=$empresaService->insertEmpresaNueva($params);
                                            $objServiceSolicitud->updateEmpresaSolicitud($params);
                                        }
                                    }
                                    $response = crearRespuestaSolicitud(200, "OK", "Solicitud Subida correctamente.");
                                    $objBaseService->gestionarEnvioMail($params,"ENVIO_CORRECCION");
                                } else {
                                    $response = crearRespuestaSolicitud(400, "Error", "No se ha podido enviar la solicitud.");
                                }
                                $response['headers'] = ['HTTP/1.1 200 OK'];
                            }else{
                                $response=crearRespuestaSolicitud(400,"Error","No se ha pudo subir archivos");
                            }
                    } else {
                        $response = crearRespuestaSolicitud(400, "error", "Fallo actualizacion de usuario");
                    }
                } else {
                    $response = crearRespuestaSolicitud(400, "error", "El usuario no existe");
                }
            } else {
                $tamaño = $objService->validarSizeArchivos($_FILES);
                $extension = $objService->validarExtensionArchivos($_FILES);
                if (isset($tamaño)) {
                    if (isset($extension)) {
                        if (!isset($datosVecino)) {
                            $insertVecino = $objServiceVecino->insertVecino($params);
                        } else {
                            $updateVecino = $objServiceVecino->updateVecino($params);
                        }
                        if ($insertVecino != -1) {
                            $params['vecino_id'] = $insertVecino;
                            
                            $insertSolicitud = $objServiceSolicitud->insertSolicitud($params);

                            if ($insertSolicitud != -1) {
                                $idSolicitud = $insertSolicitud;
                                $params["id_solicitud"]=$idSolicitud;
                                $objServiceSolicitud->insertOperacion($idSolicitud, $params["wap_persona"], 1);
                    
                                if ($params["esEmpresa"] !== 'false') {
                                    $buscarEmpresa= $empresaService->verificarExisteCuitEmpresa($params["empresaCuit"]);
                                    if (isset($buscarEmpresa)) {
                                        $params["empresa_id"]=$buscarEmpresa["id_empresa"];
                                        //update solicitud con id empresa
                                        $objServiceSolicitud->updateEmpresaSolicitud($params);
                                    }else{
                                        $params["empresa_id"]=$empresaService->insertEmpresaNueva($params);
                                        $objServiceSolicitud->updateEmpresaSolicitud($params);
                                    }
                                }

                                $arrPath = [];
                                foreach ($_FILES as $key => $value) {
                                    $nombreArchivo = "solicitud_" . $idSolicitud . "-" . $key . obtenerExtensionArchivo($value['type']);
                                    //$nombreArchivo = "licencia_" . $this->getIdTramite() . "_" . $params['descripcionArchivo'];
                                    $filePathSolicitud = getDireccionArchivoAdjunto("RMAMH", $nombreArchivo, $idSolicitud);
                                    $objService->subirArchivoServidor($value['tmp_name'], $value['type'], $value['size'], $filePathSolicitud);
                                    if ($key === "path_sellado") {
                                        $objServiceSolicitud->insertPathAdjuntos($params,$key,$nombreArchivo);
                                    }else{
                                        $arrPath[$key] = $nombreArchivo;
                                    }
                                    //Actualizar path de archivos en solicitud por cada archivo armar array de paths y update todo de una
                                }

                                $data = $objServiceSolicitud->updatePathSolcitud($idSolicitud, $arrPath);
                                // if ($params["esEmpresa"] !== 'false') {
                                //     $empresaService->updatePathEmpresa($params["empresa_id"], $arrPath["pathEmpresaDocumento"]);
                                // }
                                $response = crearRespuestaSolicitud(200, "OK", "Solicitud Subida", $data);
                                $objBaseService->gestionarEnvioMail($params,"ENVIO");

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
