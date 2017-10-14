<?php

return [
    'adminEmail' => 'admin@example.com',
    'mdm.admin.configs' => [
        'defaultUserStatus' => 0, // 0 = inactive, 10 = active
    ],
    'engravings'=>[
        'women' =>[
            "Women Engravings",
            "Women's Engravings",
            "Womens Engravings",
            "Incisione Donna",
            "Incisioni Donna",
            "نقش الكلمة النسائي",
        ],
        'men' =>[
            "Men Engravings",
            "Men's Engravings",
            "Mens Engravings",
            "Incisioni Uomo",
            "Incisione Uomo",
            "نقش الكلمة الرجالي",
        ],
        'none'=>[
            'Engravings',
            'Incisioni',
            'Incisione',
            'Grabados (Envío en 10 días)',
            'نقش كلمة مع تجهيزا منتج ٧ أيام',
        ],
    ],
    'size'=>[
        'women' =>[
            'Women Size',
            "Women's Size",
            "Womens Size",
            'Taglia Donna',
            'مقاس النساء',
        ],
        'men' =>[
            'Men Size',
            "Men's Size",
            "Mens Size",
            'Taglia Uomo',
            'مقاس الرجال',
        ],
        'none'=>[
            'Taglia',
            'Talla',
            'Size',
            'مقاس'
        ],
    ],
    'product_categories' =>[
        //戒指类
        'rings'=>[
            'label'=>'戒指',
            'children'=>[
                'ring_couple'=>[
                    'label'=>'对戒'
                ],
                'ring_single'=>[
                    'label'=>'单戒'
                ],
            ],
        ],

        //项链类
        'necklace'=>[
            'label'=>'项链',
        ],

        //手链类
        'bracelet'=>[
            'label'=>'手链',
        ],
    ],
    'us_state_map'=>[
        "Alabama"=>"AL",
        "Alaska"=>"AK",
        "Arizona"=>"AZ",
        "Arkansas"=>"AR",
        "California"=>"CA",
        "Colorado"=>"CO",
        "Connecticut"=>"CT",
        "Delaware"=>"DE",
        "Florida"=>"FL",
        "Georgia"=>"GA",
        "Hawaii"=>"HI",
        "Idaho"=>"ID",
        "Illinois"=>"IL",
        "Indiana"=>"IN",
        "Iowa"=>"IA",
        "Kansas"=>"KS",
        "Kentucky"=>"KY",
        "Louisiana"=>"LA",
        "Maine"=>"ME",
        "Maryland"=>"MD",
        "Massachusetts"=>"MA",
        "Michigan"=>"MI",
        "Minnesota"=>"MN",
        "Mississippi"=>"MS",
        "Missouri"=>"MO",
        "Montana"=>"MT",
        "Nebraska"=>"NE",
        "Nevada"=>"NV",
        "New hampshire"=>"NH",
	    "New jersey"=>"NJ",
	    "New mexico"=>"NM",
	    "New york"=>"NY",
	    "North carolina"=>"NC",
	    "North dakota"=>"ND",
	    "Ohio"=>"OH",
	    "Oklahoma"=>"OK",
	    "Oregon"=>"OR",
	    "Pennsylvania"=>"PA",
	    "Rhode island"=>"RL",
	    "South carolina"=>"SC",
	    "South dakota"=>"SD",
	    "Tennessee"=>"TN",
	    "Texas"=>"TX",
	    "Utah"=>"UT",
	    "Vermont"=>"VT",
	    "Virginia"=>"VA",
	    "Washington"=>"WA",
	    "West virginia"=>"WV",
	    "Wisconsin"=>"WI",
	    "Wyoming"=>"WY"
    ],
    'comment_visible_group_ids' => [
        'amarley'=>'0,2,5,28,31,35',
        'jeulia'=>'0,2,5,30,33,36'
    ],
    //耗材及扣减耗材用
    'product_types'=>[
        'rings_single'=>'单戒',
        'rings_set'=>'套戒',
        'rings_couple'=>'对戒',
        'necklace'=>'项链',
        'bracelet'=>'手链',
        'earrings'=>'耳环',
        'charms'=>'串珠',
    ]
];
