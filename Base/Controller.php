<?php

namespace Base;

/*
  (AUN SIN OPTIMIZAR)
  Esta clase se usa como base de los demas Controladores
  la idea es que la propiedad $model tenga el nombre del modelo de datos cque controla
  y se obtenga con la funcinon getModelName
*/

class Controller
{
    protected $model;
    protected static $model_name;

    public static function getModelName(){
      $reflection = new \ReflectionClass(get_called_class());
      $properties = $reflection->getDefaultProperties();
      return (isset($properties["model_name"])) ? $properties["model_name"] : get_called_class()."s" ;
    }

    public function index(){
      echo "200 OK";
    }

}


?>
