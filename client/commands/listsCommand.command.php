<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 17:12
 */

/**
 * Gerencia as listas cadastradas
 */
class listsCommand extends base {

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
            $this->writeln("LISTAS", ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'lists?format=json',array(200));

            $fe = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/list-create.yaml');

            $obj = json_decode($resp['response'], true);

            if(is_array($obj)) $fe->printList($obj);


        }
        die(0);

    }


    /**
     * Apaga uma lista
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;

            $resp = curlHelper::execute($this, 'lists?format=json',array(200));
            $fe = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/list-create.yaml');
            $obj = json_decode($resp['response'], true);

            $this->writeln("---");
            $this->writeln("LISTAS", ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");
            $fe->printList($obj);

            $dialog = new \ConsoleKit\Widgets\Dialog($this->console);

            $params['input::listName'] = $dialog->ask('Entre com o nome da lista a ser apagada:');


        }else{
            $params['input::listName'] = $args[1];



        }

        $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

        if($html_resp['code'] != 200) {
            $this->writeln('Esta lista não existe!', \ConsoleKit\Colors::RED);
            die(2);
        }


        $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(200),'DELETE');

        if($html_resp['code'] == 200) {
            $this->writeln('Deleção OK!', \ConsoleKit\Colors::GREEN);
            //die(0);
        }else{
            $this->writeln('Erro na deleção! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }


    }


    /**
     * Adiciona uma lista
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        if(count($args) !=  2){
            $params = null;
        }else{
            $params['input::listName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

            if($html_resp['code'] != 404) {
                $this->writeln('Esta lista já existe!', \ConsoleKit\Colors::RED);
                die(2);
            }

        }

        $fb = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/list-create.yaml');
        $resp = $fb->getData($params);

        $html_resp = curlHelper::execute($this, 'lists/'.$resp['input::listName'].'?format=json',array(201),'PUT',json_encode($resp['list::list']));

        if($html_resp['code'] == 201) {
            $this->writeln('Criação OK!', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Erro na criação! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }


    /**
     * Le uma lista
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);

        if(count($args) !=  2){

            $params['input::listName'] = $dialog->ask('Entre com o nome da lista:');

            $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

            if($html_resp['code'] != 200) {
                $this->writeln('Esta lista não existe!', \ConsoleKit\Colors::RED);
                die(2);
            }

        }else{

            $params['input::listName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

            if($html_resp['code'] != 200) {
                $this->writeln('Esta lista não existe!', \ConsoleKit\Colors::RED);
                die(2);
            }
        }

        $obj = json_decode($html_resp['response']);

        $fe = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/list-create.yaml');

        $params['list::list'] = $obj;


        $fe->printData($params);

    }


    /**
     * Le uma lista
     *
     * @param array $args
     * @param array $opts
     */
    public function executeEdit(array $args, array $opts = array())
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);

        if(count($args) !=  2){

            $params['input::listName'] = $dialog->ask('Entre com o nome da lista:');

            $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

            if($html_resp['code'] != 200) {
                $this->writeln('Esta lista não existe!', \ConsoleKit\Colors::RED);
                die(2);
            }

        }else{

            $params['input::listName'] = $args[1];

            $html_resp = curlHelper::execute($this, 'lists/'.$params['input::listName'].'?format=json',array(404,200));

            if($html_resp['code'] != 200) {
                $this->writeln('Esta lista não existe!', \ConsoleKit\Colors::RED);
                die(2);
            }
        }

        $obj = json_decode($html_resp['response']);

        $fe = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/list-create.yaml');

        $params['list::list'] = $obj;

        $resp = $fe->editData($params, array('input::listName'));

        $html_resp = curlHelper::execute($this, 'lists/'.$resp['input::listName'].'?format=json',array(200),'POST',json_encode($resp['list::list']));

        if($html_resp['code'] == 200) {
            $this->writeln('Salvo OK!', \ConsoleKit\Colors::GREEN);
            die(0);
        }else{
            $this->writeln('Erro na edição! cod:'. $html_resp['code'], \ConsoleKit\Colors::RED);
            die(2);
        }

    }

}

