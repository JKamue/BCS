<?php
function get_contents($url) {
    $parts = parse_url($url);
    $host = $parts['host'];
    $ch = curl_init();
    $header = array('GET /1575051 HTTP/1.1',
        "Host: {$host}",
        'Accept:*/*',
        'Accept-Language:de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control:max-age=0',
        'Connection:keep-alive',
        'Host:gommehd.net',
        'x-requested-with: XMLHttpRequest',
        'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36',
        'cookie: __cfduid=dcac5296911e789079b395f78f155790f1526133115; _ga=GA1.2.1786320754.1526133118; _gid=GA1.2.263680115.1526133118; _csrf=fe54b1113c9727b7bba329be37318397bc617fd0366be47718ad9f191353a486a%3A2%3A%7Bi%3A0%3Bs%3A5%3A%22_csrf%22%3Bi%3A1%3Bs%3A32%3A%228Dy0kKFMXWD25PX1yGXJalC9EOWRFquY%22%3B%7D',
        'referer: https://www.gommehd.net/clan-match',
    );

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);

    curl_setopt($ch, CURLOPT_COOKIEFILE, ROOT . 'data/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, ROOT . 'data/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function stringIsolate($string,$pos1,$pos2){
    $length = $pos2-$pos1;
    return substr($string, $pos1, $length);
}

function stringIsolateBetween($string,$needle1,$needle2,$err_mode="string") {
    //Check if both needles are in the string
    if (strpos($string,$needle1) !== false && strpos($string,$needle2) !== false) {
        $pos1 = strpos($string,$needle1);
        $pos2 = strpos($string,$needle2);
        return stringIsolate($string,$pos1+strlen($needle1),$pos2);
    } else {
        //One or both needles don't exist
        if ($err_mode == "bool") {
            return false;
        } else {
            return $string;
        }
    }
}

function stringReplaceBreaks($string) {
    return trim(preg_replace('/\s+/', ' ', $string));
}