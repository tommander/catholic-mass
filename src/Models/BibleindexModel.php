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

use TMD\OrderOfMass\Exceptions\ModelException;

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
        $this->checkAndSanitize();

    }//end initModel()


    /**
     * Check JSON structure and sanitize its content.
     *
     * 1. Check the decoded JSON structure and allow only known elements
     * 2. Sanitize all values (strings, array keys/values, object properties/values)
     *
     * @return void
     */
    private function checkAndSanitize(): void
    {
        if (is_array($this->jsonContent) !== true) {
            $this->logger->warning('No content');
            return;
        }

        $copy = $this->jsonContent;
        $this->jsonContent = [];

        foreach ($copy as $langKey => $langData) {
            if (is_string($langKey) !== true || is_array($langData) !== true) {
                $this->logger->warning('Langkey/langdata incorrect');
                continue;
            }

            $langKeyClean = \preg_replace('/[^a-z]/', '', $langKey);
            if ($langKeyClean === '') {
                $this->logger->warning('Langkey empty after cleaning');
                continue;
            }

            $this->jsonContent[$langKeyClean] = [];
            foreach ($langData as $bibleKey => $bibleData) {
                if (is_string($bibleKey) !== true || is_array($bibleData) !== true) {
                    $this->logger->warning('Biblekey/bibledata incorrect');
                    continue;
                }

                $bibleKeyClean = \preg_replace('/[^Üa-z0-9\._-]/', '', $bibleKey);
                if ($bibleKeyClean === '') {
                    $this->logger->warning('Biblekey empty after cleaning');
                    continue;
                }

                $this->jsonContent[$langKeyClean][$bibleKeyClean] = [];

                foreach ($bibleData as $dataKey => $dataValue) {
                    if ($dataKey === 'md5' && is_string($dataValue) === true) {
                        $this->jsonContent[$langKeyClean][$bibleKeyClean]['md5'] = \preg_replace('/[^0-9a-f]/', '', $dataValue);
                        continue;
                    }

                    if ($dataKey === 'file' && is_string($dataValue) === true) {
                        $this->jsonContent[$langKeyClean][$bibleKeyClean]['file'] = \preg_replace('~(\.\.|[\/\\\])~', '', $dataValue);
                        continue;
                    }

                    if ($dataKey === 'meta' && is_array($dataValue) === true) {
                        $this->jsonContent[$langKeyClean][$bibleKeyClean]['meta'] = [];
                        foreach ($dataValue as $metaKey => $metaValue) {
                            if (is_string($metaKey) !== true || is_string($metaValue) !== true) {
                                $this->logger->warning('Metakey/metavalue incorrect');
                                continue;
                            }

                            $metaKeyClean = \preg_replace('/[^A-Z]/', '', $metaKey);
                            if ($metaKeyClean === '') {
                                $this->logger->warning('Langkey empty after cleaning');
                                continue;
                            }

                            $this->jsonContent[$langKeyClean][$bibleKeyClean]['meta'][$metaKeyClean] = \htmlspecialchars($metaValue);
                        }
                    }
                }//end foreach
            }//end foreach
        }//end foreach

    }//end checkAndSanitize()


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
                if (array_key_exists('TITLE', $bibleMeta) === true) {
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
     * @return array
     */
    public function getBibleData(string $language, string $bible): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($language, $this->jsonContent) !== true) {
            throw new ModelException('Language "'.$language.'" not found', ModelException::CODE_PARAMETER);
        }

        if (is_array($this->jsonContent[$language]) !== true) {
            throw new ModelException('JSON subitem content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($bible, $this->jsonContent[$language]) !== true) {
            throw new ModelException('Bible identifier "'.$bible.'" in language "'.$language.'" not found', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent[$language][$bible];

    }//end getBibleData()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return string
     */
    public function getBibleChecksum(string $language, string $bible): string
    {
        $data = $this->getBibleData($language, $bible);

        if (array_key_exists('md5', $data) !== true) {
            throw new ModelException('MD5 not found in Bible data for "'.$language.'|'.$bible.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['md5'];

    }//end getBibleChecksum()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return string
     */
    public function getBibleFile(string $language, string $bible): string
    {
        $data = $this->getBibleData($language, $bible);

        if (array_key_exists('file', $data) !== true) {
            throw new ModelException('Filename not found in Bible data for "'.$language.'|'.$bible.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['file'];

    }//end getBibleFile()


    /**
     * Hello
     *
     * @param string $language Language code
     * @param string $bible    Bible identifier
     *
     * @return array
     */
    public function getBibleMeta(string $language, string $bible): array
    {
        $data = $this->getBibleData($language, $bible);

        if (array_key_exists('meta', $data) !== true) {
            throw new ModelException('Metadata not found in Bible data for "'.$language.'|'.$bible.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['meta'];

    }//end getBibleMeta()


}//end class
