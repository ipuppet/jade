<?php


namespace Zimings\Jade\Plugins\HtmlProcessor;


class HtmlSecuritySplit
{
    /**
     * 判断标签闭合情况
     *
     * @param string $html
     * @return array 未配对的标签
     */
    private function getIncompleteTags($html): array
    {
        $p = '/\<([^\s\>(hr)(br)]+)/';
        preg_match_all($p, $html, $tags);
        $tags = $tags[1];
        $len = count($tags);
        for ($i = 0; $i < $len; $i++) {
            if (substr($tags[$i], 0, 1) === '/') {
                $endTag = substr($tags[$i], 1);
                $index = $i - 1;
                while (1) {
                    if (isset($tags[$index])) {
                        $lastTag = $tags[$index];
                        break;
                    } else {
                        if ($index !== 0)
                            $index--;
                        else
                            break;
                    }
                }
                while (1) {
                    if ($lastTag === $endTag) {
                        unset($tags[$i]);
                        unset($tags[$index]);
                        $lastTag = '';
                    }
                    break;
                }
            }
        }
        return $tags;
    }

    /**
     * 修复被切断的html
     * @param $html
     * @param int $len
     * @param int $start
     * @return string
     */
    public function repairHtml($html, int $len, int $start = 0): string
    {
        if ($len > strlen($html)) $len = strlen($html);
        $html = mb_substr($html, $start, $len);
        $html = mb_substr($html, $start, $len);
        $incompleteTags = $this->getIncompleteTags($html);
        foreach ($incompleteTags as $incompleteTag) {
            if (mb_substr($incompleteTag, 0, 1) === '/') {
                $newTags = mb_substr($incompleteTag, 1);
                $html = "<{$newTags}>" . $html;
            } else {
                $html = $html . "</{$incompleteTag}>";
            }
        }
        return $html;
    }
}
