#!/usr/bin/env php
<?php

require_once 'includes.php';

$console = new ConsoleKit\Console();


$console->addCommand('initStorageCommand');
$console->addCommand('migrateDataCommand');


$console->run();