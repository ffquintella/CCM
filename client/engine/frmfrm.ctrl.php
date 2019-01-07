<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 04/01/17
 * Time: 12:58
 */

namespace cmdEngine;
use ConsoleKit\Colors;


/**
 * Class frmfrm - Creates a form in form ctrl
 * @package cmdEngine
 */
class frmfrm
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

    /**
     * Gets the form in form data
     * @param array $frmCtrl
     * @return array
     */
    public function get(array $frmCtrl, array $filledValues = array(), $edit = false) :array
    {

        $subresp = array();
        $depth = count($frmCtrl['ctrls']);
        $frmEngine = new formsEngine($this->command,$this->engine->getTemplate());

        for($i = 0; $i < $depth; $i++){
            $multiple = $frmCtrl['ctrls']['depth::'.$i]['multiple'];
            $ctrl = $frmCtrl['ctrls']['depth::'.$i]['ctrl'];

            if($multiple && $i > 0){
                foreach (array_values($subresp[$i-1])[0] as $key => $value){
                    $GLOBALS['subparam'] = $value;
                    $this->command->write(PROCESSING_FOR_VALUE.': ', Colors::MAGENTA );
                    $this->command->writeln($value );

                    $fv = $filledValues;
                    if(array_key_exists('sub', $fv)) {
                        for ($z = 0; $z < $i; $z++) {
                            $fv = $fv['sub'];
                        }
                    }
                    if(array_key_exists($key, $fv)) $fv = $fv[$key];
                    else $fv = array();

                    if($edit) $subresp[$i][$key]  = $frmEngine->processData($ctrl, $fv,false, true);
                    else $subresp[$i][$key]  = $frmEngine->processData($ctrl, $fv);
                }
            }else{



                if($i > 0) $GLOBALS['subparam'] = array_values($subresp[$i-1])[0];
                if($edit){
                    $tmp = $filledValues;
                    unset($tmp['sub']);
                    $subresp[$i] = $frmEngine->processData($ctrl, $tmp,false, true);
                }
                else $subresp[$i] = $frmEngine->processData($ctrl, $filledValues);

            }

        }

        $resp = null;
        for($i = $depth; $i > 0; $i--){

            $tmp = $resp;
            if(is_array($subresp)) {
                if((count($subresp) -1) >= $i - 1)
                    $resp = $subresp[$i - 1];
            }
            if($tmp != null) $resp['sub'] = $tmp;

        }

        return $resp;

    }

    /**
     *  Prints the form in form dataw
     *
     * @param array $frmCtrl
     * @return array
     */
    public function print(array $frmData, array $frmCtrl)
    {
        $depth = count($frmCtrl);
        for($i = 0; $i < $depth; $i++) {
            $multiple = $frmCtrl['depth::'.$i]['multiple'];
            $ctrl = $frmCtrl['depth::'.$i]['ctrl'];


            if($multiple && $i > 0){

                if(array_key_exists('sub', $frmData) && is_array($frmData['sub'])) {
                    foreach ($frmData['sub'] as $key => $value) {
                        $this->command->write(PROCESSING_FOR_VALUE . ': ', Colors::MAGENTA);
                        $vp = array_values($frmData)[0][$key];
                        $this->command->writeln($vp);
                        $sctrl = $frmCtrl['depth::' . $i]['ctrl'];
                        $this->engine->processData($sctrl, $frmData['sub'][$key], true);
                    }
                }
            }else{
                $this->engine->processData($ctrl, $frmData, true);

            }
        }

    }
    /**
     *  Edit the form in form data
     *
     * @param array $frmCtrl
     * @return array
     */
    public function edit(array $values, array $fieldValue)
    {
        $this->command->writeln($fieldValue['name'], Colors::GREEN);
        return $this->get($fieldValue, $values, true);
    }
}