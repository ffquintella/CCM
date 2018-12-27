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
    '<html><body>
     <img app="cid:Logo-ESI" alt="Logo ESI" width="157" height="52" /><br><br>
     <font size=+1><b>%titulo%</b></font>
     <table rules="all" style="border-color: #666;" cellpadding="10">
     <tr style="background: #eee;"><td><strong>Requisitante:</strong> </td><td> %requisitante% </td></tr>
     <tr><td><strong>Email:</strong> </td><td> %email% </td></tr>
     <tr><td><strong>Tipo de aviso:</strong> </td><td> %tipo% </td></tr>
     <tr><td><strong>Mensagem:</strong> </td><td> %mensagem% </td></tr>
     </table>
     </body></html>',
    "AVISO - ESI"
);

if (gethostname() == PRODServer)
    $mt->addImg("/srv/www/esi/images/Logos/Logo-ESI.png", "Logo-ESI");
else $mt->addImg("/tmp/Logo-ESI.png", "Logo-ESI");

addT($mt);