<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 2/1/17
 * Time: 16:48
 */


require_once 'base.command.php';
/**
 * Gerencia as listas cadastradas
 */
class serversCommand extends base {

    private $form;


    private $initialized = false;

    /**
     * @var \cmdEngine\formsEngine
     */
    private $form_engine;


    /**
     * Shows the servers avaliable
     *
     * @param array $args
     * @param array $opts
     */
    public function execute(array $args, array $opts = array()) {

        if(!loginManager::isAuthenticated()){
            $this->executeLogin();
        }

        $this->initialize();

        $list = true ;

        if(count($args) > 0){
            switch ($args[0]){
               case 'add':
                    $this->executeAdd($args,$opts);
                    break;
                case 'read':
                    $this->executeRead($args,$opts);
                    $list = false;
                    break;
                case 'edit':
                    $this->executeEdit($args,$opts);
                    $list = false;
                    break;
                case 'delete':
                    $this->executeDelete($args,$opts);
                    $list = false;
                    break;
                default:
                    $list = true;
                    break;
            }
        }


        if($list) {
            $this->writeln("---");
            $this->writeln(SERVERS, ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'servers?format=json',array(200));

            $obj = json_decode($resp['response'], true);

            //var_dump($obj); exit;

            $servers = array();

            if(is_array($obj)) {

                foreach ($obj as $srv) {

                    $servers[] = $srv['name'];

                }
            }

            if(is_string($servers)){
                die(0);
            }

            $this->form_engine->printList($servers);


        }
        die(0);

    }

    private function initialize(){
        if(! $this->initialized){
            $this->form = __DIR__ . '/../forms/servers_'.LANGUAGE.'.yaml';
            $this->form_engine = new \cmdEngine\formsEngine($this , $this->form);
        }
    }

    /**
     * Adds a server
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;
        }else{
            $params['input::srvName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'servers/'.$params['input::srvName'].'?format=json',array(404,200));

            if($html_resp['code'] != 404) {
                $this->writeln(SERVER_ALREADY_EXISTS, \ConsoleKit\Colors::RED);
                die(2);
            }

        }

        $this->initialize();

        $resp = $this->form_engine->getData($params);

        //var_dump($resp); exit;


        $ambresp = $resp['frmfrm::apps'];

        //var_dump($ambresp); exit;

        $json = '{';

        $json .= '"fqdn":"'.$resp['input::fqdn'].'",';

        $json .= '"assignments":{';
        $first1 = true;
        foreach ($ambresp['cmbbox::apps'] as $key => $app){

            if(!$first1) $json .= ',';
            $json .= '"'.$app.'":[';
            $first2 = true;
            foreach ($ambresp['sub'][$key] as $subKey => $environments){
                foreach ($environments as $int => $environment) {
                    if (!$first2) $json .= ',';
                    $json .= '"' . $environment . '"';
                    $first2 = false;
                }
            }
            $json .= ']';
            $first1 = false;

        }

        $json .= '}}';

        $html_resp = curlHelper::execute($this, 'servers/'.$resp['input::srvName'].'?format=json',array(201),'PUT',$json);

        if($html_resp['code'] == 201) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

    /**
     * Reads a server data
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){
            $params['input::srvName'] = $dialog->ask(SERVER_NAME);
        }else{
            $params['input::srvName'] = $args[1];
        }


        $html_resp = curlHelper::execute($this, 'servers/'.$params['input::srvName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(SERVER_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);
        $params['input::fqdn'] = $obj['fqdn'];


        // Converting format

        $keys = array();
        $values = array();
        foreach ($obj['assignments'] as $key => $value){
            $keys[] = $key;
            $values[] = $value;
        }

        $infrm['cmbbox::apps'] = $keys;

        $subValues = array();
        foreach ($values as $a => $b){
            $subValues[] = array('cmbbox::ambientes' => $b);
        }

        //var_dump($subValues);

        $infrm['sub'] = $subValues;

        $params['frmfrm::apps'] = $infrm;

        $this->form_engine->printData($params);

    }

    /**
     * Edita um servidor
     *
     * @param array $args
     * @param array $opts
     */
    public function executeEdit(array $args, array $opts = array())
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){
            $params['input::srvName'] = $dialog->ask(SERVER_NAME);
        }else{
            $params['input::srvName'] = $args[1];
        }

        $html_resp = curlHelper::execute($this, 'servers/'.$params['input::srvName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(SERVER_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);

        $params['input::fqdn'] = $obj['fqdn'];


        // Converting format
        $keys = array();
        $values = array();
        foreach ($obj['assignments'] as $key => $value){
            $keys[] = $key;
            $values[] = $value;
        }

        $infrm['cmbbox::apps'] = $keys;

        $subValues = array();
        foreach ($values as $a => $b){
            $subValues[] = array('cmbbox::ambientes' => $b);
        }


        $infrm['sub'] = $subValues;

        $params['frmfrm::apps'] = $infrm;

        $resp = $this->form_engine->editData($params,  array('input::srvName'));


        $ambresp = $resp['frmfrm::apps'];


        $json = '{';

        $json .= '"fqdn":"'.$resp['input::fqdn'].'",';

        $json .= '"assignments":{';
        $first1 = true;
        foreach ($ambresp['cmbbox::apps'] as $key => $app){

            if(!$first1) $json .= ',';
            $json .= '"'.$app.'":[';
            $first2 = true;
            foreach ($ambresp['sub'][$key] as $subKey => $environments){
                foreach ($environments as $int => $environment) {
                    if (!$first2) $json .= ',';
                    $json .= '"' . $environment . '"';
                    $first2 = false;
                }
            }
            $json .= ']';
            $first1 = false;

        }

        $json .= '}}';


        $html_resp = curlHelper::execute($this, 'servers/'.$resp['input::srvName'].'?format=json',array(200),'POST',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK ...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }

    /**
     * Apaga um servidor
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $name = $dialog->ask(SERVER_NAME);
        }else{
            $name = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'servers/'.$name.'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(SERVER_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $this->initialize();


        $json = '{';

        $json .= '}';


        $html_resp = curlHelper::execute($this, 'servers/'.$name.'?format=json',array(200),'DELETE',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK ...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

}

