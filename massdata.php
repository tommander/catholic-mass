<?php
	/**
	 * Class that helps to convert language JSON files to HTML
	 */ 
    class MassData {
		/** Texts language */
        public $tl = 'eng';
		/** Labels language */
        public $ll = 'eng';
		/** List of languages (from data/langlist.json) */
		public $langs = [];
		/** List of labels (from data/lng.json) */
		private $labels = [];
		/** List of Font Awesome icons [iconid => iconclass] */
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

		/**
		 * 
		 */
        function __construct() {
			$this->langs = $this->loadJson('langlist');

            if (array_key_exists('ll', $_GET)) {
                if (array_key_exists($_GET['ll'], $this->langs) !== FALSE) {
					if (preg_match('/^[a-z]{3}$/', $_GET['ll'])) {
                    	$this->ll = $_GET['ll'];
					}
                }
            }

            if (array_key_exists('tl',$_GET)) {
                if (array_key_exists($_GET['tl'], $this->langs) !== FALSE) {
					if (preg_match('/^[a-z]{3}$/', $_GET['tl'])) {
						$this->tl = $_GET['tl'];
					}
                }
            }

			$tmp = $this->loadJson($this->ll);
			if (array_key_exists('labels', $tmp) && is_array($tmp['labels'])) {
				$this->labels = $tmp['labels'];
			}
		}

		private function loadJson(string $fileName):array {
			$aFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$fileName.'.json';
			if (file_exists($aFile)) {
				$aFileCont = file_get_contents($aFile);
				if ($aFileCont !== FALSE) {
					$a = json_decode($aFileCont, true);
					if ($a !== NULL && is_array($a)) {
						return $a;
					}	
				}		
			}
			return [];
		}

        private function replcbs(array $matches):string {
			if ((!is_array($this->labels)) || count($matches) < 1) {
				return '';
			}
            return array_key_exists($matches[1], $this->labels) ? $this->labels[$matches[1]] : "???";
        }

		private function replcb(array $matches):string {
            return "<span class=\"command\">".$this->replcbs($matches)."</span>";
        }

        private function replico(array $matches):string {
			if ((!is_array($this->icons)) || count($matches) < 1 || (!array_key_exists($matches[1], $this->icons))) {
				return '';
			}
            return "<i class=\"".$this->icons[$matches[1]]."\"></i>";
        }

        public function repl(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcb', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], htmlspecialchars($text));
        }

        public function repls(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcbs', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], htmlspecialchars($text));
        }

        public function link($label = '', $text = '') {
            $putlabel = (preg_match('/^[a-z]{3}$/', $label)) ? $label : $this->ll;
            $puttext = (preg_match('/^[a-z]{3}$/', $text)) ? $text : $this->tl;
            return "index.php?ll=${putlabel}&tl=${puttext}";
        }

		private function kv2html($key, $val) {
			$skey = htmlspecialchars($key);
			$sval = htmlspecialchars($val);
			$who = '';
			$what = '';
			$cls = '';
			if ($skey == 'reading') {
				$what = "<a href=\"".$this->repls('@{dbrlink}')."\">".$this->repls('@icon{booklink} @{dbrtext}')."</a>";
			} else {
				$who = $skey;
				$what = $this->repl($sval);
				if (strcasecmp($who, 'a') === 0) {
					$what = "<strong>${what}</strong>";
				}
			}
			if ($who != '') {
				$who = "<span class=\"who\">${who}:</span>";
			} else {
				$cls = " class=\"command\"";
			}
			return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";
		}

		public function html() {
			$texts = $this->loadJson($this->tl);

			if (!array_key_exists('texts', $texts)) {
				return '';
			}

			$ret = '';
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