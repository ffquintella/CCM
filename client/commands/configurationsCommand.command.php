<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 11/1/17
 * Time: 10:48
 */


require_once 'base.command.php';
/**
 * Gerencia as listas cadastradas
 */
class configurationsCommand extends base {

    private $form;


    private $initialized = false;

    /**
     * @var \cmdEngine\formsEngine
     */
    private $form_engine;


    /**
     * Mostra as configurações cadastradas
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
            $this->writeln(CONFIGURATIONS, ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'configurations?format=json',array(200));

            $obj = json_decode($resp['response'], true);

            if(is_string($obj)){
                die(0);
            }

            $this->form_engine->printList($obj);


        }
        die(0);

    }

    private function initialize(){
        if(! $this->initialized){
            $this->form = __DIR__.'/../forms/configurations.yaml';
            $this->form_engine = new \cmdEngine\formsEngine($this , $this->form);
        }
    }

    /**
     * Adiciona uma configuração
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;
        }else{
            $params['input::confName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'configurations/'.$params['input::confName'].'?format=json',array(404,200));

            if($html_resp['code'] != 404) {
                $this->writeln(CONFIGURATION_EXISTS, \ConsoleKit\Colors::RED);
                die(2);
            }

        }

        $this->initialize();

        $resp = $this->form_engine->getData($params);

        //var_dump($resp); exit;

        $json = '{';

        $json .= '"app":"'.$resp['input::appName'].'",';


        $json .= '"values":{';
        $first1 = true;
        foreach ($resp['minput::values'] as $env => $val) {

            if (!$first1) $json .= ',';
            $json .= '"' . $env . '":"'.$val.'"';
            $first1 = false;

        }

        $json .= '}}';


        $html_resp = curlHelper::execute($this, 'configurations/'.$resp['input::confName'].'?format=json',array(201),'PUT',$json);

        if($html_resp['code'] == 201) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }



    }

    /**
     * Le uma configuração
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){
            $params['input::confName'] = $dialog->ask(CONFIGURATION_NAME);
        }else{
            $params['input::confName'] = $args[1];
        }


        $html_resp = curlHelper::execute($this, 'configurations/'.$params['input::confName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(CONFIGURATION_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);

        $params['input::appName'] = $obj['app'];

        $params['minput::values'] = $obj['values'];

        $this->form_engine->printData($params);

    }

    /**
     * Edita uma configuração
     *
     * @param array $args
     * @param array $opts
     */
    public function executeEdit(array $args, array $opts = array())
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){
            $params['input::confName'] = $dialog->ask(CONFIGURATION_NAME);
        }else{
            $params['input::confName'] = $args[1];
        }

        $html_resp = curlHelper::execute($this, 'configurations/'.$params['input::confName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(CONFIGURATION_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);

        $params['input::appName'] = $obj['app'];
        $params['minput::values'] = $obj['values'];


        //var_dump($params);
        $resp = $this->form_engine->editData($params,  array('input::confName', 'input::appName'));

        $json = '{';

        $json .= '"app":"'.$resp['input::appName'].'",';


        $json .= '"values":{';
        $first1 = true;
        foreach ($resp['minput::values'] as $env => $val) {

            if (!$first1) $json .= ',';
            $json .= '"' . $env . '":"'.$val.'"';
            $first1 = false;

        }

        $json .= '}}';



        $html_resp = curlHelper::execute($this, 'configurations/'.$resp['input::confName'].'?format=json',array(200),'POST',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }

    /**
     * Apaga uma configuração
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $name = $dialog->ask(CONFIGURATION_NAME);
        }else{
            $name = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'configurations/'.$name.'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(CONFIGURATION_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $this->initialize();


        $json = '{';

        $json .= '}';


        $html_resp = curlHelper::execute($this, 'configurations/'.$name.'?format=json',array(200),'DELETE',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

}

