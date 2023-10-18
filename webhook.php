<?php

require(__DIR__ . '/../../config/config.inc.php');
require(__DIR__ . '/../../init.php');

/**
 * @var dioqaapiconnexion $module
 */
$module = Module::getInstanceByName('dioqaapiconnexion');

if (isset($_GET["action"])) {
    $action = $_GET["action"];
    try {
        switch ($action) {
            case 'place':
                break;
            case 'device':
                $module->setTasksFromAPI(['product']);
                break;
            case 'stock':
                $module->setTasksFromAPI(['stock']);
                break;
            case 'product':
                $module->setTasksFromAPI(['product_crd']);
                break;
            case 'group':
                $module->setTasksFromAPI(['category']);
                break;
            case 'model':
                $module->setTasksFromAPI(['category', 'feature']);
                break;
            case 'brand':
                $module->setTasksFromAPI(['category', 'feature', 'brand']);
                break;
            case 'productType':
                $module->setTasksFromAPI(['category', 'feature']);
                break;
            case 'color':
                $module->setTasksFromAPI(['feature']);
                break;
            default:
                break;
        }
    } catch (Throwable $e) {
        $module->setLogTest(
            'Error webhook : ' . $e->__toString(),
            null,
            __DIR__ . '/logs_webhook/log_' . date('y-m-d-H') . 'h.log'
        );
        var_dump($e->__toString());
        http_response_code(500);
    } finally {
        $module->setLogTest(
            'call action : ' . $action,
            null,
            __DIR__ . '/logs_webhook/log_' . date('y-m-d-H') . 'h.log'
        );
    }
}
