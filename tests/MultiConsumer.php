<?php
namespace tests;

use SD\DependencyInjection\DeclarerInterface;

class MultiConsumer implements DeclarerInterface {
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
}
