<?php

require "vendor/autoload.php";

$f3 = require('vendor/bcosca/fatfree/lib/base.php');

$f3->config('application.cfg');
$config = $f3->get('config');

// trying to read the .config file
if (file_exists('.config')) {
    $_config = json_decode(file_get_contents('.config'), true);

    $config = array_merge($config, $_config);
}


$f3->route('GET /', function($f3) use ($config) {
//        $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);
//        
//        $zabbixClient->auth();
//        $zabbixClient->getHosts();
//        $zabbixClient->getApplicationsByHostId([10207]);
    //$f3->set('var', $zabbixClient->hello());

    if (is_file('.config')) {
        $_config = json_decode(file_get_contents('.config'), true);
    }

    $f3->set('api_url', isset($_config['api_url']) ? $_config['api_url'] : null);
    $f3->set('user', isset($_config['user']) ? $_config['user'] : null);
    $f3->set('password', isset($_config['password']) ? $_config['password'] : null);

    $template = new Template();
    echo $template->render('templates/index.htm');
}
);

$f3->route('POST /create', function($f3) {
    $host = $f3->get('POST.host');
    $nameTemplate = $f3->get('POST.name_template');
    $keyTemplate = $f3->get('POST.key_template');
    $values = $f3->get('POST.values');
    $type = $f3->get('POST.type');
    $formula = $f3->get('POST.formula');

    $values = explode("\n", $values);

    foreach ($values as $value) {
        $value = trim($value);
        $value = str_replace(array("."), "", $value);

        $name = str_replace("*", $value, $nameTemplate);
        $key = str_replace("*", $value, $keyTemplate);
        $formula = str_replace("*", $value, $formulaTemplate);

        var_dump($name, $key, $formula);
        echo "<hr/>";
    }
}
);

$f3->route('POST /remove', function($f3) {
    echo $f3->get('POST.name_template');
}
);

$f3->route('POST /config', function($f3) use ($config) {
    $_config = ['api_url' => $f3->get('POST.api_url'),
        'user' => $f3->get('POST.user'),
        'password' => $f3->get('POST.password')];

    file_put_contents("./.config", json_encode($_config));

    Header("Location: " . $config['home']);
}
);

$f3->run();
