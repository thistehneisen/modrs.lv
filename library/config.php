<?php
$config = array(
    'host' => 'https://www.modrs.lv/',
    'pages' => array('par', 'pieteikties', 'kontakti'),
    'database' => array(
        array('host' => 'localhost', 'db' => '', 'prefix' => 'ss_', 'user' => '', 'password' => '')
    ),
    'crawlUrls' => array(
        'https://www.ss.com/lv/real-estate/flats/riga/' => 'Dzīvokļi (Rīgā)',
        'https://www.ss.com/lv/real-estate/homes-summer-residences/riga/' => 'Mājas (Rīgā)',
        'https://www.ss.com/lv/real-estate/flats/' => 'Dzīvokļi',
        'https://www.ss.com/lv/transport/cars/' => 'Automašīnas',
        'https://www.ss.com/lv/work/are-required/' => 'Meklē darbiniekus',
        'https://www.ss.com/lv/construction/civil-work/' => 'Konstrukcijas darbi',
        'https://www.ss.com/lv/real-estate/homes-summer-residences/' => 'Mājas un vasarnīcas'
    )
);

require_once __DIR__.'/../vendor/db.class.php';
$db = new db($config['database'], '_write');
