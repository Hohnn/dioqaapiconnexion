<?php

require(__DIR__ . '/../../config/config.inc.php');
require(__DIR__ . '/../../init.php');

/**
 * @var dioqaapiconnexion $module
 */
$module = Module::getInstanceByName('dioqaapiconnexion');


try {
    if (isset($_GET["action"]) && $_GET["action"] == 'dev') {
        $module->dev();
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'executetask') {
        $module->executeTasksFromBDD();
    }
} catch (Exception $e) {
    $module->setLogTest(
        'Error cron : ' . $e->__toString(),
        null,
        __DIR__ . '/logs_test/log_' . date('y-m-d-H') . 'h.log'
    );
    var_dump($e->__toString());
    http_response_code(500);
}
