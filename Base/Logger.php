<?php

namespace Base;

class Logger{


  public function saveAccessLog($caseCode, $randomCode, $message, $logAccessType = 'ME') {

      $_ip = $GLOBALS["app"]->request->ip;
      $_os = $GLOBALS["app"]->request->operating_system;
      $_browser = $GLOBALS["app"]->request->browser;

      if ($GLOBALS["app"]->request->server_name == 'localhost') {

          $ipInfo = $GLOBALS["app"]->request->ipInfo($_ip, "Location");
          $sql = "EXEC ".$GLOBALS["app"]->DB->dbname.".dbo.usp_log_access_messages_add ";

          $parameters = array("@logAccessType='" . $logAccessType . "'",
              "@ip='" . $_ip . "'",
              "@operatingSystem='" . $_os . "'",
              "@browser='" . $_browser . "'",
              "@message='" . $message . "'"
          );

          $parameters[] = is_null($caseCode) ? "@caseCode='0'" : "@caseCode='" . $caseCode . "'";
          $parameters[] = is_null($randomCode) ? "@randomCode='0'" : "@randomCode='" . $randomCode . "'";
          $parameters[] = !is_null($ipInfo['country']) && $ipInfo['country'] != '' ? "@country='" . $ipInfo['country'] . "'" : "@country='No Disponible ñeje'";
          $parameters[] = !is_null($ipInfo['state']) && $ipInfo['state'] != '' ? "@state='" . $ipInfo['state'] . "'" : "@state='No Disponible'";
          $parameters[] = !is_null($ipInfo['city']) && $ipInfo['city'] != '' ? "@city='" . $ipInfo['city'] . "'" : "@city='No Disponible'";

          $sql .= implode(",", $parameters);

          $GLOBALS["app"]->DB->query($sql);

      }

  }



}

?>
