<?php

class DioqaapiconnexionDevModuleFrontController extends ModuleFrontController
{

    public $ajax;

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:dioqaapiconnexion/views/templates/hook/modalTimeOut.tpl');
    }
}
