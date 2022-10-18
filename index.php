<?php
/**
 * Hello
 * 
 * PHP version 7.4
 * 
 * @category MainFile
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */

define('OOM_BASE', 'orderofmass');

require __DIR__.'/src/functions.php';
require __DIR__.'/src/massdata.php';
require __DIR__.'/src/readings.php';

//Include MassData class and create an instance
$md = new MassData();
$mr = new MassReadings();
$md->reads = $mr->lectio();

?>
<!DOCTYPE html>
<html lang="<?php echo $md->repls('@{html}') ?>">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $md->isRosary() ? $md->repls('@{rosary}') : $md->repls('@{heading}') ?></title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
  </head>
  <body>
    <header>
      <h1><?php echo $md->isRosary() ? $md->repls('@{rosary}') : $md->repls('@{heading}') ?></h1>
    </header>
    <nav>
        <form name="langSel" action="index.php" method="get">
        <div id="navLabels">
            <label for="LABELS_SELECTION"><?php echo $md->repls('@{idxL}') ?></label>
            <label for="TYPE_SELECTION"><?php echo $md->repls('@{idxY}') ?></label>
            <label for="BIBLE_SELECTION"><?php echo $md->repls('@{idxB}') ?></label>
            <label for="TEXTS_SELECTION"><?php echo $md->repls('@{idxT}') ?></label>
        </div>
        <div id="navLangs">
                <select name="ll" id="LABELS_SELECTION" onchange="document.forms['langSel'].submit();">
                <?php foreach ($md->langs as $code=>$info): ?>
                    <option value="<?php echo htmlspecialchars($code) ?>"<?php echo $code == $md->ll ? ' selected="selected"' : '' ?>><?php echo htmlspecialchars($info['title']) ?></option>
                <?php endforeach; ?>
                </select>
                <select name="sn" id="TYPE_SELECTION" onchange="document.forms['langSel'].submit();">
                    <option value="mass"<?php echo $md->isRosary() ? "" : " selected=\"selected\"" ?>><?php echo $md->repls('@{heading}') ?></option>
                    <option value="rosary"<?php echo $md->isRosary() ? " selected=\"selected\"" : "" ?>><?php echo $md->repls('@{rosary}') ?></option>
                </select>
                <select name="bl" id="BIBLE_SELECTION" onchange="document.forms['langSel'].submit();">
                    <option value="">-</option>
                    <?php if (array_key_exists($md->ll, $md->bibtrans)) : ?>
                        <optgroup label="<?php echo $md->langs[$md->ll]['title'] ?>">
                        <?php foreach ($md->bibtrans[$md->ll] as $bibleid=>$bibledata) : ?>
                            <option value="<?php echo $bibleid ?>"<?php echo $md->bl == $bibleid ? ' selected="selected"' : '' ?>><?php echo $bibledata[0] ?></option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (array_key_exists($md->tl, $md->bibtrans) && $md->ll != $md->tl) : ?>
                        <optgroup label="<?php echo $md->langs[$md->tl]['title'] ?>">
                        <?php foreach ($md->bibtrans[$md->tl] as $bibleid=>$bibledata) : ?>
                            <option value="<?php echo $bibleid ?>"<?php echo $md->bl == $bibleid ? ' selected="selected"' : '' ?>><?php echo $bibledata[0] ?></option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <select name="tl" id="TEXTS_SELECTION" onchange="document.forms['langSel'].submit();">
                <?php foreach ($md->langs as $code=>$info): ?>
                    <option value="<?php echo htmlspecialchars($code) ?>"<?php echo $code == $md->tl ? ' selected="selected"' : '' ?>><?php echo htmlspecialchars($info['title']) ?></option>
                <?php endforeach; ?>
                </select>
        </div>
        </form>
        <div id="navLegend">
            <span>P = <?php echo $md->repls('@{lblP}') ?></span>
            <span>A = <?php echo $md->repls('@{lblA}') ?></span>
            <span>R = <?php echo $md->repls('@{lblR}') ?></span>
        </div>
        <div id="navDate">
        <?php if ($md->isRosary()) : ?>
            <div><?php echo date('d.m.Y') ?></div>
            <div><?php echo $md->repls('@my{'.$mr->todaysMystery().'}') ?></div>
        <?php else: ?>
            <div><?php echo date('d.m.Y', $mr->nextSunday()) ?></div>
            <div><?php echo $md->repls('@su{'.$mr->sundayLabel().'}') ?></div>
        <?php endif; ?>
        </div>
    </nav>
    <main>
        <?php echo $md->htmlObj(); ?>
    </main>
    <footer>
        <div>
        <span><?php echo $md->repls('@{license}');?>: <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU GPL v3</a></span>
        <span><?php echo $md->repls('@{source}');?>: <a href="https://github.com/tommander/catholic-mass">Repository at GitHub.com</a><?php echo showCommit(); ?></span>
        <span><?php echo $md->repls('@{author}');?>: <a href="mailto:tommander@tommander.cz">Tomáš <q>Tommander</q> Rajnoha</a></span>
        <span>&nbsp;</span>
        <span><?php echo $md->repls('@{headerimg}');?>: <a href="https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg">Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso</a> (<a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>)</span>
        <span><?php echo $md->repls('@{icons}');?>: <a href="https://fontawesome.com">Font Awesome Free 5.15.3 by @fontawesome</a> (<a href="https://fontawesome.com/license/free">Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License</a>)</span>
        <span><?php echo $md->repls('@{font}');?>: <a href="https://fonts.google.com/specimen/Source+Sans+Pro">Source Sans Pro by Paul D. Hunt</a> (<a href="https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL">Open Fonts License</a>)</span>
        <span><?php echo $md->repls('@{texts}');?>:<br>
        <?php foreach ($md->langs as $code=>$info): ?>
            <?php echo htmlspecialchars($info['title']) ?><?php if (strcasecmp($info['author'], 'Tommander') !== 0): ?> by <q><?php echo htmlspecialchars($info['author']) ?></q><?php endif; ?> (
            <?php if (!is_array($info['link'])) : ?>
                <a href="<?php echo htmlspecialchars($info['link']) ?>">source</a>
            <?php else: ?>
                <?php $cnt = 1; ?>
                <?php foreach ($info['link'] as $lnk): ?>
                    <?php echo $cnt > 1 ? ", " : "" ?><a href="<?php echo htmlspecialchars($lnk) ?>">source <?php echo $cnt++ ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        )<br>
        <?php endforeach; ?>
        </span>
        </div>
    </footer>
  </body>
</html>