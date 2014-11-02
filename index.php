<?php
require "vendor/autoload.php";

$f3 = require('vendor/bcosca/fatfree/lib/base.php');

$f3->route('GET /',
    function($f3) {
        $zabbixClient = new Zabbix\Client();
        
        $f3->set('var', $zabbixClient->hello());
        
        $template=new Template();
        echo $template->render('templates/index.htm');
    }
);

$f3->route('POST /create',
    function($f3) {
        $host = $f3->get('POST.host');
        $nameTemplate = $f3->get('POST.name_template');
        $keyTemplate = $f3->get('POST.key_template');
        $values = $f3->get('POST.values');
        $type = $f3->get('POST.type');
        $formula = $f3->get('POST.formula');
        
        $values = explode("\n", $values);
        
        foreach($values as $value) {
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

$f3->route('POST /remove',
    function($f3) {
        echo $f3->get('POST.name_template');
    }
);

$f3->run();
