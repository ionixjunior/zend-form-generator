<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/library'),
    get_include_path(),
)));

require_once 'Zend/Application.php';

$application = new \Zend_Application('development');
$application->setAutoloaderNamespaces(array('Symfony', 'Ionix'));