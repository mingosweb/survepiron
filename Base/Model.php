<?php

namespace Base;

class Model
{

  // variable que almacenará los campos dinamicos de la tabla en la base de datos
  protected $attributes;

  // el constructor recibe un array clave =>  valor con los campos y valores de la base de datos
  function __construct($obj=null){
    if(!empty($obj)){
        $this->attributes = $obj;
    }
  }

  // metodo para settear campos dinamicos
  public function __set($key,$value){
    if(property_exists($this,$key)){
      $this->{$key} = $value;
    }else{
      if(in_array($key,array_keys($this->attributes))){
        $this->attributes[$key] = $value;
        return true;
      }else{
        return false;
      }
    }
  }


  // metodo para obtener campos dinamicos
  public function __get($key){
    if(in_array($key,array_keys($this->attributes))){
      return $this->attributes[$key];
    }else{
      if(property_exists($this,$key)){
        return $this->{$key};
      }else{
        return false;
      }
    }
  }

  // metodo estático que obtiene el nombre de la tabla del modelo en DB. apartir del atributo estático table_name
  public static function getTable(){
    $reflection = new \ReflectionClass(get_called_class());
    $properties = $reflection->getDefaultProperties();
    return (isset($properties["table_name"])) ? $properties["table_name"] : get_called_class()."s" ;
  }

  // metodo estático que obtiene un alias de la tabla del modelo en DB. apartir del atributo estático table_alias
  public static function getTableAlias(){
    $reflection = new \ReflectionClass(get_called_class());
    $properties = $reflection->getDefaultProperties();
    return (isset($properties["table_alias"])) ? $properties["table_alias"] : "tbl_";
  }

  //metodo que obtiene el nombre de la primary key de la tabla del Modelo
  public static function getPrimaryKey(){
    $schema = $GLOBALS["app"]->DB->getSchema(self::getTable());
    if($schema){
      foreach($schema as $column_def){
        if($column_def["CONSTRAINT_TYPE"] == "PRIMARY KEY"){
          return $column_def["COLUMN_NAME"];
        }
      }
    }
  }

  // metodo que obtiene todos los registros del modelo establecido
  public static function getAll(){
    $sql =  "SELECT ".self::getTableAlias().".* "."FROM [".$GLOBALS["app"]->DB->dbname."].[dbo].[".self::getTable()."] as ".self::getTableAlias();
    $sql_res = $GLOBALS["app"]->DB->query($sql);
    $array_result = array();
    if(!empty($sql_res)){
      $model_class = get_called_class();
      foreach($sql_res as $row){
        $row["_id"] = $row[self::getPrimaryKey()];
        $Model = new $model_class($row);
        array_push($array_result,$Model);
      }
      return $array_result;
    }else{
      return $sql_res;
    }
  }

  // metodo que obtiene 1 unico registro del modelo establecido
  public static function find($id=1){
    $sql = sprintf(
      "SELECT %s FROM %s WHERE %s",
      self::getTableAlias().".* ",
      "[".$GLOBALS["app"]->DB->dbname."].[dbo].[".self::getTable()."] as ".self::getTableAlias(),
      self::getTableAlias().".".self::getPrimaryKey()." = $id"
    );

    $sql_res = $GLOBALS["app"]->DB->query($sql);
    $array_result = array();
    if(!empty($sql_res)){
      $model_class = get_called_class();
      foreach($sql_res as $row){
        $Model = new $model_class($row);
        array_push($array_result,$Model);
      }
      return $array_result[0];
    }else{
      return $sql_res;
    }
  }

  // function que permite realizar una busqueda personalizada
  public function query($sql=""){
    if(!empty($sql)){
      $sql_res = $GLOBALS["app"]->DB->query($sql);
      return $sql_res;
    }else{
      return null;
    }
  }

  function __sleep(){
    return array('attributes');
  }

}


?>
