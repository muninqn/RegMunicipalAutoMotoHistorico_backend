<?php

require_once("../app/base/BaseController.php");
require_once("../app/empresa/EmpresaService.php");
// require_once("SolicitudService.php");


class EmpresaController extends BaseController
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
    private function buscarEmpresa($params)
    {
        if ($this->getRequestMethod() == "POST") {
            $objService = new EmpresaService;
            $arrSolicitudes = $objService->verificarExisteCuitEmpresa($params["cuit"]);
            if (!isset($arrSolicitude)) {
                $response = crearRespuestaSolicitud(200, "OK", "Se recupero la Empresa", $arrSolicitudes);
            } else {
                $response = crearRespuestaSolicitud(200, "OK", "No hay empresa", $arrSolicitudes);
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
}
