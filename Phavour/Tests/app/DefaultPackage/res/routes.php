<?php
return array(
    'Default.index.index' => array(
        'method' => 'GET|POST|PUT|DELETE',
        'path' => '/',
        'runnable' => 'Index::index',
    ),
    'Default.middleware.called' => array(
        'method' => 'GET|POST|PUT|DELETE',
        'path' => '/middleware',
        'runnable' => 'Index::middleware',
    ),
    'Default.viewOnly.test' => array(
        'method' => 'GET',
        'path' => '/view-only',
        'runnable' => 'Index::viewOnly',
        'view.directRender' => true,
        'view.layout' => 'viewOnlyLayout'
    )
);