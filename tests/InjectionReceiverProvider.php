<?php
namespace tests;

use SD\DependencyInjection\ProviderInterface;

class InjectionReceiverProvider implements ProviderInterface {
    public function getServiceName(): string {
        return 'service';
    }

    public function provide($param) {
        return (object)[
            'param' => $param,
        ];
    }
}
