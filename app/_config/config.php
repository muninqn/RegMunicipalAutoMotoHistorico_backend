<?php
/* Archivo encargado de configurar los parámetros iniciales del proyecto */

/* Inicializamos la sesion */
session_status() === PHP_SESSION_ACTIVE ?: session_start();

/* Definimos el tipo de contenido, la codificacion y el control de cache */
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate ");

/* Root Path */
//include_once('paths.php');

/* AutoLoad composer & local */
define('ROOT_PATH', substr(__DIR__, 0, strlen(__DIR__) - 11));
//echo ROOT_PATH . 'vendor/autoload.php';
require ROOT_PATH . 'vendor/autoload.php';


/* Carga del DOTENV, acordarse de descomentar cuando .env este configurado */
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

/* Modo produccion: true */
define('PROD', $_ENV['PROD'] == 'true' ? true : false);

/* ID de la aplicacion */
define('APPID', PROD ? 85 : 86);

/* Configuracion de URLs, si no está en produccion redirige el weblogin a replica */
define('WEBLOGIN', PROD ? 'https://weblogin.muninqn.gov.ar' : 'http://200.85.183.194:90');

/* Configuracion base de datos (Si no está en produccion define la conexión para réplica) */
define('DB_HOST', PROD ? $_ENV['DB_HOST'] : '128.53.15.3');
define('DB_USER', PROD ? $_ENV['DB_USER'] : 'userturnos');
define('DB_PASS', PROD ? $_ENV['DB_PASS'] : 'turnero16');
define('DB_NAME', PROD ? $_ENV['DB_NAME'] : 'infoprueba');
define('DB_PORT', PROD ? $_ENV['DB_PORT'] : '3306');
define('DB_CHARSET', PROD ? $_ENV['DB_CHARSET'] : 'utf8');

/* Configuracion del path fIle */
define('DIR_PROJECT_NAME','RMAMH');
define('PATH_FILE_LOCAL', $_ENV['PATH_FILE_LOCAL'] == 'true' ? true : false);
define('URL_FILE_LOCAL', 'C:/xampp/htdocs/projects_files/');
define('PATH_FILE_SERVER', $_ENV['PROD'] == 'true' ? "E:/Dataserver/Produccion/projects_files/" : "E:/Dataserver/Replica/projects_files/"); 
define('TB_RMAMH_Solicitud', $_ENV['TB_RMAMH_Solicitud']); 
define('TB_RMAMH_Archivos', $_ENV['TB_RMAMH_Archivos']); 
define('TB_RMAMH_Vecino', $_ENV['TB_RMAMH_Vecino']); 
/* Configuración de tablas (Ejemplos) */
/*define('USUARIOS', $_ENV['DB_USUARIOS_TABLE']);
define('SOLICITUDES', $_ENV['DB_SOLICITUDES_TABLE']);
define('TRABAJOS', $_ENV['DB_TRABAJOS_TABLE']);
define('LOG', $_ENV['DB_LOG_TABLE']);*/

/* Definición del límite de las columnas en las tablas (Ejemplos) */
/*define('LT_USU_NOMBRE', $_ENV['LT_USU_NOMBRE']);
define('LT_USU_APELLIDO', $_ENV['LT_USU_APELLIDO']);
define('LT_USU_TELEFONO', $_ENV['LT_USU_TELEFONO']);
define('LT_USU_EMAIL', $_ENV['LT_USU_EMAIL']);
define('LT_USU_DIRRENAPER', $_ENV['LT_USU_DIRRENAPER']);

define('LT_SOL_NRORECIBO', $_ENV['LT_SOL_NRORECIBO']);
define('LT_SOL_OBS', $_ENV['LT_SOL_OBS']);

define('LT_CAP_NOMBRE', $_ENV['LT_CAP_NOMBRE']);
define('LT_CAP_APELLIDO', $_ENV['LT_CAP_APELLIDO']);
define('LT_CAP_MATRICULA', $_ENV['LT_CAP_MATRICULA']);
define('LT_CAP_LUCAPACITACION', $_ENV['LT_CAP_LUCAPACITACION']);*/
