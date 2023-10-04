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
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'executeTask') {
        $module->executeTasksFromBDD();
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'executeTaskTest') {
        $module->executeTasksFromBDD(true);
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'setProductTask') {
        $module->setTasksFromAPI(['product']);
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'setStockTask') {
        $module->setTasksFromAPI(['stock']);
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'orderCategoryTask') {
        $module->setTasksFromAPI(['orderCategory']);
    } elseif (isset($_GET["action"]) && $_GET["action"] == 'setCategoryTask') {
        $module->setTasksFromAPI(['category']);
    }
} catch (Throwable $e) {
    $module->setLogTest(
        'Error cron : ' . $e->__toString(),
        null,
        __DIR__ . '/logs_cron/log_' . date('y-m-d-H') . 'h.log'
    );
    var_dump($e->__toString());
    http_response_code(500);
}
