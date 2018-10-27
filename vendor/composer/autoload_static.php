<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc848246a7c170414c7bbc55f2b722e5f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'HTML_Formatter' => __DIR__ . '/..' . '/ressio/pharse/pharse_formatter.php',
        'HTML_NODE_ASP' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_CDATA' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_COMMENT' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_CONDITIONAL' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_DOCTYPE' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_EMBEDDED' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_TEXT' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_NODE_XML' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_Node' => __DIR__ . '/..' . '/ressio/pharse/pharse_node_html.php',
        'HTML_Parser' => __DIR__ . '/..' . '/ressio/pharse/pharse_parser_html.php',
        'HTML_Parser_Base' => __DIR__ . '/..' . '/ressio/pharse/pharse_parser_html.php',
        'HTML_Parser_HTML5' => __DIR__ . '/..' . '/ressio/pharse/pharse_parser_html.php',
        'HTML_Selector' => __DIR__ . '/..' . '/ressio/pharse/pharse_selector_html.php',
        'JSCompilerContext' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'JSMinPlus' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'JSNode' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'JSParser' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'JSToken' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'JSTokenizer' => __DIR__ . '/..' . '/ressio/pharse/third party/jsminplus.php',
        'Pharse' => __DIR__ . '/..' . '/ressio/pharse/pharse.php',
        'Tokenizer_Base' => __DIR__ . '/..' . '/ressio/pharse/pharse_tokenizer.php',
        'Tokenizer_CSSQuery' => __DIR__ . '/..' . '/ressio/pharse/pharse_selector_html.php',
        'XML_Parser_Array' => __DIR__ . '/..' . '/ressio/pharse/pharse_xml2array.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc848246a7c170414c7bbc55f2b722e5f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc848246a7c170414c7bbc55f2b722e5f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc848246a7c170414c7bbc55f2b722e5f::$classMap;

        }, null, ClassLoader::class);
    }
}