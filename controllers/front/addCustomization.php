<?php

class DioqaapiconnexionAddcustomizationModuleFrontController extends ModuleFrontController
{

    public $ajax;

    public function display()
    {
        $this->ajax = 1;

        $this->ajaxRender("hello\n");
    }
}
