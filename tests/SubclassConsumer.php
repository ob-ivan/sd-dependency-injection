<?php
namespace tests;

class SubclassConsumer extends ParentConsumer
{
    public function getService()
    {
        return $this->getContainer();
    }
}
