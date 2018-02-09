<?php

namespace Controllers;

use Base\Controller;
use Base\Front\View;
use Models\SurveyForm;
use Base\Front\HtmlForm;
use Base\Logger;

class MainController extends Controller
{


  protected static $model_name = "main";


  public function index(){

    $code = (isset($_POST["codigoCasoAleatorio"])) ? $_POST["codigoCasoAleatorio"] : $GLOBALS["app"]->request->params->cod ;

    // Verificamos de que existe un código valido
    if($code && (strlen($code) == 13)){

      // obtenemos informacion de la encuesta
      $id_encuesta = 1;
      $Survey = SurveyForm::find($id_encuesta);
      $CodigoURL = $Survey->cleanCode($code);
      $survey_codes = $Survey->getCode($CodigoURL);

      $survey_codes["CodigoCasoReal"] = 1 ; //USO SOLO PARA PRUEBAS

      $logger = new Logger();

      // if el codigo no existe con caso
      if(empty($survey_codes['CodigoCasoReal'])){

        // hacer log de error de código
        $msj_error = "El c&oacute;digo es incorrecto.";
        $title = $Survey->egl_Texto;
        $title = (!empty($title)) ? $title : 'Encuesta' ;
        //$logger->saveAccessLog(0, $CodigoURL, 'El codigo es incorrecto jeje.');
        return new View("error",array(
            "title" => $title,
            "logo" => "YPF.jpg",
            "message" => $msj_error
          )
        );

      }else{ // verficamos si fue respondida

        $DatoRespondido = $Survey->CodeAnswered($survey_codes['CodigoCasoReal']);
        $DatoRespondido = false; //SOLO PARA PRUEBAS

        if ($DatoRespondido){

            $msj_error = "Esta encuesta ya fue respondida.";
            $title = $Survey->egl_Texto;
            $title = (!empty($title)) ? $title : 'Encuesta';
            //$logger = new Logger();
            //$logger->saveAccessLog($survey_codes['CodigoCasoReal'], $CodigoURL, $msj_error, 'MS');
            return new View("error",array(
                "title" => $title,
                "logo" => "YPF.jpg",
                "message" => $msj_error
              )
            );

        }else{

          // CREAMOS FORMULARIO HTML
          $form_html = new HtmlForm(array(
            "action" => "/encuesta?cod=$code",
            "method" => "post",
            "class" => "survey-form mi-encuesta"
          ));

          //OBTENEMOS LAS PREGUNTAS A RENDERIZAR Y LAS AÑADIMOS AL FORMULARIO
          $questions = $Survey->getQuestions();
          $form_html->setQuestions($questions);

          // AGREGAMOS OTROS CAMPOS DEL formulario
          $form_html->addInput(array("type" => "hidden", "name" => "codigoCaso", "value" => $survey_codes['CodigoCasoReal']));
          $form_html->addInput(array("type" => "hidden", "name" => "codigoCasoAleatorio", "value" => $code ));
          $form_html->addInput(array("type" => "hidden", "name" => "idEncuesta", "value" => $id_encuesta ));

          // OBTENEMOS EL TITULO DE LA ENCUESTA
          $title = $Survey->egl_Texto;
          $title = (!empty($title)) ? $title : 'Encuesta';

          // CREAMOS LAS VARIABLES PRINCIPALES A RENDERIZAR
          $params_view = array(
              "title" => $title,
              "logo" => "YPF.jpg",
              "description" => '
              <hr>
              <h4  class="text-center">Encuesta de Satisfacción</h4>
              <p class="text-center">
              Queremos invitarte a responder esta encuesta</p>
              <hr>',
              //"url_desuscribe" => "/desuscribe?code=$CodigoURL",
              "codigo_caso" => $survey_codes["CodigoCasoReal"],
              "codigo_aleatorio" => $CodigoURL,
              "id_encuesta" => $id_encuesta,
          );

          // VERIFICAMOS SI LA ENCUESTA A MOSTRAR ES SOBRE UNA RESPUESTA ANTERIOR
          if(isset($_POST["Enviar"])){

            $values_default = $_POST;
            $errors = array();
            $responses = array();
            $cant = 0;

            // verificamos si las preguntas fueron respondidas
            foreach($questions as $question){
              if($question->prg_Obligatoria && !(isset($values_default["answer-".$question->prg_Id]))){
                array_push($errors,"Pregunta ".$question->prg_NroPregunta." No puede estar vacio");
              }else{
                $responses['IdPregunta' . $question->prg_NroPregunta ] = $question->prg_Id ;
                if ($cant < count($questions) - 1) {
                    $responses['IdRespuesta' . $question->prg_NroPregunta ] = intval($values_default["answer-".$question->prg_Id]);
                } else {
                    $responses['Sugerencia' . $question->prg_NroPregunta ] = $Survey->cleanCode(trim($values_default["answer-".$question->prg_Id]));
                }
              }
              $cant++;
            }

            // CUANDO OCURRIÓ UN ERROR CON LA VALIDACION DEL CODIGO
            $msj_error = implode("<br>",$errors);
            if(!empty($msj_error)){

              $params_view["adverting"] = '<div class="alert alert-danger animated bounceIn notify text-center"><span>'.$msj_error.'</span></div>';
              $params_view["questions"] = (string)$form_html->renderForm($values_default);
              return new View("main", $params_view);

            }else{
                // CUANDO SE HA RESPONDIDO LA ENCUESTA SATISFACTORIAMENTE
                  $sql = "EXEC ".$GLOBALS["app"]->DB->dbname.".dbo.RegistrarRespuestasUsuarios";

                  $parameters = array();
                  $responses['CodigoCaso'] = intval($values_default['codigoCaso']);
                  $responses['CodigoCasoAleatorio'] = $code;
                  $responses['IdEncuesta'] = intval($id_encuesta);

                  foreach ($responses as $k => $a) {
                      if (is_string($a)) {
                          $parameters[] = sprintf("@%s = '%s'", $k, utf8_decode($Survey->cleanCode($a)));
                      } else {
                          $parameters[] = sprintf("@%s = %s", $k, utf8_decode($Survey->cleanCode($a)));
                      }
                  }

                  $sql = implode(" ", array($sql, implode(",", $parameters)));

                  if ($GLOBALS["app"]->DB->query($sql)) {
                      if ($GLOBALS["app"]->request->http_host != 'localhost') {
                          //$logger->saveAccessLog($survey_codes['CodigoCasoReal'], $CodigoURL, 'El usuario completo la encuesta satisfactoriamente.', 'MS');
                      }

                      $title = $Survey->egl_Texto;
                      $title = (!empty($title)) ? $title : 'Encuesta';
                      return new View("success",array(
                          "title" => $title,
                          "logo" => "YPF.jpg",
                          "message" => "Gracias por responder la encuesta, sus comentarios y calificaciones nos ayudan a mejorar nuestros servicio."
                        )
                      );
                  }else{
                    $params_view["adverting"] = '<div class="alert alert-danger animated bounceIn notify text-center"><span> Ocurrió un error al registrar las respuestas
                    por favor intente de nuevo. </span></div>';
                    $params_view["questions"] = (string)$form_html->renderForm($values_default);
                    return new View("main", $params_view);
                  }
            }

          }else{
            $params_view["questions"] = (string)$form_html->renderForm();
            return new View("main", $params_view);
          }

        }

      }

    }else{
      // reportar errores porque no se pasó el codigo por parametro.
      //$logger = new Logger();
      $msj_error = "El c&oacute;digo es incorrecto.";
      $title = $Survey->egl_Texto;
      $title = (!empty($title)) ? $title : 'Encuesta';
      //$logger->saveAccessLog(null, null, 'NO HAY CODIGO.');
      return new View("error",array(
          "title" => $title,
          "logo" => "YPF.jpg",
          "message" => "$msj_error"
        )
      );
    }

  }


  public function desuscribe(){

      $code = (isset($GLOBALS["app"]->request->params->cod)) ? $GLOBALS["app"]->request->params->cod : null ;
      if( ($code && (strlen($code) != 13)) || empty($code) ){
        $title = $Survey->egl_Texto;
        $title = (!empty($title)) ? $title : 'Encuesta';
        return new View("error",array(
            "title" => $title,
            "logo" => "YPF.jpg",
            "message" => "El c&oacute;digo es incorrecto."
          )
        );
      }else{
        $title = $Survey->egl_Texto;
        $title = (!empty($title)) ? $title : 'Encuesta';
        $query = "exec [Epiron3ArgentinaGestion].[MC].[Blacklist_i] '" .  $GLOBALS["app"]->request->params->cod . "'";
        $result = mssql_query($query) or die("error: " . mssql_get_last_message());
        if ($result) {
          return new View("error",array(
              "title" => $title,
              "logo" => "YPF.jpg",
              "message" => "Se ha desuscrito correctamente"
            )
          );
        } else {
          return new View("error",array(
              "title" => $title,
              "logo" => "YPF.jpg",
              "message" => "No se pudo suscribir"
            )
          );
        }
      }

  }


  public function test(){
    return new View("test");
  }


}


?>
