<?php
include "../app/config/config.php";
include "../app/connection/BaseDatos.php";
$dbConn =  new BaseDatos;
$dbConn->connect();
/*
  listar todos los licencia_usuario o solo uno
  select comprobar fecha delete null
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id_usuario'])) {
        // print_r($_GET);
        //Mostrar un post
        $sql = odbc_prepare($dbConn->getConn(), 'SELECT * FROM licencia_tramite 
        INNER JOIN licencia_tipo ON licencia_tramite.id_tramite = licencia_tipo.id_tramite 
        WHERE licencia_tramite.id_usuario = ? AND licencia_tramite.deleted_at IS NULL AND licencia_tipo.deleted_at IS NULL');
        $res = odbc_execute($sql,array($_GET['id_usuario']));
        $result = array();
        //   $sql->execute();
        $arrTipo = [];
        while ($item = odbc_fetch_array($sql)) {
            if (empty($result)) {
                foreach ($item as $key => $value) {
                    // print_r($result[$i]);
                    if ($key == 'tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'vehiculo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'clase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'subclase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'id_tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } else {
                        $result["eleccionesTramite"][$key] = $value;
                    }
                }
                $result["eleccionesTramite"]["tipo"] = [];
                array_push($result["eleccionesTramite"]["tipo"], $arrTipo);
            } else {
                foreach ($item as $key => $value) {
                    // print_r($result[$i]);
                    if ($key == 'tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'vehiculo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'clase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'subclase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'id_tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    }
                }
                array_push($result["eleccionesTramite"]["tipo"], $arrTipo);
            }

            // array_push($result, $item);
        }
        // $row = odbc_fetch_array($sql);
        header("HTTP/1.1 200 OK");
        echo json_encode($result);
        exit();
    } else {
        //Mostrar lista de post
        $sql = odbc_prepare($dbConn->getConn(), "SELECT * FROM licencia_tramite INNER JOIN licencia_tipo ON licencia_tramite.id_tramite = licencia_tipo.id_tramite WHERE licencia_tramite.deleted_at IS NULL AND licencia_tipo.deleted_at IS NULL ORDER BY licencia_tramite.id_tramite");
        $res = odbc_execute($sql);
        $result = array();
        while ($item = odbc_fetch_array($sql)) {
            array_push($result, $item);
        }
        // $res = odbc_fetch_array($sql);
        $arrTipo = [];
        $arrTramite = [];
        // if (count($result) > 1) {
        $i = 0;
        while ($i < count($result)) {
            if (empty($arrTramite)) {
                foreach ($result[$i] as $key => $value) {
                    // print_r($result[$i]);
                    if ($key == 'tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'vehiculo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'clase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'subclase') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } elseif ($key == 'id_tipo') {
                        # code...
                        $arrTipo[$key] = $value;
                        // $arrTramite["eleccionesTramite$i"]["tipo$i"][$key] = $result[$i][$key];
                    } else {
                        $arrTramite["eleccionesTramite$i"][$key] = $value;
                    }
                }
                $arrTramite["eleccionesTramite$i"]["tipo"] = [];
                array_push($arrTramite["eleccionesTramite$i"]["tipo"], $arrTipo);
            } else {
                // print_r($result[$i]);
                $b = 0;
                $coincidencia = false;
                while (array_key_exists("eleccionesTramite$b", $arrTramite)) {
                    if ($result[$i]['id_tramite'] == $arrTramite["eleccionesTramite$b"]['id_tramite']) {
                        foreach ($result[$i] as $key => $value) {
                            if ($key == 'tipo') {
                                # code...
                                $arrTipo[$key] = $value;
                                // $arrTramite["eleccionesTramite$b"]["tipo$i"][$key] = $result[$i][$key];
                            } elseif ($key == 'vehiculo') {
                                # code...
                                $arrTipo[$key] = $value;
                                // $arrTramite["eleccionesTramite$b"]["tipo$i"][$key] = $result[$i][$key];
                            } elseif ($key == 'clase') {
                                # code...
                                $arrTipo[$key] = $value;
                                // $arrTramite["eleccionesTramite$b"]["tipo$i"][$key] = $result[$i][$key];
                            } elseif ($key == 'subclase') {
                                # code...
                                $arrTipo[$key] = $value;
                                // $arrTramite["eleccionesTramite$b"]["tipo$i"][$key] = $result[$i][$key];
                            } elseif ($key == 'id_tipo') {
                                # code...
                                $arrTipo[$key] = $value;
                                // $arrTramite["eleccionesTramite$b"]["tipo$i"][$key] = $result[$i][$key];
                            }
                        }
                        array_push($arrTramite["eleccionesTramite$b"]["tipo"], $arrTipo);
                        $coincidencia = true;
                    }
                    $b++;
                }
                if (!$coincidencia) {
                    foreach ($result[$i] as $key => $value) {
                        if ($key == 'tipo') {
                            # code...
                            $arrTipo[$key] = $value;
                            // $arrTramite["eleccionesTramite$b"]["tipo$b"][$key] = $result[$i][$key];
                        } elseif ($key == 'vehiculo') {
                            # code...
                            $arrTipo[$key] = $value;
                            // $arrTramite["eleccionesTramite$b"]["tipo$b"][$key] = $result[$i][$key];
                        } elseif ($key == 'clase') {
                            # code...
                            $arrTipo[$key] = $value;
                            // $arrTramite["eleccionesTramite$b"]["tipo$b"][$key] = $result[$i][$key];
                        } elseif ($key == 'subclase') {
                            # code...
                            $arrTipo[$key] = $value;
                            // $arrTramite["eleccionesTramite$b"]["tipo$b"][$key] = $result[$i][$key];
                        } elseif ($key == 'id_tipo') {
                            # code...
                            $arrTipo[$key] = $value;
                            // $arrTramite["eleccionesTramite$b"]["tipo$b"][$key] = $result[$i][$key];
                        } else {
                            $arrTramite["eleccionesTramite$b"][$key] = $value;
                        }
                    }
                    $arrTramite["eleccionesTramite$b"]["tipo"] = [];
                    array_push($arrTramite["eleccionesTramite$b"]["tipo"], $arrTipo);
                }
            }
            $i++;
        }
        // print_r($arrTipo);
        // } else {
        //     $arrTramite['eleccionesTramite'] = $result;
        // }
        // $arr['eleccionesTramite'] = $arrTramite;
        // $arr['eleccionesTramite']['tipo'] = $arrTipo;
        header("HTTP/1.1 200 OK");
        echo json_encode($arrTramite);
        exit();
    }
}
// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    $input['id_admin'] = (isset($input['id_admin']) ? $input['id_admin'] : null);
    $input['consideracion_licencia_existente'] = (isset($input['consideracion_licencia_existente']) ? $input['consideracion_licencia_existente'] : null);
    $input['tengo_mismo_domicilio'] = (isset($input['tengo_mismo_domicilio']) ? $input['tengo_mismo_domicilio'] : null);
    $input['extiende_renovacion'] = (isset($input['extiende_renovacion']) ? $input['extiende_renovacion'] : null);
    $input['edad_usuario'] = (isset($input['edad_usuario']) ? $input['edad_usuario'] : null);

    // print_r($input);
    $sqlTramite = "INSERT INTO licencia_tramite
          (id_usuario, id_admin, id_estado, terminos_condiciones, consideracion_licencia_existente, tengo_mismo_domicilio, extiende_renovacion, edad_usuario)
          VALUES(?,?,?,?,?,?,?,?)";
    $statementTramite = odbc_prepare($dbConn->getConn(), $sqlTramite);
    $resTramite = odbc_execute(
        $statementTramite,
        array(
            $input['id_usuario'],
            $input['id_admin'],
            $input['id_estado'],
            $input['terminos_condiciones'],
            $input["consideracion_licencia_existente"],
            $input["tengo_mismo_domicilio"],
            $input["extiende_renovacion"],
            $input["edad_usuario"]
        )
    );
    if ($resTramite) {
        $postId = odbc_exec($dbConn->getConn(), "SELECT @@IDENTITY AS ID");
        odbc_fetch_into($postId, $row);
        $input['id_tramite'] = $row[0];

        $sqlTipo = "INSERT INTO licencia_tipo
          (id_tramite, tipo, vehiculo, clase, subclase)
          VALUES(?,?,?,?,?)";
        $statementTipo = odbc_prepare($dbConn->getConn(), $sqlTipo);
        $resTipo = odbc_execute($statementTipo, array($input['id_tramite'], $input['tipo'], $input['vehiculo'], $input['clase'], $input["subclase"]));
        if ($resTipo) {
            $postId = odbc_exec($dbConn->getConn(), "SELECT @@IDENTITY AS ID");
            odbc_fetch_into($postId, $row);
            $input['id_tipo'] = $row[0];
        }
    }
    if ($resTramite && $resTipo) {
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
    }
}
// //Borrar
// if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
// {
// 	$id = $_GET['id'];
//   $statement = $dbConn->prepare("DELETE FROM licencia_usuario where id=:id");
//   $statement->bindValue(':id', $id);
//   $statement->execute();
// 	header("HTTP/1.1 200 OK");
// 	exit();
// }
// //Actualizar
// if ($_SERVER['REQUEST_METHOD'] == 'PUT')
// {
//     $input = $_GET;
//     $postId = $input['id'];
//     $fields = getParams($input);
//     $sql = "
//           UPDATE licencia_usuario
//           SET $fields
//           WHERE id='$postId'
//            ";
//     $statement = $dbConn->prepare($sql);
//     bindAllValues($statement, $input);
//     $statement->execute();
//     header("HTTP/1.1 200 OK");
//     exit();
// }
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
