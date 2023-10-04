<?php

require(__DIR__ . '/../../config/config.inc.php');
require(__DIR__ . '/../../init.php');

/**
 * @var dioqaapiconnexion $module
 */
$module = Module::getInstanceByName('dioqaapiconnexion');

if (isset($_GET["action"])) {
    try {
        if ($_GET["action"] == 'setProductTask') {
            $module->setTasksFromAPI(['product']);
        } elseif ($_GET["action"] == 'setStockTask') {
            $module->setTasksFromAPI(['stock']);
        } elseif ($_GET["action"] == 'setCategoryTask') {
            $module->setTasksFromAPI(['category']);
        } elseif ($_GET["action"] == 'setBrandTask') {
            $module->setTasksFromAPI(['brand']);
        } elseif ($_GET["action"] == 'setFeatureTask') {
            $module->setTasksFromAPI(['feature']);
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
}
