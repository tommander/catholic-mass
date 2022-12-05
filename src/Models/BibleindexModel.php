<?php
/**
 * Zefania-bibles index.min.json Model Unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace TMD\OrderOfMass\Models;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Zefania-bibles index.min.json Model
 */
class BibleindexModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {
        $this->loadJson('libs/zefania-bibles/index.min.json');

    }//end initModel()


    /**
     * Hello
     *
     * @param string $paramLabels  Parameter for labels language
     * @param string $paramContent Parameter for content language
     * @param string $paramBible   Parameter for current Bible translation
     *
     * @return array
     */
    public function listBiblesForSelect(string $paramLabels, string $paramContent, string $paramBible): array
    {
        $ret = [
            '' => [
                [
                    'value' => '',
                    'sel'   => false,
                    'text'  => '-',
                ],
            ],
        ];

        if (is_array($this->jsonContent) !== true) {
            return $ret;
        }

        foreach ($this->jsonContent as $langKey => $langData) {
            if (is_string($langKey) !== true) {
                continue;
            }

            if ($langKey !== $paramLabels && $langKey !== $paramContent) {
                continue;
            }

            if (is_array($langData) !== true) {
                continue;
            }

            $ret[$langKey] = [];
            foreach ($langData as $bibleID => $bibleData) {
                $bibleTitle = '';
                $bibleMeta  = $this->getBibleMeta($langKey, $bibleID);
                if (is_array($bibleMeta) === true && array_key_exists('TITLE', $bibleMeta) === true) {
                    $bibleTitle = $bibleMeta['TITLE'];
                }

                $bibleValue = $langKey.'|'.$bibleID;

                $ret[$langKey][] = [
                    'value' => $bibleValue,
                    'sel'   => ($paramBible === $bibleValue),
                    'text'  => $bibleTitle,
                ];
            }
        }//end foreach

        return $ret;

    }//end listBiblesForSelect()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return ?array
     */
    public function getBibleData(string $language, string $bible): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        if (array_key_exists($language, $this->jsonContent) !== true) {
            return null;
        }

        if (is_array($this->jsonContent[$language]) !== true) {
            return null;
        }

        if (array_key_exists($bible, $this->jsonContent[$language]) !== true) {
            return null;
        }

        return $this->jsonContent[$language][$bible];

    }//end getBibleData()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return ?string
     */
    public function getBibleChecksum(string $language, string $bible): ?string
    {
        $data = $this->getBibleData($language, $bible);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('md5', $data) !== true) {
            return null;
        }

        return $data['md5'];

    }//end getBibleChecksum()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return ?string
     */
    public function getBibleFile(string $language, string $bible): ?string
    {
        $data = $this->getBibleData($language, $bible);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('file', $data) !== true) {
            return null;
        }

        return $data['file'];

    }//end getBibleFile()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return ?array
     */
    public function getBibleMeta(string $language, string $bible): ?array
    {
        $data = $this->getBibleData($language, $bible);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('meta', $data) !== true) {
            return null;
        }

        return $data['meta'];

    }//end getBibleMeta()


}//end class
