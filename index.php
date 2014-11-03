<?php

require "vendor/autoload.php";

$f3 = require('vendor/bcosca/fatfree/lib/base.php');

$f3->config('application.cfg');
$config = $f3->get('config');

// trying to read the .config file
$_config = Config::getAll();


$config = array_merge($config, $_config);
$f3->set('config', $config);

// set flash messages
if ($f3->exists('SESSION.successMessage')) {
    $f3->set('successMessage', $f3->get('SESSION.successMessage'));
    $f3->clear('SESSION.successMessage');
} else {
    $f3->set('successMessage', null);
}

if ($f3->exists('SESSION.errorMessage')) {
    $f3->set('errorMessage', $f3->get('SESSION.errorMessage'));
    $f3->clear('SESSION.errorMessage');
} else {
    $f3->set('errorMessage', null);
}
// -- end of flash messages


$f3->route('GET /', function($f3) use ($config) {
    if ($f3->exists('SESSION.activeTab')) {
        $activeTab = $f3->get('SESSION.activeTab');
    } else {
        $activeTab = 'create';
    }

    $f3->set('activeTab', $activeTab);

    $f3->set('api_url', isset($config['apiUrl']) ? $config['apiUrl'] : null);
    $f3->set('user', isset($config['user']) ? $config['user'] : null);
    $f3->set('password', isset($config['password']) ? $config['password'] : null);
    $f3->set('host_ids', isset($config['hostIds']) ? trim(implode(',', $config['hostIds'])) : null);

    $f3->set('hosts', isset($config['hosts']) ? $config['hosts'] : null);

    $template = new Template();
    echo $template->render('templates/index.htm');
}
);

$f3->route('GET /set-active-tab', function($f3) use ($config) {

    if ($f3->exists('GET.activeTab')) {
        $f3->set('SESSION.activeTab', $f3->get('GET.activeTab'));
        $result = ['error' => false];
    } else {
        $result = ['error' => true];
    }

    echo json_encode($result);
});

$f3->route('GET /get-applications-by-host-id', function($f3) use ($config) {

    $host_id = $f3->get('GET.host_id');

    $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);

    $zabbixClient->auth();
    $tmpApplications = $zabbixClient->getApplicationsByHostId($host_id);

    $applications = [];
    foreach ($tmpApplications as $tmpApplication) {
        $applications[] = ['applicationid' => $tmpApplication['applicationid'], 'name' => $tmpApplication['name']];
    }

    echo json_encode($applications);
});

$f3->route('POST /create', function($f3) use ($config) {
    $host = $f3->get('POST.host');
    $application = $f3->get('POST.application');
    $nameTemplate = $f3->get('POST.name_template');
    $keyTemplate = $f3->get('POST.key_template');
    $values = $f3->get('POST.values');
    $type = $f3->get('POST.type');
    $formulaTemplate = $f3->get('POST.formula_template');

    $values = explode("\n", $values);

    switch ($type) {
        case 'calculated':
            $type = 15;
            break;
        case 'zabbix-trapper':
            $type = 2;
            break;
    }

    if (!$host || !$application || !$nameTemplate || !$keyTemplate || !$values || !$type) {
        $f3->set('SESSION.errorMessage', 'You should fill the form properly!');
        Header("Location: " . $config['home']);
        return;
    }

    $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);

    $zabbixClient->auth();

    $createdItems = 0;

    foreach ($values as $value) {
        $value = trim($value);
        //$value = str_replace(array("."), "", $value);

        $name = str_replace("*", $value, $nameTemplate);
        $key = str_replace("*", $value, $keyTemplate);
        $formula = str_replace("*", $value, $formulaTemplate);

        $item = [
            'name' => $name,
            'key' => $key,
            'hostid' => $host,
            'applications' => [$application],
            'type' => $type
        ];
        
        if ($formulaTemplate) {
            $item['formula'] = $formula;
        }

        $zabbixClient->createItem($item);

        $createdItems++;
    }

    $f3->set('SESSION.successMessage', "{$createdItems} items were created!");

    Header("Location: " . $config['home']);
}
);

$f3->route('GET /remove-search', function($f3) use ($config) {
    $host = $f3->get('GET.host');
    $key_template = $f3->get('GET.key_template');

    $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);

    $zabbixClient->auth();
    $tmpItems = $zabbixClient->getItemsByHost([$host]);
    
    $key_template = preg_quote($key_template,'/');
    $key_template = str_replace('\*', '(.*?)', $key_template);
    
    $items = [];
    foreach($tmpItems as $tmpItem) {
        if(preg_match('/'.$key_template.'/si', $tmpItem['key_'])) {
            $items[] = ['itemid' => $tmpItem['itemid'], 'key' => $tmpItem['key_']];
        }
    }
    
    echo json_encode($items);
});

$f3->route('POST /remove', function($f3) use ($config) {
    $itemsToRemove = $f3->get('POST.itemsToRemove');
    
    if(!count($itemsToRemove)) {
        $f3->set('SESSION.errorMessage', 'Error attempt to remove items');
        Header("Location: ".$config['home']);
    }
    
    $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);
    $zabbixClient->auth();
    
    $zabbixClient->removeItems($itemsToRemove);
    
    $f3->set('SESSION.successMessage', count($itemsToRemove)." were removed!");
    Header("Location: ".$config['home']);
}
);

$f3->route('POST /config', function($f3) use ($config) {
    $hostIds = $f3->get('POST.host_ids');

    if ($hostIds === "") {
        $hostIds = [];
    } else {
        $hostIds = explode(',', $f3->get('POST.host_ids'));

        foreach ($hostIds as &$value) {
            $value = trim($value);
        }
    }

    $_config = ['apiUrl' => $f3->get('POST.api_url'),
        'user' => $f3->get('POST.user'),
        'password' => $f3->get('POST.password'),
        'hostIds' => $hostIds];

    Config::setMap($_config);
    $_config = Config::getAll();

    $config = array_merge($config, $_config);

    $zabbixClient = new Zabbix\Client($config['apiUrl'], $config['user'], $config['password']);

    $zabbixClient->auth();
    $tmpHosts = $zabbixClient->getHosts($config['hostIds']);

    $hosts = [];
    foreach ($tmpHosts as $tmpHost) {
        $hosts[] = ['hostid' => $tmpHost['hostid'], 'name' => $tmpHost['name']];
    }

    Config::set('hosts', $hosts);

    $f3->set('SESSION.successMessage', 'Configuration was done!');

    Header("Location: " . $config['home']);
}
);

$f3->run();
