<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 04/03/14
 * Time: 00:01
 */

namespace ccm;


interface ilog
{

    public function Trace($message);

    public function Debug($message);

    public function Info($message);

    public function Warning($message);

    public function Error($message);

    public function Fatal($message);

} 