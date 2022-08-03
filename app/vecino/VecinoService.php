<?php

class VecinoService
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

    public function insertVecino($params)
    {
        $fechaNacimiento = formatearFechaNacimiento($params['fechaNacimiento']);
        $params['urlFoto']=($params['urlFoto']==="null")?null:$params['urlFoto'];
        $sqlQuery = "INSERT INTO RMAMH_Vecino (wap_persona, nombre, apellido, tipo_documento, documento, fecha_nacimiento, email, telefono, url_foto, codigo_postal, provincia, ciudad, domicilio) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bindParams = [$params['wap_persona'], $params['nombre'], $params['apellido'], $params['tipoDocumento'], $params['documento'], $fechaNacimiento, $params['email'], $params['telefono'], $params['urlFoto'], $params['codigoPostal'], $params['provincia'], $params['ciudad'], $params['domicilio']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
    public function obtenerIdVecino($params){
        $sqlQuery = "SELECT id_vecino FROM RMAMH_Vecino WHERE wap_persona=? OR documento=?";
        $bindParams = [$params['wap_persona'], $params['documento']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
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
