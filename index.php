<?php
require_once 'vendor/autoload.php';
require_once 'library/config.php';
require_once 'library/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;

$lapa = '';
if (!empty($_GET['lapa']) && in_array($_GET['lapa'], $config['pages'])) {
  $lapa = strtolower($_GET['lapa']);
}

if (!empty($_GET['aktivizet']) && strlen($_GET['aktivizet']) == 32) {
  $lapa = 'pieteikties';
  $result = $db->getRow("SELECT * FROM %s WHERE `hash`='%s' AND `active`='0'", $db->table('users'), $_GET['aktivizet']);
  if (empty($result))
    $activateText = 'Izskatās ka norādītais parametrs ir nederīgs, vai pieteikums jau ir aktivizēts.';
  else {
    $db->update('users', array('active'=>1), array('hash'=>$_GET['aktivizet']));
    $activateText = 'Paldies, tavs pieteikums ir veiksmīgi apstiprināts. Jau pavisam drīz saņemsi pirmos sludinājumu e-pastus.';
  }
}

if (!empty($_GET['atteikties']) && strlen($_GET['atteikties']) == 32) {
  $lapa = 'pieteikties';
  $result = $db->getRow("SELECT * FROM %s WHERE `hash`='%s' AND `active`='1'", $db->table('users'), $_GET['atteikties']);
  if (empty($result))
    $activateText = 'Izskatās ka norādītais parametrs ir nederīgs, vai šis pieteikums jau ir deaktivizēts.';
  else {
    $db->update('users', array('active'=>0), array('hash'=>$_GET['atteikties']));
    $activateText = 'Tu esi veiksmīgi atteicies no jaunumiem par sludinājumu kategorijām. Lai izvēlētos citas kategorijas, dodies uz Pieteikties sadaļu.';
  }
}

if (!empty($_POST)) {
  if ($lapa == 'pieteikties') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $categories = $_POST['categories'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      $error = 'Lūdzu ievadi pareizu e-pastu, uz to tiks nosūtīta apstiprinājuma vēstule.';
    else if (strlen($name) < 2)
      $error = 'Lūdzu ievadi savu vārdu. Tas ir svarīgi lai mūsu sūtītais e-pasts nenokļūtu spamā.';
    else if (empty($categories))
      $error = 'Lūdzu izvēlies vismaz vienu kategoriju par kuru vēlies saņemt jaunākos sludinājumus.';
    else {
      $hash = md5(uniqid($name.$email, true));

      $db->insert('users', array(
        'email' => $email,
        'name' => $name,
        'categories' => json_encode($categories),
        'hash' => $hash,
        'ip' => $_SERVER['REMOTE_ADDR']
      ), true);

      $renderedHTML = 'Lai apstiprinātu savu pieteikumu, lūdzu atver sekojošu saiti:<br/>';
      $renderedHTML .= '<a href="'.$config['host'].'?aktivizet='.$hash.'">'.$config['host'].'?aktivizet='.$hash.'</a><br/><br/>';
      $renderedHTML .= 'Pēc pieteikuma apstiprinājuma, reizi stundā saņemsi aktuālākos sludinājumus par sev izvēlētajām kategorijām.<br/><br/>';
      $renderedHTML .= 'Ar cieņu,<br/><a href="https://nils.digital/">Nils Putniņš</a> no <a href="https://www.modrs.lv/">MODRS.LV</a>';

      $mail = new PHPMailer();
      $mail->CharSet  = 'UTF-8';
      $mail->From     = 'ss@modrs.lv';
      $mail->FromName = 'MODRS.LV';
      
      $mail->AddAddress($email, $name);
      
      $mail->Subject  =  'Pieteikuma apstiprinājums';
      $mail->Body     =  $renderedHTML;
      $mail->IsHTML(true);
      $mail->send();

      $success = true;
    }
  }
  if (empty($lapa)) { $lapa = 'pieteikties'; }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>MODRS.LV - Sludinājumi no SS.COM tavā e-pastā</title>
  <meta name="keywords" content="ss.lv, ss.com, ss sludinājumi, ss paziņojumi, ss ziņojumi, ss e-pasts, ss epasts, ss ziņas"/>
  <meta name="description" content="Atlasi, kurus sludinājumus no SS.COM vēlies saņemt e-pastā. Darba vakances, automašīnas, dzīvokļi un būvniecības darbi. Esi pirmais rindā pēc tā, kas aktuāls."/>
  <meta name="og:title" content="MODRS.LV - SS.COM Sludinājumi tavā e-pastā"/>
  <meta name="og:type" content="article"/>
  <meta name="og:url" content="<?php print($config['host'])?>"/>
  <meta name="og:image" content="<?php print($config['host'])?>img/fbshare.png"/>
  <meta name="og:site_name" content="MODRS.LV"/>
  <meta name="og:description" content="Atlasi, kurus sludinājumus no SS.COM vēlies saņemt e-pastā. Darba vakances, automašīnas, dzīvokļi un būvniecības darbi. Esi pirmais rindā pēc tā, kas aktuāls."/>
  <meta name="og:email" content="info@modrs.lv"/>
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/mdb.min.css" rel="stylesheet">
  <link href="css/style.min.css" rel="stylesheet">
  <link href="css/multi-select.dist.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark scrolling-navbar">
    <div class="container">

      <a class="navbar-brand" href="<?php print($config['host'])?>">
        <strong>MODRS.LV</strong>
      </a>

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">

        <ul class="navbar-nav mr-auto">
          <li class="nav-item<?php if (empty($lapa)) print(' active')?>">
            <a class="nav-link" href="<?php print($config['host'])?>">Sākums
              <span class="sr-only">(current)</span>
            </a>
          </li>
          <li class="nav-item<?php if ($lapa == 'par') print(' active')?>">
            <a class="nav-link" href="<?php print($config['host'])?>?lapa=par">Par projektu</a>
          </li>
          <li class="nav-item<?php if ($lapa == 'pieteikties') print(' active')?>">
            <a class="nav-link" href="<?php print($config['host'])?>?lapa=pieteikties">Pieteikties</a>
          </li>
          <li class="nav-item<?php if ($lapa == 'kontakti') print(' active')?>">
            <a class="nav-link" href="mailto:info@modrs.lv">Kontakti</a>
          </li>
        </ul>

        <ul class="navbar-nav nav-flex-icons">
          <li class="nav-item">
            <a href="https://www.facebook.com/nilsputnins" class="nav-link" target="_blank">
              <i class="fa fa-facebook"></i>
            </a>
          </li>
          <li class="nav-item">
            <a href="https://twitter.com/putninsnils" class="nav-link" target="_blank">
              <i class="fa fa-twitter"></i>
            </a>
          </li>
        </ul>

      </div>

    </div>
  </nav>
<?php if (empty($lapa)) { ?>
  <div class="view full-page-intro" style="background-image: url('img/bg.jpg'); background-repeat: no-repeat; background-size: cover;">

    <div class="mask rgba-black-light d-flex justify-content-center align-items-center">

      <div class="container">

        <div class="row wow fadeIn">

          <div class="col-md-6 mb-4 white-text text-center text-md-left">

            <h1 class="display-4 font-weight-bold">Uzzini sludinājumus pirmais!</h1>

            <hr class="hr-light">

            <p>
              <strong>Paziņojumi par jauniem SS.COM sludinājumiem tavā e-pastā.</strong>
            </p>

            <p class="mb-4 d-none d-md-block">
              <strong>Atlasi, kurus sludinājumus no SS.COM vēlies saņemt e-pastā. Darba vakances, automašīnas, dzīvokļi un būvniecības darbi. Esi pirmais rindā pēc tā, kas aktuāls.</strong>
            </p>

            <a href="<?php print($config['host'])?>?lapa=pieteikties" class="btn btn-green btn-lg">Pieteikties bez maksas
              <i class="fa fa-user-plus ml-2"></i>
            </a>

          </div>

          <div class="col-md-6 col-xl-5 mb-4">

            <div class="card">

              <div class="card-body">

                <form name="pieteikties" method="post" action="?lapa=pieteikties">
                  <h3 class="dark-grey-text text-center">
                    <strong>Piesakies minūtes laikā:</strong>
                  </h3>
                  <hr>

                  <div class="md-form">
                    <i class="fa fa-user prefix grey-text"></i>
                    <input type="text" name="name" id="form3" class="form-control">
                    <label for="form3">Tavs vārds</label>
                  </div>
                  <div class="md-form">
                    <i class="fa fa-envelope prefix grey-text"></i>
                    <input type="text" name="email" id="form2" class="form-control">
                    <label for="form2">Tavs e-pasts</label>
                  </div>

                  <div class="text-center">
                    <button class="btn btn-green">Pieteikties bez maksas</button>
                  </div>

                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php } else if ($lapa == 'par') { ?>
  <div id="carousel-example-1z" class="carousel slide carousel-fade" data-ride="carousel">
    <div class="carousel-inner" role="listbox">
      <div class="carousel-item active">
        <div class="view" style="background-image: url('img/bg.jpg'); background-repeat: no-repeat; background-size: cover;">
          <div class="mask rgba-black-light d-flex justify-content-center align-items-center">
            <div class="text-center white-text mx-5 wow fadeIn">
              <h1 class="mb-4">
                <strong>Par projektu</strong>
              </h1>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <main>
    <div class="container">
      <section class="mt-5 wow fadeIn">
        <div class="row">

          <div class="col-md-12 mb-4">

            <h3 class="h3 mb-3">Projekta mērķis</h3>
            <p>Sniegt cilvēkiem aktuālāko informāciju no SS.COM bez to iesaistes. Apzinoties problēmu un ātrumu kādā tiek izķerti labi piedāvājumi no dažādām kategorijām, ir svarīgi tikt informētam pirmajam.
            </p>

            <h3 class="h3 mb-3">Projekta ideja un realizācija</h3>
            <p>Ideja par projekta realizāciju radās piektdienā, '18. gada 9. martā pēc tēvoča intereses saņemt sludinājumus par SS.COM kategorijām īsziņu veidolā.
            13. martā izpētot iespējas un veicot izpēti, saprotot ka šādu pakalpojumu nepiedāvā pats SS.COM, kā arī nav alternatīvu - tika izveidoti testi pēc kuriem sekoja realizācija.
            Pirmajā koda testa iterācijā tika ievākti 100+ sludinājumi no vienas pamatkategorijas, pēc kā sekoja koda refaktorings un no 4 kategorijām tika ievākti 5000+ sludinājumi.
            Veicot nelielu aptauju un saprotot ka interese pēc šāda pakalpojuma gan dzīvokļu, tā auto un darba meklētājiem būtu - projekts tika realizēts testa stadijā 14. martā.
            </p>

            <a href="?lapa=pieteikties" class="btn btn-green btn-md">Pieteikties bez maksas
              <i class="fa fa-user ml-1"></i>
            </a>
            <a target="_blank" href="https://nils.digital/" class="btn btn-indigo btn-md">Par autoru
              <i class="fa fa-info ml-1"></i>
            </a>

          </div>
        </div>
      </section>
    </div>
  </main>
<?php } else if ($lapa == 'pieteikties') { ?>
  <div id="carousel-example-1z" class="carousel slide carousel-fade" data-ride="carousel" style="height: 40%;">
    <div class="carousel-inner" role="listbox">
      <div class="carousel-item active">
        <div class="view" style="background-image: url('img/bg.jpg'); background-repeat: no-repeat; background-size: cover;">
          <div class="mask rgba-black-light d-flex justify-content-center align-items-center">
            <div class="text-center white-text mx-5 wow fadeIn">
              <h1 class="mb-4">
                <strong>Pieteikties</strong>
              </h1>
              <p class="mb-4">
                <strong>Bezmaksas jaunumi no SS.COM tavā e-pastā un tālrunī</strong>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <main>
    <div class="container">
      <section class="mt-5 wow fadeIn">
        <div class="col-md-12 mb-4">
        <?php if (!empty($error)) { ?>
        <h2>Lai pieteiktos</h2>
        <p><?php print($error)?></p>
        <?php } if (!empty($success) && $success == true) { ?>
        <h2>Pieteikšanās veiksmīga</h2>
        <p>
          Lai pabeigtu pieteikšanos, lūdzu apstiprini savu reģistrāciju atverot savu e-pastu un sekojot norādēm kas uz to tika nosūtītas.<br/><br/>
          <strong>Ja e-pasts nav pienācis, pārbaudi `Spam` jeb `Mēstuļu` sadaļas</strong>
        </p>
        <?php } else if (!empty($activateText)) { ?>
        <h2>Darbība veiksmīga</h2>
        <p><?php print($activateText)?></p>
        <?php } else { ?>
        <form method="post" action="">
          <h2>Pamatinformācija</h2>
          <div class="form-row">
              <div class="col-md-6">
                  <div class="md-form form-group">
                      <input type="text" name="name" class="form-control" id="inputName" placeholder="Tavs vārds"<?php if (!empty($_POST['name'])) print(' value="'.htmlspecialchars($_POST['name']).'"')?>>
                      <label for="inputName">Vārds</label>
                  </div>
              </div>

              <div class="col-md-6">
                  <div class="md-form form-group">
                      <input type="email" name="email" class="form-control" id="inputMail" placeholder="Tavs e-pasts"<?php if (!empty($_POST['email'])) print(' value="'.htmlspecialchars($_POST['email']).'"')?>>
                      <label for="inputMail">E-pasts</label>
                  </div>
              </div>
          </div>

          <h2>Kategorijas</h2>

          <div class="form-row md-form">
            <select id="categories" multiple="multiple" name="categories[]">
<?php foreach ($config['crawlUrls'] as $crawlUrl => $title) {
  $path = getPath($crawlUrl);
  $entries = $db->getRows("SELECT * FROM %s WHERE `path` LIKE '%s%%%%'", $db->table('categories'), $path);
?>
              <optgroup label='<?php print($title)?>'>
                <?php foreach ($entries as $entry) print('<option value="'.$entry['path'].'">'.htmlspecialchars($entry['title']).'</option>'); ?>
              </optgroup>
<?php } ?>
              </select>
          </div>
          <button type="submit" class="btn btn-primary btn-md-12">Pieteikties</button>
      </form>
<?php } ?>
        </div>
      </section>
    </div>
  </main>
<?php } if (!empty($lapa)) { ?>
  <footer class="page-footer text-center font-small wow fadeIn">
    <div class="footer-copyright py-3">
      &copy; 2018 Izstrāde 
      <a href="https://nils.digital/" target="_blank">Nils Putniņš</a>
    </div>
  </footer>
<?php } ?>
  <script type="text/javascript" src="js/jquery-3.2.1.min.js"></script>
  <script type="text/javascript" src="js/popper.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/mdb.min.js"></script>
  <script type="text/javascript" src="js/jquery.multi-select.js"></script>
  <script type="text/javascript" src="js/jquery.quicksearch.js"></script>
  <script type="text/javascript">
    new WOW().init();
    <?php if ($lapa == 'pieteikties') { ?>
      $('#categories').multiSelect({
        selectableOptgroup: true,
        cssClass: 'col-md-12',
        selectableHeader: "<input type='text' class='form-control' autocomplete='off' placeholder='Pamēģini \"Rīga\"'>",
        selectionHeader: "<input type='text' class='form-control' autocomplete='off'>",
        afterInit: function(ms){
          var that = this,
              $selectableSearch = that.$selectableUl.prev(),
              $selectionSearch = that.$selectionUl.prev(),
              selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
              selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

          that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
          .on('keydown', function(e){
            if (e.which === 40){
              that.$selectableUl.focus();
              return false;
            }
          });

          that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
          .on('keydown', function(e){
            if (e.which == 40){
              that.$selectionUl.focus();
              return false;
            }
          });
        },
        afterSelect: function(){
          this.qs1.cache();
          this.qs2.cache();
        },
        afterDeselect: function(){
          this.qs1.cache();
          this.qs2.cache();
        }
      });
    <?php } ?>
  </script>
</body>

</html>
