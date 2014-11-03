<?php

class Config {

    const CONFIG_BASENAME = '.config';
    
    public static function set($key, $value) {
        $config = json_decode(file_get_contents(self::CONFIG_BASENAME), true);
        
        $config[$key] = $value;
        
        file_put_contents('.config', json_encode($config));
        
        return true;
    }
    
    public static function get($key) {
        $config = json_decode(file_get_contents(self::CONFIG_BASENAME), true);
        
        if(isset($config[$key])) {
            return $config[$key];
        } else {
            return null;
        }
    }

}
