<?php

namespace Base;

class Router
{

  // array con las URLs disponibles con el metodo HTTP GET
  protected static $routes_get = array();
  // array con las URLs disponibles con el metodo HTTP POST
  protected static $routes_post = array();
  // array con las URLs disponibles con el metodo HTTP PUT
  protected static $routes_put = array();
  // array con las URLs disponibles con el metodo HTTP DELETE
  protected static $routes_delete = array();

  public static function getInstance(){
    return new self();
  }

  // funcion para registrar una url en el array de rutas GET
  public static function get($url,$callback){
    if($url && !empty($callback)){
      self::$routes_get[$url] = $callback;
    }else{
      echo "Lo sentimos datos invalidos";
    }
  }

  // funcion para registrar una url en el array de rutas POST
  public static function post($url,$callback){
    if($url && !empty($callback)){
      self::$routes_post[$url] = $callback;
    }else{
      echo "Lo sentimos datos invalidos";
    }
  }

  // funcion para registrar una url en el array de rutas PUT
  public static function put($url,$callback){
    if($url && !empty($callback)){
      self::$routes_put[$url] = $callback;
    }else{
      echo "Lo sentimos datos invalidos";
    }
  }

  // funcion para registrar una url en el array de rutas DELETE
  public static function delete($url,$callback){
    if($url && !empty($callback)){
      self::$routes_update[$url] = $callback;
    }else{
      echo "Lo sentimos datos invalidos";
    }
  }

  // funcion para devolver el array con las rutas GET disponibles
  public static function getRoutes(){
    return self::$routes_get;
  }

  /*

  Funcion principal que captura una petición y obtiene la URL para responder la peticion con el controlador y la
  acción correspondiente.

  */
  public static function dispatch($request){
    if($request){
      $array_method = (isset(self::${"routes_".strtolower($request->method)})) ? self::${"routes_".strtolower($request->method)} : null ;
      $request->route = ($request->route == "") ? "/" : $request->route ;
      if($request->route != "none" && !empty($array_method)){
        if(in_array($request->route,array_keys($array_method))){
            $callback = $array_method[$request->route];
            switch(gettype($callback)){
              case "object":
                $callback();
              break;
              case "string":
                $array_controller = explode("@",$callback);
                $class = "Controllers\\".$array_controller[0];
                $controller = new $class();
                if(method_exists($controller,$array_controller[1])){
                  $controller->$array_controller[1]();
                }else{
                    echo "Accion no existe";
                }
              break;
            }
        }else{
            echo "404";
        }
      }else{
        echo "ruta no valida";
      }
    }else{
      echo "Error. No se obtuvo request";
    }
  }

  public static function redirect(){

  }

}


?>
