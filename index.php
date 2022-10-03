<?php
	//Include MassData class and create an instance
	require(__DIR__.DIRECTORY_SEPARATOR.'massdata.php');
    $md = new MassData();

	/* 
	  This function includes a link to a particular commit that was deployed. File "commit" is not in the repository; it makes sense only in a particular deployment site.

	  The function is quite strict, so if the file does not exist or does not contain precisely 40 hexadecimal characters (lowercase), it returns an empty string.
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
<html lang="<?= $md->repls('${html}') ?>">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $md->repls('@{heading}') ?></title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
  </head>
  <body>
    <header>
      <h1><?= $md->repls('@{heading}') ?></h1>
    </header>
    <nav>
        <div>
            <span><?= $md->repls('@{idxL}') ?></span>
            <span><?= $md->repls('@{idxT}') ?></span>
        </div>
        <div>
            <div>
                <?php foreach ($md->langs as $code=>$info): ?>
                <a href="<?= $md->link($code, '') ?>"<?= ($code == $md->ll) ? " class=\"selected\"" : "" ?>><?= htmlspecialchars($info['title']) ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <?php foreach ($md->langs as $code=>$info): ?>
                <a href="<?= $md->link('', $code) ?>"<?= ($code == $md->tl) ? "class=\"selected\"" : "" ?>><?= htmlspecialchars($info['title']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <span>P = <?= $md->repls('@{lblP}') ?></span>
            <span>A = <?= $md->repls('@{lblA}') ?></span>
            <span>R = <?= $md->repls('@{lblR}') ?></span>
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