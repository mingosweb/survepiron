<?php

// DEFINICION DE CONSTANTEs
define("DIR_INCLUDES",$_SERVER["DOCUMENT_ROOT"]);
define("DIRVIEWS",$_SERVER["DOCUMENT_ROOT"]."/Views/");
define("DIR_LOGS",$_SERVER["DOCUMENT_ROOT"]."/logs/");
define("DIR_DIST",$_SERVER["DOCUMENT_ROOT"]."/dist/");

// CARGA DE CLASES Y RUTAS
include_once "autoload.php";
include_once "routes.php";

// CREAR INSTANCIA DE LA APLICACION
$app = Base\App::app();
$app->run();



?>
