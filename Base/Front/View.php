<?php

namespace Base\Front;

class View{

  private $filename;
  private $data = array();
  private $tag_render = "{{ }}";
  private $ext = ".php";


  function __construct($filename="",$data=null){
    $this->filename = $filename;
    if($data){
      foreach ($data as $key => $value) {
        $this->set($key,$value);
      }
    }
    $this->render();
  }


  public function loadHeaders(){
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    //header('Content-Type: text/html; charset=iso-8859-1');
  }


  public function filename(){
    return $this->filename;
  }


  public function filenameFull(){
    return DIRVIEWS.$this->filename.$this->ext;
  }


  public function changeRenderTag($str_open,$str_close){
    if($str_open && $str_close){
      $this->tag_render = "$str_open $str_close";
    }else{
      $this->tag_render = $this->tag_render;
    }
  }


  public function changeExtRender($value = ".php"){
    $this->ext = $value;
  }


  public function data(){
    return $this->data;
  }


  public function set($key,$value=null){
    $this->data[$key] = $value;
  }


  public function render(){
    $this->loadHeaders();
    if (!file_exists($this->filenamefull())){
        return "Error loading template file (".$this->filenameFull().").";
    }
    $file = $this->filenameFull();
    $output = file_get_contents($file);
    preg_match_all("/{{.+}}+/",$output,$matches);
    $array_matches = $matches[0];
    foreach($array_matches as $match ){
      $var_render = trim($match,"{{..}}");
      $data_value = (isset($this->data[$var_render])) ? $this->data[$var_render] : "" ;
      $output = str_replace($match, $data_value, $output);
    }
    echo $output;
  }


  static public function merge($templates, $separator = "n") {
      $output = "";
      foreach ($templates as $template) {
          $content = (get_class($template) !== "Template")
              ? "Error, incorrect type - expected Template."
              : $template->output();
          $output .= $content . $separator;
      }
      return $output;
  }

  /*public function inspect_tree_render($content){
    //var_dump("/@fillArea[(][\\\"|\\\'][\w][\\\"|\\\'][\\ n|\s+][\w+][\\ n|\s+]@fillArea$/m");
    //var_dump(preg_match(Template::REGEX_EXTEND,$content,$matches));
    $content = file_get_contents($this->filenameFull());
    $content = addcslashes($content,"\"\'\n");
    echo $content."<br>";
    //echo "<br>Patron: /@fillArea[(][\\\"|\'][\w]+[\\\"|\'][)][\\n+|\s+]\w+[\\n+|\s+]@fillArea/m";
    var_dump(preg_match("/@fillArea[(][\\\"|\'][\w]+[\\\"|\'][)](\n+|\s+)\w+(\n+|\s+)@fillArea/m",trim($content)));
  }*/

}

?>
