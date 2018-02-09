<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>hola</title>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  </head>
  <body>
    <script type="text/javascript">

      $(window).ready(function(){

        collector = [];

        // Array con preguntas
        questions = [

          {

            id: 1,
            number: 1,
            description: "Pregunta 1",
            answers : [
              {number: 1, label: "Label", value: "1", type: "radio", active_question: 2},
              {number: 2, label: "Label 2", value: "2", type: "radio", active_question: 3}
            ],
            required: true,
            question_parent: null
          },

          {
            id: 2,
            number: 2,
            description: "Pregunta 2",
            answers : [
              {number: 1, label: "Si", value: "si", type: "radio", active_question: null},
              {number: 2, label: "No", value: "no", type: "radio", active_question: null}
            ],
            required: true,
            question_parent: 1
          },

          {
            id: 3,
            number: 3,
            description: "Pregunta 3",
            answers : [
              {number: 1, label: "Si tambien", value: "si", type: "radio", active_question: null},
              {number: 2, label: "No tambien", value: "no", type: "radio", active_question: null}
            ],
            required: true,
            question_parent: 1
          }

        ];

        $form = $("<form></form>");

        questions.forEach(function(question){
            $question = $("<div id='question-"+question.id+"'><p>"+question.number+") "+question.description+"</p></div>");
            if(question.answers){
              $answers = $("<div></div>");
              question.answers.forEach(function(answ){
                console.log(answ);
                switch(answ.type){
                  case "radio":
                    $answer = $("<input type='radio' name='answer-"+question.id+"' value='"+answ.value+"' /><label>"+answ.label+"</label>");
                  break;
                  case "checkbox":
                  $answer = $("<input type='checkbox' name='answer-"+question.id+"' value='"+answ.value+"' /><label>"+answ.label+"</label>");
                  break;
                }
                $answer.data("question_active",answ.active_question);
                $answer.on("click",function(){
                  var array_ques = [];
                  var myName = $(this).attr("name");
                  console.log($(this).parent().find("input").not("[name*='"+myName+"']"));
                  $(this).parent().find("input").not("[name*='"+myName+"']").each(function(){
                    array_ques.push($(this).data("question_active"));
                    array_ques.forEach(function(id){
                      $("#question-"+id).css({"display": "none"});
                    });
                  });
                  $("#question-"+answ.active_question).css({"display":"block"});
                });
                $answers.append($answer);
              });
              $question.append($answers);
            }
            if(question.required){
              $answers.eq(0).find("input").prop("required",true);
            }
            if(question.question_parent){
              $question.css({"display": "none"});
            }
            $form.append($question);
        });

        $("body").append($form);


      });
    </script>
  </body>
</html>
