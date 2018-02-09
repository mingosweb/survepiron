<?php

  namespace Base;

  class Request
  {

    // array con el listado de sistemas operativos validos de la cabecera User-agent
    protected static  $OS_VALIDS = array(
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows nt 10.0/i' => 'Windows 10', // Incluido el navegador de windows 10
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i' => 'Mac OS 9',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/iphone/i' => 'iPhone',
        '/ipod/i' => 'iPod',
        '/ipad/i' => 'iPad',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/webos/i' => 'Mobile',
    );
    // array con el listado de navegadores validos de la cabecera User-agent
    protected static  $BROWSERS_VALID = array('/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/safari/i' => 'Safari',
        '/chrome/i' => 'Chrome',
        '/opera/i' => 'Opera',
        '/netscape/i' => 'Netscape',
        '/maxthon/i' => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i' => 'Handheld Browser'
    );
    // array que contendrá los campos dinámicos de la clase
    protected $headers = array(
      "ip" => "",
      "operating_system" => "",
      "browser" => "",
      "user_agent" => "",
      "query_string" => "",
      "method" => "",
      "server_name" => "",
      "uri" => "",
      "route" => ""
    );

    function __construct($headers = null){

      /*
      SE ALMACENAN LOS CAMPOS DE UN REQUEST EN EL ARRAY $headers  PARA LUEGO OBTENERLOS COMO PROPIEDADES DE ESTA CLASE AL UTILIZAR
      LAS FUNCIONES MAGICAS __get y __set
      */

      if($headers){
        $this->headers["ip"] = (isset($headers["X-Forwarded-For"])) ? $headers["X-Forwarded-For"] : $_SERVER["REMOTE_ADDR"];
        $this->headers["operating_system"] = $this->getOS();
        $this->headers["browser"] = $this->getBrowser();
        $this->headers["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
        $this->headers["query_string"] = $_SERVER["QUERY_STRING"];
        $this->headers["method"] = $_SERVER["REQUEST_METHOD"];
        $this->headers["params"] = (object)$_GET;
        $this->headers["server_name"] = $_SERVER["SERVER_NAME"];
        $this->headers['http_host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER["SERVER_NAME"];
        $this->headers["uri"] = $_SERVER["REQUEST_URI"];
        $this->headers["route"] = (!empty($_GET["url"])) ? "/".$_GET["url"] : "/";
        $this->headers["remote_addr"] = (isset($headers["REMOTE_ADDR"])) ? $headers["REMOTE_ADDR"] : null ;
        $this->headers["forwarded-for"] = (isset($headers["X-Forwarded-For"])) ? $headers["X-Forwarded-For"] : null ;
      }

    }

    // metodo para asignar valores a los campos dinamicos de la clase
    function __set($key,$value){
      if(property_exists($this,$key)){
        $this->{$key} = $value;
      }else{
        if(in_array($key,array_keys($this->headers))){
          $this->headers[$key] = $value;
        }
      }
    }

    // metodo para obtener los valores de los campos dinamicos de la clas
    function __get($key){
      if(in_array($key,array_keys($this->headers))){
        return $this->headers[$key];
      }else{
        if(property_exists($this,$key)){
          return $this->{$key};
        }else{
          return "none";
        }
      }
    }

    // metodo para obener un objeto de tipo Request
    public static function Request(){
      return new self(apache_request_headers());
    }

    // function para obtener el Sistema operativo
    protected function getOS(){
        foreach (self::$OS_VALIDS as $regex => $value) {
            if (preg_match($regex, $_SERVER["HTTP_USER_AGENT"])) {
                return $value;
            }
        }
    }

    // funcion para obtener el navegador
    protected function getBrowser(){
        foreach (self::$BROWSERS_VALID as $regex => $value) {
            if (preg_match($regex, $_SERVER["HTTP_USER_AGENT"])) {
                return $value;
            }
        }
    }

    // metodo para obtener el user-agent en forma de JSON
    public function useragent(){
        return json_encode($_SERVER['HTTP_USER_AGENT']);
    }

    // metodo para obtener informacion geografia de una dirección IP
    public function ipInfo($ip = NULL, $purpose = "location", $deep_detect = TRUE) {


        $output = NULL;
        // Obtenemos la dirección IP real si existe o la del REMOTO
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $this->remote_addr;
            if ($deep_detect) {
                if (filter_var(@$this->forwarded_for, FILTER_VALIDATE_IP))
                    $ip = $this->forwarded_for;
                if (filter_var(@$this->http_client_ip, FILTER_VALIDATE_IP))
                    $ip = $this->http_client_ip;
            }
        }

        // reemplazar espacios vacios con NULL
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));

        $support = array(
          "country",
          "countrycode",
          "state",
          "region",
          "city",
          "location",
          "address"
        );

        // Lista de continentes
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antartica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );

        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            // validamos de que el codigo del pais sea de dos cifras
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                            "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

  }

?>
