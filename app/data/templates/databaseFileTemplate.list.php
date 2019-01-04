<?php

namespace ccm;

require_once ROOT . "/class/linkedList.class.php";

//require_once ROOT."/data/templates/databaseFileTemplate.list.php";

class databaseFileTemplateStorage extends singleton
{

    private $templateList;

    function addT($template)
    {
        if ($this->templateList == null) $this->templateList = new linkedList();
        $this->templateList->insertLast($template);
    }

    function getTemplates()
    {
        return $this->templateList;
    }

}

function addDBT($template)
{
    databaseFileTemplateStorage::get_instance()->addT($template);
}

function getDBTemplateList()
{
    return databaseFileTemplateStorage::get_instance()->getTemplates();
}

function getDBTemplate($version)
{

    $tList = databaseFileTemplateStorage::get_instance()->getTemplates();

    $tList->rewind();

    while ($tList->valid()) {
        $dbt = $tList->current()->readNode();
        if ($dbt->getVersion() == $version) return $dbt;
        $tList->next();
    }

    throw new \Exception("The specified themplate couldn't be found");
}