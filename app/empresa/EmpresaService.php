<?php

class EmpresaService
{

    public function insertEmpresaNueva($params)
    {

        $sqlQuery = "INSERT INTO RMAMH_Empresa (empresaCuit, empresaRazonSocial) VALUES(?,?)";
        $bindParams = [$params['empresaCuit'], $params['empresaRazonSocial']];
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }
    public function updateEmpresa($params)
    {
        $sqlQuery = "UPDATE RMAMH_Empresa SET empresaCuit=?, empresaRazonSocial=? WHERE id_empresa=?";
        $bindParams = [$params['empresaCuit'], $params['empresaRazonSocial'],$params["empresa_id"]];
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlUpdateDelete($sqlQuery, $bindParams);
    }
    public function updatePathEmpresa($idEmpresa,$path)
    {
        $sqlQuery = "UPDATE RMAMH_Empresa SET pathEmpresaDocumento=? WHERE id_empresa=?";
        $bindParams = [$path,$idEmpresa];
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlInsert($sqlQuery, $bindParams);
    }

    public function verificarExisteCuitEmpresa($cuit){
        $sqlQuery = "SELECT id_empresa,empresaRazonSocial FROM RMAMH_Empresa WHERE empresaCuit=? AND deleted_at IS NULL";
        $bindParams = [$cuit];
        $database = new BaseDatos;
        $database->connect();
        return $database->ejecutarSqlSelect($sqlQuery, $bindParams);
    }
}
