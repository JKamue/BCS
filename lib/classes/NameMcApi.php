<?php


class NameMcApi
{
    /**
     * Converts an old name to the UUID that most recently used that name
     *
     * @param $name Username
     * @return string|bool the UUID
     */
    public static function oldNameToUUID($name) {
        $html = get_contents("https://de.namemc.com/search?q=$name");
        $html = stringReplaceBreaks($html);
        $uuid = stringIsolateBetween($html,"<samp style=\"font-size: 90%\">","</samp> </div> <div");
        $uuid = str_replace("-","", $uuid);
        if (strlen($uuid) != 32) {
            return false;
        }
        return $uuid;
    }
}