<?php

/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 08/08/17
 * Time: 15:34
 */
class Ccsd_Tex_LanguageTest extends PHPUnit_Framework_TestCase
{

    public function testGetLangName() {
        $lang = Ccsd_Tex_Language::getLanguage('Chinese');
        $this -> assertEquals('chinois', $lang -> getName('fr'));
        $this -> assertEquals('Chinesisch', $lang -> getName('de'));
        $this -> assertEquals('中国語', $lang -> getName('ja'));
    }

    public function testGetLangRegexp() {
        $lang = Ccsd_Tex_Language::getLanguage('Chinese');
        $this -> assertEquals('/\\b\\p{Han}+\\b/u', $lang -> getLangRegexp());
        $lang = Ccsd_Tex_Language::getLanguage('Japanese');
        $this -> assertEquals('/\\b[\\p{Hiragana}\\p{Katakana}\\p{Han}]+\\b/u', $lang -> getLangRegexp());
    }

    public function testhasLang()
    {
        $langZh = Ccsd_Tex_Language::getLanguage('Chinese');
        $langJa = Ccsd_Tex_Language::getLanguage('Japanese');
        $this -> assertEquals(0   , $langZh->hasLang('Bonjour étranger'));
        $this -> assertEquals(1, $langZh->hasLang('Chinese 人有悲欢离合 Japanese 露の世は corean 그들에게'));
        $this -> assertEquals(1, $langJa->hasLang('Chinese 人有悲欢离合 Japanese 露の世は corean 그들에게'));
        $this -> assertEquals(0, $langZh->hasLang('Chinese without word boundary 人有悲欢离合露の世は그들에게'));
    }

    public function testAddLanguageTags() {
        $text  = '\\section{Sanskrit}सर्वे मानवाः स्वतन्त्राः समुत्पन्नाः' . "\n";
        $text .= '\\section{CJK}Here\'s some Chinese:' . "\n";
        $text .= '人有悲欢离合，月有阴晴圆缺。此事古难全，但愿人长久，千里共婵娟。'. "\n";
        $text .= '\\section{Arabic}'. "\n";
        $text .= 'بعد هامش وإقامة المتحدة و, أم السادس وبالرغم فقد. بعد أن صفحة شمال بداية, أسر حصدت تزامناً ما. ٣٠ نقطة المحيط بمحاولة مكن, مع شمال يتبقّ تحت. خلاف أكثر دون من, الأرض أعلنت فرنسية ٣٠ على.
';
        $text .= '\\section{Japanese}' . "\n";
        $text .= '露の世は、露の世ながら、さりながら。' . "\n";
        $langZh = Ccsd_Tex_Language::getLanguage('Chinese');
        $langSa = Ccsd_Tex_Language::getLanguage('Sanskrit');
        $langAr = Ccsd_Tex_Language::getLanguage('Arabic');
        $langJa = Ccsd_Tex_Language::getLanguage('Japanese');

        $this -> assertRegExp('/\\\\begin\\{sanskrit\\}/', $langSa -> addLanguageTags($text));
        $this -> assertRegExp('/\\\\begin\\{Arabic\\}/'  , $langAr -> addLanguageTags($text));
        $this -> assertRegExp('/\\{\\\\japanesefont /'   , $langJa -> addLanguageTags($text));
}
}