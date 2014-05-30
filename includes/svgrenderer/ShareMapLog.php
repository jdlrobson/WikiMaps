<?php
/*
 * ShareMap PHP library https://github.com/ShareMap/ShareMap-php
 * Developed under ShareMap project http://sharemap.org/
 * Copyright (c) 2014, ShareMap Project, All rights reserved.
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.
 */
namespace ShareMapPhp;

class ShareMapLog {

    public static $logToConsole = false;

    public static function info($msg) {
        if (ShareMapLog::$logToConsole === true) {
            echo $msg . "\n";
        }
    }
    
    public static function logVar($key,$val) {
       ShareMapLog::info($key . " = ".$val);
    }

    public static function error($msg) {
        ShareMapLog::info("ERROR " . $msg);
    }

}
