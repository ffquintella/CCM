<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 11/01/17
 * Time: 10:19
 */

namespace cmdEngine;
use cmdEngine\exceptions\invalidFormException;
use ConsoleKit\Colors;


/**
 * The input control
 *
 * Class input
 * @package cmdEngine
 */
class input
{
    /**
     * @var \ConsoleKit\Command
     */
    private $command;

    /**
     * @var array
     */
    private $configs;

    /**
     * @var array
     */
    private $engine;


    public function __construct(formsEngine $engine)
    {
        $this->engine = $engine;
        $this->command = $engine->getCommand();
        $this->configs = $engine->getConfigs();

    }


    public function get(array $frmCtrl, bool $optional = false): string{
        $resp = '';

        if(!array_key_exists('message',$frmCtrl)){
            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form  is invalid since the message field is mandatory in a input');
        }
        if(!array_key_exists('name',$frmCtrl)){
            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form  is invalid since the name filed is mandatory in a input.');
        }
        $dialog = new \ConsoleKit\Widgets\Dialog($this->command->getConsole());

        if($optional){
            $this->command->write($frmCtrl['name']. ' ', Colors::CYAN);
            $dresp = $dialog->ask(OPTIONAL_VALUE.' '.$this->configs['yes_no'], $this->configs['no'], true);
            if($dresp == $this->configs['no']){
                return $resp;
            }
        }

        $valid = false;
        while(!$valid) {

            $resp = $dialog->ask($frmCtrl['message']);
            if(array_key_exists('validator',$frmCtrl)){
                if(substr($frmCtrl['validator'], 0, 3) == '$F$'){
                    $validationFunc = substr($frmCtrl['validator'], 3, strlen($frmCtrl['validator']));
                    $valid = call_user_func($validationFunc, $this->command, $resp);
                }else {
                    $valid = preg_match($frmCtrl['validator'], $resp);
                }
                if(!$valid) $this->command->writeln(INVALID_VALUE, Colors::RED);
            }else $valid = true;
        }
        return $resp;
    }

    public function edit(string $name, array $values, array $formInput): string
    {
        //var_dump($formInput);
        $this->print($name, $values[$name]);
        $resp = $this->get($formInput);
        return $resp;
    }

    public function print(string $fieldName, ?string $val){

        if (is_null($val)) $val = "";

        $this->command->write($fieldName.' ', Colors::GREEN);
        $this->command->writeln($val, Colors::BLUE);

    }

}