<?php

namespace Models;

use Base\Model;

class Answer extends Model
{

  private static $table_name = "Respuestas";
  private static $table_alias = "R";

  function __construct($array){
    parent::__construct($array);
  }

  public static function findByIdQuestion($id){
    $sql =  "SELECT ".self::getTableAlias().".* "."FROM [".$GLOBALS["app"]->DB->dbname."].[dbo].[".self::getTable()."] as ".self::getTableAlias()." ";
    $sql .= " WHERE rta_IdPregunta = ".$id;
    $sql_res = $GLOBALS["app"]->DB->query($sql);
    $array_result = array();
    if(!empty($sql_res)){
      $model_class = get_called_class();
      foreach($sql_res as $row){
        $Model = new $model_class($row);
        array_push($array_result,$Model);
      }
      return $array_result;
    }else{
      return $sql_res;
    }
  }

}


?>
