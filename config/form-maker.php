<?php

return [
    'forms' => [
        [
            'label'    => 'Vendor Mappings',
            'name'     => 'vendor_mappings',
            'settings' => [
                'showInRestApi' => true,
                'storage'       => [
                    'type'     => 'json',
                    'location' => 'shared_table'
                ],
                'conditions'    => [
                    'term_page' => [
                        'taxonomy' => 'wcpv_product_vendors',
                        'action_name' => 'Adjust Mappings',
                        'page_title' => 'Vendor Mappings',
                        'menu_title' => 'Vendor Mappings',
                        'slug' => 'vendor_mappings'
                    ]
                ]
            ],
            'fields'   => [
                [
                    'type'          => 'repeater',
                    'label'         => 'Vendor Mappings',
                    'name'          => 'vendor_mappings',
                    'subfields'     => [
                        [
                            'type'           => 'text',
                            'label'          => 'Vendor Category',
                            'name'           => 'vendor_category',
                            'defaultValue'   => '',
                            'required'       => true,
                            'characterLimit' => 50
                        ],
                        [
                            'type'         => 'multiselect',
                            'label'        => 'Product Gender',
                            'name'         => 'product_gender',
                            'defaultValue' => [],
                            'required'     => true,
                            'options'      => [
                                [
                                    'value' => 'men',
                                    'label' => 'Men'
                                ],
                                [
                                    'value' => 'women',
                                    'label' => 'Women'
                                ],
                                [
                                    'value' => 'unisex',
                                    'label' => 'Unisex'
                                ]
                            ]
                        ],
                        [
                            'type'         => 'multiselect',
                            'label'        => 'Product Type',
                            'name'         => 'product_type',
                            'defaultValue' => [],
                            'required'     => true,
                            'options'      => [
                                [
                                    'value' => 'simple',
                                    'label' => 'Simple'
                                ],
                                [
                                    'value' => 'variable',
                                    'label' => 'Variable'
                                ]
                            ]
                        ],
                        [
                            'type'         => 'multiselect',
                            'label'        => 'Attributes',
                            'name'         => 'attributes',
                            'defaultValue' => [],
                            'required'     => true,
                            'options'      => []
                        ],
                    ]
                ],

            ]
        ]
    ]
];
