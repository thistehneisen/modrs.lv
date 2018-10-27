<?php
/**
 * Parses the BBC news feed
 *
 * Demonstrates selectors
 *
 * @author RESS.IO Team
 * @package Pharse
 * @link https://github.com/ressio/pharse
 *
 * FORKED FROM
 * @author Niels A.D.
 * @package Ganon
 * @link http://code.google.com/p/ganon/
 *
 * @license http://dev.perl.org/licenses/artistic.html Artistic License
 */

include_once('../pharse.php');

/** @var HTML_Node $html */
$html = Pharse::file_get_dom('http://newsrss.bbc.co.uk/rss/newsonline_world_edition/front_page/rss.xml');


if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    //PHP 5.3.0 and higher

    echo 'Last updated: ', $html('lastBuildDate', 0)->getPlainText(), "<br><br>\n";

    foreach ($html('item') as $item) {
        echo 'Title: ', $item('title', 0)->getPlainText(), "<br>\n";
        echo 'Date: ', $item('pubDate', 0)->getPlainText(), "<br>\n";
        echo 'Link: ', $item('link', 0)->getPlainText(), "<br><br>\n";
    }

} else {

    echo 'Last updated: ', $html->select('lastBuildDate', 0)->getPlainText(), "<br><br>\n";

    foreach ($html->select('item') as $item) {
        echo 'Title: ', $item->select('title', 0)->getPlainText(), "<br>\n";
        echo 'Date: ', $item->select('pubDate', 0)->getPlainText(), "<br>\n";
        echo 'Link: ', $item->select('link', 0)->getPlainText(), "<br><br>\n";
    }

}

echo 'done';
