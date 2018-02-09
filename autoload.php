<?php

spl_autoload_register(function($class_name){
    $dir_base = "";
    $array_class_name = explode("\\",$class_name);
    $path_file_class = "";
    if(count($array_class_name) > 1){
      $path_file_class = implode("/",array( $dir_base, implode("/",$array_class_name).".php" ));
    }else{
      $path_file_class = implode("/",array( $dir_base, $class_name.".php" ));
    }
    if(file_exists(__DIR__."/".$path_file_class)){
          include $path_file_class;
    }
});

?>
