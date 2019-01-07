<?php
/**
 * User: felipe.quintella
 * Date: 12/06/13
 * Time: 15:06
 */

namespace ccm;

require_once ROOT . "/baseincludes.php";

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class log implements ilog
{


    public function Trace($message, $context = array() )
    {
        $this->write($message, "Trace", $context);
    }

    private function write($msg, $level, $context = array() )
    {

        $log = new Logger('ccm');
        $alertLevel = null;

        switch (LOGLEVEL) {
            case "Trace":
                $alertLevel = 6;
                break;
            case "DEBUG":
                $alertLevel = Logger::DEBUG;
                break;
            case "INFO":
                $alertLevel = Logger::INFO;
                break;
            case "ERROR":
                $alertLevel = Logger::ERROR;
                break;
            case "WARNING":
                $alertLevel = Logger::WARNING;
                break;
            case "FATAL":
                $alertLevel = Logger::CRITICAL;
                break;
            default:
                $alertLevel = Logger::INFO;
                break;
        }

        $handler = new RotatingFileHandler(LOGDIR . "ccm.log",31, $alertLevel);

        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: message=\"%message%\" context=%context% \n");
        $handler->setFormatter($formatter);
        $log->pushHandler($handler);

        switch ($level) {
            case "Trace":
                $log->log($alertLevel, $msg, $context);
                break;
            case "Debug":
                $log->addDebug($msg, $context);
                break;
            case "Info":
                $log->addInfo($msg, $context);
                break;
            case "Error":
                $log->addError($msg, $context);
                break;
            case "Warning":
                $log->addWarning($msg, $context);
                break;
            case "Fatal":
                $log->addCritical($msg, $context);
                break;
            default:
                $log->addNotice($msg, $context);
                break;
        }


    }

    public function Debug($message, $context = array())
    {
        $this->write($message, "Debug", $context);
    }

    public function Info($message, $context = array())
    {
        $this->write($message, "Info", $context);
    }

    public function Warning($message, $context = array())
    {
        $this->write($message, "Warning", $context);
    }

    public function Error($message, $context = array())
    {
        $this->write($message, "Error", $context);
    }

    public function Fatal($message, $context = array())
    {
        $this->write($message, "Fatal", $context);
    }
}