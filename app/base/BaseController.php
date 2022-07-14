<?php

require_once("../app/base/BaseService.php");

class BaseController
{

    private $requestMethod;
    private $id_tramite;
    private $id_wap_persona;
    private $id_usuario;
    private $perfil;

    public function __construct()
    {
        $this->requestMethod = null;
        $this->id_tramite = null;
        $this->id_wap_persona = null;
        $this->id_usuario = null;
        //$this->personGateway = new PersonGateway($db);
    }

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }
    public function setIdTramite($id_tramite)
    {
        $this->id_tramite = $id_tramite;
    }
    public function setIdWapPersona($id_wap_persona)
    {
        $this->id_wap_persona = $id_wap_persona;
    }
    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }
    public function setPerfilUsuario($perfil)
    {
        $this->perfil = $perfil;
    }



    public function getRequestMethod()
    {
        return $this->requestMethod;
    }
    public function getIdTramite()
    {
        return $this->id_tramite;
    }
    public function getIdWapPersona()
    {
        return $this->id_wap_persona;
    }
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }
    public function getPerfilUsuario()
    {
        return $this->perfil;
    }

    protected function obtenerDatosTabla($idTramite, $nombreTabla)
    {
        $objService = new BaseService;
        return $objService->obtenerDatosTabla($idTramite, $nombreTabla);
    }
    protected function obtenerTokenRenaper($nombreTabla)
    {
        $objService = new BaseService;
        return $objService->obtenerTokenRenaper($nombreTabla);
    }

    protected function actualizarPathArchivoTabla($idTramite, $idActivo, $nombreTabla,  $nombreCampo, $pathArchivo)
    {
        $objService = new BaseService;
        return $objService->actualizarPathArchivoTabla($idTramite, $idActivo, $nombreTabla,  $nombreCampo, $pathArchivo);
    }

    protected function obtenerIdPasoActivo($idTramite, $nombreTabla)
    {
        $objService = new BaseService;
        return $objService->obtenerIdPasoActivo($idTramite, $nombreTabla);
    }

    protected function cambiarEstadoPaso($idTramite, $nombreTabla, $nuevoEstado)
    {
        $objService = new BaseService;
        return $objService->cambiarEstadoPaso($idTramite, $nombreTabla, $nuevoEstado);
    }

    protected function cambiarEstadoTramite($nuevoEstado)
    {
        $dbConn = new BaseDatos;
        $dbConn->connect();
        if (is_array($nuevoEstado)) {
            $this->setIdTramite($nuevoEstado["idTramite"]);
            $nuevoEstado = $nuevoEstado["estado"];
        }
        $sqlQuery = "SELECT id_estado FROM licencia_tramite WHERE id_tramite=? AND deleted_at IS NULL";

        $sqlStatement = odbc_prepare($dbConn->getConn(), $sqlQuery);
        $bindParams = [$this->getIdTramite()];
        $pudoBindear = odbc_execute($sqlStatement, $bindParams);

        if ($pudoBindear) {
            $arrayResponse = odbc_fetch_array($sqlStatement);
        }


        switch ($nuevoEstado) {
            case 'autorizacion':
                # code...

                if ($arrayResponse["id_estado"] > 3) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 3;
                }
                break;
            case 'apto_medico':
                # code...
                if ($arrayResponse["id_estado"] > 4) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 4;
                }
                break;
            case 'cursos':
                # code...
                $idEstado = 5;
                if ($arrayResponse["id_estado"] > 5) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 6;
                }
                break;
            case 'cenat':
                # code...

                if ($arrayResponse["id_estado"] > 6) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 6;
                }
                break;
            case 'tasamunicipal':
                # code...

                if ($arrayResponse["id_estado"] > 7) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 7;
                }
                break;
            case 'turno':
                # code...

                if ($arrayResponse["id_estado"] > 8) {
                    $idEstado = $arrayResponse["id_estado"];
                } else {
                    $idEstado = 8;
                }
                break;
            case 'completado':
                # code...
                $idEstado = 9;
                break;
            case 'aceptado':
                # code...
                $idEstado = 10;
                break;
            case 'rechazado':
                # code...
                $idEstado = 11;
                break;
            default:
                # code...
                $idEstado = 1;
                break;
        }


        $sqlQuery = "UPDATE licencia_tramite SET id_estado = ?, modified_at = CURRENT_TIMESTAMP WHERE id_tramite=?";

        $sqlStatement = odbc_prepare($dbConn->getConn(), $sqlQuery);
        $bindParams = [$idEstado, $this->getIdTramite()];
        $pudoBindear = odbc_execute($sqlStatement, $bindParams);

        if ($pudoBindear) {

            $response['body'] = [
                "code" => 200,
                "status" => "OK",
                // "message" => "El estado del paso ha cambiado a " . $nuevoEstado
            ];

            $response['headers'] = ['HTTP/1.1 200 OK'];
        } else {
            $response['headers'] = ['HTTP/1.1 500 Internal Server Error'];
            $response['body'] = [
                "code" => 500,
                "status" => "error",
                "message" => "Ocurrio un error al ejecutar la consulta."
            ];
        }
        return $response;
    }

    protected function cancelarPasoTramite($idTramite, $nombreTabla)
    {
        $objService = new BaseService;
        return $objService->cancelarPasoTramite($idTramite, $nombreTabla);
    }
}
