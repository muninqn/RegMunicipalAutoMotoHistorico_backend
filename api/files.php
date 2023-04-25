<?php

//archivos necesarios para la configuracion general
include "../app/_util/functions.php";
include "../app/_config/config.php";
require_once "../app/_connection/BaseDatos.php";

//Utilizamos auth controller para corroborar que exista el usuario
require_once "../app/auth/AuthController.php";
//Usamos el servicio de tramite para obtener el id del tramite activo por parte del usuario
// require_once "../app/tramite/TramiteService.php";

//Llamamos al controlador encargado de la ejecucion de toda esta seccion
require_once "../app/files/FilesController.php";

//definimos encabezados para permitir acceso de todos lados, definir el tipo de contenido y los metodos HTTP permitidos
header("Access-Control-Allow-Origin: *");
header("Content-Type: multipart/form-data; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

//seteamos el limite de memoria a 256MB para permitir que suban imagenes descomunales
ini_set('memory_limit', '256M');

//capturamos los parametros recibidos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bodyParamsArray = $_POST;
} else {
    $bodyParamsArray = json_decode(file_get_contents('php://input'), true);
}

//print_r($bodyParamsArray); //utilizado para ver si recibis las weas xd
//print_r($_FILES); //utilizado para ver si recibis las weas xd

//verificamos que se obtenga el SESSIONKEY
if (!isset($bodyParamsArray['SESSIONKEY']) || $bodyParamsArray['SESSIONKEY'] == '') {

    $response = crearRespuestaSolicitud(401, "error", "Acceso denegado. Usted no tiene permisos para ejecutar esta peticion.");
    $response['headers'] = [
        "HTTP/1.1 401 Unauthorized",
        'WWW-Authenticate: Basic realm="Acceso a recurso protegido"',
    ];
    retornarRespuestaSolicitud($response);
} else {
    //verificamos que el usuario sea un usuario valido y obtenemos sus permisos
    $authController = new AuthController();
    // if (($userData = $authController->getUserData($bodyParamsArray['SESSIONKEY'], (PROD ? 77 : 71))) != null) {
    if (($userData = $authController->getUserData($bodyParamsArray['SESSIONKEY'])) != null) {
        //obtenemos los nombres del controller y el metodo desde la url
        $URL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $URL = explode('/', $URL);

        //se fija en la 5ta parte de la url de la api, debería ser el controller files
        if (($controller = obtenerController($URL[5])) != null) {
        // if (isset($URL[5]) && $URL[5] == "files") {
            // $controller = new FilesController();

            if (isset($URL[6])) {
                //captura el tipo de peticion HTTP y el nombre del metodo buscado
                $requestMethod = $_SERVER["REQUEST_METHOD"];
                $methodName = $URL[6];

                //busca el id del tramite activo por parte del usuario
                // $objTramiteService = new TramiteService();
                // $idTramiteUser = $objTramiteService->obtenerIdTramitePersona($userData['referenciaID']);

                if (isset($userData["perfilUsuario"])) {
                    $controller->setPerfilUsuario($userData["perfilUsuario"]);
                }
                // if (isset($idTramiteUser['id_tramite'])) {
                //     $controller->setIdTramite($idTramiteUser['id_tramite']);
                // }
                $controller->setIdWapPersona($userData['referenciaID']);
                $controller->setRequestMethod($requestMethod);

                $response = $controller->procesarRespuesta($methodName, deutf8ize($bodyParamsArray));
            } else {
                // Si no existe el metodo, retornamos la respuesta
                $response = getArrayNotFound("No se especifico que accion ejecutar.");
            }
        } else {
            // Si no existe el modulo, retornamos la respuesta
            $response = getArrayNotFound("El modulo indicado es incorrecto.");
        }
    } else {
        //el usuario no fue encontrado con el sessionkey obtenido
        $response = getArrayNotFound("El usuario indicado no es válido.");
    }
    retornarRespuestaSolicitud($response);
}
