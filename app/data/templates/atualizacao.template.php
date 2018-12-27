<?php
/**
 * Created by Felipe F Quintella
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 18:57
 * To change this template use File | Settings | File Templates.
 */

require_once ROOT . "/data/templates/mailTemplate.list.php";
require_once ROOT . "/data/templates/mailTemplate.class.php";

$mt = new mailTemplate(
    "aviso",
    '<html>
  <head>
    <meta content="text/html; charset=windows-1252" http-equiv="content-type">
  </head>
  <body> <img app="cid:Logo-ESI" alt="Logo ESI" height="52" width="157"><br>
    <br>
    <font size="+1"><b>Aviso de atualização emitido em: %data%</b></font>
    <table rules="all" style="border-color: #666666; width: 459px; height: 160px;"
      cellpadding="10">
      <tbody>
        <tr style="background: #eee;">
          <td colspan="2" rowspan="1">%titulo% </td>
        </tr>
        <tr>
          <td><strong>Ação necessária:</strong> </td>
          <td> %mensagem% </td>
        </tr>
        <tr>
          <td><strong>Servidores:</strong> </td>
          <td> %servidores% </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>',
    "Atualização de servidores - ESI"
);

if (gethostname() == PRODServer)
    $mt->addImg("/srv/www/esi/images/Logos/Logo-ESI.png", "Logo-ESI");
else $mt->addImg("/tmp/Logo-ESI.png", "Logo-ESI");

addT($mt);