<?php
/**
 * Arquivo de configurações de variáveis
 * User: felipe
 * Date: 26/02/14
 * Time: 19:52
 */

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if (!defined('ROOT'))
    define('ROOT', dirname(__FILE__));

date_default_timezone_set("America/Sao_Paulo");

/* ENTRE COM AS VARIÁVEIS DO LOG */
$logLevel = "DEBUG";
define('LOGLEVEL', "DEBUG");


//define('LOGDIR', ROOT."/../logs/");
if (file_exists("/var/log/") && (get_current_user() == 'root' || get_current_user() == 'nginx' )) {
    define('LOGDIR', "/var/log/gcc/");
} else {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        //echo 'This is a server using Windows!';
        if (!file_exists("c:\\temp")) {
            mkdir("c:\\temp");
        }
        define('LOGDIR', "c:\\temp\\logs");
    } else {
        define('LOGDIR', "/tmp/logs/");
    }
}

define('SESSION_TIME', 600); // 10m

define('LOGPROVIDER', 'monolog');  // now can be monolog or internal
define('LOGCLASS', ROOT . "/class/log-monolog-stream.class.php");

define('VERBOSELEVEL', verbose::DEBUG);

//define('TEST_VERBOSE_LEVEL', verbose::INFO);
define('TEST_VERBOSE_LEVEL', verbose::DEBUG);

/* Variáveis genéricas */
define('SMTPServer', "smtp.abc.com");
define('FROM', "ccm@abc.com");
define('FROM_NAME', "CCM Server");

/* Controle de ambiente */
//define('PRODServer', "DC5086");
//define('DevSMTPServer', "smtp.abc.com");

/* Variaveis de controle de request */
define('HTTP_REQ_TIMEOUT', 15);

define('PHP_TIMEOUT', 300);

/* Autenticação LDAP */
define('LDAPServer', "ldaps://ldap1");
define('LDAPServer2', "ldaps://ldap2");
define('LDAPServer3', "ldaps://ldap3");
define('LDAPPort', 636);
define('LDAPUserPrefix', '@abc.com');

// CUIDADO !!!!
// AMBOS precisam estar on para entrar em produção
define('HTTPSRequired', false);
define('AUTENTICATIONRequired', false);

/* Controle das senhas */
define('PASS_SIZE', 25);
define('USER_PASS_SIZE', 15);


/* APPS */
define('APP_KEY_SIZE', 32);

/* Variáveis de autentication */
define('AUTH_TOKEN_TIME', 60);
//define('SHARED_VALUES_SERVER', 'memcache');
//define('SHARED_VALUES_IMPLEMENTATION', 'liteMemcache');

define('SHARED_VALUES_SERVER', 'redis');
define('SHARED_VALUES_IMPLEMENTATION', 'predis');


/* Variáveis do cofre */
define('VAULT_TYPE', 'pmp');
//define('VAULT_SERVER1_URL', 'https://error.fgv.br');
define('VAULT_SERVER1_URL', 'https://c1.abc.com');
define('VAULT_SERVER2_URL', 'https://c2.abc.com');
define('VAULT_BASE_URI', '/restapi/json/v1');
define('VAULT_AUTHTOKEN', 'xxxxxxxxx');

/* CACHE */
define('CACHE_DEFAULT_TIMEOUT', 1200); // 20 minutes
define('CACHE_DNS_TIMEOUT', 600); // 10 minutes

/** Creating the basic files if they don't exists */
if (!file_exists(LOGDIR)) mkdir(LOGDIR);

class verbose
{
    const TRACE = 6;
    const DEBUG = 5;
    const INFO = 4;
    const ALERT = 3;
    const WARNING = 2;
    const ERROR = 1;
    const NONE = 0;
}