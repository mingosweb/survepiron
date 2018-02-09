<?php

namespace Base;

class DB
{

  private $host;
  private $user;
  private $pass;
  private $dbname;
  private $conn;

  // el constructor devuelve un nuevo objecto con datos de conexion y la conexion misma
  function __construct($conn=null,$properties=null){
    $this->host = (isset($properties["host"])) ? $properties["host"] : "" ;
    $this->user = (isset($properties["user"])) ? $properties["user"] : "" ;
    $this->pass = (isset($properties["password"])) ? $properties["password"] : "" ;
    $this->dbname = (isset($properties["dbname"])) ? $properties["dbname"] : "" ;
    $this->conn = $conn;
  }

  function __get($key){
    if(property_exists($this,$key)){
      return $this->{$key};
    }
  }

  function __set($key,$value){
    if(property_exists($this,$key)){
      $this->{$key} = $value;
    }
  }


  // metodo que obtiene una instancia de base de datos con una conexion lista para usar
  public static function getDB($array_data = null ){
    if($array_data){
          $array_db = $array_data;
    }else{
      if ($_SERVER['SERVER_NAME'] == 'localhost'){
          $array_db = self::getEnv('dev');
      }else{
          $array_db = self::getEnv('prod');
      }
    }
    $args = self::validate_params($array_db);
    if($args != null){
      $ConnID2 = mssql_connect($args["host"], $args["user"], $args["password"]);
      return new self($ConnID2,$args);
    }else{
      return null;
    }
  }


  // funcion que lee el archivo de settings para obtener los parametros de conexion
  public static function getEnv($type = 'dev'){
        $settings = parse_ini_file(DIR_INCLUDES.'settings.ini', true);
        return $type == 'dev' ? $settings['db_dev'] : $settings['db_prod'];
  }


  // funcion para validar que los parametros de conexion no esén vacios
  public static function validate_params($args){
      $args = in_array(null,$args) ? null : $args ;
      return $args;
  }


  // funcion que permite realizar una query cualquiera
  public function query($sql){

    if($this->conn){
        $rows = array();
         if(empty($sql)){
           return null;
         }else{
           try {
              $query_res = mssql_query($sql);
              if(!empty($query_res) && (gettype($query_res)=="resource")){
                while($query_row = mssql_fetch_assoc($query_res)){
                   array_push($rows,$query_row);
                }
              }else{
                return true;
              }
              return $rows;
           } catch (Exception $e) {
             echo $e->getMessage();
             return null;
           }
         }
    }else{
      return null;
    }

  }


  public function getSchema($tablename=null){
    if($this->conn && $tablename){
      $sql = "SELECT
          C.COLUMN_NAME,
          C.DATA_TYPE,
          C.IS_NULLABLE,
          (
  	         COALESCE(
  		           (
  		               SELECT
  		                 A.CONSTRAINT_TYPE
  		                   FROM ".$this->dbname.".INFORMATION_SCHEMA.TABLE_CONSTRAINTS A, ".$this->dbname.".INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE B
  		                     WHERE A.CONSTRAINT_NAME = B.CONSTRAINT_NAME AND B.COLUMN_NAME = C.COLUMN_NAME and B.TABLE_NAME = C.TABLE_NAME
  		           )
  		           ,
  		           null
  	         )
           ) as CONSTRAINT_TYPE
           FROM
              ".$this->dbname.".INFORMATION_SCHEMA.COLUMNS C
            WHERE
            C.TABLE_NAME = '".$tablename."'";
      $sql_res = $this->query($sql);
      return $sql_res;
    }else{
      echo "Error. verifique si se pasó por parametro el nombre de la tabla o si la conexión
      a la base de datos se hizo con exito";
      return null;
    }
  }

}


?>
