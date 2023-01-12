<?php

class VecinoService
{
    public function insertVecino($params)
    {
        $fechaNacimiento = formatearFechaNacimiento($params['fechaNacimiento']);
        $params['urlFoto']=($params['urlFoto']==="null")?null:$params['urlFoto'];
        $params['telefonoAlternativo']=($params['telefonoAlternativo']==="null")?null:$params['telefonoAlternativo'];
        $params['emailAlternativo']=($params['emailAlternativo']==="null")?null:$params['emailAlternativo'];
        $sqlQuery = "INSERT INTO RMAMH_Vecino (wap_persona, nombre, apellido, tipo_documento, documento, fecha_nacimiento, email, telefono, url_foto, codigo_postal, provincia, ciudad, domicilio,telefonoAlternativo,emailAlternativo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bindParams = [$params['wap_persona'], $params['nombre'], $params['apellido'], $params['tipoDocumento'], $params['documento'], $fechaNacimiento, $params['email'], $params['telefono'], $params['urlFoto'], $params['codigoPostal'], $params['provincia'], $params['ciudad'], $params['domicilio'],$params['telefonoAlternativo'],$params['emailAlternativo']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
    public function updateVecino($params)
    {
        $params['telefonoAlternativo']=($params['telefonoAlternativo']==="null")?null:$params['telefonoAlternativo'];
        $params['emailAlternativo']=($params['emailAlternativo']==="null")?null:$params['emailAlternativo'];
        $sqlQuery = "UPDATE RMAMH_Vecino SET telefonoAlternativo=?,emailAlternativo=? WHERE id_vecino=?";
        $bindParams = [$params['telefonoAlternativo'],$params['emailAlternativo'],$params['id_vecino']];

        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function obtenerIdVecino($params){
        $sqlQuery = "SELECT id_vecino,Nombre,CorreoElectronico,emailAlternativo FROM RMAMH_Vecino 
        INNER JOIN wapPersonas ON  wap_persona = ReferenciaID
        WHERE wap_persona=?";
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
}
