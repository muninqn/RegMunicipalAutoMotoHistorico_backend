<?php
class BaseDatos
{

    private $conn_string;
    private $user;
    private $pass;
    private $conn;
    private $charset;

    public $db;
    public $msj_error;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->db = DB_NAME;
        $this->charset = DB_CHARSET;
    }

    public function connect()
    {
        // $this->conn_string = 'DRIVER={SQL Server};SERVER=' . $this->host . ';PORT=1433;DATABASE=' . $this->db . ';charset=' . $this->charset;
        $this->conn_string = 'DRIVER={SQL Server};SERVER=' . $this->host . ';DATABASE=' . $this->db . ';charset=' . $this->charset;
        $this->conn = odbc_connect($this->conn_string, $this->user, $this->pass, SQL_CUR_USE_ODBC);
        if (!$this->conn) {
            exit("<strong>Ya ocurrido un error tratando de conectarse con el origen de datos.</strong> " . $this->conn);
        } else {
            $this->setConn($this->conn);
            // echo "Todo Correcto";
        }
    }

    public function setConn($conn)
    {
        $this->conn = $conn;
    }

    public function getConn()
    {
        return $this->conn;
    }

    //Obtener parametros para updates
    public function getParams($input)
    {
        $filterParams = [];
        foreach ($input as $param => $value) {
            $filterParams[] = "$param=:$param";
        }
        return implode(", ", $filterParams);
    }

    //Asociar todos los parametros a un sql
    public function bindAllValues($statement, $params)
    {
        foreach ($params as $param => $value) {
            $op = "=";
            if (isset($value)) {
                $statement .= " AND " . $param . $op . $value;
            }
        }
        return $statement;
    }

    private function getStatement($sqlQuery)
    {
        return odbc_prepare($this->getConn(), $sqlQuery);
    }

    private function executeQuery($statement, $bindParams = null)
    {
        //var_dump($statement);
        //print_r($bindParams);
        return odbc_execute($statement, $bindParams);
    }

    private function getSelectData($statement)
    {
        return odbc_fetch_array($statement);
    }

    private function getLastIdInsert()
    {
        $lastId = -1;
        if ($queryExecution = odbc_exec($this->getConn(), "SELECT @@IDENTITY AS ID")) {
            odbc_fetch_into($queryExecution, $row);
            $lastId = $row[0];
        }
        return $lastId;
    }

    private function getAffectedRows($statement)
    {
        return odbc_num_rows($statement);
    }

    public function ejecutarSqlSelectListar($sqlQuery, $bindParams = [])
    {
        $response = null;
        if ($statement = $this->getStatement($sqlQuery)) {
            if ($pudoEjecutar = $this->executeQuery($statement, $bindParams)) {
                $response = [];
                while($unaTupla = $this->getSelectData($statement)){
                    array_push($response, $unaTupla);
                }
            }
        }
        return $response;
    }

    public function ejecutarSqlSelect($sqlQuery, $bindParams = [])
    {
        $response = null;
        if ($statement = $this->getStatement($sqlQuery)) {
            if ($pudoEjecutar = $this->executeQuery($statement, $bindParams)) {
                if ($datosObtenidos = $this->getSelectData($statement)) {
                    $response = $datosObtenidos;
                }
            }
        }
        return $response;
    }

    public function ejecutarSqlInsert($sqlQuery, $bindParams = null)
    {
        $response = -1;
        if ($statement = $this->getStatement($sqlQuery)) {
            if ($pudoEjecutar = $this->executeQuery($statement, deutf8ize($bindParams))) {
                $response = $this->getLastIdInsert();
            }
        }
        return $response;
    }

    public function ejecutarSqlUpdateDelete($sqlQuery, $bindParams)
    {
        $response = 0;
        if ($statement = $this->getStatement($sqlQuery)) {
            if ($pudoEjecutar = $this->executeQuery($statement, $bindParams)) {
                if (($affectedRows = $this->getAffectedRows($statement)) > 0) {
                    $response = $affectedRows;
                }
            }
        }
        return $response;
    }
}
