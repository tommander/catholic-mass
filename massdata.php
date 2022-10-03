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
			'booklink' => 'fas fa-book-reader',
			'bread' => 'fas fa-cookie-bite',
			'wine' => 'fas fa-wine-glass-alt'
		];

		/**
		 * Sets {@see $tl} and {@see $ll} and then loads content from language files to {@see $langs} and {@see $labels}
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

		/**
		 * Loads a JSON language file into an associative array
		 * 
		 * @param string $fileName Name of the file, without directory and extension
		 * @return array Content of the file or an empty array
		 */
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

		/**
		 * This function replaces label IDs with respective label texts.
		 *
		 * @param array $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
		 * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error
		 * @see https://www.php.net/manual/en/function.preg-replace-callback-array
		 * @used-by repls()
		 */
        private function replcbs(array $matches):string {
			if ((!is_array($this->labels)) || count($matches) < 2) {
				return '';
			}
            return array_key_exists($matches[1], $this->labels) ? $this->labels[$matches[1]] : "???";
        }

		/**
		 * This function replaces label IDs with respective label texts.
		 * 
		 * It is actually the same as {@see replcbs()}, but it wraps the returned value in a "span" tag with the class "command".
		 *
		 * @param array $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
		 * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error, in every case wrapped as noted in the description
		 * @used-by repl()
		 * @see https://www.php.net/manual/en/function.preg-replace-callback-array
		 */
		private function replcb(array $matches):string {
            return "<span class=\"command\">".$this->replcbs($matches)."</span>";
        }

		/**
		 * This function replaces icon IDs with respective Font Awesome icons.
		 *
		 * @param array $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched icon ID)
		 * @return string Font Awesome icon in the form of an "i" tag with the respective CSS class or an empty string in case of an error
		 * @used-by repl()
		 * @used-by repls()
		 * @see https://www.php.net/manual/en/function.preg-replace-callback-array
		 */
        private function replico(array $matches):string {
			if ((!is_array($this->icons)) || count($matches) < 2 || (!array_key_exists($matches[1], $this->icons))) {
				return '';
			}
            return "<i class=\"".$this->icons[$matches[1]]."\"></i>";
        }

		/**
		 * Regex replacement of label and icon placeholders in a text.
		 * 
		 * @param string $text Text that may contain label/icon placeholders
		 * @return string Text with replaced label/icon placeholders
		 * @uses replcb()
		 * @uses replico()
		 */
        public function repl(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcb', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], htmlspecialchars($text));
        }

		/**
		 * Regex replacement of label and icon placeholders in a text.
		 * 
		 * @param string $text Text that may contain label/icon placeholders
		 * @return string Text with replaced label/icon placeholders
		 * @uses replcbs()
		 * @uses replico()
		 */
        public function repls(string $text) {
            return preg_replace_callback_array(['/@\{([A-Za-z0-9]+)\}/' => 'self::replcbs', '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'], htmlspecialchars($text));
        }

		/**
		 * Creates a URL to this web app using specified labels and/or text language.
		 *
		 * @param string $label Language for labels (default '' stands for current labels language)
		 * @param string $text Language for texts (default '' stands for current texts language)
		 * @return string Relative URL to the web app page with chosen languages as GET parameters
		 */
        public function link($label = '', $text = '') {
            $putlabel = (preg_match('/^[a-z]{3}$/', $label)) ? $label : $this->ll;
            $puttext = (preg_match('/^[a-z]{3}$/', $text)) ? $text : $this->tl;
            return "index.php?ll=${putlabel}&tl=${puttext}";
        }

		/**
		 * Converts a JSON object to an HTML code.
		 * 
		 * JSON object stands for a single piece of text (prayer, command, response etc.)
		 * 
		 * @param string $key "Who says that" (a single letter or empty string for a command)
		 */
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