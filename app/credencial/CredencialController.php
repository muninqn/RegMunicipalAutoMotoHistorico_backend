<?php

require_once("../app/base/BaseController.php");
require_once("CredencialService.php");

class CredencialController extends BaseController
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
            //$response['headers'] = ['HTTP/1.1 404 Not Found'];
        }
        return $response;
    }

    private function generarUnaCredencial($params)
    {
        if ($this->getRequestMethod() == "POST") {
            if (
                isset($params['tituloCredencial']) &&
                isset($params['numeroCredencial']) &&
                isset($params['razonSocial']) &&
                isset($params['fechaNacimiento']) &&
                isset($params['fechaOtorgado']) &&
                isset($params['fechavencimiento']) &&
                isset($params['urlFoto'])) {

                $objService = new CredencialService;
                $resultSet = $objService->crearCredencial($params);
                if ($resultSet != null) {
                    $response = crearRespuestaSolicitud(200, "OK", "Credencial creada.", $resultSet);
                } else {
                    $response = crearRespuestaSolicitud(400, "error", "No se puedo crear.");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response['headers'] = ['HTTP/1.1 400 Error'];
                $response = crearRespuestaSolicitud(400, "error", "Error, Falta especificar parametros.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }

    private function generarCredencialConDorso($params)
    {
        if ($this->getRequestMethod() == "POST") {

            if (
                (
                    isset($params['datosFrente']) &&
                    isset($params['datosFrente']['tituloCredencial']) &&
                    isset($params['datosFrente']['numeroCredencial']) &&
                    isset($params['datosFrente']['razonSocial']) &&
                    isset($params['datosFrente']['fechaNacimiento']) &&
                    isset($params['datosFrente']['fechaOtorgado']) &&
                    isset($params['datosFrente']['fechavencimiento']) &&
                    isset($params['datosFrente']['urlFoto'])
                ) && 
                (
                    isset($params['datosDorso']) &&
                    isset($params['datosDorso']['numeroRecibo']) &&
                    isset($params['datosDorso']['observaciones'])
                )) {

                $objService = new CredencialService;
                $resultSet = $objService->crearCredencialDorso($params);
                if ($resultSet != null) {
                    $response = crearRespuestaSolicitud(200, "OK", "Credencial creada.", $resultSet);
                } else {
                    $response = crearRespuestaSolicitud(400, "error", "No se puedo crear.");
                }
                $response['headers'] = ['HTTP/1.1 200 OK'];
            } else {
                $response['headers'] = ['HTTP/1.1 400 Error'];
                $response = crearRespuestaSolicitud(400, "error", "Error, Falta especificar parametros.");
            }
        } else {
            $response = crearRespuestaSolicitud(400, "error", "Metodo HTTP equivocado.");
        }
        return $response;
    }
}
