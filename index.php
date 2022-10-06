<?php
/**
 * @package OrderOfMass
 */

	define('OOM_BASE', 'orderofmass');

	//Include MassData class and create an instance
	require(__DIR__.DIRECTORY_SEPARATOR.'massdata.php');
    $md = new MassData();

	require(__DIR__.DIRECTORY_SEPARATOR.'readings.php');
    $mr = new MassReadings();
	$md->reads = $mr->lectio();

	/**  
	 * This function returns a link to a particular commit that was deployed.

	 * The function is quite strict, so if the file does not exist or does not contain precisely 40 hexadecimal characters (lowercase), it returns an empty string.
	 * 
	 * Note that the file "commit" is not in the repository; it makes sense only in a particular deployment site.
	 * 
	 * @return string HTML link ("a" tag) to a particular deployed commit.
	*/
	function showCommit() {
		$commitFileName = __DIR__.DIRECTORY_SEPARATOR.'commit';
		if (!file_exists($commitFileName)) {
			return '';
		}

		$commit = trim(file_get_contents($commitFileName));
		if (!preg_match('/^[a-f0-9]{40}$/', $commit)) {
			return '';
		}
		
		return sprintf(' (<a href="https://github.com/tommander/catholic-mass/commit/%s">commit %s</a>)', $commit, substr($commit, 0, 7));
	}

?>
<!DOCTYPE html>
<html lang="<?= $md->repls('@{html}') ?>">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $md->isRosary() ? $md->repls('@{rosary}') : $md->repls('@{heading}') ?></title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
  </head>
  <body>
    <header>
      <h1><?= $md->isRosary() ? $md->repls('@{rosary}') : $md->repls('@{heading}') ?></h1>
    </header>
    <nav>
		<form name="langSel" action="index.php" method="get">
        <div id="navLabels">
            <label for="LABELS_SELECTION"><?= $md->repls('@{idxL}') ?></label>
            <label for="TYPE_SELECTION"><?= $md->repls('@{idxY}') ?></label>
            <label for="TEXTS_SELECTION"><?= $md->repls('@{idxT}') ?></label>
        </div>
        <div id="navLangs">
				<select name="ll" id="LABELS_SELECTION" onchange="document.forms['langSel'].submit();">
                <?php foreach ($md->langs as $code=>$info): ?>
					<option value="<?= htmlspecialchars($code) ?>"<?= $code == $md->ll ? ' selected="selected"' : '' ?>><?= htmlspecialchars($info['title']) ?></option>
                <?php endforeach; ?>
				</select>
				<select name="sn" id="TYPE_SELECTION" onchange="document.forms['langSel'].submit();">
					<option value="mass"<?= $md->isRosary() ? "" : " selected=\"selected\"" ?>><?= $md->repls('@{heading}') ?></option>
					<option value="rosary"<?= $md->isRosary() ? " selected=\"selected\"" : "" ?>><?= $md->repls('@{rosary}') ?></option>
				</select>
				<select name="tl" id="TEXTS_SELECTION" onchange="document.forms['langSel'].submit();">
                <?php foreach ($md->langs as $code=>$info): ?>
					<option value="<?= htmlspecialchars($code) ?>"<?= $code == $md->tl ? ' selected="selected"' : '' ?>><?= htmlspecialchars($info['title']) ?></option>
                <?php endforeach; ?>
				</select>
        </div>
		</form>
        <div id="navLegend">
            <span>P = <?= $md->repls('@{lblP}') ?></span>
            <span>A = <?= $md->repls('@{lblA}') ?></span>
            <span>R = <?= $md->repls('@{lblR}') ?></span>
        </div>
		<div id="navDate">
		<?php if ($md->isRosary()): ?>
			<div><?= date('d.m.Y') ?></div>
			<div><?= $md->repls('@my{'.$mr->todaysMystery().'}') ?></div>
		<?php else: ?>
			<div><?= date('d.m.Y', $mr->nextSunday()) ?></div>
			<div><?= $md->repls('@su{'.$mr->sundayLabel().'}') ?></div>
		<?php endif; ?>
		</div>
    </nav>
    <main>
		<?= $md->html(); ?>
    </main>
    <footer>
		<div>
		<span><?= $md->repls('@{license}');?>: <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU GPL v3</a></span>
        <span><?= $md->repls('@{source}');?>: <a href="https://github.com/tommander/catholic-mass">Repository at GitHub.com</a><?= showCommit(); ?></span>
        <span><?= $md->repls('@{author}');?>: <a href="mailto:tommander@tommander.cz">Tomáš <q>Tommander</q> Rajnoha</a></span>
        <span>&nbsp;</span>
        <span><?= $md->repls('@{headerimg}');?>: <a href="https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg">Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso</a> (<a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>)</span>
        <span><?= $md->repls('@{icons}');?>: <a href="https://fontawesome.com">Font Awesome Free 5.15.3 by @fontawesome</a> (<a href="https://fontawesome.com/license/free">Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License</a>)</span>
        <span><?= $md->repls('@{font}');?>: <a href="https://fonts.google.com/specimen/Source+Sans+Pro">Source Sans Pro by Paul D. Hunt</a> (<a href="https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL">Open Fonts License</a>)</span>
        <span><?= $md->repls('@{texts}');?>:<br>
        <?php foreach ($md->langs as $code=>$info): ?>
          <?= htmlspecialchars($info['title']) ?><?php if (strcasecmp($info['author'], 'Tommander') !== 0): ?> by <q><?= htmlspecialchars($info['author']) ?></q><?php endif; ?> (
          <?php if (!is_array($info['link'])): ?>
           <a href="<?= htmlspecialchars($info['link']) ?>">source</a>
          <?php else: ?>
            <?php $cnt = 1; ?>
            <?php foreach ($info['link'] as $lnk): ?>
              <?= $cnt > 1 ? ", " : "" ?><a href="<?= htmlspecialchars($lnk) ?>">source <?= $cnt++ ?></a>
            <?php endforeach; ?>
          <?php endif; ?>
          )<br>
        <?php endforeach; ?>
        </span>
		</div>
    </footer>
  </body>
</html>