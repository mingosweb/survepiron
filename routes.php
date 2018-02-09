<?php

use Base\Router;

Router::get("/encuesta", "MainController@index");
Router::post("/encuesta", "MainController@index");
Router::get("/test","MainController@test");

//Router::get("/desuscribe","MainController@desuscribe");

/*

Router::get("/",function(){
  echo "Hola";
});

*/

?>
