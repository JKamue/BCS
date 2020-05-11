-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Mai 2020 um 22:57
-- Server-Version: 10.1.22-MariaDB
-- PHP-Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `bcs`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `applications`
--

CREATE TABLE `applications` (
  `id` varchar(37) NOT NULL,
  `added` date NOT NULL,
  `done` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `clan`
--

CREATE TABLE `clan` (
  `ClanUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanTag` varchar(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanName` varchar(17) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastActive` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastMatch` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `enemy`
--

CREATE TABLE `enemy` (
  `EnemyUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanTag` varchar(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanName` varchar(17) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `game`
--

CREATE TABLE `game` (
  `GameID` varchar(33) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Win` tinyint(1) NOT NULL,
  `Elo` tinyint(4) NOT NULL,
  `GameTime` smallint(6) NOT NULL,
  `BACGame` tinyint(1) NOT NULL,
  `MapID` tinyint(4) NOT NULL,
  `LineupID` varchar(33) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `EnemyUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `MatchID` varchar(37) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lineup`
--

CREATE TABLE `lineup` (
  `LineupID` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ClanUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Player1UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Player2UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Player3UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Player4UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `map`
--

CREATE TABLE `map` (
  `MapID` tinyint(4) NOT NULL,
  `MapName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `member`
--

CREATE TABLE `member` (
  `PlayerID` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `MVP` smallint(6) NOT NULL,
  `Betten` smallint(6) NOT NULL,
  `Kills` smallint(6) NOT NULL,
  `Killed` smallint(6) NOT NULL,
  `Quits` smallint(6) NOT NULL,
  `Died` smallint(6) NOT NULL,
  `ClanUUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `BAC` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `player`
--

CREATE TABLE `player` (
  `UUID` varchar(37) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(17) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `support`
--

CREATE TABLE `support` (
  `ID` varchar(14) NOT NULL,
  `Header` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Status` tinyint(4) NOT NULL,
  `Magnitude` tinyint(4) NOT NULL,
  `Kontakt` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `team`
--

CREATE TABLE `team` (
  `name` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `applications`
--
ALTER TABLE `applications`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `clan`
--
ALTER TABLE `clan`
  ADD PRIMARY KEY (`ClanUUID`);

--
-- Indizes für die Tabelle `enemy`
--
ALTER TABLE `enemy`
  ADD PRIMARY KEY (`EnemyUUID`);

--
-- Indizes für die Tabelle `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`GameID`),
  ADD KEY `EnemyUUID` (`EnemyUUID`),
  ADD KEY `LineupID` (`LineupID`),
  ADD KEY `MapID` (`MapID`),
  ADD KEY `MapID_2` (`MapID`),
  ADD KEY `ClanUUID` (`ClanUUID`);

--
-- Indizes für die Tabelle `lineup`
--
ALTER TABLE `lineup`
  ADD PRIMARY KEY (`LineupID`),
  ADD KEY `Player1UUID` (`Player1UUID`),
  ADD KEY `ClanUUID` (`ClanUUID`),
  ADD KEY `Player2UUID` (`Player2UUID`),
  ADD KEY `Player3UUID` (`Player3UUID`),
  ADD KEY `Player4UUID` (`Player4UUID`);

--
-- Indizes für die Tabelle `map`
--
ALTER TABLE `map`
  ADD PRIMARY KEY (`MapID`);

--
-- Indizes für die Tabelle `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`PlayerID`),
  ADD KEY `ClanUUID` (`ClanUUID`),
  ADD KEY `UUID` (`UUID`);

--
-- Indizes für die Tabelle `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`UUID`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `game`
--
ALTER TABLE `game`
  ADD CONSTRAINT `game_ibfk_1` FOREIGN KEY (`MapID`) REFERENCES `map` (`MapID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `game_ibfk_2` FOREIGN KEY (`EnemyUUID`) REFERENCES `enemy` (`EnemyUUID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `game_ibfk_3` FOREIGN KEY (`LineupID`) REFERENCES `lineup` (`LineupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `game_ibfk_4` FOREIGN KEY (`ClanUUID`) REFERENCES `clan` (`ClanUUID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `lineup`
--
ALTER TABLE `lineup`
  ADD CONSTRAINT `lineup_ibfk_1` FOREIGN KEY (`ClanUUID`) REFERENCES `clan` (`ClanUUID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineup_ibfk_2` FOREIGN KEY (`Player1UUID`) REFERENCES `member` (`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineup_ibfk_3` FOREIGN KEY (`Player2UUID`) REFERENCES `member` (`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineup_ibfk_4` FOREIGN KEY (`Player3UUID`) REFERENCES `member` (`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineup_ibfk_5` FOREIGN KEY (`Player4UUID`) REFERENCES `member` (`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`ClanUUID`) REFERENCES `clan` (`ClanUUID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `member_ibfk_2` FOREIGN KEY (`UUID`) REFERENCES `player` (`UUID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
