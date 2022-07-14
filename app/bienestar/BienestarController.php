<?php

require_once("../app/base/BaseController.php");
require_once("../app/bienestar/BienestarService.php");
require_once("BienestarService.php");


class BienestarController extends BaseController
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

    private function obtenerDatosBienestar()
    {
        if ($this->getRequestMethod() == "POST") {

            $objService = new BienestarService;
            $resultSet = $objService->getDatosBienestar();
            if ($resultSet != null) {
                $response = crearRespuestaSolicitud(200, "OK", "El registro solicitado fue encontrado.", $resultSet);
            } else {
                $response = crearRespuestaSolicitud(400, "error", "No se encontró el registro solicitado.");
            }
            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
}
