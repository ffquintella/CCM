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
class credentialsCommand extends base {

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
            $this->writeln("CREDENTIALS", ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'credentials?format=json',array(200));

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
            $this->form = __DIR__.'/../forms/credentials.yaml';
            $this->form_engine = new \cmdEngine\formsEngine($this , $this->form);
        }
    }

    /**
     * Adiciona uma credencial
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;
        }else{
            $params['input::credName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'credentials/'.$params['input::credName'].'?format=json',array(404,200));

            if($html_resp['code'] != 404) {
                $this->writeln('Esta credencial já existe!', \ConsoleKit\Colors::RED);
                die(2);
            }

        }

        $this->initialize();

        $resp = $this->form_engine->getData($params);

        //var_dump($resp); exit;

        $json = '{';

        $json .= '"app":"'.$resp['input::appName'].'",';
        $json .= '"type":"'.$resp['cmbbox::type'][0].'",';

        if( $resp['cmbbox::type'][0] == 'local') {
            $json .= '"values":{';
            $first1 = true;
            foreach ($resp['minput::values'] as $env => $val) {

                if (!$first1) $json .= ',';
                $json .= '"' . $env . '":"'.$val.'"';
                $first1 = false;

            }
        }

        if( $resp['cmbbox::type'][0] == 'vault') {
            $json .= '"vaultIds":{';
            $first1 = true;
            foreach ($resp['minput::vaultIds'] as $env => $val) {

                if (!$first1) $json .= ',';
                $json .= '"' . $env . '":"'.$val.'"';
                $first1 = false;

            }
        }

        $json .= '}}';

        //var_dump($json);
        //var_dump($params['input::credName']);
        //exit;

        $html_resp = curlHelper::execute($this, 'credentials/'.$resp['input::credName'].'?format=json',array(201),'PUT',$json);

        if($html_resp['code'] == 201) {
            $this->writeln('Criação OK!', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Erro na criação! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }



    }

    /**
     * Le uma credencial
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){
            $params['input::credName'] = $dialog->ask('Entre com o nome da credencial:');
        }else{
            $params['input::credName'] = $args[1];
        }


        $html_resp = curlHelper::execute($this, 'credentials/'.$params['input::credName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln('Esta credencial não existe!', \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);

        $params['input::appName'] = $obj['app'];

        $params['cmbbox::type'] = array($obj['type']);


        if($obj['type'] == 'vault') $params['minput::values'] = null;
        else{
            $params['minput::values'] = $obj['values'];
        }

        if($obj['type'] == 'local') $params['minput::vaultIds'] = null;
        else{
            $params['minput::vaultIds'] = $obj['vaultIds'];
        }

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
            $params['input::credName'] = $dialog->ask('Entre com o nome da credencial:');
        }else{
            $params['input::credName'] = $args[1];
        }

        $html_resp = curlHelper::execute($this, 'credentials/'.$params['input::credName'].'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln('Esta credencial não existe!', \ConsoleKit\Colors::RED);
            die(2);
        }

        $obj = json_decode($html_resp['response'], true);

        $params['input::appName'] = $obj['app'];

        $params['cmbbox::type'] = array($obj['type']);


        if($obj['type'] == 'vault') $params['minput::values'] = null;
        else{
            $params['minput::values'] = $obj['values'];
        }

        if($obj['type'] == 'local') $params['minput::vaultIds'] = null;
        else {
            $params['minput::vaultIds'] = $obj['vaultIds'];
        }

        $resp = $this->form_engine->editData($params,  array('input::credName', 'input::appName', 'cmbbox::type'));

        $json = '{';

        $json .= '"app":"'.$resp['input::appName'].'",';

        if( $resp['cmbbox::type'][0] == 'local') {
            $json .= '"values":{';
            $first1 = true;
            foreach ($resp['minput::values'] as $env => $val) {

                if (!$first1) $json .= ',';
                $json .= '"' . $env . '":"'.$val.'"';
                $first1 = false;

            }
        }

        if( $resp['cmbbox::type'][0] == 'vault') {
            $json .= '"vaultIds":{';
            $first1 = true;
            foreach ($resp['minput::vaultIds'] as $env => $val) {

                if (!$first1) $json .= ',';
                $json .= '"' . $env . '":"'.$val.'"';
                $first1 = false;

            }
        }

        $json .= '}}';


        $html_resp = curlHelper::execute($this, 'credentials/'.$resp['input::credName'].'?format=json',array(200),'POST',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('Salvo OK!', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Erro na edição! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }

    /**
     * Apaga uma credencial
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->initialize();
        if(count($args) !=  2){

            $name = $dialog->ask('Entre com o nome da credencial:');
        }else{
            $name = $args[1];

        }

        $html_resp = curlHelper::execute($this, 'credentials/'.$name.'?format=json',array(404,200));

        if($html_resp['code'] == 404) {
            $this->writeln('Esta credencial não existe!', \ConsoleKit\Colors::RED);
            die(2);
        }

        $this->initialize();


        $json = '{';

        $json .= '}';


        $html_resp = curlHelper::execute($this, 'credentials/'.$name.'?format=json',array(200),'DELETE',$json);

        if($html_resp['code'] == 200) {
            $this->writeln('Deleção OK!', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Erro na deleção! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }

}

