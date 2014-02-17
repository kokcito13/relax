<?php
class Application_Model_Kernel_TextRedactor
{

    public static function cutText($text, $count = 100)
    {
        $check = substr($text, 0, $count);
        if (trim(substr($check, $count - 1, $count)) == '') return $check;
        else return self::cutText($text, $count + 1);
    }

    public static function makeTranslit($text)
    {
        $text = trim($text);
        $text = str_replace(' ', '_', $text);
        $text = self::translate('ru', 'en', $text);
        $text = mb_strtolower($text, 'utf8');

        return $text;
    }

    public static function  translate($from_lan, $to_lan, $text)
    {
        $json            = json_decode(file_get_contents('https://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=' . urlencode($text) . '&langpair=' . $from_lan . '|' . $to_lan));
        $translated_text = $json->responseData->translatedText;

        return $translated_text;
    }
}