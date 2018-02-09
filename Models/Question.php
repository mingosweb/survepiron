<?php

namespace Models;

use Base\Model;

class Question extends Model
{

  private static $table_name = "Preguntas";
  private static $table_alias = "P";

  function __construct($array){

    parent::__construct($array);

  }

  public function getAnswers(){

    return Answer::findByIdQuestion($this->prg_Id);

  }

  // aqui hay cambios
  public static function findByIdSurvey($id){

    $sql =  "SELECT ".self::getTableAlias().".* "."FROM [".$GLOBALS["app"]->DB->dbname."].[dbo].[".self::getTable()."] as ".self::getTableAlias()." ";
    $sql .= " WHERE prg_IdEncuesta = ".$id;
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
