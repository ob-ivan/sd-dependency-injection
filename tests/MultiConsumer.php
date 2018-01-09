<?php
namespace tests;

use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\AutoDeclarerTrait;
use SD\DependencyInjection\ContainerAwareTrait;
use SD\DependencyInjection\DeclarerInterface;

class MultiConsumer implements AutoDeclarerInterface, DeclarerInterface {
    use AutoDeclarerTrait;
    use ContainerAwareTrait;

    private $post;
    private $request;

    public function declareDependencies() {
        return ['post', 'request'];
    }

    public function setPost($post) {
        $this->post = $post;
    }

    public function getPost() {
        return $this->post;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    public function getContainer() {
        return $this->container;
    }
}
