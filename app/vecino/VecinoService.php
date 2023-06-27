<?php

class VecinoService
{
    public function insertVecino($params)
    {
        // $params['urlFoto']=($params['urlFoto']==="null")?null:$params['urlFoto'];
        $params['telefonoAlternativo']=($params['telefonoAlternativo']==="null")?null:$params['telefonoAlternativo'];
        $params['emailAlternativo']=($params['emailAlternativo']==="null")?null:$params['emailAlternativo'];
        if (array_key_exists('fotoUsuario_id',$params)) {
            $sqlQuery = "INSERT INTO RMAMH_Vecino (wap_persona, codigo_postal, provincia, ciudad, domicilio,telefonoAlternativo,emailAlternativo,fotoUsuario_id) VALUES(?,?,?,?,?,?,?,?)";
            $bindParams = [$params['wap_persona'], $params['codigoPostal'], $params['provincia'], $params['ciudad'], $params['domicilio'],$params['telefonoAlternativo'],$params['emailAlternativo'],$params['fotoUsuario_id']];
        }else{
            $sqlQuery = "INSERT INTO RMAMH_Vecino (wap_persona, codigo_postal, provincia, ciudad, domicilio,telefonoAlternativo,emailAlternativo) VALUES(?,?,?,?,?,?,?)";
            $bindParams = [$params['wap_persona'], $params['codigoPostal'], $params['provincia'], $params['ciudad'], $params['domicilio'],$params['telefonoAlternativo'],$params['emailAlternativo']];
        }


        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
    public function updateVecino($params)
    {
        $params['telefonoAlternativo']=($params['telefonoAlternativo']==="null")?null:$params['telefonoAlternativo'];
        $params['emailAlternativo']=($params['emailAlternativo']==="null")?null:$params['emailAlternativo'];
        if (array_key_exists('fotoUsuario_id',$params)) {
            $sqlQuery = "UPDATE RMAMH_Vecino SET telefonoAlternativo=?,emailAlternativo=?,fotoUsuario_id=? WHERE id_vecino=?";
            $bindParams = [$params['telefonoAlternativo'],$params['emailAlternativo'],$params['fotoUsuario_id'],$params['id_vecino']];
        }else{
            $sqlQuery = "UPDATE RMAMH_Vecino SET telefonoAlternativo=?,emailAlternativo=? WHERE id_vecino=?";
            $bindParams = [$params['telefonoAlternativo'],$params['emailAlternativo'],$params['id_vecino']];
        }
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function obtenerIdVecino($params){
        $sqlQuery = "SELECT id_vecino,Nombre,CorreoElectronico FROM RMAMH_Vecino INNER JOIN wapPersonas ON RMAMH_Vecino.wap_persona = wapPersonas.ReferenciaID WHERE wap_persona=?";
        $bindParams = [$params['wap_persona']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function obtenerDatosVecino($params){
        $sqlQuery = "SELECT telefonoAlternativo,emailAlternativo FROM RMAMH_Vecino WHERE wap_persona=?";
        $bindParams = [$params['wap_persona']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
    public function obtenerDniGeneroVecino($params){
        $sqlQuery = "SELECT wapPersonas.Documento,wapPersonas.Genero
        FROM wapPersonas
        WHERE ReferenciaID=?";
        $bindParams = [$params['idPersona']];
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
}
