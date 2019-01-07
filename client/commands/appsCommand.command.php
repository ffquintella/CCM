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
class appsCommand extends base {

    private $form;


    private $initialized = false;

    /**
     * @var \cmdEngine\formsEngine
     */
    private $form_engine;


    /**
     * Mostra as listas cadastradas
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
            $this->writeln(APPS, ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'apps?format=json',array(200));

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
            $this->form = __DIR__ . '/../forms/apps_'.LANGUAGE.'.yaml';
            $this->form_engine = new \cmdEngine\formsEngine($this , $this->form);
        }
    }

    /**
     * Adiciona uma app
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;
        }else{
            $params['input::appName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'apps/'.$params['input::appName'].'?format=json',array(404,200));

            if($html_resp['code'] != 404) {
                $this->writeln(APP_EXISTS, \ConsoleKit\Colors::RED);
                die(2);
            }

        }

        $this->initialize();

        $resp = $this->form_engine->getData($params);

       // var_dump($resp); exit;

        $json = '{';

        $json .= '"environments":'.json_encode($resp['cmbbox::environments']);

        if(array_key_exists('input::key', $resp) &&  $resp['input::key'] != ''){
            $json .= ',"key":'.json_encode($resp['input::key']);
        }

        $json .= '}';


        $html_resp = curlHelper::execute($this, 'apps/'.$resp['input::appName'].'?format=json',array(201),'PUT',$json);

        if($html_resp['code'] == 201) {
            $this->writeln('OK ...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

    /**
     * Reads an app
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $params['input::appName'] = $dialog->ask(APP_NAME);
        }else{
            $params['input::appName'] = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'apps/'.$params['input::appName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(APP_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response']);

        $params['cmbbox::environments'] = $obj->environments;

        $params['input::key'] = $obj->key;

        $params['label::owner'] = $obj->owner;

        $params['label::creationT'] = gmdate("d/m/Y H:i:s", $obj->creationT);


        $this->form_engine->printData($params);


    }

    /**
     * Edita uma app
     *
     * @param array $args
     * @param array $opts
     */
    public function executeEdit(array $args, array $opts = array())
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $params['input::appName'] = $dialog->ask(APP_NAME);
        }else{
            $params['input::appName'] = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'apps/'.$params['input::appName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(APP_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response']);

        //var_dump($obj);

        $params['cmbbox::environments'] = $obj->environments;

        $params['input::key'] = $obj->key;

        $params['label::owner'] = $obj->owner;

        $params['label::creationT'] = gmdate("d/m/Y H:i:s", $obj->creationT);

        $resp = $this->form_engine->editData($params,  array('input::appName'));

        $json = '{';

        $json .= '"environments":'.json_encode($resp['cmbbox::environments']);

        if($resp['input::key'] != ''){
            $json .= ',"key":'.json_encode($resp['input::key']);
        }

        $json .= '}';

        $html_resp = curlHelper::execute($this, 'apps/'.$resp['input::appName'].'?format=json',array(200),'POST',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }

    /**
     * Apaga uma app
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $params['input::appName'] = $dialog->ask(APP_NAME);
        }else{
            $params['input::appName'] = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'apps/'.$params['input::appName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln(APP_DOESNT_EXISTS, \ConsoleKit\Colors::RED);
            die(2);
        }

        $this->initialize();


        $json = '{';

        $json .= '}';


        $html_resp = curlHelper::execute($this, 'apps/'.$params['input::appName'].'?format=json',array(200),'DELETE',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('OK...', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Error! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

}

