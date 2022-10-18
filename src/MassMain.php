<?php
/**
 *
 */

/**
 *
 */
class MassMain
{
    /**
     * @var DI\Container
     */
    private $_container;

    /**
     * Creates a container
     */
    public function __construct()
    {
        $this->_container = new DI\Container();
    }

    /**
     *
     * @return array<string, mixed>
     */
    private function _prepareHtmlData()
    {
    }

    /**
     * Runs the app (builds a final HTML)
     *
     * @return void
     */
    public function run()
    {
        $template = __DIR__.'/../templates/main.html';
        if (!file_exists($template)) {
            return;
        }

        $htmldata = $this->_prepareHtmlData();
        if (!$htmldata) {
            return;
        }

        $content = file_get_contents($template);

        //preg_replace

        return $content;
    }
}
