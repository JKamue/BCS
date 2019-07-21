<?php

class MojangApi
{
    public static function namesToUUID($array) {
        $url = "https://api.mojang.com/profiles/minecraft";
        $parts = parse_url($url);
        $host = $parts['host'];
        $ch = curl_init();
        $header = array('POST /1575051 HTTP/1.1',
            "Host: {$host}",
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Content-Type: application/json',);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        return $result;
    }

    public static function UUIDToName($uuid){
        return json_decode(file_get_contents("https://api.mojang.com/user/profiles/$uuid/names"),true);
    }
}