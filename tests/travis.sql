
# Create DB
CREATE DATABASE IF NOT EXISTS 'test_zim' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE 'travis';
--
-- Table structure for table 'suki_c_allow'
--

CREATE TABLE 'suki_c_allow' (
  'allowID' int(10) UNSIGNED NOT NULL,
  'name' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'groups' text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_board'
--

CREATE TABLE 'suki_c_board' (
  'boardID' int(10) UNSIGNED NOT NULL,
  'title' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'description' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'url' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'icon' varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  'color' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_cron'
--

CREATE TABLE 'suki_c_cron' (
  'cronID' int(10) UNSIGNED NOT NULL,
  'title' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'itemLimit' int(10) NOT NULL,
  'itemCount' int(10) NOT NULL,
  'keywords' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'hash' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'boardID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'topicID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'userID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'userName' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'enabled' int(1) UNSIGNED NOT NULL DEFAULT '0',
  'url' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'tags' varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  'footer' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'topicPrefix' varchar(255) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_message'
--

CREATE TABLE 'suki_c_message' (
  'msgID' int(10) UNSIGNED NOT NULL,
  'msgTime' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'msgModified' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'reason' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'reasonBy' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'boardID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'topicID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'approved' int(1) UNSIGNED NOT NULL DEFAULT '0',
  'userID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'userName' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'userEmail' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'userIP' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'title' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'body' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'tags' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'url' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_remember'
--

CREATE TABLE 'suki_c_remember' (
  'userID' int(11) NOT NULL,
  'token' varchar(255) NOT NULL,
  'expires' int(11) NOT NULL,
  'selector' text CHARACTER SET utf8mb4 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_ses'
--

CREATE TABLE 'suki_c_ses' (
  'session_id' varchar(255) NOT NULL DEFAULT '',
  'data' text,
  'ip' varchar(45) DEFAULT NULL,
  'agent' varchar(300) DEFAULT NULL,
  'stamp' int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_topic'
--

CREATE TABLE 'suki_c_topic' (
  'topicID' int(10) UNSIGNED NOT NULL,
  'fmsgID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'lmsgID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'boardID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'numReplies' int(10) NOT NULL DEFAULT '1',
  'solved' int(1) UNSIGNED NOT NULL DEFAULT '0',
  'locked' int(1) NOT NULL DEFAULT '0',
  'sticky' int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'suki_c_user'
--

CREATE TABLE 'suki_c_user' (
  'userID' int(10) UNSIGNED NOT NULL,
  'userName' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'userEmail' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'userIP' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'title' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'registered' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'posts' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'groupID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'groups' text COLLATE utf8mb4_unicode_ci NOT NULL,
  'lastLogin' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'avatar' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'avatarType' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'webUrl' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'webSite' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'passwd' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'passwdSalt' varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  'lmsgID' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'is_active' int(10) UNSIGNED NOT NULL DEFAULT '0',
  'last_active' int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table 'suki_c_allow'
--
ALTER TABLE 'suki_c_allow'
  ADD PRIMARY KEY ('allowID'),
  ADD UNIQUE KEY 'allowID' ('allowID');

--
-- Indexes for table 'suki_c_board'
--
ALTER TABLE 'suki_c_board'
  ADD PRIMARY KEY ('boardID'),
  ADD UNIQUE KEY 'boardID' ('boardID');

--
-- Indexes for table 'suki_c_cron'
--
ALTER TABLE 'suki_c_cron'
  ADD PRIMARY KEY ('cronID'),
  ADD UNIQUE KEY 'cronID' ('cronID');

--
-- Indexes for table 'suki_c_message'
--
ALTER TABLE 'suki_c_message'
  ADD UNIQUE KEY 'msgID' ('msgID'),
  ADD KEY 'topicID' ('topicID'),
  ADD KEY 'boardID' ('boardID');

--
-- Indexes for table 'suki_c_ses'
--
ALTER TABLE 'suki_c_ses'
  ADD PRIMARY KEY ('session_id');

--
-- Indexes for table 'suki_c_topic'
--
ALTER TABLE 'suki_c_topic'
  ADD UNIQUE KEY 'topicID' ('topicID'),
  ADD KEY 'fmsgID' ('fmsgID'),
  ADD KEY 'lmsgID' ('lmsgID');

--
-- Indexes for table 'suki_c_user'
--
ALTER TABLE 'suki_c_user'
  ADD PRIMARY KEY ('userID'),
  ADD UNIQUE KEY 'userID' ('userID');

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table 'suki_c_allow'
--
ALTER TABLE 'suki_c_allow'
  MODIFY 'allowID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table 'suki_c_board'
--
ALTER TABLE 'suki_c_board'
  MODIFY 'boardID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table 'suki_c_cron'
--
ALTER TABLE 'suki_c_cron'
  MODIFY 'cronID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table 'suki_c_message'
--
ALTER TABLE 'suki_c_message'
  MODIFY 'msgID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5207;
--
-- AUTO_INCREMENT for table 'suki_c_topic'
--
ALTER TABLE 'suki_c_topic'
  MODIFY 'topicID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=732;
--
-- AUTO_INCREMENT for table 'suki_c_user'
--
ALTER TABLE 'suki_c_user'
  MODIFY 'userID' int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
