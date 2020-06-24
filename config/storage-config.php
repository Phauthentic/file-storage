<?php

declare(strict_types=1);

return [
    'storage' => [
        'adapters' => [
            'local' => [
                'class' => \Phauthentic\Infrastructure\Storage\Factories\LocalFactory::class,
                'options' => [
                    'root' => '/'
                ]
            ]
        ],
        'manipulations' => [
            'model' => [
                'avatar' => [
                    'optimizeOriginal' => false,
                    'variants' => [
                        'widen600' => [
                            'widen' => [200],
                            'thumb' => [200],
                            'flip' => ['left'],
                            'optimize' => true
                        ]
                    ]
                ]
            ],
            'collection' => [
                'avatar' => [
                    'optimizeOriginal' => false,
                    'variants' => [
                        'widen600' => [
                            'widen' => [200],
                            'thumb' => [200],
                            'flip' => ['left'],
                            'optimize' => true
                        ]
                    ]
                ]
            ]
        ]
    ]
];
