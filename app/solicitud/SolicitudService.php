<?php

class SolicitudService
{

    public function insertSolicitud($params)
    {
        $estadoInicial = 1;
        $params['modelo'] = ($params['modelo'] === "null") ? null : $params['modelo'];
        $params['motor'] = ($params['motor'] === "null") ? null : $params['motor'];
        $params['chasis'] = ($params['chasis'] === "null") ? null : $params['chasis'];
        $params['fecha_fabricacion'] = ($params['fecha_fabricacion'] === "null") ? null : $params['fecha_fabricacion'];
        $params['otros'] = ($params['otros'] === "null") ? null : $params['otros'];
        $params['partes_no_originales'] = ($params['partes_no_originales'] === "null") ? null : $params['partes_no_originales'];
        if ($params["esEmpresa"] === 'false') {
            $sqlQuery = "INSERT INTO RMAMH_Solicitud (vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales,esEmpresa) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
            $params["esEmpresa"] = 0;
            $bindParams = [$params['vecino_id'], $estadoInicial, $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales'], $params["esEmpresa"]];
        } else {
            $params["esEmpresa"] = true;
            $sqlQuery = "INSERT INTO RMAMH_Solicitud (vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales,esEmpresa,empresaCuit,empresaRazonSocial) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $bindParams = [$params['vecino_id'], $estadoInicial, $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales'], $params["esEmpresa"], $params['empresaCuit'], $params['empresaRazonSocial']];
        }



        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
    public function updateRevisionSolicitud($params, $historial)
    {
        #= ?, modified_at = CURRENT_TIMESTAMP WHERE id_tramite=? and deleted_at IS NULL
        $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?";

        $params['modelo'] = ($params['modelo'] === "null") ? null : $params['modelo'];
        $params['motor'] = ($params['motor'] === "null") ? null : $params['motor'];
        $params['chasis'] = ($params['chasis'] === "null") ? null : $params['chasis'];
        $params['fecha_fabricacion'] = ($params['fecha_fabricacion'] === "null") ? null : $params['fecha_fabricacion'];
        $params['otros'] = ($params['otros'] === "null") ? null : $params['otros'];
        $params['partes_no_originales'] = ($params['partes_no_originales'] === "null") ? null : $params['partes_no_originales'];
        if ($params["esEmpresa"] === 'false') {
            $params["esEmpresa"] = 0;
        } else {
            $params["esEmpresa"] = true;
        }
        $estadoInicial = 1;
        $bindParams = [$estadoInicial];
        foreach ($historial as $key => $value) {
            if (array_key_exists($key, $params)) {
                if (!($key === 'estado_id')) {

                    if ($historial[$key] !== $params[$key]) {
                        $sqlQuery .= ", $key=?";
                        array_push($bindParams, $params[$key]);
                    }
                }
            }
        }
        array_push($bindParams, $params['id_solicitud']);
        $sqlQuery .= ", modified_at = CURRENT_TIMESTAMP WHERE id_solicitud=? and deleted_at IS NULL";

        // var_dump($sqlQuery);
        // var_dump($bindParams);
        // die();
        // $sqlQuery = "INSERT INTO RMAMH_Solicitud (vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        // $bindParams = [$params['vecino_id'], $estadoInicial, $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function insertSolicitudHistorico($params)
    {
        // var_dump($params);
        // die();
        $sqlQuery = "INSERT INTO RMAMH_SolicitudHistorico (solicitud_id,vecino_id, estado_id,patente, marca, tipo, modelo, motor, chasis, fecha_fabricacion,observacion, path_declaracion_jurada,path_titulo,path_boleto_compra,path_fotografia1,path_fotografia2,path_fotografia3,caracteristicas_historia, otros, partes_no_originales,accion) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $bindParams = [$params['id_solicitud'], $params['vecino_id'], $params['estado_id'], $params['patente'], $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params["observacion"], $params["path_declaracion_jurada"], $params["path_titulo"], $params["path_boleto_compra"], $params["path_fotografia1"], $params["path_fotografia2"], $params["path_fotografia3"], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales'], $params["accion"]];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }

    public function updatePathSolcituModificacion($idSolicitud, $arrPath)
    {

        $sqlQuery = "UPDATE RMAMH_Solicitud SET";
        $bindParams = [];
        // var_dump(count($bindParams) === 0);
        // die();
        if (array_key_exists('pathEmpresaDocumento', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " pathEmpresaDocumento=?";
            } else {
                $sqlQuery .= " ,pathEmpresaDocumento=?";
            }
            array_push($bindParams, $arrPath['pathEmpresaDocumento']);
        }
        if (array_key_exists('decjurada', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_declaracion_jurada=?";
            } else {
                $sqlQuery .= " ,path_declaracion_jurada=?";
            }
            array_push($bindParams, $arrPath['decjurada']);
        }
        if (array_key_exists('foto0', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia1=?";
            } else {
                $sqlQuery .= " ,path_fotografia1=?";
            }

            array_push($bindParams, $arrPath['foto0']);
        }
        if (array_key_exists('fileTitulo', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_titulo=?";
            } else {
                $sqlQuery .= " ,path_titulo=?";
            }

            array_push($bindParams, $arrPath['fileTitulo']);
        }
        if (array_key_exists('fileBoleto', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_boleto_compra=?";
            } else {
                $sqlQuery .= " ,path_boleto_compra=?";
            }
            array_push($bindParams, $arrPath['fileBoleto']);
        }
        if (array_key_exists('foto1', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia2=?";
            } else {
                $sqlQuery .= " ,path_fotografia2=?";
            }
            array_push($bindParams, $arrPath['foto1']);
        }
        if (array_key_exists('foto2', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia3=?";
            } else {
                $sqlQuery .= " ,path_fotografia3=?";
            }
            array_push($bindParams, $arrPath['foto2']);
        }

        $sqlQuery .= " WHERE id_solicitud=? AND deleted_at IS NULL";
        array_push($bindParams, $idSolicitud);
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function updatePathSolcitud($idSolicitud, $arrPath)
    {
        $sqlQuery = "UPDATE RMAMH_Solicitud SET path_declaracion_jurada=?,path_fotografia1=?";
        $bindParams = [$arrPath['decjurada'], $arrPath['foto0']];
        if (array_key_exists('pathEmpresaDocumento', $arrPath)) {
            $sqlQuery .= " ,pathEmpresaDocumento=?";
            array_push($bindParams, $arrPath['pathEmpresaDocumento']);
        }
        if (array_key_exists('fileTitulo', $arrPath)) {
            $sqlQuery .= " ,path_titulo=?";
            array_push($bindParams, $arrPath['fileTitulo']);
        }
        if (array_key_exists('fileBoleto', $arrPath)) {
            $sqlQuery .= " ,path_boleto_compra=?";
            array_push($bindParams, $arrPath['fileBoleto']);
        }
        if (array_key_exists('foto1', $arrPath)) {
            $sqlQuery .= " ,path_fotografia2=?";
            array_push($bindParams, $arrPath['foto1']);
        }
        if (array_key_exists('foto2', $arrPath)) {
            $sqlQuery .= " ,path_fotografia3=?";
            array_push($bindParams, $arrPath['foto2']);
        }
        $sqlQuery .= " WHERE id_solicitud=? AND deleted_at IS NULL";
        array_push($bindParams, $idSolicitud);

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function updateEstadoSolcitud($params)
    {   
        if ($params["estado"] === "APROBAR" || $params["estado"] === "EDICION_PATENTE") {
            $estado = 2;
            if (isset($params["unaPath"])) {
                $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,pathFotoVehiculoAdmin=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                $bindParams = [$estado, $params["patente"], $params["unaPath"], $params["solicitud"]];
            } else {
                $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                $bindParams = [$estado, $params["patente"], $params["solicitud"]];
            }
        }
        
        if ($params["estado"] === "RECHAZAR") {
            $estado = 3;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?,deleted_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["solicitud"]];
        }

        if ($params["estado"] === "CORREGIR") {
            $estado = 5;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, observacion=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["observacion"], $params["solicitud"]];
        }

        if ($params["estado"] === "CANCELAR") {
            $estado = 4;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?,deleted_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["solicitud"]];
        }

        if ($params["estado"] === "APROBAR_DOCUMENTACION") {
            $estado = 6;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=? WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["solicitud"]];
        }
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function selectSolicitudes()
    {
        $sqlQuery = "SELECT id_solicitud,nombre,apellido,documento,estado_id
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino";
        //WHERE deleted_at is null
        $bindParams = [];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
    }
    public function selectSolicitudParaHistorico($params)
    {
        $sqlQuery = "SELECT *
        FROM RMAMH_Solicitud
        WHERE id_solicitud=?";
        //AND deleted_at is null
        $bindParams = [$params['id_solicitud']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function selectSolicitudPorID($params)
    {
        $sqlQuery = "SELECT *
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino
        WHERE id_solicitud=?";
        //AND deleted_at is null
        $bindParams = [$params['id_solicitud']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function verificarSolicitudUsuario($params)
    {
        $sqlQuery = "SELECT *
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino
        WHERE documento=?";
        // AND deleted_at is null
        $bindParams = [$params['documento']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function verificarSolicitudesUsuario($params)
    {
        $sqlQuery = "SELECT id_solicitud,nombre,apellido,estado_id
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino
        WHERE documento=?";
        //AND deleted_at is null
        $bindParams = [$params['documento']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
    }
    public function buscarPatente($params)
    {
        $sqlQuery = "SELECT patente
        FROM RMAMH_Solicitud
        WHERE patente=? AND deleted_at is null";
        $bindParams = [$params['patente']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }

    public function insertOperacion($unaSolicitud, $unVecino, $unaOperacion)
    {
        $sqlQuery = "INSERT INTO RMAMH_Operacion (solicitud_id,wap_persona_op, operacion) VALUES(?,?,?)";

        $bindParams = [$unaSolicitud, $unVecino, $unaOperacion];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }

}
