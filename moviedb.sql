--
-- Database: `moviedb`
--

-- --------------------------------------------------------

--
-- Structure of the table `movie`
--

CREATE TABLE IF NOT EXISTS `movie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `director` varchar(128) NOT NULL,
  `year` year(4) NOT NULL,
  `title` varchar(128) NOT NULL,
  `pick` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=425 ;