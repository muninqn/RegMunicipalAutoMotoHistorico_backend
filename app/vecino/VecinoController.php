<?php

require_once("../app/base/BaseController.php");
require_once("../app/vecino/VecinoService.php");
// require_once("SolicitudService.php");


class VecinoController extends BaseController
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
    private function buscarVecino($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new VecinoService;
            $arrDatosVecino = $objService->obtenerDatosVecino($params);
            if (isset($arrDatosVecino)) {
                $response = crearRespuestaSolicitud(200, "OK", "Se recuperaron datos vecino", $arrDatosVecino);
            } else {
                $response = crearRespuestaSolicitud(200, "OK", "No hay datos vecino", $arrDatosVecino);
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
    private function verificarImagenVecino($params)
    {
        if ($this->getRequestMethod() == "POST") {
            // $params["wap_persona"] = $this->getIdWapPersona();
            $objService = new VecinoService;
            $arrDatosVecino = $objService->obtenerDniGeneroVecino($params);
            if (isset($arrDatosVecino)) {
                $img = verificarImagenRennaper($arrDatosVecino['Documento'],$arrDatosVecino['Genero']);
                if ($img !== FALSE) {
                    $response = crearRespuestaSolicitud(200, "OK", "Tiene imagen", "SI");
                }else{
                    $response = crearRespuestaSolicitud(200, "OK", "No tiene imagen", NULL);
                }
            } else {
                $response = crearRespuestaSolicitud(200, "OK", "No hay datos vecino", $arrDatosVecino);
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
}
