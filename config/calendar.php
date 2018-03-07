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
                'types' => [
                    'default' => [
                        'name' => 'Event',
                        'value' => 'default_event',
                    ],
                ],
            ],
            [
                'name' => 'Shifts',
                'value' => 'shifts',
                'types' => [
                    'morning_shift' => [
                        'name' => 'Morning Shift',
                        'value' => 'morning_shift',
                    ],
                    'evening_shift' => [
                        'name' => 'Evening Shift',
                        'value' => 'evening_shift',
                    ],
                    'night_shift' => [
                        'name' => 'Night Shift',
                        'value' => 'night_shift',
                    ],
                ],
            ],
        ], // Types
    ]
];
