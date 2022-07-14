<?php
include "../app/config/config.php";
include "../app/connection/BaseDatos.php";

if (!isset($_GET['SESSIONKEY']) || !isset($_GET['userProfiles']) || $_GET['userProfiles'] != (2 || 3)) {
    header("HTTP/1.1 401 Error");
    echo json_encode($arr['error'] = "Acceso denegado");
    exit();
}

$dbConn = new BaseDatos;
$dbConn->connect();
/*
listar todos los licencia_autorizacion o solo uno
select comprobar fecha delete null
 */
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        break;
    case 'POST':
        break;
    case 'PUT':
        $this->create_contact($name);
        break;
    case 'DELETE':
        $this->delete_contact($name);
        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: GET, PUT, DELETE');
        break;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_tramite'])) {
        //Mostrar un post
        $sql = odbc_prepare($dbConn->getConn(), "SELECT * FROM licencia_autorizacion where id_tramite=? AND deleted_at IS NULL");
        $res = odbc_execute($sql, array($_GET['id_tramite']));
        //   $sql->execute();
        header("HTTP/1.1 200 OK");
        $row = odbc_fetch_array($sql);
        echo json_encode($row);
        exit();
    } else {
        if ($_GET['userProfiles'] == 3) {
            //Mostrar lista de post
            $sql = odbc_prepare($dbConn->getConn(), "SELECT * FROM licencia_usuario WHERE deleted_at IS NULL");
            $res = odbc_execute($sql);
            $result = array();
            while ($item = odbc_fetch_array($sql)) {
                array_push($result, $item);
            }
            // $res = odbc_fetch_array($sql);

            $arr['licencia_usuario'] = $result;
            header("HTTP/1.1 200 OK");
            echo json_encode($arr);
        } else {
            header("HTTP/1.1 403 Error");
            echo json_encode($arr['error'] = "Acceso denegado");
        }
        exit();
    }
}

// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    !isset($_POST['SESSIONKEY']);
    
    $input = $_POST;
    // print_r($_POST);
    $sql = "INSERT INTO licencia_autorizacion
          (id_wap_personas, telefono_actual, email_actual, grupo_sanguineo, factor)
          VALUES(?,?,?,?,?)";
    $statement = odbc_prepare($dbConn->getConn(), $sql);
    $res = odbc_execute($statement, array($input['id_wap_personas'], $input['telefono_actual'], $input['email_actual'], $input['grupo_sanguineo'], $input["factor"]));
    if ($res) {
        $postId = odbc_exec($dbConn->getConn(), "SELECT @@IDENTITY AS ID");
        odbc_fetch_into($postId, $row);
        $input['id'] = $row[0];
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
    } else {
        echo "cualquier cosa";
    }
}

header("HTTP/1.1 400 Bad Request");
