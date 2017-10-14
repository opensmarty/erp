<?php

$params = require(__DIR__ . '/params.php');
require_once(__DIR__.'/../helpers/ItemStatus.php');
require_once(__DIR__.'/../helpers/Options.php');
$config = [
    'id' => 'basic',
    'timezone' => 'Asia/Shanghai',
    'language' => 'zh-CN',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'name'=>'SSY ERP',
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            'mainLayout' => '@app/views/layouts/main.php',
            'controllerMap' => [
                'user' => [
                    'class' => 'app\controllers\UserController',
                ],
                'assignment' => [
                    'class' => 'mdm\admin\controllers\AssignmentController',
                    'userClassName' => 'app\models\User',
                    'idField' => 'user_id',
                    'usernameField' => 'username',
//                    'fullnameField' => 'profile.full_name',
//                    'extraColumns' => [
//                        [
//                            'attribute' => 'full_name',
//                            'label' => 'Full Name',
//                            'value' => function($model, $key, $index, $column) {
//                                return $model->profile->full_name;
//                            },
//                        ],
//                        [
//                            'attribute' => 'dept_name',
//                            'label' => 'Department',
//                            'value' => function($model, $key, $index, $column) {
//                                return $model->profile->dept->name;
//                            },
//                        ],
//                        [
//                            'attribute' => 'post_name',
//                            'label' => 'Post',
//                            'value' => function($model, $key, $index, $column) {
//                                return $model->profile->post->name;
//                            },
//                        ],
//                    ],
//                    'searchClass' => 'app\models\UserSearch'
                ],
            ],
        ]
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Woqf07_MS39H2o95QuRnyG9VoiT0KfMH',
        ],
        'cache' => [
//            'class' => 'yii\caching\FileCache',
            'class' => 'yii\caching\DummyCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'loginUrl' => ['/site/login'],
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],

        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache'
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'appendTimestamp' => false,
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
                    'skin' => 'skin-blue',
                ],
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'js' => [
                        '//cdn.bootcss.com/jquery/1.12.4/jquery.min.js',
                    ]
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'menu*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'menu' => 'menu.php',
                        'menu/error' => 'error.php',
                    ],
                ],
                'rbac*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'rbac' => 'rbac-admin.php',
                        'rbac/error' => 'error.php',
                    ],
                ],
                'user' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'user' => 'user.php',
                        'user/error' => 'error.php',
                    ],
                ],
                'route*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'route' => 'route.php',
                        'route/error' => 'route.php',
                    ],
                ],
            ],
        ],

    ],
    //components end
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/*',
            'api/*',
            'admin/*',
            //'some-controller/some-action',
            // The actions listed here will be allowed to everyone including guests.
            // So, 'admin/*' should not appear here in the production, of course.
            // But in the earlier stages of your development, you may probably want to
            // add a lot of actions here until you finally completed setting up rbac,
            // otherwise you may not even take a first step.
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
//     $config['bootstrap'][] = 'debug';
//     $config['modules']['debug'] = [
//         'class' => 'yii\debug\Module',
//         'allowedIPs' => ['127.0.0.1','::1','36.*.*.*','125.*.*.*','1.*.*.*'],
//     ];
}

return $config;
