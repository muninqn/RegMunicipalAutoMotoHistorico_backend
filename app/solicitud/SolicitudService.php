<?php

class SolicitudService
{
    public function getDatosBienestar()
    {
        //edad debe ser null
        $sqlQuery = "SELECT 
        delegacion.nombre,
        BienestarAnimal_PreTurno.id_preturno, 
        BienestarAnimal_PreTurno.tipoTratamiento,
        BienestarAnimal_PreTurno.referecia147,
        BienestarAnimal_PreTurno.created_at,
        BienestarAnimal_PreTurno.deleted_at ,
        BienestarAnimal_Vecino.id_vecino,
        BienestarAnimal_Vecino.ReferenciaID,
        BienestarAnimal_Vecino.documento,
        BienestarAnimal_Vecino.nombre,
        BienestarAnimal_Vecino.email,
        BienestarAnimal_Vecino.emailAlternativo,
        BienestarAnimal_Vecino.telefono,
        BienestarAnimal_Vecino.telefonoAlternativo ,
        BienestarAnimal_Vecino.ciudad
        FROM BienestarAnimal_PreTurno 
        inner join BienestarAnimal_Vecino on persona_id = id_vecino
        inner join BienestarAnimal_turnero on BienestarAnimal_PreTurno.id_turnero  = BienestarAnimal_turnero.id_turnero
        inner join delegacion on delegacion_id = id_delegacion";

        $bindParams = [];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelectListar($sqlQuery, $bindParams);
    }

    public function insertSolicitud($params)
    {
        $sqlQuery = "INSERT INTO RMAMH_Solicitud (vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        $estadoInicial = 1;
        $params['modelo'] = ($params['modelo'] === "null") ? null : $params['modelo'];
        $params['motor'] = ($params['motor'] === "null") ? null : $params['motor'];
        $params['chasis'] = ($params['chasis'] === "null") ? null : $params['chasis'];
        $params['fecha_fabricacion'] = ($params['fecha_fabricacion'] === "null") ? null : $params['fecha_fabricacion'];
        $params['otros'] = ($params['otros'] === "null") ? null : $params['otros'];
        $params['partes_no_originales'] = ($params['partes_no_originales'] === "null") ? null : $params['partes_no_originales'];

        $bindParams = [$params['vecino_id'], $estadoInicial, $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales']];

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
        $sqlQuery = "INSERT INTO RMAMH_SolicitudHistorico (solicitud_id,vecino_id, estado_id, marca, tipo, modelo, motor, chasis, fecha_fabricacion, caracteristicas_historia, otros, partes_no_originales) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

        $bindParams = [$params['id_solicitud'], $params['vecino_id'], $params['estado_id'], $params['marca'], $params['tipo'], $params['modelo'], $params['motor'], $params['chasis'], $params['fecha_fabricacion'], $params['caracteristicas_historia'], $params['otros'], $params['partes_no_originales']];

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
        if ($params["estado"] === "APROBAR") {
            $estado = 2;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, patente=? WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["patente"], $params["solicitud"]];
        }
        if ($params["estado"] === "RECHAZAR") {
            $estado = 3;

            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?,deleted_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["solicitud"]];
        }
        if ($params["estado"] === "CORREGIR") {
            $estado = 5;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?, observacion=? WHERE id_solicitud=? AND deleted_at IS NULL";
            $bindParams = [$estado, $params["observacion"], $params["solicitud"]];
        }
        if ($params["estado"] === "CANCELAR") {
            $estado = 3;
            $sqlQuery = "UPDATE RMAMH_Solicitud SET estado_id=?,deleted_at=CURRENT_TIMESTAMP WHERE id_solicitud=? AND deleted_at IS NULL";
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


    public function verificarPreturnoVecino($params)
    {
        $sqlQuery = "SELECT COUNT(*) as tienePreturno
        FROM BienestarAnimal_PreTurno
        WHERE persona_id = ? AND deleted_at is null";
        $bindParams = [$params];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function verificarPreturnoDisponibles($params)
    {
        $sqlQuery = "SELECT cantidadPreTurnos FROM BienestarAnimal_turnero WHERE BienestarAnimal_turnero.id_turnero = ?";
        $bindParams = [$params['id_turnero']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function buscarPreturnoVecino($params)
    {
        $sqlQuery = "SELECT BienestarAnimal_PreTurno.id_preturno, wapPersonas.Nombre AS vecino, wapPersonas.CorreoElectronico AS wapEmail, wapPersonas.Celular AS wapTelefono,BienestarAnimal_Vecino.nombre, 
        BienestarAnimal_Vecino.email AS vecinoEmail, BienestarAnimal_Vecino.emailAlternativo AS vecinoEmailAlternativo, BienestarAnimal_Vecino.telefono AS vecinoTelefono, BienestarAnimal_Vecino.telefonoAlternativo AS vecinoTelefonoAlternativo, 
        BienestarAnimal_PreTurno.tipoTratamiento, ST_Raza.nombre AS raza, BienestarAnimal_Mascota.sexo AS mascotaSexo, BienestarAnimal_Mascota.edad AS mascotaEdad,
        BienestarAnimal_Mascota.tamanio AS mascotaTamanio, BienestarAnimal_Mascota.peso AS mascotaPeso,BienestarAnimal_Mascota.tipo AS tipoMascota, BienestarAnimal_Mascota.enfermedad AS mascotaEnfermedad, delegacion.nombre AS delegacionNombre, BienestarAnimal_PreTurno.created_at as fecha
        FROM BienestarAnimal_PreTurno  
        LEFT JOIN BienestarAnimal_turnero ON BienestarAnimal_PreTurno.id_turnero = BienestarAnimal_turnero.id_turnero 
        LEFT JOIN BienestarAnimal_Vecino ON BienestarAnimal_PreTurno.persona_id = BienestarAnimal_Vecino.id_vecino 
        LEFT JOIN wapPersonas ON BienestarAnimal_Vecino.ReferenciaID = wapPersonas.ReferenciaID 
        LEFT JOIN BienestarAnimal_Mascota ON BienestarAnimal_PreTurno.mascota_id = BienestarAnimal_Mascota.id_mascota 
        LEFT JOIN ST_Raza ON BienestarAnimal_Mascota.raza_id = ST_Raza.id_raza 
        LEFT JOIN delegacion ON BienestarAnimal_turnero.delegacion_id = delegacion.id_delegacion 
        WHERE BienestarAnimal_PreTurno.persona_id=? AND BienestarAnimal_PreTurno.deleted_at IS NULL AND BienestarAnimal_turnero.deleted_at IS NULL";
        $bindParams = [$params];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }

    public function gestionarEnvioMail($params)
    {
        $emailBody = "<p>Estimado/a. <strong>" . $params["datosVecino"]["nombre"] . "</strong> su Pre Turno fue registrado correctamente.</p>
        <p>Personal de Bienestar Animal se contactará para confirmar día y hora que deberá llevar su animal.</p>
        <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";

        if (isset($params['datosVecino']['emailAlternativo'])) {
            $emailParams = [
                "email" => $params["datosVecino"]['emailAlternativo'],
                "asunto" => "Pre Turnos Bienestar Animal",
                "emailBody" => $emailBody,
                "attachments" => ["bienestar_animal.pdf"]
            ];
            $respuesta = $this->enviarMailTramiteCompletado($emailParams);
            if (!($respuesta == null)) {
                return $respuesta;
            }
        }
        $emailParams = [
            "email" => $params["datosVecino"]['email'],
            "asunto" => "Pre Turnos Bienestar Animal",
            "emailBody" => $emailBody,
            "attachments" => ["bienestar_animal.pdf"]
        ];
        return $this->enviarMailTramiteCompletado($emailParams);
    }

    private function enviarMailTramiteCompletado($params)
    {
        $emailResponse['error'] = null;
        if ($params != null && isset($params['email'])) {
            $url = "https://weblogin.muninqn.gov.ar/api/Mail";

            $postParams = [
                "address" => $params['email'],
                "subject" => $params['asunto'],
                "htmlBody" => $params['emailBody'],
                "attachments" => ["bienestar_animal.pdf"]
            ];
            $postHeaders = ["Content-Type: application/json"];

            //echo $postParams;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => $postHeaders,
                CURLOPT_POSTFIELDS => json_encode($postParams),
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            //print_r($response);
            $emailResponse = json_decode($response, true);
        } else {
            $emailResponse['error'] = "No se encontro el email al cual enviar la notificacion.";
        }
        return $emailResponse['error'];
    }
}
