<?php
require_once("../app/files/FilesController.php");
require_once("../app/solicitud/SolicitudController.php");
require_once("../app/vecino/VecinoController.php");
require_once("../app/empresa/EmpresaController.php");


// require_once("../app/delegacion/DelegacionController.php");
// require_once("../app/turnero/TurneroController.php");
// require_once("../app/mascota/MascotaController.php");
// require_once("../app/preturno/PreTurnoController.php");
// require_once("../app/vecino/VecinoController.php");
// require_once("../app/credencial/CredencialController.php");


function getArrayNotFound($message)
{
    $response['headers'] = [
        "HTTP/1.1 404 Not Found",
    ];
    $response['body'] = [
        'code' => 404,
        'status' => "error",
        'message' => $message,
    ];
    return $response;
}

function retornarRespuestaSolicitud($response)
{
    if (isset($response['headers'])) {
        foreach ($response['headers'] as $unHeader) {
            header($unHeader);
        }
    }
    echo json_encode($response['body']);
    exit();
}

function repararChars($unCampito)
{
    return htmlspecialchars(iconv("iso-8859-1", "utf-8", $unCampito));
}

function formatearFechaAceptadaPorLaCuarentona($unaFechaConBarritas)
{
    return date("Y-m-d", strtotime($unaFechaConBarritas));
}
function formatearFechaNacimiento($unaFechaConBarritas)
{
    return explode("T", $unaFechaConBarritas)[0];
}

function crearRespuestaSolicitud($code, $status, $message, $data = null)
{
    $response['body'] = [
        "code" => $code,
        "status" => $status,
        "message" => $message,
        "data" => utf8ize($data)
    ];

    return $response;
}

function obtenerController($controllerName)
{
    switch (strtolower($controllerName)) {
        case 'solicitud': //controlador de solicitud
            $controller = new SolicitudController();
            break;
        case 'files': //controlador de files
            $controller = new FilesController();
            break;
        case 'vecino': //controlador de vecino
            $controller = new VecinoController();
            break;
        case 'empresa': //controlador de vecino
            $controller = new EmpresaController();
            break;

        default: //no se encontro controlador
            $controller = null;
            break;
    }

    return $controller;
}

/* Funcion que recibe el nombre del archivo, el id de la solicitud y el tipo por si corresponde a una categoria/paso del proyecto
y retorna el camino al archivo para ser almacenado en la base de datos */
function getDireccionArchivoAdjunto($nombreProyecto, $nombreArchivo, $idSolicitud)
{
    $filePath = null;

    if (PATH_FILE_LOCAL) {
        $target_path_local = $idSolicitud != null
            ? URL_FILE_LOCAL . $nombreProyecto . "/" . $idSolicitud . "/"
            : URL_FILE_LOCAL . $nombreProyecto . "/nodeberiapasar/" . $idSolicitud . "/";
    } else {
        $target_path_local = $idSolicitud != null
            ? PATH_FILE_SERVER . $nombreProyecto . "/" . $idSolicitud . "/"
            : PATH_FILE_SERVER . $nombreProyecto . "/nodeberiapasar/" . $idSolicitud . "/";
    }
    // var_dump($target_path_local);
    // die;
    if (!file_exists($target_path_local)) {
        mkdir($target_path_local, 0755, true);
    };

    if ($nombreArchivo != null) {
        $filePath = $target_path_local . $nombreArchivo;
    }

    return $filePath;
}

/* Funcion que recibe el nombre del archivo, un array con extensiones permitidas y verifica que la extensión del archivo se condiga con alguna de las permitidas */
function verificarExtensionValida($nombreArchivo, $arrayExtensionesPermitidas = ['jpg', 'jpeg', 'png', 'bmp', 'pdf'])
{
    $regexExtensionesPermitidas = "/(?).(" . implode("|", $arrayExtensionesPermitidas) . ")$/i";
    return preg_match($regexExtensionesPermitidas, $nombreArchivo);
}

/* Funcion que recibe el nombre del archivo y retorna la extension del archivo precedida por un punto */
function obtenerExtensionArchivo($fileType)
{
    if (str_contains($fileType, "image/")) {
        $extension = ".jpg";
    } elseif (str_contains($fileType, "application/pdf")) {
        $extension = ".pdf";
    } else {
        $extension = null;
    }
    return $extension;
}
/* Funcion que recibe el base64 del archivo y retorna */
function obtenerArchivo($fileType,$idSolicitud)
{   
    $fileExtension = pathinfo($fileType, PATHINFO_EXTENSION);

    // Definimos el tipo de archivo
    if ($fileExtension == "pdf") {
        $fileMimeType = "application/" . $fileExtension;
    } else {
        $fileMimeType = "image/" . $fileExtension;
    }

    // Obtenemos el archivo y lo convertimos a base64
    
    if (!(str_contains($fileType,"RMAMH"))) {
        if (PATH_FILE_LOCAL) {
            $fileType="../../../projects_files/RMAMH/".$idSolicitud."/".$fileType;
        }else{
            $fileType=PATH_FILE_SERVER . "RMAMH/".$idSolicitud."/".$fileType;
        }
    }

    
    $fileData = file_get_contents($fileType);
    $base64File = "data:$fileMimeType;base64," . base64_encode($fileData);
    return $base64File;
}

/* El formato de la fecha se pone año dia mes para que lo acepte la consulta en la db, despues lo muestra bien */
function formatearFechaCenat($unaFecha)
{
    return str_replace(": ", ":", date('Y-m-d h:i:s', strtotime($unaFecha)));
}

function verEstructura($e, $die = false)
{
    echo "<pre>";
    print_r($e);
    echo "</pre>";
    if ($die) die();
}

function buscarPorEstado($unEstado)
{
    $sqlQuery = "";
    if (isset($unEstado)) {
        switch ($unEstado) {
            case 'completado':
                # code...
                $sqlQuery .= " AND licencia_tramite.id_estado = 9 GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
                break;
            case 'incompleto':
                # code...
                $sqlQuery .= " AND licencia_tramite.id_estado <> 9 AND licencia_tramite.id_estado <> 10 AND licencia_tramite.id_estado <> 11 GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
                break;
            case 'aprobado':
                # code...
                $sqlQuery .= " AND licencia_tramite.id_estado = 10 GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
                break;
            case 'rechazado':
                # code...
                $sqlQuery .= " AND licencia_tramite.id_estado = 11 GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
                break;

            default:
                # code...
                $sqlQuery .= " GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
                break;
        }
    } else {
        $sqlQuery .= " GROUP BY wapPersonas.Nombre, wapPersonas.Documento, licencia_tramite.id_tramite, licencia_tipo.tipo, licencia_estado.descripcion";
    }

    return $sqlQuery;
}

function utf8ize($d)
{
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string($d)) {
        if (!mb_detect_encoding($d, "UTF-8", true)) {
            return utf8_encode($d);
        }
        return $d;
    }
    return $d;
}
/**
 * Funcion que permite decodificar un string del charset utf8 antes de insertarlo en la DB, para que no se webee
 */
function deutf8ize($d)
{
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = deutf8ize($v);
        }
    } else if (is_string($d)) {
        if (mb_detect_encoding($d, "UTF-8", true)) {
            $d = utf8_decode($d);
        }
        return $d;
    }
    return $d;
}

function diferenciaEntre($fecha1)
{
    $todayDate = new DateTime("now");
    $realizadoDate = new DateTime($fecha1);
    return $todayDate->diff($realizadoDate)->days;
}

function sumarEjeDatosY($original)
{
    $valor = $original + 4;
    return $valor;
}

function verificarImagenRennaper($documento,$genero){
    if ($genero === "M") {
        $carpeta="MASCULINO";
    }else{
        if ($genero === "F") {
            $carpeta="FEMENINO";
        }else{
            if (PATH_FILE_LOCAL) {
                $carpeta="NO%20BINARIO";
            }else{
                $carpeta="NO BINARIO";
            }
        }   
    }
    
    if (PATH_FILE_LOCAL) {
        $direccionAConsultar="https://weblogin.muninqn.gov.ar/DataServerOK/webRenaper/";
    }else {
        if (PROD) {
            $direccionAConsultar="E:/Dataserver/Produccion/webRenaper/";
        }else{
            $direccionAConsultar="E:/Dataserver/Replica/webRenaper/";
        }
    }
    $arrContextOptions = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false,),);
    return @file_get_contents($direccionAConsultar.$carpeta."/".$genero.$documento.".png", false, stream_context_create($arrContextOptions));
}