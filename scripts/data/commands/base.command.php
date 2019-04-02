<?php

use ConsoleKit\Colors;

class base extends ConsoleKit\Command
{

    public $commandName;

    public function information(){

        $commandName = $this->commandName;

        $box = new ConsoleKit\Widgets\Box($this->getConsole(), " {$commandName} Script");
        $box->write();

        echo "\n";

    }

}