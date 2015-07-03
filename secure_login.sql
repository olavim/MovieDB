--
-- Database: `secure_login`
--

-- --------------------------------------------------------

--
-- Structure of the table `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `user_id` int(11) NOT NULL,
  `time` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure of the table `members`
--

CREATE TABLE IF NOT EXISTS `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` char(128) NOT NULL,
  `salt` char(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Example user `long`, whose password is `johnson`.
-- The PHP script which generated the password:
-- 
-- hash('sha512', "johnson" . "72c494e1da497d8847a11be976c7cd1a2f3d846e9384685c6207820a1d9cdf17ec3db7cdddb1df4a6ec5a5101d97d25b78f6d63b8db328854b745aaaed761e60");
--
-- The `salt` bit of the password is merely a randomly generated sha512 hash.
--

INSERT INTO `members` (`id`, `username`, `email`, `password`, `salt`) VALUES
(1, 'long', 'long@longmail.com', '260ee2ca75b00ef2520e7e83c4154bfc14e1ffecf450e055f874d8e760d2d880e9324bfd45b167ce2335ffa06e5c054fe6926716fa0744bcb792a642288e4ad3', '72c494e1da497d8847a11be976c7cd1a2f3d846e9384685c6207820a1d9cdf17ec3db7cdddb1df4a6ec5a5101d97d25b78f6d63b8db328854b745aaaed761e60');
