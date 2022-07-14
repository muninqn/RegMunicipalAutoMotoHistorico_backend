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
}
