<?php
namespace tests;

use SD\DependencyInjection\ProviderInterface;

class InjectionReceiverProvider implements ProviderInterface {
    public function getServiceName(): string {
        return 'service';
    }

    public function provide(string $param = '') {
        return (object)[
            'param' => $param,
        ];
    }
}
