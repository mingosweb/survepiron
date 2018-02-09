<?php

namespace Base\Front;

class HtmlForm
{

  // array con propiedades del formulario pasadas por parametros
  private $_properties = array(
    "target" => "",
    "novalidate " => "",
    "name" => "",
    "method" => "POST",
    "enctype" => "",
    "autocomplete" => "",
    "action" => "",
    "accept-charset" => "",
    "accept" => "",
    "class" => "",
    "id" => ""
  );
  private $fields = array();
  private $questionsArray = array();


  function __construct($properties=null){
    if($properties){
      $this->initAttributes($properties);
    }
  }

  public function setQuestions($array){
    $this->questionsArray = $array;
  }

  public function initAttributes($array_config=null){
    foreach($array_config as $attr => $value ){
      if(in_array($attr,array_keys($this->_properties))){
        $this->_properties[$attr] = $value;
      }
    }
  }


  public function renderForm($valuesFromForm = null){

    $html_form = "<form ";
    foreach($this->_properties as $attr => $val){
      if(!empty($val)){
        $html_form .= $attr."='".$val."' " ;
      }
    }
    $html_form .= ">";

    // renderizamos las preguntas
    if(!empty($this->questionsArray)){
      $html_form .= (!empty($valuesFromForm)) ? $this->renderQuestions($this->questionsArray,$valuesFromForm) : $this->renderQuestions($this->questionsArray) ;
    }else{
      $html_form .= "";
    }

    $html_form .= "<div class='form-group' style='text-align: center;'>
        <div class='col-lg-12' style='padding-bottom: 5px;'>
            <button type='submit' name='Enviar' class='btn btn-primary'>Enviar</button>
        </div>
    </div>";
    $html_form .= $this->renderFieldsAdded();
    $html_form .= "</form>";
    return $html_form;
  }


  public function addField($array_config){
    if(!empty($array_config)){
      foreach($array_config as $key => $val){
        array_push($this->fields,$array_config);
      }
    }
  }


  public function addInput($array){
    $array["html_type"] = "input";
    array_push($this->fields,$array);
  }


  public function addTextArea($array){
    $array["html_type"] = "textarea";
    array_push($this->fields,$array);
  }


  public function renderFieldsAdded(){

    $result = "";
    $attrs_field = array(
      "type",
      "class",
      "value",
      "id",
      "name"
    );

    if(!empty($this->fields)){
      $field_html = "";
      foreach($this->fields as $field){
          switch($field["html_type"]){
            case "textarea":
            break;
            case "input":
              $field_html .= "<input ";
              foreach($field as $attr => $val){
                if(in_array($attr, $attrs_field)){
                  $field_html .= "$attr='".$val."' ";
                }
              }
              $field_html .= ">";
            break;
          }
          $result .= $field_html;
      }
      return $field_html;
    }

  }

  /*
    funcion que convierte un array de objetos de tipo Question y los convierte en
    codigo HTML.

    el primer parametro es el array con las preguntas obtenidas de la encuesta desde la base de datos y
    el segundo parametro es opcional y contiene las respuestas obtenidas desde un formulario previamente
    util al momento de validar campos obligatorios y demas

  */
  public function renderQuestions($questions,$values_default=null){

    $html_final = "";
    $html_question = "";

    if(!empty($questions)){

      foreach($questions as $question){

        $answers = $question->getAnswers();
        $html_answers = "";
        $asterisco = ($question->prg_Obligatoria) ? "<strong style='color:red'> * </strong>" : "" ;
        $required = ($question->prg_Obligatoria) ? "required" : "" ;

        if($answers){

          if(count($answers) > 1){
              $html_question .= "<div class='form-group survey-question'>
                <p class='question-text'>".$question->prg_NroPregunta.") ".utf8_encode($question->prg_Texto).$asterisco."</p>
                #items#
                <!--<small class='form-text text-muted'>".utf8_encode($question->prg_Texto)."</small>-->
              </div>";
          }else{
              $html_question  .= "<div class='form-group survey-question'>
              <label for='exampleFormControlTextarea1'>".$question->prg_NroPregunta.") ".utf8_encode($question->prg_Texto).$asterisco."</label>
              #items#
              <!--<small class='form-text text-muted'>".utf8_encode($question->prg_Texto)."</small>-->
            </div>";
          }

          // la cantidad se necesita para los radio y checks, pues solo uno de ellos es suficiente para que sea requerido
          $cant = 1;
          foreach($answers as $answer){

            $required = ($question->prg_Obligatoria && $cant == 1) ? "required" : "" ;
            switch($answer->rta_TipoRespuesta){
              case "text":
                $default = ($values_default["answer-".$question->prg_Id]) ? $values_default["answer-".$question->prg_Id] : "" ;
                $html_answers .= "<textarea class='form-control' id='answer-".$answer->rta_Id."' name='answer-".$question->prg_Id."' rows='3' $required> $default </textarea>";
              break;
              case "radio":
                $default = ($answer->rta_Id == @$values_default["answer-".$question->prg_Id] ) ? "checked" : "" ;
                $html_answers .= '<div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" id="answer-'.$answer->rta_Id.'" name="answer-'.$question->prg_Id.'" value="'.$answer->rta_Id.'"'.$default." ".$required.'>
                  <label class="form-check-label" for="inlineCheckbox1">'.$answer->rta_Texto.'</label>
                </div>';
              break;
              case "checkbox":
              $default = ($answer->rta_Id == @$values_default["answer-".$question->prg_Id] ) ? "checked" : "" ;
              $html_answers .= '<div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="answer-'.$answer->rta_Id.'" name="answer-'.$question->prg_Id.'" value="'.$answer->rta_Id.'"'.$default." ".$required.'>
                <label class="form-check-label" for="inlineCheckbox1">'.$answer->rta_Texto.'</label>
              </div>';
              break;
            }
            $cant++;

          }

          $html_question = str_replace("#items#",$html_answers,$html_question);

        }

      }

      return $html_question;

    }else{
      return "nada :|";
    }

  }


}


?>
