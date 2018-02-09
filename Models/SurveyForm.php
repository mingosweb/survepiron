<?php

  namespace Models;

  use Base\Model;

  class SurveyForm extends Model{


    private static $table_name = "EncuestaGeneral";
    private static $table_alias = "E";


    function __construct($array){
      parent::__construct($array);
    }


    public function getQuestions(){
      return Question::findByIdSurvey($this->egl_Id);
    }


    public function getCode($vCode){

        $result = array();
        if($GLOBALS["app"]->request->server_name == "localhost"){
          $vSQL = "EXEC Epiron3ArgentinaGestion.dbo.ValidationRandomCode @RandomCode  = '$vCode' ";
        }else{
          $vSQL = "EXEC Epiron3ArgentinaGestion.[dbo].[ValidationRandomCode]  @RandomCode = '$vCode' ";
        }
        $rs = $this->query($vSQL);
        if($rs){
          $result['CodigoCasoAleatorio'] = $rs[0]['RandomId'];
          $result['CodigoCasoReal'] = $rs[0]['RecordId'];
        }
        return $result;

    }


    public function cleanCode($vPalabra){

      $vPalabra = strip_tags(trim($vPalabra));
      $vPalabra = str_replace(' ', ' ', $vPalabra);
      $vPalabra = str_replace('%', '-', $vPalabra);
      $vPalabra = str_replace('"', '', $vPalabra);
      $vPalabra = str_replace("'", '', $vPalabra);
      $vPalabra = str_replace("+", '-', $vPalabra);
      $vPalabra = str_replace("<", '-', $vPalabra);
      $vPalabra = str_replace(">", '-', $vPalabra);
      return $vPalabra;

    }


    function CodeAnswered($vCode) {

        $result = array();
        $vSQL = "EXEC [".$GLOBALS["app"]->DB->dbname."].[dbo].[RespuestasUsuario_Repetido] $vCode ";
        $resultado = $this->query($vSQL);
        $result['CodigoCasoReal'] = (!empty($resultado)) ? true : false ;
        return $result;

    }


  }

?>
