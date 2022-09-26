<?php require("mass_lang.php"); ?>
<?php
            if (array_key_exists('ll',$_GET)) {
                if (array_search($_GET['ll'], $GLOBALS['languages']) !== FALSE) {
                    $GLOBALS['lang']['label'] = $_GET['ll'];
                }
            }
            if (array_key_exists('lt',$_GET)) {
                if (array_search($_GET['lt'], $GLOBALS['languages']) !== FALSE) {
                    $GLOBALS['lang']['text'] = $_GET['lt'];
                }
            }
?>
<!DOCTYPE html>
<html<?php switch($GLOBALS['lang']['label']){ case 'eng': echo ' lang="en"'; break; case 'ces': echo ' lang="cs"'; break; case 'tgl': echo ' lang="tl"';}?>>
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= eLabel('heading'); ?></title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
  </head>
  <body>
    <header>
      <h1><?= eLabel('heading'); ?></h1>
    </header>
    <div id="main">
      <nav>
        <div>
          <span><?= eLabel('idxL'); ?>: </span>
          <span><?= eLabel('idxT'); ?>: </span>
        </div>
        <div>
          <span>
            <a href="<?= genLink('eng') ?>"><img <?php if($GLOBALS['lang']['label'] == 'eng'){ echo 'class="selected" '; } ?>src="flags/eng.png" alt="English labels"></a>
            <a href="<?= genLink('ces') ?>"><img <?php if($GLOBALS['lang']['label'] == 'ces'){ echo 'class="selected" '; } ?>src="flags/ces.png" alt="České popisky"></a>
            <a href="<?= genLink('tgl') ?>"><img <?php if($GLOBALS['lang']['label'] == 'tgl'){ echo 'class="selected" '; } ?>src="flags/tgl.png" alt="Tagalog labels"></a>
          </span>
          <span>
            <a href="<?= genLink('','eng') ?>"><img <?php if($GLOBALS['lang']['text'] == 'eng'){ echo 'class="selected" '; } ?>src="flags/eng.png" alt="English texts"></a>
            <a href="<?= genLink('','ces') ?>"><img <?php if($GLOBALS['lang']['text'] == 'ces'){ echo 'class="selected" '; } ?>src="flags/ces.png" alt="České texty"></a>
            <a href="<?= genLink('','tgl') ?>"><img <?php if($GLOBALS['lang']['text'] == 'tgl'){ echo 'class="selected" '; } ?>src="flags/tgl.png" alt="Tagalog texts"></a>
          </span>
        </div>
        <div>
          <span>P = <?= eLabel('lblP'); ?></span>
          <span>A = <?= eLabel('lblA'); ?></span>
          <span>R = <?= eLabel('lblR'); ?></span>
        </div>
      </nav>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayP"><i class="fas fa-cross"></i> <?= eText('t01');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><?= eText('t02');?></p>
      <p class="sayA"><?= eText('tAwy');?></p>
      <p class="sayP"><?= eText('t03');?></p>
      <p class="sayA"><em><?= eLabel('silentPrayer');?></em></p>
      <div class="options">
        <div class="third">
          <p class="sayA"><?= eText('t04a');?></p>
        </div>
        <div class="third">
          <p class="sayP"><?= eText('t04b1');?></p>
          <p class="sayA"><?= eText('t04b2');?></p>
          <p class="sayP"><?= eText('t04b3');?></p>
          <p class="sayA"><?= eText('t04b4');?></p>
        </div>
        <div class="third">
          <p class="sayP"><?= eText('t04c1');?></p>
          <p class="sayA"><?= eText('t04c2');?></p>
          <p class="sayP"><?= eText('t04c3');?></p>
          <p class="sayA"><?= eText('t04c4');?></p>
          <p class="sayP"><?= eText('t04c5');?></p>
          <p class="sayA"><?= eText('t04c6');?></p>
        </div>
      </div>
      <p class="sayP"><?= eText('t05');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <div class="options">
        <div class="half">
          <p class="sayP"><?= eText('tLhm');?></p>
          <p class="sayA"><?= eText('tLhm');?></p>
          <p class="sayP"><?= eText('tChm');?></p>
          <p class="sayA"><?= eText('tChm');?></p>
          <p class="sayP"><?= eText('tLhm');?></p>
          <p class="sayA"><?= eText('tLhm');?></p>
        </div>
        <div class="half">
          <p class="sayP">Kyrie, eleison.</p>
          <p class="sayA">Kyrie, eleison.</p>
          <p class="sayP">Christe, eleison.</p>
          <p class="sayA">Christe, eleison.</p>
          <p class="sayP">Kyrie, eleison.</p>
          <p class="sayA">Kyrie, eleison.</p>
        </div>
      </div>
      <p class="sayP"><?= eText('t06a');?>&hellip;</p>
      <p class="sayA">&hellip; <?= eText('t06b');?></p>
      <p class="sayP"><?= eText('tLetspray');?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em>&hellip; <?= eText('tPvvv');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayA"><?= sit() ?></p>
      <p class="sayR"><i class="fas fa-bible"></i> <em><?= eLabel('read1');?></em>&hellip; <?= eText('wotl');?></p>
      <p class="sayA"><?= eText('tbtg');?></p>
      <p class="sayR"><i class="fas fa-bible"></i> <em><?= eLabel('psalm');?></em></p>
      <p class="sayR"><i class="fas fa-bible"></i> <em><?= eLabel('read2');?></em>&hellip; <?= eText('wotl');?></p>
      <p class="sayA"><?= eText('tbtg');?></p>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayP"><?= eText('tGwy');?></p>
      <p class="sayA"><?= eText('tAwy');?></p>
      <p class="sayP"><?= eText('t07');?></p>
      <p class="sayA"><i class="fas fa-cross"></i> <?= eText('t08');?></p>
      <p class="sayP"><i class="fas fa-bible"></i> <em><?= eLabel('readE');?></em>&hellip; <?= eText('wotl');?></p>
      <p class="sayA"><?= eText('t09');?></p>
      <p class="sayA"><?= sit() ?></p>
      <p class="sayP"><i class="far fa-comment"></i> <em><?= eLabel('homily');?></em></p>
      <p class="sayA"><?= stand() ?></p>
      <div class="options">
        <div class="half">
          <p class="sayA"><?= eText('tNCC');?></p>
        </div>
        <div class="half">
          <p class="sayA"><?= eText('tAC');?></p>
        </div>
      </div>
      <p class="sayR"><em><?= eLabel('intercess');?></em></p>
      <p class="sayA"><?= eText('tLHOP');?></p>
      <p class="sayA"><?= sit() ?></p>
      <p class="sayR"><em><?= eLabel('offertory');?></em></p>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayP"><?= eText('t10');?></p>
      <p class="sayA"><?= eText('t11');?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><?= eText('tGwy');?></p>
      <p class="sayA"><?= eText('tAwy');?></p>
      <p class="sayP"><?= eText('t12');?></p>
      <p class="sayA"><?= eText('t13');?></p>
      <p class="sayP"><?= eText('t14');?></p>
      <p class="sayA"><?= eText('t15');?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em></p>
      <p class="sayA"><?= eText('t16');?></p>
      <p class="sayA"><?= kneel() ?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em></p>
      <p class="sayP"><?= eText('t17a');?></p>
      <div class="options">
        <div class="third">
          <p class="sayA"><?= eText('t17b');?></p>
        </div>
        <div class="third">
          <p class="sayA"><?= eText('t17c');?></p>
        </div>
        <div class="third">
          <p class="sayA"><?= eText('t17d');?></p>
        </div>
      </div>
      <p class="sayP"><em><?= eLabel('prayer');?></em>&hellip; <?= eText('t18');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><?= eText('t19');?></p>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayA"><?= eText('t20');?></p>
      <p class="sayP"><?= eText('t21');?></p>
      <p class="sayA"><?= eText('t22');?></p>
      <p class="sayP"><?= eText('t23');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><?= eText('t24');?></p>
      <p class="sayA"><?= eText('tAwy');?></p>
      <p class="sayP"><?= eText('t25');?></p>
      <p class="sayA"><i class="far fa-handshake"></i> <?= eText('t26');?></p>
      <p class="sayA"><?= kneel() ?></p>
      <p class="sayP"><em><?= eLabel('breakhost');?></em></p>
      <p class="sayA"><?= eText('t27');?></p>
      <p class="sayP"><?= eText('t28');?></p>
      <p class="sayA"><?= eText('t29');?></p>
      <p class="sayP"><em><?= eLabel('holycomm');?>:</em> <?= eText('t30');?></p>
      <p class="sayA"><em><?= eLabel('holycomm');?>:</em> <?= eText('tAmen');?></p>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayA"><?= sit() ?></p>
      <p class="sayP"><em><?= eLabel('announce');?></em></p>
      <p class="sayA"><?= stand() ?></p>
      <p class="sayP"><?= eText('tGwy');?></p>
      <p class="sayA"><?= eText('tAwy');?></p>
      <p class="sayP"><?= eText('t31');?></p>
      <p class="sayP"><em><?= eLabel('prayer');?></em></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><i class="fas fa-cross"></i> <?= eText('t32');?></p>
      <p class="sayA"><?= eText('tAmen');?></p>
      <p class="sayP"><i class="fas fa-hiking"></i> <?= eText('t33');?></p>
      <p class="sayA"><?= eText('tbtg');?></p>
    </div>
    <footer>
      <div>
        <span><?= eLabel('footnote');?> (<a title="To the extent possible under law, Tomáš Rajnoha has waived all copyright and related or neighboring rights to this work published from Czechia." rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0 1.0 Universal</a>).</span>
        <span><?= eLabel('download');?>: <a href="tmdcz_mass_v1.zip" title=".ZIP archive, 23.1 MiB"><i class="far fa-file-archive"></i>tmdmass1.zip</a> (SHA1: <code>ed3b99938470d1d3b2a34726a5387ecc59aaffd4</code>).</span>
        <span><?= eLabel('author');?>: <a href="mailto:tommander@tommander.cz">Tomáš <q>Tommander</q> Rajnoha</a></span>
        <span>&nbsp;</span>
        <span><?= eLabel('headerimg');?>: <a href="https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg">Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso</a> (<a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>)</span>
        <span><?= eLabel('flags');?>: <a href="https://github.com/gosquared/flags">Flags [1d382a9] by GoSquared</a> (<a href="https://github.com/gosquared/flags/blob/master/LICENSE.txt">MIT license</a>)</span>
        <span><?= eLabel('icons');?>: <a href="https://fontawesome.com">Font Awesome Free 5.15.2 by @fontawesome</a> (<a href="https://fontawesome.com/license/free">Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License</a>)</span>
        <span><?= eLabel('font');?>: <a href="https://fonts.google.com/specimen/Source+Sans+Pro">Source Sans Pro by Paul D. Hunt</a> (<a href="https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL">Open Fonts License</a>)</span>
        <span><?= eLabel('texts');?>: <a href="https://www.catholicbishops.ie/wp-content/uploads/2011/02/Order-of-Mass.pdf">English</a>, <a href="https://www.cirkev.cz/cs/mse-svata">Čeština</a>, <a href="https://ourparishpriest.blogspot.com/2018/12/holy-mass-in-filipino-tagalog.html">Tagalog</a></span>
      <div>
    </footer>
  </body>
</html>