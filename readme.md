
<h1 align="center">
  <br>
  <a href="https://cwstats.de"><img src="http://cwstats.de/img/logo_leather_small.png" alt="Cwstats Logo" width="400"></a>
</h1>

<h4 align="center">GommeHDnet bedwars stats tracker</h4>


<p align="center">
  <a href="#about">About</a> •
  <a href="#key-features">Key Features</a> •
  <a href="#apis">Apis</a>
</p>

<h1 align="center">
<img src="http://cwstats.de/img/cwstats-demo.png" width="1000">
</h1>

## About

Sadly GommeHDnet never wrote a nice page displaying various clan war stats even though they should have the data to do this easily.
Since they don't have an API and did not want to provide me with one I had to write an own API parsing their html.

The system is old, I would have done a lot different nowadays. Nonetheless **525 clans** with **roughly 3000 players** signed up.
On launch day BCS had up to **400 users** but it quickly dropped to **~80 daily active users** which makes it my most used project so far.

Due to a bad database layout as well as bad queries loading a clan now takes very long. I also <a href="https://twitter.com/BetterCWBWStats/status/1259823049903407106">dropped developement of BCS</a> in May 2020.
I am planning to let it run unsupervised until August of 2021.

  
## Key Features

The idea of BCS was to give the user as much information as possible and the demand shows that it succeeded.
BCS displays a variety of different stats including:

* Clanstats - detailed information for one (registered) clan
  * Overall
    * How many games did they play?
    * Whats their winrate?
    * Total playtime, last activity
  * Enemies - How often did a clan face an enemy? How often did they win and loose?
  * Map stats - What's the winrate on each map and how often did they play on it?
  * Member stats - How did each member perform? What's their KD? How many beds did they destroy?
  * Lineup stats - Shows which groups of 4 players perform the best
* Playerstats - detailed information on a single player
  * Resume - Which clans did they join in the past? How did the player perform there?
  * Current stats
    * Winrate, KD, played games
    * Current ranking relative to all other BCS players
  

## Apis

GommeHDnet never supported BCS - at least they tolerated it but this still meant that I had to find out how their system works in order to find enpoints I could parse to get useful information. BCS uses two different APIs. The first API is the one parsing information from GommeHDnet. This API has its own repo and can be found <a href="https://github.com/JKamue/GommeApi">here</a>
 
The second API is used to query the stored and proccessed data from the BCS backend. 
I encourage everybody to use it since it sends some great information taht could also be used in 3rd party software like plugins.
Here is a short list of each endpoint:
* Clanstats: http://cwstats.de/api/api.php?clanname=CowBuilders&type=clan
* Memberstats (long loading time): http://cwstats.de/api/api.php?clanname=CowBuilders&type=member
* Mapstats: http://cwstats.de/api/api.php?clanname=CowBuilders&type=map
* Lineupstats: http://cwstats.de/api/api.php?clanname=CowBuilders&type=lineup
* EnemyStats: http://cwstats.de/api/api.php?clanname=CowBuilders&type=enemy
* All above (long loading time): http://cwstats.de/api/api.php?clanname=%7Bclanname%7D
* Overall stats of the system itself http://cwstats.de/api/api.php?bcsstats
* Search clans and players with a name http://cwstats.de/api/api.php?search=word
* Member stats http://cwstats.de/api/api.php?member=memberId
* Member stats by name http://cwstats.de/api/api.php?player=name
* Basic stats of all clans http://cwstats.de/api/api.php?getAllClans
* Get the current ranking http://cwstats.de/api/api.php?getRanking
<br>

---
> [cwstats.de](https://www.cwstats.de) &nbsp;&middot;&nbsp;
> Twitter [@JKamue_dev](https://twitter.com/JKamue_dev) &nbsp;&middot;&nbsp;
> Twitter [@BetterCWBWStats](https://twitter.com/BetterCWBWStats)
