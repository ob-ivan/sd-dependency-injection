<?php
namespace tests;

use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\AutoDeclarerTrait;
use SD\DependencyInjection\ContainerAwareTrait;

class ParentConsumer implements AutoDeclarerInterface
{
    use AutoDeclarerTrait;
    use ContainerAwareTrait;
}
