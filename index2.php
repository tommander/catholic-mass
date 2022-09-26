<?php 
    class MassData {
        public $jd;
        public $tl;
        public $ll;

        function __construct() {
            $this->jd = json_decode(file_get_contents('data.json'), true);
            $this->tl = 'eng';
            $this->ll = 'eng';

            if (array_key_exists('ll', $_GET)) {
                if (array_search($_GET['ll'], $this->jd['languages']) !== FALSE) {
                    $this->ll = $_GET['ll'];
                }
            }
            if (array_key_exists('tl',$_GET)) {
                if (array_search($_GET['tl'], $this->jd['languages']) !== FALSE) {
                    $this->tl = $_GET['tl'];
                }
            }
        }

        private function replcb(array $matches):string {
            return $this->jd['labels'][$matches[1]][$this->ll];
        }

        public function repl($text) {
            return preg_replace_callback('/@\{([A-Za-z0-9]+)\}/', 'self::replcb', $text);
        }

        public function link($label = '', $text = '') {
            $putlabel = ($label != '') ? $label : $this->ll;
            $puttext = ($text != '') ? $text : $this->tl;
            return "index2.php?ll=${putlabel}&tl=${puttext}";
        }
    }

    $md = new MassData();
?>
<?php
?>
<!DOCTYPE html>
<html lang="<?= $md->jd['languages'][$md->ll]['html']?>">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $md->repl('@{heading}') ?></title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
  </head>
  <body>
    <header>
      <h1><?= $md->repl('@{heading}') ?></h1>
    </header>
    <nav>
        <div>
            <span><?= $md->repl('@{idxL}') ?></span>
            <span><?= $md->repl('@{idxT}') ?></span>
        </div>
        <div>
            <div>
                <?php foreach ($md->jd['languages'] as $lng=>$cont): ?>
                <a href="<?= $md->link($lng, '') ?>"><?= $cont['title'] ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <?php foreach ($md->jd['languages'] as $lng=>$cont): ?>
                <a href="<?= $md->link('', $lng) ?>"><?= $cont['title'] ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <span>P = <?= $md->repl('@{lblP}') ?></span>
            <span>A = <?= $md->repl('@{lblA}') ?></span>
            <span>R = <?= $md->repl('@{lblR}') ?></span>
        </div>
    </nav>
    <main>
        <?php foreach ($md->jd['texts'][$md->tl] as $one): ?>
            <?php if (!is_array($one)): ?>
                <div>Wut???</div>
            <?php elseif (count($one) == 1): ?>
            <?php foreach ($one as $k=>$v): ?>
                <div>
                    <!-- <?= var_export($one) ?> -->
                    <span><?= $k ?>: </span>
                    <span><?= $md->repl($v) ?></span>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <div>
                    wooohooo
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>
    <footer>
    </footer>
  </body>
</html>