<?php

namespace Base;

class App
{

  protected $config = array(
    "db" => array(),
  );

  protected $DB;
  protected $request;

  /*

  el constructor debe ejecutarse en una unica instancia disponible para toda la aplciacion con la sentencia
  $GLOBALS. Este constructor se encarga de obtener una instancia de la base de datos y de la peticion en Los
  campos DB y request.

  */
  function __construct($config_array = null)
  {

    if($config_array){
      foreach($config_array as $config => $value){
        $this->{$key} = $value;
      }
    }

    $db = DB::getDB($this->db);
    $this->request = Request::Request();
    $this->DB = (!empty($db)) ? $db : null;

  }

  // funcion para asignar valores a las propiedades dinamicas
  function __set($key,$value){
    if($key && $value){
      if(in_array($key,$this->$config)){
        $this->{$key} = $value();
      }else{
        if(property_exists($this,$key)){
          $this->{$key} = $value;
        }
      }
    }
  }

  // funcion para obtener valores de las propiedades dinamicas
  function __get($key){
    if($key){
      if(in_array($key,$this->config)){
        return $this->{$key};
      }else{
        if(property_exists($this,$key)){
          return $this->{$key};
        }
      }
    }
  }

  // retorna la instancia de la bsae de datos
  public function getDB(){
    return $this->DB;
  }

  // funcion estatica para devolver una instancia de la aplicacion
  public static function app($config_array=null){
    return new self($config_array);
  }

  // funcion para poner en marcha la aplicación
  public function run(){

    Router::dispatch($this->request);

  }

}

?>
