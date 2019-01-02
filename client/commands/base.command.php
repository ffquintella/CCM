<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 17:26
 */


class base extends ConsoleKit\Command
{
    /**
     * @var ConsoleKit\Widgets\Dialog
     */
    public $dialog;

    /**
     * Executa a rotina de login
     */
    public function executeLogin(){
        /**
         * @var ConsoleKit\Widgets\Dialog
         */
        $this->dialog = new ConsoleKit\Widgets\Dialog($this->console);

        $this->writeln("** Login Necessário **", ConsoleKit\Colors::CYAN+ConsoleKit\Colors::BOLD);

        $login =  $this->dialog->ask('Entre com o login:');

        $dialog = new passwordDialog($this->console);

        $password = $dialog->ask('Entre com o password:');


        // Get cURL resource
        $ch = curl_init();

        $url = urlManager::getbaseURL().'authenticationLogin?format=json';

        //var_dump($url);

        // Set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        if(INSECURE_SSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic ". base64_encode("$login:$password"),
            ]
        );


        // Send the request & save response to $resp
        $resp = curl_exec($ch);

        if(!$resp) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        } else {
            /*echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "\nResponse HTTP Body : " . $resp;*/

            if( curl_getinfo($ch, CURLINFO_HTTP_CODE) == 401){
                $this->writeln("Login inválido !!", \ConsoleKit\Colors::RED+\ConsoleKit\Colors::BLINK);
                $this->writeln("Tente Novamente.");
                // Close request to clear up some resources
                curl_close($ch);
                $this->executeLogin();
                return;

            }
            if( curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
                $this->writeln("Login OK...", \ConsoleKit\Colors::BLUE);

                $obj = json_decode($resp);

                //var_dump($obj);

                loginManager::writeSession($obj->userName, $obj->tokenValue);


                return;
            }

            echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "\nResponse HTTP Body : " . $resp;
        }

        // Close request to clear up some resources
        curl_close($ch);


    }

}