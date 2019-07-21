<?php

function get_contents($url) {
    fopen("cookies.txt", "w");
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

    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    curl_close($ch);
    fwrite(fopen("test.txt","w+"),$result);
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

class GommeApi
{

    //Convert a Clan UUID to a Name
    public static function convertUUIDtoName($clan_uuid) {
        $a = 10;
        start:

        $data = self::fetchClanCws($clan_uuid,$a);
        $clans = array();

        for ($i=0;$i<$a;$i++) {
            $winner = $data[$i]['winner'];
            $loser = $data[$i]['loser'];

            if (isset($clans[$winner])) {
                $clans[$winner] += 1;
            } else {
                $clans[$winner] = 1;
            }

            if (isset($clans[$loser])) {
                $clans[$loser] += 1;
            } else {
                $clans[$loser] = 1;
            }
        }
        $found = null;

        foreach ($clans as $key => $clan) {
            if ($clan == $a) {
                if ($found != null) {
                    $a += 10;
                    goto start;
                }
                $found = $key;
            }
        }

        return $found;
    }

    //Convert a Clan Name to a UUID
    public static function convertNameToUUID($clan_name) {
        $data = self::fetchClanStats($clan_name);
        return $data['uuid'];
    }

    //Get Rank, Wins, Points, Loses, UUID and Tag by Clanname
    public static function fetchClanStats($clan_name) {
        $url = "https://www.gommehd.net/clan-profile/wars/?name=".urlencode($clan_name);

        //Just for now to get through the spam Protection
        $html = get_contents($url);

        //Check if Clan exists (if clan doesn't exist the body will contain "Not Found (#404)" as title
        if (strpos($html, "Not Found (#404): Can't load data of Clan") !== false) {
            return false;
        }

        $html = stringReplaceBreaks($html);
        $response = array();

        //Isolate Clanname (to be 100% sure)
        $response['name'] = stringIsolateBetween($html,"data-clan_name=\"","\" data-clan_uuid=\"");
        //Isolate UUID
        $response['uuid'] = stringIsolateBetween($html,'data-clan_uuid="','"> <div id="pendingMatches');
        //Isolate ClanTag
        $response['tag'] = stringIsolateBetween($html,'class="clanTag">[',']</span>');


        //Look if clan has Youtube
        if (strpos($html, "a href=\"https://www.youtube.com/channel/") !== false) {
            $response['youtube'] = stringIsolateBetween($html,"<a href=\"https://www.youtube.com/channel/","\" target=\"_blank\"> <i class=\"fa fa-youtube-play\">");
            $html = str_replace($response['youtube']."\" target=\"_blank\">","",$html);
            $response['youtube'] = "https://www.youtube.com/channel/".$response['youtube'];
        }

        //Look if clan has twitter
        if (strpos($html, "<a href=\"https://twitter.com/") !== false) {
            $html = str_replace("\" target=\"_blank\">Shop","",$html);
            $response['twitter'] = "https://twitter.com/".stringIsolateBetween($html,"<a href=\"https://twitter.com/","\" target=\"_blank\">");
        }

        //Isolate the block the other information is standing in
        $table = stringIsolateBetween($html,"class=\"table bedwars\">","</table>");
        $pieces = explode("<td",$table);
        if (strpos($html, "Keiner</span></td>") !== false) {
            $response['rank'] = null;
        } else {
            $response['rank'] = stringIsolateBetween($pieces[1], "<span> #", "</span>");
        }
        $response['wins'] = stringIsolateBetween($pieces[2],"class=\"wins\">","</span>");
        $response['elo'] = stringIsolateBetween($pieces[3],"<span>","</span>");
        $response['loses'] = stringIsolateBetween($pieces[4],"class=\"loses\">","</span>");

        return $response;
    }

    //Get Member List
    public static function fetchClanMembers($clan_name) {
        $url = "https://www.gommehd.net/clan-profile/members-list?clanName=".urlencode($clan_name)."&offset=0&limit=10";

        //Just for no to get through the spam Protection
        $html = get_contents($url);
        $html = stringReplaceBreaks($html);

        if ($html == null){
            return false;
        }

        $parts = explode("<div class=\"clanMembersList\">",$html);

        $tmp = array();

        //Loop through each part and get the player name from the Link (/player/index?playerName=[name]"> <div class="media-object)
        for ($i=1;$i<4;$i++) {
            if (isset($parts[$i])) {
                $tmp[$i - 1] = array();
                $parts_sub = explode("<div class=\"media\">", $parts[$i]);
                foreach ($parts_sub as &$part) {
                    $player = stringIsolateBetween($part, "playerName=", "\"> <div class=\"media-object");
                    if ($player != " " and $player !== null) {
                        array_push($tmp[$i - 1], $player);
                    }
                }
            }
        }

        $return = array();
        if (isset($tmp[0][0])) {
            $return['leader'] = $tmp[0];
        }
        if (isset($tmp[1][0])) {
            $return['mods'] = $tmp[1];
        }
        if (isset($tmp[2][0])) {
            $return['member'] = $tmp[2];
        }

        return $return;
    }

    //Get Clan History
    public static function fetchClanHistory($clan_name) {
        $url1 = "https://www.gommehd.net/clan-profile/history/?name=".urlencode($clan_name)."&page=1";
        $url2 = "https://www.gommehd.net/clan-profile/history/?name=".urlencode($clan_name)."&page=2";
        $html = array();
        $html[0] = get_contents($url1);

        //Check if Clan exists (if clan doesn't exist the body will contain "Not Found (#404)" as title
        if (strpos($html[0], "<title>Not Found (#404)</title>") !== false) {
            return false;
        }

        $html[1] = get_contents($url2);

        //TODO Clan gründen und schauen was html[1] ist, wenn es keine zweite Seite gibt

        $parts = array();

        for ($i = 0;$i<2;$i++) {
            $answer = $html[$i];
            $answer = stringReplaceBreaks($answer);
            $answer = stringIsolateBetween($answer,"<tbody>","</tbody>");
            $arr = explode("<tr>",$answer);
            $parts = array_merge($parts,$arr);
        }

        $return = array();

        foreach ($parts as &$part) {
            $tmp = array();

            if ($part != " " and $part != null and $part != "") {
                //Get Datetime
                $date = stringIsolateBetween($part, "style=\"padding: 14px;\">", "</td> <td class");
                $time = stringIsolateBetween($part, "</td> <td class=\"col-md-1\" style=\"padding: 14px;\">", "</td> <td st");
                $tmp['datetime'] = $date . " " . $time;

                //Get Player
                $player = stringIsolateBetween($part, "<span", "an>");
                $player = stringIsolateBetween($player, ";\">", "</sp");
                $tmp['player'] = $player;

                //Get Event
                if (strpos($part, "verlassen") !== false) {
                    $tmp['action'] = "left clan";
                } elseif (strpos($part, "betreten") !== false) {
                    $tmp['action'] = "joined clan";
                } elseif (strpos($part, "degradiert") !== false) {
                    $tmp['action'] = "demoted";
                    if (strpos($part, "Moderator") !== false) {
                        $tmp['rank'] = "Moderator";
                    } elseif (strpos($part, "Mitglied") !== false) {
                        $tmp['rank'] = "Member";
                    }
                } elseif (strpos($part, "befördert") !== false) {
                    $tmp['action'] = "promoted";
                    if (strpos($part, "Admin") !== false) {
                        $tmp['rank'] = "Leader";
                    } elseif (strpos($part, "Moderator") !== false) {
                        $tmp['rank'] = "Moderator";
                    }
                } else {
                    $tmp['action'] = "unknown";
                }

                array_push($return, $tmp);
            }
        }

        return $return;
    }

    //Get Last CWs
    public static function fetchClanCws($clan_uuid,$amount,$game="bedwars",$finished="true") {
        $url = "https://www.gommehd.net/clans/get-matches?game=".$game."&finished=".$finished."&clanUuid=".$clan_uuid."&amount=".$amount;
        $html = get_contents($url);

        if (strpos($html, "noch keine ClanWars") !== false) {
            return false;
        }

        $html = stringReplaceBreaks($html);
        $cws = explode("</tr>",$html);
        $return = array();

        for ($i=0;$i<$amount;$i++) {
            $cw = $cws[$i];
            $tmp = array();

            //Isolate matchid
            $tmp['matchid'] = substr(stringIsolateBetween($cw, "<tr id=\"", "\" style=\"display: none;\">"),6);

            //Get winner and Looser
            $raw = stringIsolateBetween($cw,"<div class=\"result\">","</div>");
            $cw = str_replace($raw,"",$cw);
            $clans = explode("</span>",$raw);

            foreach ($clans as &$clan) {
                $name = stringIsolateBetween($clan,"\">","</a>");
                if (strpos($clan, "lose") !== false) {
                    $tmp['loser'] = $name;
                } else {
                    $tmp['winner'] = $name;
                }
            }

            //Get datetime
            $date = stringIsolateBetween($cw,"date\">","</span> <span");
            $time = stringIsolateBetween($cw,"time\">","</span> </td>");
            $tmp['datetime'] = $date." ".$time;

            //Get Map
            $cw = str_replace($date."</span","",$cw);
            $cw = str_replace($time."</span","",$cw);
            $tmp['map'] = stringIsolateBetween($cw,"<span>","</span> </td>");

            //Get length
            $cw = str_replace("<span>".$tmp['map']."</span> </td>","",$cw);
            $tmp['duration'] = stringIsolateBetween($cw,"<span>","</span> </td>");
            array_push($return,$tmp);
        }

        return $return;
    }

    //Get CW Stats
    //Spieltyp, Map, Gewinner Clan, Verlierer Clan, MVP, Spielstart, Spieldauer, ELO, ID, ReplayID, ChatlogID
    public static function fetchCwStats($cw_id) {
        //https://www.gommehd.net/clan-match?id=
        $url = "https://www.gommehd.net/clan-match?id=".$cw_id;
        $html = get_contents($url);
        $html = stringReplaceBreaks($html);

        //Check if Clan exists (if clan doesn't exist the body will contain "Not Found (#404)" as title
        if (strpos($html, "<title>Not Found (#404)</title>") !== false) {
            return false;
        }

        $clans = stringIsolateBetween($html,"<h1 class=\"text-center teams-title\">","</h1>");
        $other = str_replace($clans,"",$html);
        $clans = explode("<span class=\"vsLabel\">vs.</span>",$clans);


        $return = array();
        $return['winner'] = array();
        $return['loser'] = array();
        //14695513-cf0b-4286-a28b-cc019868f0b6
        foreach ($clans as &$clan) {
            $name = stringIsolateBetween($clan,"<span style=\"\"> "," </span> </a>");

            if($name != strip_tags($name)) {
                $name = stringIsolateBetween($clan,"style=\"\"> "," </span>");
            }

            if($name != strip_tags($name)) {
                $name = stringIsolateBetween($clan,"style=\"text-decoration: line-through;\">"," </span>");
            }

            $tag = stringIsolateBetween($name,"[","]");
            $name = str_replace(" ","",$name);
            $name = str_replace("[$tag]","",$name);

            if (strpos($other, $name) !== false) {
                $type = "winner";
            } else {
                $type = "loser";
            }

            $return[$type]['name'] = $name;
            $return[$type]['tag'] = $tag;
        }

        //Isolate MVP name
        $other = str_replace(stringIsolateBetween($other,"</h1>","<div>"),"",$other);
        $return['mvp'] = stringIsolateBetween($other,"playerName=","\" style=\"color:");

        //Isolate ELO
        $return['elo'] = stringIsolateBetween($html,"<td align=\"center\"><i class=\"fa fa-exchange\" aria-hidden=\"true\"></i></td>","<td align=\"center\"><i class=\"fa fa-key\" aria-hidden=\"true\"></i></td>");
        $return['elo'] = stringIsolateBetween($return['elo'],")</td> ", "</td> </tr>");
        $data = str_split($return['elo']);
        $res = "";
        foreach($data as &$char) {
            if (is_numeric($char)) {
                $res .= $char;
            }
        }
        $return['elo'] = $res;
        return $return;
    }

    //Get Players that participated in CW
    public static function fetchCwPlayers($cw_id) {
        //https://www.gommehd.net/clan-match/get-players?matchId=".$_GET['matchid']
        $url = "https://www.gommehd.net/clan-match/get-players?matchId=".$cw_id;
        $html = get_contents($url);
        $html = stringReplaceBreaks($html);

        if (strpos($html, "unerwarteter Fehler") !== false) {
            return false;
        }

        $part = explode("<tr",$html);

        $clans = array();
        $clans[0] = array();
        array_push($clans[0],$part[2],$part[3],$part[4],$part[5]);
        $clans[1] = array();
        array_push($clans[1],$part[6],$part[7],$part[8],$part[9]);

        $return = array();

        $bac_game = true;

        foreach ($clans as &$clan) {
            $status = "loser";
            $name = null;
            $first = true;
            $tmp = array();
            $tmp['lineup'] = array();
            foreach ($clan as &$member) {
                if (strpos($member, "winner") !== false) {
                    $status = "winner";
                }
                if ($first == true) {
                    $first = false;
                    $name = stringIsolateBetween($member,"\"font-size: 18px;\">","</strong></a>");

                    if($name != strip_tags($name)) {
                        $name = stringIsolateBetween($member,"text-decoration: line-through;\">","</strong> <td");
                    }
                }

                $user = array();
                if (strpos($member, "badlion") !== false) {
                    $user['bac'] = true;
                    $user['name'] = stringIsolateBetween($member,"</div> ","<a href=\"https://client");
                    $user['name'] = str_replace(" ","",$user['name']);
                } else {
                    $user['bac'] = false;
                    $bac_game = false;
                    $user['name'] = stringIsolateBetween($member,"</div> "," </td> <td>");
                    $user['name'] = str_replace(" ","",$user['name']);
                }
                array_push($tmp['lineup'],$user);
            }
            $tmp['name'] = $name;
            $return[$status] = $tmp;
        }
        $return['bac'] = $bac_game;

        return $return;
    }

    //Get CW History
    public static function fetchCwActions($cw_id) {
        $url = "https://www.gommehd.net/clan-match/get-actions?matchId=".$cw_id;
        $html = get_contents($url);
        $html = stringReplaceBreaks($html);

        $parts = explode("<tr",$html);
        $response = array();
        for ($i=3;$i<count($parts)-1;$i++) {
            $part = $parts[$i];
            $pieces = explode("</td>",$part);
            $tmp = array();
            $tmp['time'] = substr(stringIsolateBetween($pieces[0],"\">","</td>"),2);

            if (strpos($pieces[1], "fa-sign-out") !== false) {
                $tmp['action'] = "quit";
                $tmp['subject'] = stringIsolateBetween($pieces[2],";\">","</span>");
            } elseif (strpos($pieces[1], "fa-sign-in") !== false) {
                $tmp['action'] = "joined";
                $tmp['subject'] = stringIsolateBetween($pieces[2],";\">","</span>");
            } elseif (strpos($pieces[1], "fa-bed") !== false) {
                $tmp['action'] = "destroyed";
                $tmp['object'] = stringIsolateBetween($pieces[2],";\">","</span>");
                $pieces[2] = str_replace(";\">".$tmp['object']."<","",$pieces[2]);
                $tmp['subject'] = stringIsolateBetween($pieces[2],";\">","</span>");
            } elseif (strpos($pieces[1], "fa-heart") !== false) {
                if (strpos($pieces[2], "getötet") !== false) {
                    $tmp['object'] = stringIsolateBetween($pieces[2],";\">","</span>");
                    $pieces[2] = str_replace(";\">".$tmp['object']."<","",$pieces[2]);
                    $tmp['subject'] = stringIsolateBetween($pieces[2],";\">","</span>");
                    $tmp['action'] = "killed";
                } else {
                    $tmp['action'] = "died";
                    $tmp['subject'] = stringIsolateBetween($pieces[2],";\">","</span>");
                }
            }
            array_push($response,$tmp);


        }
        return $response;
    }

    //Get List of latest CWs
    public static function fetchLastCws($amount) {
        $url = "https://www.gommehd.net/clans/get-matches?game=bedwars&finished=true&amount=".$amount;
        $html = get_contents("https://jkamue.de/referrer.php?url=".$url);
        $html = stringReplaceBreaks($html);
        $cws = explode("</tr>",$html);
        $return = array();

        for ($i=0;$i<$amount;$i++) {
            $cw = $cws[$i];
            $tmp = array();

            //Isolate matchid
            $tmp['matchid'] = substr(stringIsolateBetween($cw, "<tr id=\"", "\" style=\"display: none;\">"),6);

            //Get winner and Looser
            $raw = stringIsolateBetween($cw,"<div class=\"result\">","</div>");
            $cw = str_replace($raw,"",$cw);
            $clans = explode("</span>",$raw);

            foreach ($clans as &$clan) {
                $name = stringIsolateBetween($clan,"\">","</a>");
                if (strpos($clan, "lose") !== false) {
                    $tmp['loser'] = $name;
                } else {
                    $tmp['winner'] = $name;
                }
            }

            //Get datetime
            $date = stringIsolateBetween($cw,"date\">","</span> <span");
            $time = stringIsolateBetween($cw,"time\">","</span> </td>");
            $tmp['datetime'] = $date." ".$time;

            //Get Map
            $cw = str_replace($date."</span","",$cw);
            $cw = str_replace($time."</span","",$cw);
            $tmp['map'] = stringIsolateBetween($cw,"<span>","</span> </td>");

            //Get length
            $cw = str_replace("<span>".$tmp['map']."</span> </td>","",$cw);
            $tmp['duration'] = stringIsolateBetween($cw,"<span>","</span> </td>");
            array_push($return,$tmp);
        }

        return $return;
    }
}