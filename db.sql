-- phpMyAdmin SQL Dump
-- version 3.4.3deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Dim 01 Janvier 2012 à 23:27
-- Version du serveur: 5.1.57
-- Version de PHP: 5.3.8-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `ponywalls`
--

-- --------------------------------------------------------

--
-- Structure de la table `keywords`
--

CREATE TABLE IF NOT EXISTS `keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=99 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Structure de la table `walls`
--

CREATE TABLE IF NOT EXISTS `walls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size` varchar(25) DEFAULT NULL,
  `rating` char(1) DEFAULT NULL,
  `poster` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `orig_filename` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- Structure de la table `wall_keywords`
--

CREATE TABLE IF NOT EXISTS `wall_keywords` (
  `idWall` int(11) NOT NULL,
  `idKeyword` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
