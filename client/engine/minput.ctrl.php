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
 * The multiple input control
 *
 * Class minput
 * @package cmdEngine
 */
class minput
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

    /**
     * @var array
     */
    private $prevResults;

    public function __construct(formsEngine $engine, array $prevResults)
    {
        $this->engine = $engine;
        $this->command = $engine->getCommand();
        $this->configs = $engine->getConfigs();
        $this->prevResults = $prevResults;
    }

    /**
     * @param array $frmCtrl
     * @param array $filledValues
     * @param bool $edit
     * @return array|null
     *
     * @throws invalidFormException
     */
    public function get(array $frmCtrl, array $filledValues = array(), $edit = false) :?array
    {

        $resp = array();

        if(array_key_exists('display',$frmCtrl)){
            $result = $this->engine->getResult();
            $display = false;
            eval('$display='.$frmCtrl['display']);
            if(!$display){
                return null;
            }
        }

        if(!array_key_exists('interactions', $frmCtrl)) throw new invalidFormException(FORM_INVALID.' '.THE_FIELD.': interactions '.IS_MANDATORY);
        if(!\strTools::startsWith($frmCtrl['interactions'], '$F$')) throw new invalidFormException(FORM_INVALID.' '.THE_FIELD.': interactions '.IS_INVALID_FORMATED);

        $interFunc = substr($frmCtrl['interactions'],3,strlen($frmCtrl['interactions']));

        $inters = call_user_func($interFunc, $this->command, $this->prevResults);

        foreach ($inters as $key => $value){
            $input = new input($this->engine);
            $this->command->write(VALUE.': ', Colors::GREEN);
            $this->command->writeln($value, Colors::WHITE);

            $intFrmCtrl = ['name' => $frmCtrl['name'].'-'.$key,
                'message' => $frmCtrl['message']];

            $iresp = $input->get($intFrmCtrl);

            $resp[$value] = $iresp;
        }


        return $resp;
    }

    public function print(?array $fields, string $val){

        if($fields == null) return;

        $this->command->writeln($val.' ...', Colors::GREEN);

        foreach ($fields as $key => $val2) {
            $this->command->write('  '.$key.': ', Colors::GREEN);
            $this->command->writeln($val2, Colors::BLUE);
        }

    }

    public function edit(?array $values, array $formInput): ?array
    {

        if($values == null) return null;

        $dialog = new \ConsoleKit\Widgets\Dialog($this->command->getConsole());
        $resp = array();
        foreach ($values as $key => $val){
            $this->command->write(VALUE.': ', Colors::GREEN);
            $this->command->writeln($key, Colors::WHITE);
            $resp[$key] = $dialog->ask($formInput['message'],$val, true);
        }

        return $resp;


    }

}