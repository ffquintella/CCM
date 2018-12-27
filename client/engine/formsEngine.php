<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 29/12/16
 * Time: 17:49
 */

namespace cmdEngine;
use cmdEngine\exceptions\fileNotFoundException;
use cmdEngine\exceptions\invalidFormException;
use ConsoleKit\Colors;
use \Symfony\Component\Yaml\Yaml;

require_once "frmfrm.ctrl.php";
require_once "minput.ctrl.php";
require_once "input.ctrl.php";

/**
 * Class formsBuilder
 * @package cmdEngine
 */
class formsEngine
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var \ConsoleKit\Console
     */
    private $console;

    /**
     * @var \ConsoleKit\Command
     */
    private $command;

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return \ConsoleKit\Console
     */
    public function getConsole(): \ConsoleKit\Console
    {
        return $this->console;
    }

    /**
     * @return \ConsoleKit\Command
     */
    public function getCommand(): \ConsoleKit\Command
    {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @var array
     */
    private $configs;

    /**
     * @var array
     */
    private $result;

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * formsBuilder constructor.
     * @param string $formTemplate - The path to the yaml file containing the template to this form
     *
     * @throws fileNotFoundException
     */
    public function __construct(\ConsoleKit\Command $command, string $formTemplate)
    {

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //echo 'This is a server using Windows!';
            $formTemplate = str_replace('/','\\',$formTemplate);
        }


        $this->template = $formTemplate;
        if(!file_exists($formTemplate)){
            throw new fileNotFoundException('The file:'.$formTemplate.' could not be found');
        }

        $this->console = $command->getConsole();

        $this->command = $command;

        $this->configs['yes'] = 'Y';
        $this->configs['no'] = 'N';
        $this->configs['yes_no'] = '[Y/N]';
        $this->configs['invalid_value'] = 'Invalid Value';


    }

    /**
     * Displays the data and returns an array
     *
     * @param $filledInfo - Data that we already know and don't wants filled
     *
     * @return array
     */
    public function getData(?array $filledInfo = null):array
    {

        $form = Yaml::parse(file_get_contents($this->template));

        $result = $this->processData($form, $filledInfo);

        return $result;

    }

    /**
     * Displays the data to be edited
     *
     * @param $filledInfo - Data that we already know and don't wants filled
     * @param $ro - List of read only fields
     *
     * @return array - The resulting object
     */
    public function editData(?array $filledInfo = null, array $ro = array())
    {
        $form = Yaml::parse(file_get_contents($this->template));
        $result = $this->processData($form, $filledInfo, true, true, $ro);

        return $result;

    }

    /**
     * Displays the data
     *
     * @param $filledInfo - Data that we already know and don't wants filled
     *
     */
    public function printData(?array $filledInfo = null)
    {
        $form = Yaml::parse(file_get_contents($this->template));
        $this->processData($form, $filledInfo, true);

    }

    public function processData(array $data, ?array $filledInfo = null, bool $printOnly = false, bool $edit = false, array $ro = array()): array
    {

        if(array_key_exists('yes', $data)) $this->configs['yes'] = $data['yes'];
        if(array_key_exists('no', $data)) $this->configs['no'] = $data['no'];
        if(array_key_exists('yes_no', $data)) $this->configs['yes_no'] = $data['yes_no'];
        if(array_key_exists('invalid_value', $data)) $this->configs['invalid_value'] = $data['invalid_value'];

        if($filledInfo != null) $fi = $filledInfo;
        else $fi = array();

        $i = 1;
        $this->result = $fi;

        $validFields = array();

        foreach ($data as $key => $value){

            $type = $this->classificate('::', $key);
            switch ($type){
                case 'title':
                    $title =  new \ConsoleKit\Widgets\Box($this->console, $value);
                    $title->write();
                    $this->console->writeln('');
                    break;
                case 'input':
                    $input = new input($this);
                    if($edit && !in_array($key,$ro)) {
                        $this->command->write($i . ' :', Colors::BLUE);
                        $validFields[$i] = $key;
                    }
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) {
                            $this->result[$key] = $fi[$key];
                            $input->print($value['name'], $fi[$key]);
                        }else{
                            $input->print($value['name'], $fi[$key]);
                        }
                    }else {
                        if(array_key_exists('optional', $value)) $result[$key] = $input->get($value, true);
                        else $this->result[$key] = $input->get($value);
                    }
                    break;
                case 'cmbbox':
                    if($edit && !in_array($key,$ro)) {
                        $this->command->write($i . ' :', Colors::BLUE);
                        $validFields[$i] = $key;
                    }
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) $this->result[$key] = $fi[$key];
                        $this->printCmb($fi[$key], $value['name']);
                    }else {
                        $this->result[$key] = $this->getCmb($value);
                    }
                    break;
                case 'label':
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) $result[$key] = $fi[$key];
                        $this->printLabel($fi[$key], $value['name']);
                    }
                    break;
                case 'list':
                    if($edit && !in_array($key,$ro)) {
                        $this->command->writeln($i.' - Lista', Colors::BLUE);
                        $validFields[$i] = $key;
                    }
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) $this->result[$key] = $fi[$key];
                        $this->printList($fi[$key]);
                    }else {
                        $this->result[$key] = $this->getList($value);
                    }
                    break;
                case 'frmfrm':
                    $ff = new frmfrm($this);
                    if($edit && !in_array($key,$ro)) {
                        $this->command->write($i . ' :', Colors::BLUE);
                        $validFields[$i] = $key;
                    }
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) $this->result[$key] = $fi[$key];
                        //var_dump($value);
                        $tmp = $this->result;
                        $ff->print($fi[$key], $value['ctrls']);
                        $this->result = $tmp;
                    }else {
                        $tmp2 = $ff->get($value);
                        $this->result[$key] = $tmp2;
                    }
                    break;
                case 'minput':
                    $mi = new minput($this, $this->result);
                    if($edit && !in_array($key,$ro) && $fi[$key] != null) {
                        $this->command->write($i . ' :', Colors::BLUE);
                        $validFields[$i] = $key;
                    }
                    if(array_key_exists($key, $fi) || $printOnly) {
                        if(!$printOnly) $this->result[$key] = $fi[$key];
                        $mi->print($fi[$key], $value['name']);
                    }else {
                        $this->result[$key] = $mi->get($value);
                    }
                    break;
                default:
                    // type not identified
                    break;
            }
            $i++;
        }
        if($edit){
            $editn = $this->getEditField($validFields);

            switch ($editn) {
                case 0:
                    break;
                default:
                    switch ($this->classificate('::', $validFields[$editn])){
                        case 'input':
                            $input = new input($this);
                            $this->result[$validFields[$editn]] = $input->edit($validFields[$editn],$fi, $data[$validFields[$editn]]);
                            $this->result = $this->processData($data, $this->result, true, true, $ro);
                            break;
                        case 'list':
                            $this->result[$validFields[$editn]] = $this->editList($fi[$validFields[$editn]]);
                            $this->result = $this->processData($data, $this->result, true, true, $ro);
                            break;
                        case 'cmbbox':
                            $this->result[$validFields[$editn]] = $this->editCmb($fi[$validFields[$editn]], $data[$validFields[$editn]]);
                            $this->result = $this->processData($data, $this->result, true, true, $ro);
                            break;
                        case 'frmfrm':
                            $ff = new frmfrm($this);
                            $this->result = $fi;
                            $this->result[$validFields[$editn]] = $ff->edit($fi[$validFields[$editn]], $data[$validFields[$editn]]);
                            $this->result = $this->processData($data, $this->result, true, true, $ro);
                            break;
                        case 'minput':
                            $mi = new minput($this, $this->result);
                            $this->result[$validFields[$editn]] = $mi->edit($fi[$validFields[$editn]], $data[$validFields[$editn]]);
                            $this->result = $this->processData($data, $this->result, true, true, $ro);
                            break;
                        default:
                            break;
                    }

                    break;
            }
        }

        return $this->result;
    }


    /**
     * @param array $value
     * @param array $selectedValues
     * @return array
     * @throws \Exception
     * @throws invalidFormException
     */
    private function getCmb(array $value, array $selectedValues = array()) :array
    {

        if(!array_key_exists('name', $value)
            || !array_key_exists('message', $value)
            || !array_key_exists('options', $value)){

            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form '.$this->template.' is invalid since the name, message and options fields are mandatory in a cmb.');
        }

        $resp = array();

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);

        if(is_array($value['options'])){
            $options = $value['options'];
        }else{

            if(substr($value['options'], 0, 3) == '$F$'){

                $validationFunc = substr( $value['options'], 3, strlen($value['options']) );

                $options = call_user_func($validationFunc, $this->command);
            }else {
                $options = explode(':',$value['options']);
            }

            if(!is_array($options)) throw new \Exception('Invalid options function');
        }

        if(array_key_exists('multiple',$value)) $multiple = $value['multiple'];
        else $multiple = false;

        $selection = array();

        for($z = 0 ; $z < count($options); $z++){
            if( in_array($options[$z] ,$selectedValues) ) $selection[] = $z+1;
        }

        $this->printList($options, true, $selection);

        if(!$multiple){
            while(true) {
                $s = $dialog->ask($value['message']);
                //var_dump($options);
                //var_dump($s);
                if (is_numeric($s)) {
                    if (array_key_exists($s-1, $options)){
                        $selection = array($s);
                        break;
                    } else $this->command->writeln(INPUT_INVALID, Colors::RED);
                } else {
                    $this->command->writeln(INPUT_INVALID, Colors::RED);
                }
            }

        }else {
            while(true) {
                $s = $dialog->ask($value['message'] . ' [' . Q_TO_EXIT . ']', 'q');
                if($s == 'q'){
                    break;
                }else{
                    if(is_numeric($s)){
                        if(array_key_exists($s-1, $options)){
                            if(in_array($s, $selection)) unset($selection[$s-1]);
                            else {
                                $selection[] = $s;
                            }
                            $this->printList($options, true, $selection);
                        }else $this->command->writeln(INPUT_INVALID, Colors::RED);
                    }else{
                        $this->command->writeln(INPUT_INVALID, Colors::RED);
                    }
                }
            }
        }

        foreach ($selection as $key => $value){

            $resp[] = $options[$value-1];
            //$resp[$options[$value-1]] = $subs[$key];
        }

        return $resp;
    }


    private function editCmb(array $values, array $fieldValue): array
    {
        $this->command->writeln($fieldValue['name'], Colors::GREEN);
        return $this->getCmb($fieldValue, $values);
    }

    private function editList(array $list): array
    {
        $this->printList($list, true);

        $valid = array();
        for($i = 1; $i <= count($list)+1; $i++) $valid[] = $i;
        $nedit = $this->getEditField($valid, true);
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        if($nedit == 0) return $list;
        else{
            if($nedit > 0) {
                $list[$nedit - 1] = $dialog->ask(NEW_VALUE);
                $list = $this->editList($list);
            }else{
                if($nedit == -1){
                    // -1 ADD
                    $list[] = $dialog->ask(NEW_VALUE);
                }else{
                    // -2 REMOVE
                    $index = $dialog->ask(VAL_REMOVE);
                    if(is_numeric($index)){
                        if(array_key_exists($index, $list)){
                            unset($list[$index-1]);
                            return $list;
                        }else $this->command->writeln(INPUT_INVALID);
                    }else $this->command->writeln(INPUT_INVALID);

                    $list = $this->editList($list);
                }
            }
        }
        return $list;
    }

    private function getEditField(array $validFields, bool $add_remove = false): int
    {
        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $this->command->write(TYPE.' [');
        if($add_remove) $this->command->write(A_TO_ADD.' / '.R_TO_REMOVE.' / ');
        $this->command->writeln(Q_TO_EXIT.']');
        $fnumber = $dialog->ask(EDIT_FIELD_N . ':');
        if($add_remove && ($fnumber == 'a')) return -1;
        if($add_remove && ($fnumber == 'r')) return -2;

        if($fnumber == 'q'){
            return 0;
        }else {
            if (is_numeric($fnumber)) {
                if (array_key_exists($fnumber, $validFields)) {
                    return $fnumber;
                }
            }
        }

        return $this->getEditField($validFields, $add_remove);

    }

    private function getList($value): array{
        $resp = array();
        if(!array_key_exists('message',$value)){
            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form '.$this->template.' is invalid since the message filed is mandatory in a list.');
        }
        if(!array_key_exists('more_message',$value)){
            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form '.$this->template.' is invalid since the more_message filed is mandatory in a list.');
        }
        if(!array_key_exists('name',$value)){
            $this->command->writeln('Invalid form!', Colors::RED);
            throw  new invalidFormException('Form '.$this->template.' is invalid since the name filed is mandatory in a list.');
        }

        $default = '';
        if(array_key_exists('default',$value)){
            $default = $value['default'];
        }

        $dialog = new \ConsoleKit\Widgets\Dialog($this->console);
        $cont = true;
        $i = 1;
        if(array_key_exists('minsize', $value)) $minsize = (int)$value['minsize'];
        else $minsize = 0;
        while($cont){
            if($i >= $minsize) {
                if($dialog->ask($value['more_message'].' '.$this->configs['yes_no'].' ',$this->configs['no'],true) == $this->configs['no']){
                    $cont = false ;
                }
            }
            if($cont) {

                $valid = false;
                $r1 = '';
                while(!$valid) {
                    $r1 = $dialog->ask($value['message'],$default);

                    if(array_key_exists('validator',$value)){
                        if(substr($value['validator'], 0, 3) == '$F$'){
                            $validationFunc = substr($value['validator'], 3, strlen($value['validator']));
                            $valid = call_user_func($validationFunc, $this->command, $r1);
                        }else {
                            $valid = preg_match($value['validator'], $r1);
                        }
                        if(!$valid) $this->command->writeln($this->configs['invalid_value'], Colors::RED);
                    }else $valid = true;
                }

                $resp[] = $r1;

            }
            $this->printList($resp);
            $i++;
        }


        return $resp;
    }

    public function printList(array $values, bool $numbers = false, array $selection = array()){
        $i = 1;
        foreach ($values as $key => $value){
            if($numbers) $this->command->write($i.'- ', Colors::GREEN);
            else $this->command->write(' - ', Colors::GREEN);
            if($numbers && in_array($key+1, $selection)) $this->command->write('*', Colors::GREEN);
            $this->command->writeln($value, Colors::WHITE);
            $i++;
        }
    }

    public function printCmb(array $values, string $name){

        //var_dump($values);
        $this->command->writeln($name, Colors::GREEN);

        foreach ($values as $key => $value){

            $this->command->write(' - ', Colors::GREEN);
            $this->command->writeln($value, Colors::WHITE);

        }

    }

    public function printLabel(string $value, string $name){
        $this->command->write($name.' ', Colors::GREEN);
        $this->command->writeln($value, Colors::BLUE);
    }


    private function classificate(string $separator, string $key): string
    {
        $exp_key =  explode($separator, $key);
        if(count($exp_key) > 1){
            return $exp_key[0];
        }else return $key;
    }

}