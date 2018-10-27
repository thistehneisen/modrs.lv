<?php
function apilog($message) { print(strftime('%F %X').": {$message}\r\n"); }
function getPath($url) {
    $url = rtrim($url, '/');
    $url = str_replace(array('http://', 'https://', 'www.ss.com'), array(NULL, NULL, NULL), $url);
    return $url;
}
