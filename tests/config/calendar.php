<?php

return [
    'Calendar' => [
        'Configs' => [
            'color' => '#337ab7',
        ],
        'Types' => [
            [
                'name' => 'Default',
                'value' => 'default',
                'calendar_events' => [
                    'default' => [
                        'properties' => [
                            'start_date' => [
                                'options' => [
                                    'startTime' => '09:00',
                                ],
                            ],
                            'end_date' => [
                                'options' => [
                                    'endTime' => '18:00',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Shifts',
                'value' => 'shifts',
                'calendar_events' => [
                    'morning_shift' => [
                        'properties' => [
                            'start_date' => [
                                'options' => [
                                    'startTime' => '09:00',
                                ],
                            ],
                            'end_time' => [
                                'options' => [
                                    'endTime' => '17:00',
                                ],
                            ],
                        ],
                    ],
                    'evening_shift' => [
                        'properties' => [
                            'start_date' => [
                                'options' => [
                                    'startTime' => '17:00',
                                ]
                            ],
                            'end_date' => [
                                'options' => [
                                    'endTime' => '01:00'
                                ]
                            ],
                        ],
                    ],
                    'night_shift' => [
                        'properties' => [
                            'start_date' => [
                                'options' => [
                                    'startTime' => '01:00',
                                ],
                            ],
                            'end_date' => [
                                'options' => [
                                    'endTime' => '09:00',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ], // Types
    ]
];
