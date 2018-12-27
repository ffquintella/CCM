<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 04/01/17
 * Time: 11:19
 */

include_once "../../client/includes.php";
include_once "../../client/tools/curlHelper.php";

$params['input::srvName'] = "teste2";


$GLOBALS['SESSION_TOKEN'] = 'tNO6pw6daEqgIxCahw2iohjwQRA9aQaIh/jwW+XfaABLyLOwbC6FOwLNwJWw';

$console = new \ConsoleKit\Console();
$cmd = new base($console);

$html_resp = curlHelper::execute($cmd, 'servers/'.$params['input::srvName'].'?format=json',array(404,200));

/*
if($html_resp['code'] != 200) {
    $this->writeln('Este servidor nao existe!', \ConsoleKit\Colors::RED);
    die(2);
}*/

$obj = json_decode($html_resp['response'], true);

$form = '../../client/forms/servers.yaml';
$form_engine = new \cmdEngine\formsEngine($cmd , $form);

$resp = $form_engine->getData($params);

$params['cmbbox::apps'] = $obj['assignments'];
$params['input::fqdn'] = $obj['fqdn'];

//$resp = $form_engine->editData($params,  array('input::srvName'));

/*
$json = '{';

$json .= '"fqdn":"'.$resp['input::fqdn'].'",';

$json .= '"assignments":{';
$first1 = true;
foreach ($resp['cmbbox::apps'] as $key => $value){

    foreach ($value as $app => $environments){
        if(!$first1) $json .= ',';
        $json .= '"'.$app.'":[';
        $first2 = true;
        foreach ($environments as $key => $environment){
            if(!$first2) $json .= ',';
            $json .= '"'.$environment.'"';
            $first2 = false;
        }
        $json .= ']';
        $first1 = false;
    }
}

$json .= '}}';

$html_resp = curlHelper::execute($this, 'servers/'.$resp['input::srvName'].'?format=json',array(201),'PUT',$json);

if($html_resp['code'] == 201) {
    $this->writeln('Criação OK!', \ConsoleKit\Colors::GREEN);
    die(0);
}else{
    $this->writeln('Erro na criação! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
    die(2);
}
*/