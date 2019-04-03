<?php


use ConsoleKit\Colors;


class migrateDataCommand extends base
{

    private $fromSecure, $toSecure;
    private $fromRedisClient, $toRedisClient;

    private $logger;

    public function localInfo(){

        echo Colors::colorize("Please use: \n", Colors::CYAN, Colors::BLACK);
        echo Colors::colorize("ccm_data.php init-storage <<parameters>>  \n", Colors::YELLOW, Colors::BLACK);
        echo Colors::colorize("where possible parameters are:  \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --fromRedisURI=[URI] - The ccm URI in the format tcp://server:port \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --fromRedisDatabase=[DB num] -  The number of the redis database we are migrating from\n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --fromVersion=[num] - The number of the ccm version you are migrating from \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --fromMasterKey=[string] - The encryption masterkey on the from server \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --destinationRedisURI=[key] - The key to access the ssl encryption of the redis server \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --destinationDatabase=[DB num] -  The number of the redis database we are migrating to\n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --destinationVersion=[num] - The number of the ccm version you are migrating to \n", Colors::WHITE, Colors::BLACK);
        echo Colors::colorize("   --destinationMasterkey=[string] - The encryption masterkey on the destination server \n", Colors::WHITE, Colors::BLACK);

    }

    public function execute(array $args, array $opts = array())
    {

        $this->logger = new Katzgrau\KLogger\Logger('/tmp/migrationLog');

        $this->commandName = "Migrate Data";
        $this->information();

        $parameters_ok = true;

        if(!array_key_exists('fromRedisURI', $opts)){
            echo Colors::colorize("You must enter a fromRedisURI! \n", Colors::RED);
            $parameters_ok = false;
        }

        if(!array_key_exists('fromRedisDatabase', $opts)){
            echo Colors::colorize("You must enter a fromRedisDatabase! \n", Colors::RED);
            $parameters_ok = false;
        }else{
            $this->fromRedisClient = new Predis\Client($opts['fromRedisURI'].'?database='.$opts['fromRedisDatabase']);
        }

        if(!array_key_exists('fromVersion', $opts)){
            echo Colors::colorize("You must enter a fromVersion! \n", Colors::RED);
            $parameters_ok = false;
        }else {
            if ($opts['fromVersion'] != '1.4') {
                echo Colors::colorize("From version is invalid! Valid values are: 1.4; \n", Colors::RED);
                $parameters_ok = false;
            }
        }

        if(!array_key_exists('fromMasterKey', $opts)){
            echo Colors::colorize("You must enter a fromMasterKey! \n", Colors::RED);
            $parameters_ok = false;
        }else{
            $this->fromSecure = new \ccm\Secure($opts['fromMasterKey']);
        }

        if(!array_key_exists('destinationRedisURI', $opts)){
            echo Colors::colorize("You must enter a destinationRedisURI! \n", Colors::RED);
            $parameters_ok = false;
        }

        if(!array_key_exists('destinationDatabase', $opts)){
            echo Colors::colorize("You must enter a destinationDatabase! \n", Colors::RED);
            $parameters_ok = false;
        }else{
            $this->toRedisClient = new Predis\Client($opts['destinationRedisURI'].'?database='.$opts['destinationDatabase']);
        }

        if(!array_key_exists('destinationVersion', $opts)){
            echo Colors::colorize("You must enter a destinationVersion! \n", Colors::RED);
            $parameters_ok = false;
        }else {
            if ($opts['destinationVersion'] != '1.5') {
                echo Colors::colorize("destinationVersion is invalid! Valid values are: 1.5; \n", Colors::RED);
                $parameters_ok = false;
            }
        }

        if(!array_key_exists('destinationMasterkey', $opts)){
            echo Colors::colorize("You must enter a destinationMasterkey! \n", Colors::RED);
            $parameters_ok = false;
        }else{
            $this->toSecure = new \ccm\Secure($opts['destinationMasterkey']);
        }

        if(!$parameters_ok){
            $this->localInfo();
        }else {
            $executionPipe = array();

            if($opts['fromVersion'] == '1.4') {
                if ($opts['destinationVersion'] == '1.5') {
                    $executionPipe[] = 'migrateDataCommand::migrate_14_15';
                }
            }

            $this->logger->info("Starting to migrate data  with parameters: ", $opts);

            foreach ($executionPipe as $func){
                call_user_func($func);
            }

        }
    }

    public function migrate_14_15(){
        echo Colors::colorize("Migrating from version 1.4 to version 1.5 \n", Colors::CYAN);

        $this->migrate_users_v1();
        $this->migrate_lists_v1();
        $this->migrate_apps_v1();
        $this->migrate_servers_v1();

    }

    public function migrate_users_v1(){
        echo Colors::colorize("Starting user migration. Protocol v1 \n", Colors::YELLOW);

        echo Colors::colorize("Listing users from origin \n", Colors::WHITE);

        $users = $this->fromRedisClient->keys('user:*');

        echo Colors::colorize("Processing ".count($users)." users \n", Colors::WHITE);
        $this->logger->info("Processing ".count($users)." users ");
        //var_dump($users);

        $total = count($users);
        $progress = new ConsoleKit\Widgets\ProgressBar($this->getConsole(), $total);

        foreach ($users as $user){
            $progress->incr();

            $tmp = $this->fromRedisClient->get($user);

            $userFrom = unserialize($this->fromSecure->decrypt($tmp));

            $userFrom->setSec($this->fromSecure);

            $this->logger->info("Processing user: ". $userFrom->getName());

            $pwd = $userFrom->getPassword();

            $salt = $userFrom->getSalt();

            //var_dump($salt); var_dump($pwd);

            try {
                $toUser = new \ccm\userAccount(strtolower($userFrom->getName()), $salt . '#:#' . $pwd, $userFrom->getAuthentication());

                $toUser->addPermission($userFrom->getPermissions());

                $this->toRedisClient->set('user:' . strtolower($toUser->getName()), $this->toSecure->encrypt(serialize($toUser)));

                $this->logger->info("Updating index... ");
                $this->toRedisClient->sadd('index:'."user", array(strtolower($toUser->getName())));

            }catch (Exception $ex){
                $this->logger->error("Error: ". $ex->getMessage());
                exit();
            }


        }

        $progress->stop();
    }

    public function migrate_lists_v1()
    {
        echo Colors::colorize("Starting list migration. Protocol v1 \n", Colors::YELLOW);

        echo Colors::colorize("Listing lists from origin \n", Colors::WHITE);

        $lists = $this->fromRedisClient->keys('list:*');

        echo Colors::colorize("Processing " . count($lists) . " lists \n", Colors::WHITE);
        $this->logger->info("Processing ".count($lists)." lists ");

        //var_dump($users);

        $total = count($lists);
        $progress = new ConsoleKit\Widgets\ProgressBar($this->getConsole(), $total);

        foreach ($lists as $list) {
            $progress->incr();

            $tmp = $this->fromRedisClient->get($list);

            $listFrom = unserialize($this->fromSecure->decrypt($tmp));

            $this->logger->info("Processing ". $list);


            try {
                $listTo = new \ccm\linkedList();

                while($listFrom->current() != null){
                    $listTo->insertLast($listFrom->current());
                    $listFrom->next();
                }

                $this->toRedisClient->set(strtolower($list), $this->toSecure->encrypt(serialize($listTo)));

                $this->logger->info("Updating index... ");
                $listName = substr($list, 5);
                var_dump($listName);
                $this->toRedisClient->sadd('index:'."list", array(strtolower($listName)));

            }catch (Exception $ex){
                $this->logger->error("Error: ". $ex->getMessage());
                exit();
            }


        }
        $progress->stop();
    }

    public function migrate_apps_v1()
    {
        echo Colors::colorize("Starting app migration. Protocol v1 \n", Colors::YELLOW);

        echo Colors::colorize("Listing apps from origin \n", Colors::WHITE);

        $apps = $this->fromRedisClient->keys('app:*');

        echo Colors::colorize("Processing " . count($apps) . " apps \n", Colors::WHITE);
        $this->logger->info("Processing ".count($apps)." apps ");

        //var_dump($users);

        $total = count($apps);
        $progress = new ConsoleKit\Widgets\ProgressBar($this->getConsole(), $total);

        foreach ($apps as $app) {
            $progress->incr();

            $tmp = $this->fromRedisClient->get($app);

            $appFrom = unserialize($this->fromSecure->decrypt($tmp));

            //var_dump($appFrom);

            $this->logger->info("Processing ". $app);


            try {
                $appTo = new \ccm\app(strtolower($appFrom->getName()), strtolower($appFrom->getOwner()), $appFrom->getCreationT() );

                $appEnvs = $appFrom->getEnvironments();

                while($appEnvs->current() != null){

                    $env = $appEnvs->current()->data;

                    $appTo->addEnvironment($env, false);
                    $appEnvs->next();
                }

                $appTo->setKey($appFrom->getKey());
                $appTo->setOldKey($appFrom->getOldKey());

                //var_dump($appTo);

                $this->toRedisClient->set(strtolower($app), $this->toSecure->encrypt(serialize($appTo)));

                $this->logger->info("Updating index... ");
                $this->toRedisClient->sadd('index:'."app", array(strtolower($appFrom->getName())));

                $this->logger->info("Updating ref key-app for key=".$appTo->getKey()." appName=".$appTo->getName());
                $this->toRedisClient->set('ref:key-app:'.md5($appTo->getKey()), $appTo->getName());


            }catch (Exception $ex){
                $this->logger->error("Error: ". $ex->getMessage());
                exit();
            }

        }
        $progress->stop();
    }

    public function migrate_servers_v1()
    {
        echo Colors::colorize("Starting servers migration. Protocol v1 \n", Colors::YELLOW);

        echo Colors::colorize("Listing servers from origin \n", Colors::WHITE);

        $servers = $this->fromRedisClient->keys('server:*');

        echo Colors::colorize("Processing " . count($servers) . " servers \n", Colors::WHITE);
        $this->logger->info("Processing ".count($servers)." servers ");

        //var_dump($users);

        $total = count($servers);
        $progress = new ConsoleKit\Widgets\ProgressBar($this->getConsole(), $total);

        foreach ($servers as $server) {
            $progress->incr();

            $tmp = $this->fromRedisClient->get($server);

            //var_dump($tmp);
            $sser = $this->fromSecure->decrypt($tmp);

            //var_dump($sser);


            $serverFrom = unserialize($sser);

            //var_dump($serverFrom);


            $this->logger->info("Processing ". $serverFrom->getName());


            try {
                $serverTo = new \ccm\server($serverFrom->getName(), $serverFrom->getFQDN());

                foreach($serverFrom->getAssignments() as $app => $envs){

                    foreach ($envs as $env) {
                        $serverTo->assign($app, $env, false);
                    }
                }

                //var_dump($serverTo);

                $this->toRedisClient->set(strtolower($server), $this->toSecure->encrypt(serialize($serverTo)));

                $this->logger->info("Updating index... ");
                $this->toRedisClient->sadd('index:'."server", array(strtolower($serverFrom->getName())));

            }catch (Exception $ex){
                $this->logger->error("Error: ". $ex->getMessage());
                exit();
            }



        }
        $progress->stop();
    }

}