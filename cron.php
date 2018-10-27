<?php
require_once 'vendor/autoload.php';
require_once 'library/config.php';
require_once 'library/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;

if (empty($argv[1])) { // Just retrieve the RSS updates
    $categorySlugs = $db->getRows("SELECT * FROM %s", $db->table('categories'));
    foreach ($categorySlugs as $categorySlug) {
        $requestUrl = 'https://www.ss.com'.$categorySlug['path'].'rss/';
    
        try {
            $contents = simplexml_load_file($requestUrl);
            
            if ($contents === false) {
                apilog("Unable to retrieve data for {$categorySlug['path']}, skipping.");
                continue;
            }
        } catch (Exception $e) {
            apilog("Unable to retrieve data for {$categorySlug['path']}, skipping. ".$e->getMessage());
            continue;
        }
    
        $channelData = $contents->channel;
        $itemCount = count($channelData->item);
        apilog("Retrieving information for '{$categorySlug['path']}' ({$categorySlug['title']}), containing {$itemCount} items.");
    
        foreach ($channelData->item as $item) {
            $title = trim(preg_replace('/\s\s+/', ' ',$item->title));
            $url = trim($item->link);
            $pubDate = trim($item->pubDate);
            $description = trim(preg_replace('/\s\s+/', ' ', strip_tags(str_replace(array('<br>', '<br/>'), array(' ', ' '), $item->description))));
            $hash = md5($categorySlug['path'].$item->link.$item->pubDate);
    
            $existingData = $db->getRow("SELECT * FROM %s WHERE `hash`='%s'", $db->table('classifieds'), $hash);
            if (empty($existingData)) {
                apilog("- Found new entry '{$title}', added to database.");
            }
    
            $insertData = array(
                'path' => $categorySlug['path'],
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'hash' => $hash,
                'added_at' => $pubDate
            );
    
            $db->insert('classifieds', $insertData, true);
        }
    }
    
    apilog('Task finished, database updated.');
} else if ($argv[1] == 'notifications') {
    $users = $db->getRows("SELECT * FROM %s WHERE `active`='1'", $db->table('users'));

    foreach ($users as $user) {
        $categories = json_decode($user['categories'], true);
        $lastSent = $user['sent_at'] ?? $user['created_at'];
        $renderedHTML = 'Apskats par jaunākajiem sludinājumiem no SS.COM:<br/><br/>';
        $classifiedCount = 0;

        foreach ($categories as $category) {
            $latestData = $db->getRows("SELECT * FROM %s WHERE `path` LIKE '%s%%%%' AND `created_at`>'%s' ORDER BY `id` DESC LIMIT 15", $db->table('classifieds'), $category, $lastSent);
            if (!empty($latestData)) {
                $renderedHTML .= '<strong><a href="https://www.ss.com'.$category.'">https://www.ss.com'.$category.'</a>:</strong><ol>';
                foreach ($latestData as $classified) {
                    $renderedHTML .= '<li><a href="'.$classified['url'].'">'.$classified['title'].'</a><ul><li>'.str_replace('Apskatīt sludinājumu','',$classified['description']).'</li></ul></li>';
                    $classifiedCount++;
                }
                $renderedHTML .= '</ol><br/><br/>';
            }
        }

        if (empty($classifiedCount) || $classifiedCount == 0) { // Nothing new
            apilog("No new classifieds found for {$user['email']}");
            continue;
        }
    
        $renderedHTML .= '<br/>P.S. Ja vēlies izmainīt sev aktuālās kategorijas, dodies uz <a href="https://www.modrs.lv/?lapa=pieteikties">https://www.modrs.lv/?lapa=pieteikties</a> un ievadi savus datus atkārtoti, izvēloties jaunās kategorijas.<br/><br/>';
        $renderedHTML .= 'Ar cieņu,<br><a href="https://www.modrs.lv/">MODRS.LV</a> komanda<br/><br/><a href="https://www.modrs.lv/?atteikties='.$user['hash'].'">Pārtraukt aktualitāšu saņemšanu</a>';

        $mail = new PHPMailer();
        $mail->CharSet  = 'UTF-8';
        $mail->From     = 'ss@modrs.lv';
        $mail->FromName = 'MODRS.LV';
        
        $mail->AddAddress($user['email'], $user['name']);
        
        $mail->Subject  =  'Jaunākie sludinājumi no SS.COM';
        $mail->Body     =  $renderedHTML;
        $mail->IsHTML(true);
        $mail->send();

        $db->update('users', array('sent_at'=>strftime('%F %X')), array('id'=>$user['id']));
    }

    apilog("Notification sending finished.");
    exit;
} else if ($argv[1] == 'categories') {
    foreach ($config['crawlUrls'] as $crawlUrl => $crawlUrlData) {
        try {
            $contents = Pharse::file_get_dom($crawlUrl);
            
            if ($contents === false) {
                apilog("Unable to retrieve data for {$crawlUrl}, skipping.");
                continue;
            }
        } catch (Exception $e) {
            apilog("Unable to retrieve data for {$crawlUrl}, skipping. ".$e->getMessage());
            continue;
        }
    
        foreach ($contents('h4.category a[href]') as $element) {
            $db->insert('categories', array(
                'path' => $element->href,
                'title' => $element->getPlainText()
            ), true);
        }
    }
}
