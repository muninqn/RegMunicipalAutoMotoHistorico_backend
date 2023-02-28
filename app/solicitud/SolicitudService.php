<?php
class SolicitudService
{
    public function verificarSiNumeroReciboExiste($numeroRecibo){
        $sqlQuery = "SELECT numero_recibo, id_solicitud
        FROM RMAMH_Solicitud
        WHERE numero_recibo = ?";
        //WHERE deleted_at is null
        $bindParams = [$numeroRecibo];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function insertSolicitud($params)
    {
        $estadoInicial = 1;
        $params['modelo'] = ($params['modelo'] === "null" || $params['modelo'] === "") ? null : $params['modelo'];
        $params['motor'] = ($params['motor'] === "null" || $params['motor'] === "") ? null : $params['motor'];
        $params['chasis'] = ($params['chasis'] === "null" || $params['chasis'] === "") ? null : $params['chasis'];
        $params['fecha_fabricacion'] = ($params['fecha_fabricacion'] === "null" || $params['fecha_fabricacion'] === "") ? null : $params['fecha_fabricacion'];
        $params['otros'] = ($params['otros'] === "null" || $params['otros'] === "") ? null : $params['otros'];
        $params['partes_no_originales'] = ($params['partes_no_originales'] === "null" || $params['partes_no_originales'] === "") ? null : $params['partes_no_originales'];
        $params["esEmpresa"]=($params["esEmpresa"] === 'false')?0:true;

        $sqlQuery = "INSERT INTO RMAMH_Solicitud (vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales,esEmpresa,numero_recibo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bindParams = [$params['vecino_id'], $estadoInicial, $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales'], $params["esEmpresa"],$params["numero_recibo"]];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }

    public function updateEmpresaSolicitud($params){
        $sqlQuery = "UPDATE RMAMH_Solicitud SET empresa_id=? WHERE id_solicitud=? AND deleted_at IS NULL";
        $bindParams = [$params["empresa_id"],$params["id_solicitud"]];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function updateRevisionSolicitud($params, $historial)
    {
        #= ?, modified_at = CURRENT_TIMESTAMP WHERE id_tramite=? and deleted_at IS NULL
        $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?";

        ($params['modelo'] === "null" || $params['modelo'] === "") ? $params['modelo'] = null : $params['modelo'] =$params['modelo'];
        ($params['motor'] === "null" || $params['motor'] === "") ? $params['motor'] = null : $params['motor'] =$params['motor'];
        ($params['chasis'] === "null" || $params['chasis'] === "") ? $params['chasis'] = null : $params['chasis'] =$params['chasis'];
        ($params['fecha_fabricacion'] === "null" || $params['fecha_fabricacion'] === "") ? $params['fecha_fabricacion'] = null : $params['fecha_fabricacion'] =$params['fecha_fabricacion'];
        ($params['otros'] === "null" || $params['otros'] === '') ? $params['otros'] = null : $params['otros'] = $params['otros'];
        ($params['partes_no_originales'] === "null" || $params['partes_no_originales'] === "") ? $params['partes_no_originales'] = null : $params['partes_no_originales'] =$params['partes_no_originales'];
        if ($params["esEmpresa"] === 'false') {
            $params["esEmpresa"] = 0;
        } else {
            $params["esEmpresa"] = true;
            
        }
        $estadoInicial = 1;
        $bindParams = [$estadoInicial];
        foreach ($historial as $key => $value) {
            if (array_key_exists($key, $params)) {
                if (!($key === 'estado_id') && !($key === 'empresaCuit') && !($key === 'empresaRazonSocial') && !($key === 'path_sellado')) {

                    if ($historial[$key] !== $params[$key]) {
                        $sqlQuery .= ", $key=?";
                        array_push($bindParams, $params[$key]);
                    }
                }
            }
        }
        array_push($bindParams, $params['id_solicitud']);
        $sqlQuery .= ", modified_at = CURRENT_TIMESTAMP WHERE id_solicitud=? and deleted_at IS NULL";
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function insertSolicitudHistorico($paramsAnteriores, $paramsNuevos)
    {   
        // var_dump($paramsAnteriores);
        // var_dump($paramsNuevos);
        // die;
        $response=0;
        if (array_key_exists("esEmpresa",$paramsNuevos)) {
            $paramsNuevos["esEmpresa"] = ($paramsNuevos["esEmpresa"] === 'false') ? '0' : '1';
        }
        $database = new BaseDatos;
        foreach ($paramsAnteriores as $key => $value) {
            if (array_key_exists($key, $paramsNuevos)) {

                if ($paramsNuevos[$key] === 'null' || $paramsNuevos[$key] === "") {
                    $paramsNuevos[$key] = NULL;
                }
                if ( str_contains($key,"path")|| $paramsAnteriores[$key] !== $paramsNuevos[$key]) {
                    
                    $sqlQuery = "INSERT INTO RMAMH_SolicitudHistorico (solicitud_id,campo,valor_anterior,valor_nuevo) VALUES(?,?,?,?)";

                    $bindParams = [$paramsNuevos['id_solicitud'], $key, $paramsAnteriores[$key], $paramsNuevos[$key]];
                    $database->connect();
                    $response = $database->ejecutarSqlInsert($sqlQuery, $bindParams);
                }
            }
        }
        return $response;
    }

    public function insertPathAdjuntos($params, $indice,$nombreArchivo)
    {   
        $tbAdjuntos=TB_RMAMH_Archivos;
        $sqlQuery = "INSERT INTO $tbAdjuntos (solicitud_id,nombre_archivo,estado_id) VALUES (?,?,?)";
        $bindParams = [];
        if ($indice === "path_sellado") {
            array_push($bindParams, $params["id_solicitud"]);
            array_push($bindParams, $nombreArchivo);
            array_push($bindParams, 1);
        }
        if (count($bindParams) > 0) {
            $database = new BaseDatos;
            $database->connect();
            return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
        }
    }

    public function updatePathAdjuntos($params, $indice,$nombreArchivo)
    {   
        $tbAdjuntos=TB_RMAMH_Archivos;
        $sqlQuery = "UPDATE $tbAdjuntos SET nombre_archivo=?";
        $bindParams = [];
        if ($indice === "path_sellado") {
            $sqlQuery.=" WHERE solicitud_id=? AND id_archivo=? AND deleted_at IS NULL";
            array_push($bindParams, $nombreArchivo);
            array_push($bindParams, $params["id_solicitud"]);
            array_push($bindParams, $params["id_$indice"]);
        }
        if (count($bindParams) > 0) {
            $database = new BaseDatos;
            $database->connect();
            return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
        }
    }

    public function updatePathSolcituModificacion($idSolicitud, $arrPath)
    {
        $sqlQuery = "UPDATE RMAMH_Solicitud SET";
        $bindParams = [];
        if (array_key_exists('pathEmpresaDocumento', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " pathEmpresaDocumento=?";
            } else {
                $sqlQuery .= " ,pathEmpresaDocumento=?";
            }
            array_push($bindParams, $arrPath['pathEmpresaDocumento']);
        }
        if (array_key_exists('path_declaracion_jurada', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_declaracion_jurada=?";
            } else {
                $sqlQuery .= " ,path_declaracion_jurada=?";
            }
            array_push($bindParams, $arrPath['path_declaracion_jurada']);
        }
        if (array_key_exists('path_fotografia1', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia1=?";
            } else {
                $sqlQuery .= " ,path_fotografia1=?";
            }

            array_push($bindParams, $arrPath['path_fotografia1']);
        }
        if (array_key_exists('path_titulo', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_titulo=?";
            } else {
                $sqlQuery .= " ,path_titulo=?";
            }

            array_push($bindParams, $arrPath['path_titulo']);
        }
        if (array_key_exists('path_boleto_compra', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_boleto_compra=?";
            } else {
                $sqlQuery .= " ,path_boleto_compra=?";
            }
            array_push($bindParams, $arrPath['path_boleto_compra']);
        }
        if (array_key_exists('path_fotografia2', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia2=?";
            } else {
                $sqlQuery .= " ,path_fotografia2=?";
            }
            array_push($bindParams, $arrPath['path_fotografia2']);
        }
        if (array_key_exists('path_fotografia3', $arrPath)) {
            if (count($bindParams) === 0) {
                $sqlQuery .= " path_fotografia3=?";
            } else {
                $sqlQuery .= " ,path_fotografia3=?";
            }
            array_push($bindParams, $arrPath['path_fotografia3']);
        }

        $sqlQuery .= " WHERE id_solicitud=? AND deleted_at IS NULL";
        array_push($bindParams, $idSolicitud);
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function updatePathSolcitud($idSolicitud, $arrPath)
    {
        $sqlQuery = "UPDATE RMAMH_Solicitud SET";
        $bindParams = [];
        if (array_key_exists('path_declaracion_jurada', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_declaracion_jurada=?":" path_declaracion_jurada=?";
            array_push($bindParams, $arrPath['path_declaracion_jurada']);
        }

        if (array_key_exists('path_fotografia1', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_fotografia1=?":" path_fotografia1=?";
            array_push($bindParams, $arrPath['path_fotografia1']);
        }

        if (array_key_exists('pathEmpresaDocumento', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,pathEmpresaDocumento=?":" pathEmpresaDocumento=?";
            array_push($bindParams, $arrPath['pathEmpresaDocumento']);
        }
        if (array_key_exists('path_titulo', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_titulo=?":" path_titulo=?";
            array_push($bindParams, $arrPath['path_titulo']);
        }
        if (array_key_exists('path_boleto_compra', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_boleto_compra=?":" path_boleto_compra=?";
            array_push($bindParams, $arrPath['path_boleto_compra']);
        }
        if (array_key_exists('path_fotografia2', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_fotografia2=?":" path_fotografia2=?";
            array_push($bindParams, $arrPath['path_fotografia2']);
        }
        if (array_key_exists('path_fotografia3', $arrPath)) {
            $sqlQuery .= (count($bindParams) > 0 )?" ,path_fotografia3=?":" path_fotografia3=?";
            array_push($bindParams, $arrPath['path_fotografia3']);
        }
        if (count($bindParams) > 0) {
            $sqlQuery .= " WHERE id_solicitud=? AND deleted_at IS NULL";
            array_push($bindParams, $idSolicitud);
            $database = new BaseDatos;
            $database->connect();
            return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
        }

    }

    public function updateEstadoSolcitud($params)
    {
        if ($params["estado"] === "APROBAR" || $params["estado"] === "EDICION_PATENTE") {
            $estado = 2;
            if (isset($params["pathFotoVehiculoAdmin"]) && isset($params["pathFotoVehiculoAdmin2"])) {
                $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,pathFotoVehiculoAdmin=?,pathFotoVehiculoAdmin2=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                $bindParams = [$estado, $params["patente"], $params["pathFotoVehiculoAdmin"], $params["pathFotoVehiculoAdmin2"], $params["solicitud"]];
            } else {
                if (isset($params["pathFotoVehiculoAdmin"])) {
                    $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,pathFotoVehiculoAdmin=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                    $bindParams = [$estado, $params["patente"], $params["pathFotoVehiculoAdmin"], $params["solicitud"]];
                } else {
                    if (isset($params["pathFotoVehiculoAdmin2"])) {
                        $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,pathFotoVehiculoAdmin2=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                        $bindParams = [$estado, $params["patente"], $params["pathFotoVehiculoAdmin2"], $params["solicitud"]];
                    } else {
                        $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=?,modified_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
                        $bindParams = [$estado, $params["patente"], $params["solicitud"]];
                    }
                }
            }
        }

        if ($params["estado"] === "RECHAZAR") {
            $estado = 3;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?,observacion=?,deleted_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado,$params["observacion"], $params["solicitud"]];
        }

        if ($params["estado"] === "CORREGIR") {
            $params["observacion"]=($params["observacion"] === 'null')?null:$params["observacion"];
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
        $sqlQuery =
        "SELECT id_solicitud,Nombre,Documento,estado_id,RMAMH_Solicitud.created_at
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino INNER JOIN wapPersonas ON RMAMH_Vecino.wap_persona = wapPersonas.ReferenciaID ORDER BY RMAMH_Solicitud.created_at DESC";
        //WHERE deleted_at is null
        $bindParams = [];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
    }
    public function selectSolicitudParaHistorico($params)
    {
        $tbAdjuntos=TB_RMAMH_Archivos;
        $sqlQuery = "SELECT *
        FROM RMAMH_Solicitud
        LEFT JOIN RMAMH_Empresa ON RMAMH_Empresa.id_empresa = RMAMH_Solicitud.empresa_id
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
        INNER JOIN wapPersonas ON RMAMH_Vecino.wap_persona = wapPersonas.ReferenciaID
        LEFT JOIN RMAMH_Empresa ON RMAMH_Empresa.id_empresa = RMAMH_Solicitud.empresa_id
        WHERE id_solicitud=?";
        //AND deleted_at is null
        $bindParams = [$params['id_solicitud']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }

    public function selectAdjuntosSolicitudPorID($params)
    {
        $tbAdjuntos=TB_RMAMH_Archivos;
        $sqlQuery = "SELECT *
        FROM $tbAdjuntos WHERE solicitud_id=? AND deleted_at IS NULL";
        //AND deleted_at is null
        $bindParams = [$params['id_solicitud']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
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
        $sqlQuery = "SELECT id_solicitud,Nombre,estado_id,marca,modelo,RMAMH_Solicitud.created_at
        FROM RMAMH_Vecino
        INNER JOIN RMAMH_Solicitud ON RMAMH_Solicitud.vecino_id = RMAMH_Vecino.id_vecino
        INNER JOIN wapPersonas ON RMAMH_Vecino.wap_persona = wapPersonas.ReferenciaID
        WHERE wap_persona=? ORDER BY RMAMH_Solicitud.created_at DESC";
        //AND deleted_at is null
        $bindParams = [$params['usuario']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
    }
    public function buscarPatente($params)
    {
        $sqlQuery = "SELECT patente
        FROM RMAMH_Solicitud
        WHERE patente=? AND id_solicitud <> ? AND deleted_at is null";
        $bindParams = [$params['patente'],$params["solicitud"]];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }

    public function insertOperacion($unaSolicitud, $unVecino, $unaOperacion)
    {
        $sqlQuery = "INSERT INTO RMAMH_Operacion (solicitud_id,wap_persona_op, estado_id) VALUES(?,?,?)";

        $bindParams = [$unaSolicitud, $unVecino, $unaOperacion];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
}
