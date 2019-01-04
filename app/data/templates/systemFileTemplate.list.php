<?php

namespace ccm;

require_once ROOT . "/class/linkedList.class.php";


class sysFileTemplateStorage extends singleton
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

function addSysT($template)
{
    sysFileTemplateStorage::get_instance()->addT($template);
}

function getSysTemplateList()
{
    return sysFileTemplateStorage::get_instance()->getTemplates();
}

function getSysTemplate($version)
{

    $tList = sysFileTemplateStorage::get_instance()->getTemplates();

    $tList->rewind();

    while ($tList->valid()) {
        $dbt = $tList->current()->readNode();
        if ($dbt->getVersion() == $version) return $dbt;
        $tList->next();
    }

    throw new \Exception("The specified themplate couldn't be found");
}