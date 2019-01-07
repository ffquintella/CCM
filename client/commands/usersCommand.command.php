<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 12/12/16
 * Time: 20:44
 */

/**
 * Lista todos os usuários  cadastradas
 */
class usersCommand extends base {
    /**
     * Lista os usuários cadastrados
     *
     * @param array $args
     * @param array $opts
     */
    public function execute(array $args, array $opts = array()) {

        if(!loginManager::isAuthenticated()){
            $this->executeLogin();
        }

        //var_dump($args);
        //var_dump($opts);

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
            $this->writeln(USERS, ConsoleKit\Colors::BLUE);
            $this->writeln("-----------------");

            $resp = curlHelper::execute($this, 'accounts?format=json',array(200));

            $fe = new \cmdEngine\formsEngine($this , __DIR__ . '/../forms/apps_pt_br.yaml');

            $obj = json_decode($resp['response'], false);

            $users = array();

            foreach ($obj as $act ){
                $users[] = $act->name;
            }

            asort($users);

            if(is_array($obj)) $fe->printList($users);
        }

    }

    /**
     * Adiciona um usuário
     *
     * @param array $args
     * @param array $opts
     */
    public function executeAdd(array $args, array $opts = array())
    {

        $this->writeln("---");
        $this->writeln(CREATING_A_NEW_USER, \ConsoleKit\Colors::CYAN);
        $this->writeln("-----------------");

        $this->dialog =  new ConsoleKit\Widgets\Dialog($this->console);

        $cont = true;
        $name = "";

        while ($cont) {

            if($name == "") {
                if (array_key_exists('name', $opts)) {
                    $name = $opts['name'];
                } else {
                    if (count($args) != 2) {
                        $name = $this->dialog->ask(ENTER_THE_USER_NAME);
                    } else {
                        $name = $args[1];
                    }
                }
            }


            //$url = urlManager::getbaseURL() . 'accounts/' . urlencode($name);

            $url =  'accounts/' . urlencode($name);

            $resp = curlHelper::execute($this, $url, array(200,204), 'GET');

            if ($resp['code'] == 550) {
                $this->writeln(PERMISSION_DENIED."!!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln(PERMISSION_DENIED_EXPLANATION);
                return;
            }
            if ($resp['code'] == 200) {

                $this->writeln(USER_ALREADY_EXISTS."...", \ConsoleKit\Colors::RED);
                $name = $this->dialog->ask(ENTER_THE_USER_NAME);
                //curl_close($ch);

            }else {
                if ($resp['code'] == 204) {
                    $cont = false;

                } else {
                    $this->writeln("Error!", \ConsoleKit\Colors::MAGENTA);
                    return;
                }
            }


        }

        if(array_key_exists('password', $opts)){
            $password = $opts['password'];
        }else{
            $gpass = null;
            while ($gpass != YES_L && $gpass != NO_L) $gpass = $this->dialog->ask(GENERATE_PASSWORD,YES_L,true);

            if($gpass == 'Y'){
                $password = randomString::get(16);
            }else{
                $dialog = new passwordDialog($this->console);

                $password = "";
                while(!validators::passwordComplexity($password)) {
                    $password = $dialog->ask(ENTER_PASSWORD);
                    if(!validators::passwordComplexity($password)) $this->writeln(WEAK_PASSWORD);
                }
            }

        }
        $perms = array();
        $admin = null;
        while ($admin != YES_L && $admin != NO_L) $admin = $this->dialog->ask(USER_IS_ADMINISTRATOR,NO_L,true);

        if($admin == YES_L){
            $perms['admin'] = true;
        }

        $aperm = null;
        while ( $aperm != NO_L){
            $aperm = $this->dialog->ask(ADD_PERMISSION, NO_L, true);
            if($aperm == YES_L){
                $perm = $this->dialog->ask(PERMISSION_NAME);
                $pval = $this->dialog->ask(PERMISSION_VALUE);
                $perms[$perm] = $pval;

                foreach ($perms as $key => $val){
                    $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                }
            }
        }

        $auth = null;
        while ($auth != 'local' && $auth != 'ldap') $auth = $this->dialog->ask(AUTHENTICATION_TYPE,'local',true);

        // Creating the final json
        $json = '{';

        $json = $json.'"name":"'.$name.'",';

        $json = $json.'"password":"'.$password.'"';


        if(count($perms) > 0 ){
            $json = $json.',"permissions":{';
            $first = true;
            foreach ($perms as $key => $val){
                if(!$first) $json = $json.',';
                $json = $json.'"'.$key.'":"'.$val.'"';
                $first = false;

            }
            $json = $json.'}';
        }


        $json = $json.',"authentication":"'.$auth.'"';

        $json = $json.'}';



        $url =  'accounts/' . urlencode($name);


        $resp = curlHelper::execute($this,$url,array('200', '201'),'PUT',$json);


        if(!$resp) {
            die('Error!');
        } else {

            if ($resp['code'] == 550) {
                $this->writeln(PERMISSION_DENIED."!!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln(PERMISSION_DENIED_EXPLANATION);
                return;

            }
            if ($resp['code'] == 201) {

                $this->writeln(USER_CREATED);
                $this->writeln("--------------------------", \ConsoleKit\Colors::YELLOW);
                $this->write("Name: ");
                $this->writeln($name, \ConsoleKit\Colors::CYAN);
                $this->write("Password: ");
                $this->writeln($password, \ConsoleKit\Colors::CYAN);
                $this->writeln("--------------------------", \ConsoleKit\Colors::YELLOW);

            }else {

                $this->writeln("Error!", \ConsoleKit\Colors::MAGENTA);
                $this->writeln("Resposta : " . print_r($resp));
                return;

            }

        }



    }

    /**
     * Lê os dados de um usuário específico
     *
     * @param array $args
     * @param array $opts
     */
    public function executeRead(array $args, array $opts = array())
    {
        $this->dialog =  new ConsoleKit\Widgets\Dialog($this->console);
        $user = null;

        if(count($args) !=  2){
            //$this->writeln("Use user read <<user name>>",\ConsoleKit\Colors::YELLOW);
            $user = $this->dialog->ask(ENTER_THE_USER_NAME);
        }else{
            $user = $args[1];
        }


        $url =  'accounts/' . urlencode($user);

        $resp = curlHelper::execute($this,$url,array('200'));


        if (!$resp) {
            die('Error!');
        } else {

            if ($resp['code'] == 550) {
                $this->writeln(PERMISSION_DENIED." !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln(PERMISSION_DENIED_EXPLANATION);
                return;

            }
            if ($resp['code'] == 200) {

                $userVals = json_decode($resp['response'], true);

                $this->writeln("---", \ConsoleKit\Colors::BLUE);

                $this->write("Name: ");
                $this->writeln($userVals['name'], \ConsoleKit\Colors::CYAN);

                $this->write("Authentication: ");
                $this->writeln($userVals['authentication'], \ConsoleKit\Colors::CYAN);


                $this->writeln("** Permissions **");

                if(array_key_exists('permissions', $userVals)){
                    foreach ($userVals['permissions'] as $key => $val){
                        $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                    }
                }

                $this->writeln("------------------");

            } else {

                $this->writeln("Error", \ConsoleKit\Colors::MAGENTA);

            }
        }



    }

    /**
     * Edita os dados de um usuário específico
     *
     * @param array $args
     * @param array $opts
     */
    public function executeEdit(array $args, array $opts = array())
    {
        $this->dialog =  new ConsoleKit\Widgets\Dialog($this->console);
        $user = null;

        if(count($args) !=  2){
            //$this->writeln("Use user read <<user name>>",\ConsoleKit\Colors::YELLOW);
            $user = $this->dialog->ask(ENTER_THE_USER_NAME);
        }else{
            $user = $args[1];
        }


        $url =  'accounts/' . urlencode($user);

        $resp = curlHelper::execute($this,$url,array('200', '204'),'GET');


        if (!$resp) {
            die('Error!');
        } else {


            if ($resp['code'] == 550) {
                $this->writeln(PERMISSION_DENIED, \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln(PERMISSION_DENIED_EXPLANATION);
                return;
            }
            if ($resp['code'] == 200) {

                $userVals = json_decode($resp['response'] , true);

                $this->writeln("---", \ConsoleKit\Colors::BLUE);

                $this->write("Name: ");
                $this->writeln($userVals['name'], \ConsoleKit\Colors::CYAN);

                $aresp = null;
                while ($aresp != YES_L && $aresp != NO_L) $aresp = $this->dialog->ask(CHANGE_AUTHENTICATION.' ['.$userVals['authentication'].'] '.Y_N, NO_L, true);

                if($aresp == 'S') {
                    $auth = null;
                    while ($auth != 'local' && $auth != 'ldap') $auth = $this->dialog->ask(AUTHENTICATION_TYPE,'local',true);
                }else {
                    $auth = $userVals['authentication'];
                }

                $this->write("Authentication: ");
                $this->writeln($auth, \ConsoleKit\Colors::CYAN);

                $password = "";
                if($auth == 'local'){
                    $passresp = null;
                    while ($passresp != YES_L && $passresp != NO_L) $passresp = $this->dialog->ask(CHANGE_PASSWORD.' '.Y_N, NO_L, true);
                    if($passresp == YES_L){
                        $dialog = new passwordDialog($this->console);

                        while(!validators::passwordComplexity($password)) {
                            $password = $dialog->ask(ENTER_PASSWORD);
                            if(!validators::passwordComplexity($password)) $this->writeln(WEAK_PASSWORD);
                        }
                    }

                }

                $this->writeln("** Permissions **");

                $aperm = null;
                if(array_key_exists('permissions', $userVals)) $perms = $userVals['permissions'];
                else $perms = array();

                foreach ($perms as $key => $val){
                    $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                }

                while ( $aperm != NO_L){
                    $aperm = $this->dialog->ask(ADD_PERMISSION, NO_L, true);
                    if($aperm == YES_L){
                        $perm = $this->dialog->ask(PERMISSION_NAME);
                        $pval = $this->dialog->ask(PERMISSION_VALUE);
                        $perms[$perm] = $pval;

                        foreach ($perms as $key => $val){
                            $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                        }
                    }
                }

                $arem = null;
                while ( $arem != NO_L && count($perms) > 0){
                    $arem = $this->dialog->ask(REMOVE_PERMISSION.' '.Y_N, NO_L, true);
                    if($arem == YES_L){
                        $perm = $this->dialog->ask(PERMISSION_NAME);
                        if(array_key_exists($perm,$perms)) unset($perms[$perm]);
                        else $this->writeln(NAME_INVALID,\ConsoleKit\Colors::RED);

                        foreach ($perms as $key => $val){
                            $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                        }

                    }
                }


                foreach ($perms as $key => $val){
                    $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                }


                $this->writeln("------------------");

                $url =  'accounts/' . urlencode($user);

                // Creating the final json
                $json = '{';

                $json = $json.'"name":"'.$user.'"';

                if($password != "") $json = $json.',"password":"'.$password.'"';

                if(count($perms) > 0 ){
                    $json = $json.',"permissions":{';
                    $first = true;
                    foreach ($perms as $key => $val){
                        if(!$first) $json = $json.',';
                        $json = $json.'"'.$key.'":"'.$val.'"';
                        $first = false;

                    }
                    $json = $json.'}';
                }
                $json = $json.',"authentication":"'.$auth.'"';

                $json = $json.'}';


                $resp = curlHelper::execute($this,$url, array(200), 'POST', $json);


                if(!$resp) {
                    die('Error!');
                } else {

                    if ($resp['code'] == 550) {
                        $this->writeln(PERMISSION_DENIED, \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                        $this->writeln(PERMISSION_DENIED_EXPLANATION);
                        return;

                    }
                    if ($resp['code'] == 200) {

                        $this->writeln(USER_CHANGED);

                    }else {

                        $this->writeln("Error!", \ConsoleKit\Colors::MAGENTA);
                        return;

                    }

                }

            } else {
                $this->writeln("Error!", \ConsoleKit\Colors::MAGENTA);
            }
        }

    }


    /**
     * Edita os dados de um usuário específico
     *
     * @param array $args
     * @param array $opts
     */
    public function executeDelete(array $args, array $opts = array())
    {

        $this->dialog =  new ConsoleKit\Widgets\Dialog($this->console);
        $user = null;

        if(count($args) !=  2){
            //$this->writeln("Use user read <<user name>>",\ConsoleKit\Colors::YELLOW);
            $user = $this->dialog->ask('Entre com o nome do usuário:');
        }else{
            $user = $args[1];
        }


        $url = 'accounts/' . urlencode($user);

        $resp = curlHelper::execute($this, $url, array('200', '204'), 'GET');


        if (!$resp) {
            $this->writeln(USER_NOT_FOUND,\ConsoleKit\Colors::BLUE);
            exit;
        } else {
            //echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($resp['code'] == 550) {
                $this->writeln(PERMISSION_DENIED." !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln(PERMISSION_DENIED_EXPLANATION);
                return;

            }
            if ($resp['code'] == 200) {

                $userVals = json_decode($resp['response'] , true);

                $this->writeln("---", \ConsoleKit\Colors::BLUE);
                $this->writeln(USER_DATA, \ConsoleKit\Colors::BLUE);
                $this->writeln("------------------------", \ConsoleKit\Colors::BLUE);

                $this->write("Name: ");
                $this->writeln($userVals['name'], \ConsoleKit\Colors::CYAN);

                $this->write("Authentication: ");
                $this->writeln($userVals['authentication'], \ConsoleKit\Colors::CYAN);

                $this->writeln("** Permissions **");

                if(array_key_exists('permissions', $userVals)){
                    foreach ($userVals['permissions'] as $key => $val){
                        $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                    }
                }

                $this->writeln("#######################################", \ConsoleKit\Colors::RED);
                $this->writeln("# ".NO_RETURN_PROCEED." #", \ConsoleKit\Colors::RED);
                $this->writeln("#######################################", \ConsoleKit\Colors::RED);

                $resp = null;
                while ($resp != YES_W && $resp != NO_W) $resp = $this->dialog->ask(TYPE_YES_TO_PROCEED,NO_W,true);

                if($resp == YES_W){

                    $url =  'accounts/' . urlencode($userVals['name']);

                    $h_resp = curlHelper::execute($this,$url, array('200'), 'DELETE');

                    if (!$h_resp) {
                        die('Error!');
                    } else {

                        if ($h_resp['code']== 200) {
                            $this->writeln(USER_DELETED);
                        }
                    }
                }

            }
            else {

                $this->writeln("Error!", \ConsoleKit\Colors::MAGENTA);

            }
        }


    }

}

