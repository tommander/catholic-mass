<?php
/**
 * Language unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

use TMD\OrderOfMass\Models\{BiblemapModel,BooklistModel,LanglabelsModel};
use TMD\OrderOfMass\Exceptions\{OomException,ModelException};

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Language-related functionality
 */
class Language
{

    /**
     * Logger service
     *
     * @var Logger
     */
    private $logger;

    /**
     * List of available Font Awesome icons.
     *
     * This is just to make it easier to reference icons (you don't have to use the whole FontAwesome reference that goes to the `class` attribute).
     *
     * - Array key is the icon ID as used in the placeholder `@icon{iconID}`
     * - Array value is the CSS class of the `<i>` tag that Font Awesome uses
     *
     * @var array<string, string>
     */
    private $icons = [
        'cross'    => 'fas fa-cross',
        'bible'    => 'fas fa-bible',
        'bubble'   => 'far fa-comment',
        'peace'    => 'far fa-handshake',
        'walk'     => 'fas fa-hiking',
        'stand'    => 'fas fa-male',
        'sit'      => 'fas fa-chair',
        'kneel'    => 'fas fa-pray',
        'booklink' => 'fas fa-book-reader',
        'bread'    => 'fas fa-cookie-bite',
        'wine'     => 'fas fa-wine-glass-alt',
        'heading'  => 'fas fa-folder-tree',
    ];

    /**
     * GetParams instance
     *
     * @var GetParams
     */
    private $getParams;

    /**
     * BibleReader instance
     *
     * @var BibleReader
     */
    private $bibleReader;

    /**
     * Lectionary instance
     *
     * @var Lectionary
     */
    private $lectionary;

    /**
     * Hello
     *
     * @var BooklistModel
     */
    private $bookListModel;

    /**
     * Hello
     *
     * @var LanglabelsModel
     */
    private $langLabelsModel;

    /**
     * Hello
     *
     * @var BiblemapModel
     */
    private $bibleMapModel;


    /**
     * Save service instances
     *
     * @param Logger          $logger          Logger service
     * @param GetParams       $getParams       GetParams service
     * @param BibleReader     $bibleReader     BibleReader service
     * @param Lectionary      $lectionary      Lectionary service
     * @param BooklistModel   $bookListModel   Booklist model
     * @param LanglabelsModel $langLabelsModel Langlabels model
     * @param BiblemapModel   $bibleMapModel   Biblemap model
     */
    public function __construct(Logger $logger, GetParams $getParams, BibleReader $bibleReader, Lectionary $lectionary, BooklistModel $bookListModel, LanglabelsModel $langLabelsModel, BiblemapModel $bibleMapModel)
    {
        $this->logger          = $logger;
        $this->getParams       = $getParams;
        $this->bibleReader     = $bibleReader;
        $this->lectionary      = $lectionary;
        $this->bookListModel   = $bookListModel;
        $this->langLabelsModel = $langLabelsModel;
        $this->bibleMapModel   = $bibleMapModel;

    }//end __construct()


    /**
     * Changes a Bible verse reference into a placeholder (e.g.
     * `$bib[Psalms]{Ps 1.1}`) and, if Bible translation is defined, adds the
     * respective text.
     *
     * @param string $ref Bible verse reference (e.g. `Ps 1.1`)
     *
     * @return string
     */
    public function replbb($ref)
    {
        $addition = $this->bibleReader->getVerse($ref);
        if ($addition !== '') {
            $addition = ' '.$addition;
        }

        $bookShort = $ref;

        if (preg_match('/^(\S+)(.*)$/', $ref, $m) !== 1 || count($m) !== 3) {
            throw new OomException('Verse reference is incorrect: "'.$ref.'"');
        }

        $bookNum  = $this->bookListModel->abbreviationToNumber($m[1]);
        $bookFull = $this->bookListModel->numberToName($bookNum);

        $bookShortTmp = $this->bibleMapModel->numberToAbbreviation($bookNum);
        if ($bookShortTmp !== '') {
            $bookShort = $bookShortTmp.$m[2];
        }

        $bookFullTmp = $this->bibleMapModel->numberToName($bookNum);
        if ($bookFullTmp !== '') {
            $bookFull = $bookFullTmp;
        }

        return '@bib['.$bookFull.']{'.$bookShort.'}'.$addition;

    }//end replbb()


    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * Placeholders list:
     *
     * - @{text} - the string `text` is treated as a key in the `xxx_labels.json` file, object `labels`
     * - @su{text} - the string `text` is treated as a key in the `xxx_labels.json` file, object `sundays`
     * - @my{text} - the string `text` is treated as a key in the `xxx_labels.json` file, object `mysteries`
     * - @bib{string} - the string `string` is treated as a key in the `xxx_labels.json` file, object `bible`
     * - @icon{text} - the string `text` is treated as a key in the {@see Language::icons} array
     *
     * Please note that `text` consists of latin letters (uppercase or lowercase) and digits. On the other hand, `string` consists of any character except for square brackets.
     *
     * @param string $text        Text that may contain label/icon placeholders
     * @param bool   $wrapCommand Whether to wrap label placeholders with `span` tag with class `command` (default `false`)
     *
     * @return string Text with replaced label/icon placeholders
     *
     * @psalm-suppress MissingClosureParamType
     */
    public function repls(string $text, bool $wrapCommand=false)
    {
        $ret = preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/'           => function ($matches) use ($wrapCommand) {
                    if (count($matches) < 2) {
                        throw new OomException('Incorrect label placeholder');
                    }

                    $label = $this->langLabelsModel->getLabel($matches[1]);

                    if ($wrapCommand !== true) {
                        return $label;
                    }

                    return "<span class=\"command\">$label</span>";
                },
                '/@su\{([A-Za-z0-9]+)\}/'         => function ($matches) {
                    if (count($matches) < 2) {
                        throw new OomException('Incorrect sunday placeholder');
                    }

                    $sunday = $this->langLabelsModel->getSunday($matches[1]);

                    return $sunday;
                },
                '/@my\{([A-Za-z0-9]+)\}/'         => function ($matches) {
                    if (count($matches) < 2) {
                        throw new OomException('Incorrect mystery placeholder');
                    }

                    $mystery = $this->langLabelsModel->getMystery($matches[1]);

                    return $mystery;
                },
                '/@bib\[([^\]]+)?\]\{([^\}]+)\}/' => function ($matches) {
                    if (count($matches) < 3) {
                        throw new OomException('Incorrect verse reference placeholder');
                    }

                    return sprintf("<br><abbr title=\"%s\">%s</abbr><br>", $matches[1], $matches[2]);
                },
                '/@icon\{([A-Za-z0-9]+)\}/'       => function ($matches) {
                    if (count($matches) < 2
                        || array_key_exists($matches[1], $this->icons) === false
                    ) {
                        throw new OomException('Incorrect icon placeholder');
                    }

                    return "<i class=\"".$this->icons[$matches[1]]."\"></i>";
                },
            ],
            $text
        );

        if (is_string($ret) !== true) {
            throw new OomException('Return value for placeholder replacement is not string: "'.var_export($ret, true).'"');
        }

        return $ret;

    }//end repls()


}//end class
