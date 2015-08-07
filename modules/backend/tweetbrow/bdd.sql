-- phpMyAdmin SQL Dump
-- version 4.4.9
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Mar 23 Juin 2015 à 09:27
-- Version du serveur :  5.5.42
-- Version de PHP :  5.5.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `lyber`
--

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE IF NOT EXISTS `favoris` (
  `id_tweet` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `favoris`
--

INSERT INTO `favoris` (`id_tweet`, `id_user`, `date_create`) VALUES
(22, 1, '2015-05-21 07:29:06'),
(31, 16, '2015-05-22 09:39:55'),
(40, 16, '2015-05-22 09:40:55');

-- --------------------------------------------------------

--
-- Structure de la table `follows`
--

CREATE TABLE IF NOT EXISTS `follows` (
  `id_follower` int(11) NOT NULL,
  `id_following` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `follows`
--

INSERT INTO `follows` (`id_follower`, `id_following`, `date_create`) VALUES
(1, 2, '2015-05-21 07:15:16'),
(1, 9, '2015-05-21 07:34:55'),
(1, 11, '2015-05-21 07:34:56'),
(1, 14, '2015-05-21 07:34:57'),
(14, 1, '2015-05-20 18:49:24'),
(15, 1, '2015-05-22 09:37:24'),
(16, 1, '2015-05-22 09:39:38'),
(16, 2, '2015-05-22 09:39:39'),
(16, 15, '2015-05-22 09:38:19'),
(17, 2, '2015-05-22 09:54:01');

-- --------------------------------------------------------

--
-- Structure de la table `retweets`
--

CREATE TABLE IF NOT EXISTS `retweets` (
  `id_tweet` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `retweets`
--

INSERT INTO `retweets` (`id_tweet`, `id_user`, `date_create`) VALUES
(3, 1, '2015-05-19 08:56:07'),
(4, 1, '2015-05-21 07:27:18'),
(20, 1, '2015-05-21 07:33:12'),
(22, 1, '2015-05-21 07:28:57'),
(25, 1, '2015-05-21 11:36:57'),
(40, 16, '2015-05-22 09:40:50');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `user` int(11) NOT NULL,
  `token` varchar(20) NOT NULL,
  `ip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `sessions`
--

INSERT INTO `sessions` (`user`, `token`, `ip`) VALUES
(17, '356435321567ae11fc0b', '172.31.1.144'),
(9, '9b20e98328e8033fc64a', '172.31.1.120');

-- --------------------------------------------------------

--
-- Structure de la table `tweets`
--

CREATE TABLE IF NOT EXISTS `tweets` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `message` varchar(140) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `response` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

--
-- Contenu de la table `tweets`
--

INSERT INTO `tweets` (`id`, `author`, `message`, `date_create`, `response`) VALUES
(3, 1, 'First tweet !', '2015-05-18 13:54:41', NULL),
(4, 2, 'Tweet de lucas brow !', '2015-05-18 22:00:00', NULL),
(20, 1, 'Nouveau Tweet', '2015-05-20 14:14:50', NULL),
(21, 1, 'Test TweetBrow', '2015-05-20 14:15:27', NULL),
(22, 1, '@loriszv Matthieu chante chante et chante !', '2015-05-20 14:29:15', NULL),
(25, 1, '@loriszv Cool ton tweet !', '2015-05-20 14:42:48', 3),
(27, 1, '@lucas Yo brow', '2015-05-20 14:51:45', 3),
(31, 1, '@loriszv Salut mon collègue ..', '2015-05-20 19:24:30', NULL),
(34, 1, '@loriszv Hello world!', '2015-05-21 07:07:12', 31),
(35, 1, '@loriszv yess !', '2015-05-21 07:29:22', 22),
(38, 16, '@loriszv toto', '2015-05-22 09:40:01', 31),
(40, 16, '@babar test', '2015-05-22 09:40:38', 38),
(43, 16, '@babar jgkvkcjc', '2015-05-22 09:42:02', 40),
(47, 16, '@babar jfkvkvm', '2015-05-22 09:51:58', 43);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pseudo` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `login`, `email`, `password`, `date_create`, `pseudo`) VALUES
(1, 'loriszv', 'venturelli.loris@gmail.com', '098f6bcd4621d373cade4e832627b4f6', '2015-05-18 13:47:15', 'Lorisssss'),
(2, 'lucas', 'lucas@lucas.fr', '098f6bcd4621d373cade4e832627b4f6', '2015-05-19 07:06:44', 'Browwww'),
(9, 'luca', 'qsd', '511e33b4b0fe4bf75aa3bbac63311e5a', '2015-05-20 09:22:59', 'Luciluc <3'),
(11, 'loriszvs', 'qsd@qsd.fr', '511e33b4b0fe4bf75aa3bbac63311e5a', '2015-05-20 09:25:38', 'Love you'),
(14, 'loris', 'vl@lunqds.fr', '098f6bcd4621d373cade4e832627b4f6', '2015-05-20 18:43:49', 'LorisVenturelli'),
(15, 'pierrd', 'pierre@test.com', '84675f2baf7140037b8f5afe54eef841', '2015-05-22 09:36:20', 'pierre'),
(16, 'babar', 'babar@toto.com', '56f46611dfa80d0eead602cbb3f6dcee', '2015-05-22 09:38:12', 'babar'),
(17, 'lulu', 'lulu@test.com', '654e4dc5b90b7478671fe6448cab3f32', '2015-05-22 09:49:04', 'lulu');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id_tweet`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id_follower`,`id_following`),
  ADD KEY `id_following` (`id_following`);

--
-- Index pour la table `retweets`
--
ALTER TABLE `retweets`
  ADD PRIMARY KEY (`id_tweet`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`token`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `user_2` (`user`),
  ADD KEY `user` (`user`);

--
-- Index pour la table `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author`),
  ADD KEY `author_2` (`author`),
  ADD KEY `response` (`response`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `tweets`
--
ALTER TABLE `tweets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=48;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`id_tweet`) REFERENCES `tweets` (`id`),
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`id_follower`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`id_following`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `retweets`
--
ALTER TABLE `retweets`
  ADD CONSTRAINT `retweets_ibfk_1` FOREIGN KEY (`id_tweet`) REFERENCES `tweets` (`id`),
  ADD CONSTRAINT `retweets_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `tweets`
--
ALTER TABLE `tweets`
  ADD CONSTRAINT `tweets_ibfk_1` FOREIGN KEY (`author`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tweets_ibfk_2` FOREIGN KEY (`response`) REFERENCES `tweets` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
