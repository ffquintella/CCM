<?php

use ConsoleKit\Colors;

class initStorageCommand extends base {


    public function localInfo(){

        echo Colors::colorize("Please use: \n", Colors::CYAN, Colors::BLACK);
        echo Colors::colorize("ccm_data.php init-storage <<parameters>>  \n", Colors::YELLOW, Colors::BLACK);
        echo Colors::colorize("where possible parameters are:  \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --redisURI=[URI] - The redis URI in the format tcp://server:port \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --redisDatabase=[DB num] - The number of the redis database \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --redisEncryptionKey=[key] - The key to access the ssl encryption of the redis server \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --masterAdmin=[name] - The login of the master admin \n", Colors::WHITE, Colors::BLACK);

    }

    public function execute(array $args, array $opts = array())
    {

        $this->commandName = "Init Storage";
        $this->information();

        //var_dump($args);
        //var_dump($opts);

        $parameters_ok = true;

        if(!array_key_exists('redisURI', $opts)){
            echo Colors::colorize("You must enter a redisURI! \n", Colors::RED);
            $parameters_ok = false;
        }
        if(!array_key_exists('redisDatabase', $opts)){
            echo Colors::colorize("You must enter a redisDatabase! \n", Colors::RED);
            $parameters_ok = false;
        }
        if(!array_key_exists('masterAdmin', $opts)){
            echo Colors::colorize("You must enter a masterAdmin! \n", Colors::RED);
            $parameters_ok = false;
        }

        if(!$parameters_ok){
            $this->localInfo();
        }else{
            if(array_key_exists('redisEncryptionKey', $opts))
                $this->runCommand($opts['redisURI'], intval($opts['redisDatabase']), $opts['masterAdmin'], $opts['redisEncryptionKey']);
            else $this->runCommand($opts['redisURI'], intval($opts['redisDatabase']), $opts['masterAdmin']);
        }


    }

    private function runCommand(String $redisURI, Int $redisDatabase, String $masterAdmin, String $encryptionKey = ''){
        $total = 10;
        $progress = new ConsoleKit\Widgets\ProgressBar($this->getConsole(), $total);

        /*for ($i = 0; $i < $total; $i++) {
            $progress->incr();
            usleep(10000);
        }*/

        if($encryptionKey != ''){
            $client = new Predis\Client($redisURI.'?ssl[verify_peer]=0&database='.$redisDatabase); //tls://127.0.0.1
        }else{
            $client = new Predis\Client($redisURI.'?database='.$redisDatabase); //'tcp://10.0.0.1?alias=first-node'
        }

        $progress->incr(); // 1

        $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());
        $pwd = $dialog->ask("\nPlease enter the password:");
        $progress->incr(); // 2
        //$console->writeln("hello $name");

        $sec = new \ccm\Secure();

        $ua = (new \ccm\userAccount($masterAdmin, $pwd, 'local'))->addPermission(
            array("admin" => true));

        //var_dump($ua);
        //var_dump($client);

        //$client->set('test' , "123");
        $client->set('user:' . $ua->getName(), $sec->encrypt(serialize($ua)));

        $progress->incr(); // 3


        // LISTS
        $list = new \ccm\linkedList();
        //$list->insertLast('production'); $progress->incr(); // 4
        //$list->insertLast('development'); $progress->incr(); // 5
        //$list->insertLast('testing'); $progress->incr(); // 6
        //$list->insertLast('homolog'); $progress->incr(); // 7
        $client->set('list:'.'environments', $sec->encrypt(serialize($list))); $progress->incr(); // 8


        // INDEXES
        $client->sadd('index:'."user", array($ua->getName())); $progress->incr(); // 9
        $client->sadd('index:'."list", array('environments')); $progress->incr(); // 10


        $progress->stop();
    }
}