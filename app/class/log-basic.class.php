<?php
/**
 * User: felipe.quintella
 * Date: 12/06/13
 * Time: 15:06
 */

namespace ccm;


require_once ROOT . "/baseincludes.php";


class log implements ilog
{

    public function Trace($message)
    {
        $this->write($message, "Trace");
    }

    private function write($msg, $level)
    {
        $date = date('d/m/Y H:i:s');
        $log = "Date:" . $date . " | Level:" . $level . " " . $msg . "\n";
        error_log($log, 3, LOGDIR . "ccm.log");
    }

    public function Debug($message)
    {
        $this->write($message, "Debug");
    }

    public function Info($message)
    {
        $this->write($message, "Info");
    }

    public function Warning($message)
    {
        $this->write($message, "Warning");
    }

    public function Error($message)
    {
        $this->write($message, "Error");
    }

    public function Fatal($message)
    {
        $this->write($message, "Fatal");
    }
}