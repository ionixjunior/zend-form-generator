<?php

require_once 'autoloader.php';

# Use adapter PDO_PGSQL or PDO_MYSQL :)
$dbConfig = new \Zend_Config(
    array(
        'database' => array(
            'adapter' => ADAPTER,
            'params' => array(
                'host'     => HOST,
                'dbname'   => DATABASE,
                'username' => USER,
                'password' => PASS,
                'port'	   => PORT
            )
        )
    )
);

$dbAdapter = \Zend_Db::factory($dbConfig->database);

$cli = new \Symfony\Component\Console\Application('Ionix Zend Form Generator', 'Version 1');
$cli->setCatchExceptions(true);
$cli->getHelperSet()->set(new \Ionix\Console\Helper\DbAdapter($dbAdapter), 'dbAdapter');
$cli->addCommands(array(
	new \Ionix\Console\Command\Zend\Form\GenerateFormsCommand()
));    
$cli->run();