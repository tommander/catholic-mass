<?php 
    class MassData {
        public $tl;
        public $ll;
		public $langs = [];
		public $labels = [];
		private $icons = [
			'cross' => 'fas fa-cross',
			'bible' => 'fas fa-bible',
			'bubble' => 'far fa-comment',
			'peace' => 'far fa-handshake',
			'walk' => 'fas fa-hiking',
			'stand' => 'fas fa-male',
			'sit' => 'fas fa-chair',
			'kneel' => 'fas fa-pray',
			'booklink' => 'fas fa-book-reader'
		];

        function __construct() {
			$this->langs = json_decode(file_get_contents('data/langlist.json'), true);

            $this->tl = 'eng';
            $this->ll = 'eng';

            if (array_key_exists('ll', $_GET)) {
                if (array_key_exists($_GET['ll'], $this->langs) !== FALSE) {
                    $this->ll = $_GET['ll'];
                }
            }
            if (array_key_exists('tl',$_GET)) {
                if (array_key_exists($_GET['tl'], $this->langs) !== FALSE) {
                    $this->tl = $_GET['tl'];
                }
            }

			$tmp = json_decode(file_get_contents('data/'.$this->ll.'.json'), true);
			$this->labels = $tmp['labels'];
			unset($tmp);
		}

        private function replcbs(array $matches):string {
            return array_key_exists($matches[1], $this->labels) ? $this->labels[$matches[1]] : "???";
        }

		private function replcb(array $matches):string {
            return "<span class=\"command\">".$this->replcbs($matches)."</span>";
        }

        private function replico(array $matches):string {
            return "<i class=\"".$this->icons[$matches[1]]."\"></i>";
        }

        public function repl(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcb', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], $text);
        }

        public function repls(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcbs', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], $text);
        }

        public function link($label = '', $text = '') {
            $putlabel = ($label != '') ? $label : $this->ll;
            $puttext = ($text != '') ? $text : $this->tl;
            return "index.php?ll=${putlabel}&tl=${puttext}";
        }

		private function kv2html($key, $val) {
			$who = '';
			$what = '';
			$cls = '';
			if ($key == 'reading') {
				$what = "<a href=\"".$this->repls('@{dbrlink}')."\">".$this->repls('@icon{booklink} @{dbrtext}')."</a>";
			} else {
				$who = $key;
				$what = $this->repl($val);
				if (strcasecmp($who, 'a') === 0) {
					$what = "<strong>${what}</strong>";
				}
			}
			if ($who != '') {
				$who = "<span class=\"who\">${who}:</span>";
			} else {
				$cls = " class=\"gre\"";
			}
			return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";
		}

		public function html() {
			$ret = '';
			$texts = json_decode(file_get_contents('data/'.$this->tl.'.json'), true);

			foreach ($texts['texts'] as $one) {
				if (!is_array($one)) {
					continue;
				}
				
				if (count($one) == 1) {
					foreach($one as $k=>$v) {
						$ret .= $this->kv2html($k, $v);
					}
					continue;
				}

				$ret .= "<div class=\"options\">\r\n";
				foreach ($one as $oneone) {
					$ret .= "<div>\r\n";
					if (count($oneone) == 1) {
						foreach($oneone as $k=>$v) {
							$ret .= $this->kv2html($k, $v);
						}
					} else {
						foreach ($oneone as $oneoneone) {
							foreach($oneoneone as $k=>$v) {
								$ret .= $this->kv2html($k, $v);
							}
	
						}
					}
					$ret .= "</div>\r\n";
				}
				$ret .= "</div>\r\n";
			}

			return $ret;
		}

    }
?>