<?php

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

        $parts = explode("<h3 class=\"panel-title\">",$html);

        $return = array();

        //Loop through each part and get the player name from the Link (/player/index?playerName=[name]"> <div class="media-object)
        for ($i=1;$i<4;$i++) {
            $tmp = array();

            if (isset($parts[$i])) {
                $tmp = array();
                $parts_sub = explode("<div class=\"media\">", $parts[$i]);

                $type = $parts_sub[0];
                array_splice($parts_sub, 0, 1);

                foreach ($parts_sub as &$part) {
                    $player = stringIsolateBetween($part, "playerName=", "\"> <div class=\"media-object");
                    if ($player != " " and $player !== null) {
                        array_push($tmp, $player);
                    }
                }

                if (strpos($type, "Clan Leader") !== false) {
                    $return['leader'] = $tmp;
                } else  if (strpos($type, "Clan Mods") !== false) {
                    $return['mods'] = $tmp;
                } else {
                    $return['member'] = $tmp;
                }
            }
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

        for ($i=0;$i<count($cws)-1;$i++) {
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
    //Spieltyp, Map, Gewinner Clan, Verlierer Clan, MVP, Spielstart, Spieldauer, ELO, ID, ReplayID
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
        $tmp = array();
        $return['matchid'] = $cw_id;
        $return['winner'] = array();
        $return['loser'] = array();

        for($i = 0; $i < 2; $i++) {
            $clan = $clans[$i];
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


            $tmp[$i]['name'] = $name;
            $tmp[$i]['tag'] = $tag;
        }

        //Isolate MVP name
        $other = str_replace(stringIsolateBetween($other,"</h1>","<div>"),"",$other);
        $return['mvp'] = stringIsolateBetween($other,"playerName=","\" style=\"color:");

        //Detect winner
        $piece = explode("<td>Gewinner-Clan</td>",$html)[1];
        if (strpos($piece, $tmp[0]['name']) !== false) {
            // Clan 1 won
            $return['winner'] = $tmp[0];
            $return['loser'] = $tmp[1];
        } else {
            // Clan 2 won
            $return['winner'] = $tmp[1];
            $return['loser'] = $tmp[0];
        }


        $table = stringIsolateBetween($other,"<table class=\"table mapInf\">","</a> </td> </tr> </table>");
        $parts = explode("<tr>",$table);

        $return['datetime'] = stringIsolateBetween($parts[1],"Spielstart</td> <td>","</td> </tr>");
        $return['duration'] = stringIsolateBetween($parts[2],"Dauer</td> <td>","</td> </tr>");
        $return['elo'] = stringIsolateBetween($parts[3],"verloren)</td> <td>","</td> </tr>");
        $return['replay'] = stringIsolateBetween($parts[5],"Replay-ID</td> <td> "," </td> </tr>");

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
            $push = true;

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
            } elseif (strpos($pieces[1], "fa fa-clock-o") !== false) {
                $push = false;
            }

            if ($push) {
                array_push($response,$tmp);
            }

        }
        return $response;
    }

    //Get List of latest CWs
    public static function fetchLastCws($amount) {
        $url = "https://www.gommehd.net/clans/get-matches?game=bedwars&finished=true&amount=".$amount;
        $html = get_contents($url);
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