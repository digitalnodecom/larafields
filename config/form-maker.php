<?php

return [
    'forms' => [
        [
            'label'    => 'Example Field Group',
            'name'     => 'example_field_group',
            'settings' => [
                'showInRestApi' => true,
                'storage'       => [
                    'type'     => 'json',
                    'location' => 'shared_table'
                ],
                'conditions'    => [
                    'term_page' => [
                        'taxonomy' => 'wcpv_product_vendors',
                        'action_name' => 'Change Mappings',
                        'page_title' => 'Brand Testing',
                        'menu_title' => 'Brand Testing',
                        'slug' => 'brand_testing'
                    ],
                    'page' => [
                        'page_title' => 'Testing',
                        'menu_title' => 'Testing',
                        'slug' => 'testing'
                    ],
                ]
            ],
            'fields'   => [
                [
                    'type'           => 'text',
                    'label'          => 'Primer input',
                    'name'           => 'primer_input',
                    'defaultValue'   => 'ja ti prefil',
                    'required'       => true,
                    'characterLimit' => 50
                ],
                [
                    'type'           => 'text',
                    'label'          => 'Primer input dva',
                    'name'           => 'primer_input_dva',
                    'defaultValue'   => 'ja ti prefil dva',
                    'required'       => true,
                    'characterLimit' => 50
                ],
                [
                    'type'           => 'textarea',
                    'label'          => 'Textform Input',
                    'name'           => 'textform_input',
                    'defaultValue'   => 'Sample multiline text',
                    'required'       => false,
                    'characterLimit' => 200
                ],
                [
                    'type'         => 'number',
                    'label'        => 'Brojche Input',
                    'name'         => 'brojche_input',
                    'defaultValue' => 10,
                    'required'     => true,
                    'minValue'     => 1,
                    'maxValue'     => 100
                ],
                [
                    'type'         => 'multiselect',
                    'label'        => 'Product Gender',
                    'name'         => 'product_gender',
                    'defaultValue' => '',
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
                //[
                //    'type'             => 'relationship',
                //    'label'            => 'Relationship Field',
                //    'name'             => 'relationship_field',
                //    'relationshipTo'   => [
                //        'post_type' => 'order'
                //    ],
                //    'required'         => true,
                //    'minRelationships' => 1,
                //    'maxRelationships' => 5,
                //    'selectMultiple'   => true,
                //    'bidirectional'    => false
                //],
                [
                    'type'          => 'repeater',
                    'label'         => 'Repeatable Group',
                    'name'          => 'repeater_field',
                    'subfields'     => [
                        [
                            'type'           => 'text',
                            'label'          => 'Sub Field 1',
                            'name'           => 'sub_field_1',
                            'defaultValue'   => '',
                            'required'       => true
                        ],
                        [
                            'type'           => 'text',
                            'label'          => 'Sub Field 2',
                            'name'           => 'sub_field_2',
                            'defaultValue'   => '',
                            'required'       => true
                        ]
                    ]
                ]

            ]
        ],
        [
            'label'    => 'Example Field Group Term',
            'name'     => 'example_field_group_term',
            'settings' => [
                'showInRestApi' => true,
                'storage'       => [
                    'type'     => 'json',
                    'location' => 'shared_table'
                ],
                'conditions'    => [
                    [ 'taxonomy' => 'product_brand' ]
                ]
            ],
            'fields'   => [
                [
                    'type'          => 'repeater',
                    'label'         => 'Employees',
                    'name'          => 'employees',
                    'subfields'     => [
                        [
                            'type'           => 'text',
                            'label'          => 'First Name',
                            'name'           => 'first_name',
                            'defaultValue'   => '',
                            'required'       => true,
                        ],
                        [
                            'type'           => 'text',
                            'label'          => 'Last Name',
                            'name'           => 'last_name',
                            'defaultValue'   => '',
                            'required'       => true,
                        ]
                    ]
                ],
                [
                    'type'           => 'text',
                    'label'          => 'Primer input',
                    'name'           => 'primer_input',
                    'defaultValue'   => 'ja ti prefil',
                    'required'       => true,
                    'characterLimit' => 50
                ],
                [
                    'type'           => 'text',
                    'label'          => 'Primer input dva',
                    'name'           => 'primer_input_dva',
                    'defaultValue'   => 'ja ti prefil dva',
                    'required'       => true,
                    'characterLimit' => 50
                ],
                [
                    'type'           => 'textarea',
                    'label'          => 'Textform Input',
                    'name'           => 'textform_input',
                    'defaultValue'   => 'Sample multiline text',
                    'required'       => false,
                    'characterLimit' => 200
                ],
                [
                    'type'         => 'number',
                    'label'        => 'Brojche Input',
                    'name'         => 'brojche_input',
                    'defaultValue' => 10,
                    'required'     => true,
                    'minValue'     => 1,
                    'maxValue'     => 100
                ],
                [
                    'type'         => 'multiselect',
                    'label'        => 'Product Gender',
                    'name'         => 'product_gender',
                    'defaultValue' => '',
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
                    'type'             => 'relationship',
                    'label'            => 'Relationship Field',
                    'name'             => 'relationship_field',
                    'relationshipTo'   => [
                        'post_type' => 'order'
                    ],
                    'required'         => true,
                    'minRelationships' => 1,
                    'maxRelationships' => 5,
                    'selectMultiple'   => true,
                    'bidirectional'    => false
                ],
                //[
                //    'type'        => 'repeater',
                //    'label'       => 'Repeater Field',
                //    'name'        => 'repeater_field',
                //    'layout'      => 'block',
                //    'required'    => true,
                //    'minRows'     => 1,
                //    'maxRows'     => 5,
                //    'buttonLabel' => 'Add Row',
                //    'fields'      => [
                //        [
                //            'type'           => 'text',
                //            'label'          => 'Nested Text Input',
                //            'name'           => 'nested_text_input',
                //            'defaultValue'   => '',
                //            'required'       => true,
                //            'characterLimit' => 50
                //        ],
                //        [
                //            'type'             => 'relationship',
                //            'label'            => 'Nested Relationship Field',
                //            'name'             => 'nested_relationship_field',
                //            'relationshipTo'   => 'taxonomy',
                //            'required'         => false,
                //            'minRelationships' => 0,
                //            'maxRelationships' => 3,
                //            'selectMultiple'   => true,
                //            'bidirectional'    => false
                //        ]
                //    ]
                //]
            ]
        ]
    ]
];
