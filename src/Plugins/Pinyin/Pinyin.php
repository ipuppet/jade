<?php


namespace Ipuppet\Jade\Plugins\Pinyin;


class Pinyin
{
    private $chineseSpellList;
    private $separator = ' ';

    public function __construct()
    {
        $this->chineseSpellList = include 'PinyinMap.php';
    }

    public function setChineseSpellList($chineseSpellList)
    {
        $this->chineseSpellList = $chineseSpellList;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * 汉字转拼音函数
     *
     * @param string $chinese 汉字字符串
     * @param bool $ifShorthand 是否全拼
     * @param bool $ifUppercase 首字母是否大写
     * @return string
     */

    public function getChineseSpells($chinese, $ifShorthand = false, $ifUppercase = false)
    {
        $chinese = preg_replace("/\s/is", "_", $chinese);
        $chinese = preg_replace("/(|\~|\`|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\-|\+|\=|\{|\}|\[|\]|\||\\|\:|\;|\"|\'|\<|\,|\>|\.|\?|\/)/is", "", $chinese);
        $chineseSpells = '';
        // 识别UTF-8
        if (strlen("拼音") > 4)
            $chinese = iconv('UTF-8', 'GBK', $chinese);
        if (!$ifShorthand) {
            // 全拼
            for ($i = 0; $i < strlen($chinese); $i++) {
                if (ord($chinese[$i]) > 128) {
                    $char = $this->asi2py(ord($chinese[$i]) + ord($chinese[$i + 1]) * 256);
                    $chineseSpells .= $char;
                    $i++;
                } else {
                    $chineseSpells .= $chinese[$i];
                }
                $chineseSpells .= $this->separator;
            }
        } else {
            // 首字母
            for ($i = 0; $i < strlen($chinese); $i++) {
                if (ord($chinese[$i]) > 128) {
                    $char = $this->asi2py(ord($chinese[$i]) + ord($chinese[$i + 1]) * 256);
                    $chineseSpells .= $char[0];
                    $i++;
                } else {
                    $chineseSpells .= $chinese[$i];
                }
                $chineseSpells .= $this->separator;
            }
        }
        // 判断是否输出小写字符
        return ($ifUppercase == true ? $chineseSpells : strtolower($chineseSpells));
    }

    private function asi2py($char)
    {
        $chineseSpells = $this->chineseSpellList;
        foreach ($chineseSpells as $chineseSpell) {
            if (array_search($char, $chineseSpell) !== false) {
                return key($chineseSpells);
            }
            next($chineseSpells);
        }
        return null;
    }
}
