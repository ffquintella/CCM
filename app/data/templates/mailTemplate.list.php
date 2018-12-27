<?php

require_once ROOT . "/class/linkedList.class.php";
require_once ROOT . "/data/templates/mailTemplate.class.php";

class mailTemplateStorage extends singleton
{

    private $templateList;

    function __construct()
    {
        $this->templateList = new linkedList();
    }

    function addT($template)
    {
        $this->templateList->insertLast($template);
    }

    function getTemplates()
    {
        return $this->templateList;
    }

}

function addT($template)
{
    mailTemplateStorage::get_instance()->addT($template);
}

function getTemplateList()
{
    return mailTemplateStorage::get_instance()->getTemplates();
}