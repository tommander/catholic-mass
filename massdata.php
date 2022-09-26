<?php 
    class MassData {
        public $jd;
        public $tl;
        public $ll;
		private $icons = [
			'cross' => 'fas fa-cross',
			'bible' => 'fas fa-bible',
			'bubble' => 'far fa-comment',
			'peace' => 'far fa-handshake',
			'walk' => 'fas fa-hiking',
			'stand' => 'fas fa-male',
			'sit' => 'fas fa-chair',
			'kneel' => 'fas fa-pray'
		];

        function __construct() {
            $this->jd = json_decode(file_get_contents('data.json'), true);
            $this->tl = 'eng';
            $this->ll = 'eng';

            if (array_key_exists('ll', $_GET)) {
                if (array_key_exists($_GET['ll'], $this->jd['languages']) !== FALSE) {
                    $this->ll = $_GET['ll'];
                }
            }
            if (array_key_exists('tl',$_GET)) {
                if (array_key_exists($_GET['tl'], $this->jd['languages']) !== FALSE) {
                    $this->tl = $_GET['tl'];
                }
            }
        }

        private function replcb(array $matches):string {
            return "<span class=\"command\">".$this->jd['labels'][$matches[1]][$this->ll]."</span>";
        }

        private function replcbs(array $matches):string {
            return $this->jd['labels'][$matches[1]][$this->ll];
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
            return "index2.php?ll=${putlabel}&tl=${puttext}";
        }

		public function html() {
			$ret = '';

			foreach ($this->jd['texts'][$this->tl] as $one) {
				if (!is_array($one)) {
					continue;
				}
				
				if (count($one) == 1) {
					foreach($one as $k=>$v) {
						$ret .= "<div><span class=\"who\">$k:</span><span class=\"what\">".$this->repl($v)."</span></div>\r\n";
					}
					continue;
				}

				$ret .= "<div class=\"options\">\r\n";
				foreach ($one as $oneone) {
					$ret .= "<div>\r\n";
					if (count($oneone) == 1) {
						foreach($oneone as $k=>$v) {
							$ret .= "<div><span class=\"who\">$k:</span><span class=\"what\">".$v."</span></div>\r\n";
						}
					} else {
						foreach ($oneone as $oneoneone) {
							foreach($oneoneone as $k=>$v) {
								$ret .= "<div><span class=\"who\">$k:</span><span class=\"what\">".$v."</span></div>\r\n";
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