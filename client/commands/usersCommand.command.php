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

            $fe = new \cmdEngine\formsEngine($this , __DIR__.'/../forms/apps.yaml');

            $obj = json_decode($resp['response'], false);

            $users = array();

            foreach ($obj as $act ){
                $users[] = $act->name;
            }

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
            while ($gpass != 'Y' && $gpass != 'N') $gpass = $this->dialog->ask(GENERATE_PASSWORD,'Y',true);

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
        while ($admin != 'Y' && $admin != 'N') $admin = $this->dialog->ask(USER_IS_ADMINISTRATOR,'N',true);

        if($admin == 'Y'){
            $perms['admin'] = true;
        }

        $aperm = null;
        while ( $aperm != 'N'){
            $aperm = $this->dialog->ask(ADD_PERMISSION, 'N', true);
            if($aperm == 'Y'){
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
            $user = $this->dialog->ask('Entre com o nome do usuário:');
        }else{
            $user = $args[1];
        }

        // Get cURL resource
        $ch = curl_init();

        $url = urlManager::getbaseURL() . 'accounts/' . urlencode($user);

        //var_dump($url);

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
            ]
        );


        // Send the request & save response to $resp
        $resp = curl_exec($ch);


        if (!$resp) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        } else {
            //echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 550) {
                $this->writeln("Permissão negada !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln("Seu usuário não tem permissão para executar este comando.");

                curl_close($ch);
                return;

            }
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {

                $userVals = json_decode($resp, true);

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


                $this->writeln("Erro no pedido!", \ConsoleKit\Colors::MAGENTA);
                $this->writeln("Código : " . curl_getinfo($ch, CURLINFO_HTTP_CODE));


            }
        }


        // Close request to clear up some resources
        curl_close($ch);


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
            $user = $this->dialog->ask('Entre com o nome do usuário:');
        }else{
            $user = $args[1];
        }

        // Get cURL resource
        $ch = curl_init();

        $url = urlManager::getbaseURL() . 'accounts/' . urlencode($user);

        //var_dump($url);

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
            ]
        );


        // Send the request & save response to $resp
        $resp = curl_exec($ch);


        if (!$resp) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        } else {
            //echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 550) {
                $this->writeln("Permissão negada !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln("Seu usuário não tem permissão para executar este comando.");

                curl_close($ch);
                return;

            }
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {

                $userVals = json_decode($resp, true);

                $this->writeln("---", \ConsoleKit\Colors::BLUE);

                $this->write("Name: ");
                $this->writeln($userVals['name'], \ConsoleKit\Colors::CYAN);

                $aresp = null;
                while ($aresp != 'S' && $aresp != 'N') $aresp = $this->dialog->ask('Deseja mudar a autenticação? ['.$userVals['authentication'].'] [S/N]', 'N', true);

                if($aresp == 'S') {
                    $auth = null;
                    while ($auth != 'local' && $auth != 'ldap') $auth = $this->dialog->ask('Defina o tipo de autenticação [local/ldap]?','local',true);
                }else {
                    $auth = $userVals['authentication'];
                }

                $this->write("Authentication: ");
                $this->writeln($auth, \ConsoleKit\Colors::CYAN);

                $password = "";
                if($auth == 'local'){
                    $passresp = null;
                    while ($passresp != 'S' && $passresp != 'N') $passresp = $this->dialog->ask('Deseja mudar a senha? [S/N]', 'N', true);
                    if($passresp == 'S'){
                        $dialog = new passwordDialog($this->console);

                        while(!validators::passwordComplexity($password)) {
                            $password = $dialog->ask('Entre com a senha da conta:');
                            if(!validators::passwordComplexity($password)) $this->writeln('Senha muito fraca.');
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

                while ( $aperm != 'N'){
                    $aperm = $this->dialog->ask('Deseja adicionar uma permissão? [S/N]', 'N', true);
                    if($aperm == 'S'){
                        $perm = $this->dialog->ask('Entre com o nome da permissão:');
                        $pval = $this->dialog->ask('Entre com o valor da permissão:');
                        $perms[$perm] = $pval;

                        foreach ($perms as $key => $val){
                            $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                        }
                    }
                }

                $arem = null;
                while ( $arem != 'N' && count($perms) > 0){
                    $arem = $this->dialog->ask('Deseja remover uma permissão? [S/N]', 'N', true);
                    if($arem == 'S'){
                        $perm = $this->dialog->ask('Entre com o nome da permissão:');
                        if(array_key_exists($perm,$perms)) unset($perms[$perm]);
                        else $this->writeln('Este nome não existe!',\ConsoleKit\Colors::RED);

                        foreach ($perms as $key => $val){
                            $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                        }

                    }
                }


                foreach ($perms as $key => $val){
                    $this->write("-: "); $this->write($key.": "); $this->writeln($val, \ConsoleKit\Colors::GREEN);
                }


                $this->writeln("------------------");

                // Get cURL resource
                $ch2 = curl_init();

                $url = urlManager::getbaseURL() . 'accounts/' . urlencode($user);


                // Set url
                curl_setopt($ch2, CURLOPT_URL, $url);

                // Set method
                curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'POST');

                // Set options
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch2, CURLOPT_CAINFO, "cacert.pem");

                // Set headers
                curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                        "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
                    ]
                );

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


                // Set body
                curl_setopt($ch2, CURLOPT_POST, 1);
                curl_setopt($ch2, CURLOPT_POSTFIELDS, $json);

                // Send the request & save response to $resp
                $resp = curl_exec($ch2);


                if(!$resp) {
                    die('Error: "' . curl_error($ch2) . '" - Code: ' . curl_errno($ch));
                } else {

                    if (curl_getinfo($ch2, CURLINFO_HTTP_CODE) == 550) {
                        $this->writeln("Permissão negada !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                        $this->writeln("Seu usuário não tem permissão para executar este comando.");

                        curl_close($ch2);
                        return;

                    }
                    if (curl_getinfo($ch2, CURLINFO_HTTP_CODE) == 200) {

                        $this->writeln("Usuário alterado com sucesso!");

                    }else {

                        $this->writeln("Erro no pedido!", \ConsoleKit\Colors::MAGENTA);
                        $this->writeln("Código : " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
                        $this->writeln("Resposta : " . $resp);
                        return;

                    }

                }



                // Close request to clear up some resources
                curl_close($ch2);


            } else {


                $this->writeln("Erro no pedido!", \ConsoleKit\Colors::MAGENTA);
                $this->writeln("Código : " . curl_getinfo($ch, CURLINFO_HTTP_CODE));


            }
        }


        // Close request to clear up some resources
        curl_close($ch);


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

        // Get cURL resource
        $ch = curl_init();

        $url = urlManager::getbaseURL() . 'accounts/' . urlencode($user);

        //var_dump($url);

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
            ]
        );


        // Send the request & save response to $resp
        $resp = curl_exec($ch);


        if (!$resp) {
            $this->writeln('Usuário não encontrado!',\ConsoleKit\Colors::BLUE);
            exit;
        } else {
            //echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 550) {
                $this->writeln("Permissão negada !!", \ConsoleKit\Colors::RED + \ConsoleKit\Colors::BLINK);
                $this->writeln("Seu usuário não tem permissão para executar este comando.");

                curl_close($ch);
                return;

            }
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                curl_close($ch);
                $userVals = json_decode($resp, true);

                $this->writeln("---", \ConsoleKit\Colors::BLUE);
                $this->writeln("DADOS DO USUÁRIO", \ConsoleKit\Colors::BLUE);
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
                $this->writeln("# NÃO EXISTE VOLTA DESEJA PROSSEGUIR? #", \ConsoleKit\Colors::RED);
                $this->writeln("#######################################", \ConsoleKit\Colors::RED);

                $resp = null;
                while ($resp != 'sim' && $resp != 'nao') $resp = $this->dialog->ask('Digite sim para apagar [sim/nao]?','nao',true);

                if($resp == 'sim'){
                    // Get cURL resource
                    $ch = curl_init();

                    $url = urlManager::getbaseURL() . 'accounts/' . urlencode($userVals['name']);

                    //var_dump($url);

                    // Set url
                    curl_setopt($ch, CURLOPT_URL, $url);

                    // Set method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                    // Set options
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

                    // Set headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "AUTHORIZATION: " . $GLOBALS['SESSION_TOKEN'],
                        ]
                    );


                    // Send the request & save response to $resp
                    $resp = curl_exec($ch);

                    if (!$resp) {
                        die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                    } else {

                        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                            $this->writeln('Usuário apagado com sucesso.');
                        }
                    }
                    curl_close($ch);
                }

            }
            else {


                $this->writeln("Erro no pedido!", \ConsoleKit\Colors::MAGENTA);
                $this->writeln("Código : " . curl_getinfo($ch, CURLINFO_HTTP_CODE));


            }
        }


    }

}

