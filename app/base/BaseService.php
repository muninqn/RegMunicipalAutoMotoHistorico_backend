<?php

class BaseService
{

    public function obtenerIdPasoActivo($idTramite, $nombreTabla)
    {
        $sqlQuery = "SELECT id_$nombreTabla FROM licencia_$nombreTabla WHERE id_tramite=? AND deleted_at IS NULL";
        $bindParams = [$idTramite];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }

    public function obtenerDatosTabla($idTramite, $nombreTabla)
    {
        $sqlQuery = "SELECT * FROM licencia_$nombreTabla WHERE id_tramite=? AND deleted_at IS NULL";
        $bindParams = [$idTramite];
        //echo $sqlQuery;
        //print_r($bindParams);

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function obtenerTokenRenaper($nombreTabla)
    {
        $sqlQuery = "SELECT Token FROM $nombreTabla";
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery);
    }

    public function actualizarPathArchivoTabla($idTramite, $idActivo, $nombreTabla, $nombreCampo, $pathArchivo)
    {
        $response = null;

        if ($nombreCampo != null) {
            if ($idActivo != null) {
                //existe el registro, se debe actualizar (UPDATE)
                $sqlQuery = "UPDATE licencia_$nombreTabla SET $nombreCampo = ?, modified_at = CURRENT_TIMESTAMP WHERE id_tramite=? and deleted_at IS NULL";
            } else {
                //no existe el registro, se debe crear (INSERT)
                $sqlQuery = "INSERT INTO licencia_$nombreTabla ($nombreCampo, id_tramite) VALUES (?, ?)";
            }
            $bindParams = [$pathArchivo, $idTramite];

            //echo $sqlQuery;
            //print_r($bindParams);

            $database = new BaseDatos;
            $database->connect();
            if ($idActivo != null) {
                $response = $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
                //echo "llegue1";
            } else {
                if (($response = $database->ejecutarSqlInsert($sqlQuery, $bindParams)) > 0) {
                    //echo "llegue2";
                    //echo $idTramite;
                    $response = $this->obtenerDatosTabla($idTramite, $nombreTabla);
                    //print_r($this->obtenerDatosTabla($idTramite, $nombreTabla));
                }
            }
        }
        return $response;
    }

    public function cambiarEstadoPaso($idTramite, $nombreTabla, $nuevoEstado)
    {
        $sqlQuery = "UPDATE licencia_$nombreTabla SET estado = ?, modified_at = CURRENT_TIMESTAMP WHERE id_tramite=? AND deleted_at IS NULL";
        $bindParams = [$nuevoEstado, $idTramite];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function cancelarPasoTramite($idTramite, $nombreTabla)
    {
        $sqlQuery = "UPDATE licencia_$nombreTabla SET deleted_at = CURRENT_TIMESTAMP WHERE id_tramite=? AND deleted_at IS NULL";
        $bindParams = [$idTramite];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }

    public function gestionarEnvioMail($datosVecino, $estado)
    {
        $enviarEmail = true;
        switch ($estado) {
            case 'APROBAR':
                $emailBody = "<p>Estimado/a. <strong>" . $datosVecino["nombre"] . "</strong> su Solicitud para REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO fue <strong>APROBADA</strong>.</p>
        <p>Por favor revise la aplicacion para poder ver su credencial.</p>
        <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";
                break;
            case 'EDICION_PATENTE':
                $emailBody = "<p>Estimado/a. <strong>" . $datosVecino["nombre"] . "</strong> su Solicitud para REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO fue <strong>APROBADA</strong>.</p>
        <p>Su PATENTE fue modificadada.Por favor revise la aplicacion para poder ver su credencial.</p>
        <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";
                break;
            case 'RECHAZAR':
                $emailBody = "<p>Estimado/a. <strong>" . $datosVecino["nombre"] . "</strong> su Solicitud para REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO fue <strong>RECHAZADA</strong>.</p>
                <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";
                break;
            case 'CORREGIR':
                $emailBody = "<p>Estimado/a. <strong>" . $datosVecino["nombre"] . "</strong> su Solicitud para REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO tiene <strong>OBSERVACIONES</strong> realizadas por personal de Administracion.</p>
        <p>Por favor revise la aplicacion para corregir su solicitud.</p>
        <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";
                break;
            case 'CANCELAR':
                $emailBody = "<p>Estimado/a. <strong>" . $datosVecino["nombre"] . "</strong> su Solicitud para REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO fue <strong>CANCELADA</strong>.</p>
        <p>Debera realizar el tramite nuevamente.</p>
        <p><strong>Este servicio gratuito es exclusivo para los/as vecinas/as de la ciudad de Neuquén.</strong></p>";
                break;
            default:
                $enviarEmail = false;
                break;
        }
        if ($enviarEmail) {
            if (isset($datosVecino['emailAlternativo'])) {
                $emailParams = [
                    "email" => $datosVecino['emailAlternativo'],
                    "asunto" => "REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO",
                    "emailBody" => $emailBody,
                ];
                $envio = $this->enviarMailTramiteCompletado($emailParams);
            }
            $emailParams = [
                "email" => $datosVecino['email'],
                "asunto" => "REGISTRO MUNICIPAL DE AUTOMOVILES Y MOTOCICLETAS HISTORICO",
                "emailBody" => $emailBody,
            ];
            $envio = $this->enviarMailTramiteCompletado($emailParams);
            if (isset($envio)) {
                $enviarEmail = false;
            }
        }
        return $enviarEmail;
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
                // "attachments" => ["bienestar_animal.pdf"]
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
