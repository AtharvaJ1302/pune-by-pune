-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 14, 2025 at 10:44 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `community`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '123');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities` (
  `city_id` int NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  `state_id` int DEFAULT NULL,
  PRIMARY KEY (`city_id`),
  KEY `state_id` (`state_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`city_id`, `city_name`, `state_id`) VALUES
(1, 'Mumbai', 1),
(2, 'Pune', 1),
(3, 'Nagpur', 1),
(4, 'Bengaluru', 2),
(5, 'Mysuru', 2),
(6, 'Hubli', 2),
(7, 'Chennai', 3),
(8, 'Coimbatore', 3),
(9, 'Madurai', 3),
(10, 'Kolkata', 4),
(11, 'Howrah', 4),
(12, 'Siliguri', 4);

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

DROP TABLE IF EXISTS `communities`;
CREATE TABLE IF NOT EXISTS `communities` (
  `community_id` int NOT NULL AUTO_INCREMENT,
  `community_name` varchar(255) NOT NULL,
  `community_description` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `admin` tinyint(1) DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `organized_by` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `location` varchar(255) DEFAULT NULL,
  `pincode_id` int DEFAULT NULL,
  PRIMARY KEY (`community_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`community_id`, `community_name`, `community_description`, `image_path`, `created_at`, `admin`, `user_id`, `organized_by`, `status`, `location`, `pincode_id`) VALUES
(3, 'PyData Pune', 'Hello EveryOne,\r\nWelcome to PyData Pune!\r\nPyData is all about fostering data science and open source. We believe in \"Open Code = Better Science\". There are 100+ PyData chapters across the globe and some major Pydata conferences are PyData Berlin, London, or Amsterdam. Details are here: [https://pydata.org](https://pydata.org)\r\nPyData is an educational program of NumFOCUS, a 501(c)3 non-profit organization in the United States. PyData provides a forum for the international community of users and developers of data analysis tools to share ideas and learn from each other. The global PyData network promotes discussion of best practices, new approaches, and emerging technologies for data management, processing, analytics, and visualization. PyData communities approach data science using many languages, including (but not limited to) Python, Julia, and R.\r\nThe PyData Code of Conduct ( [https://pydata.org/code-of-conduct.html](https://pydata.org/code-of-conduct.html) ) governs this meetup. To discuss any issues or concerns relating to the code of conduct or the behavior of anyone at a PyData meetup, don\'t hesitate to get in touch with NumFOCUS Executive Director Leah Silen (+1 512-222-5449; leah@numfocus.org ) or the chapter organizer, Mayank Mishra (pydata.pune@gmail.com)\r\nWe feature local as well as international speakers at our meetups. Beginners or experts - everyone is welcome at PyData.\r\nPlease contribute to our community by sharing your wisdom and expertise, even if it’s just a 5-minute lightning talk. Don’t worry if you are a first-time speaker - experienced speakers are on the team and happy to coach you. Contact us. (pydata.pune@gmail.com)\r\nWe are looking forward to seeing you at our next meetup. Please respect that our community forums may not be used for advertising. Please feel free to ping us if you have any questions or suggestions. Call for Action: follow up on Twitter.\r\nJoin Telegram PyData Group : https://t.me/joinchat/Ffj-IBGHogMHmHUQIzN6_...\r\nJoin Discord PyData Group: [https://discord.gg/8eBJS3](https://discord.gg/8eBJS3)\r\nTwitter handle: [https://twitter.com/PydataPune](https://twitter.com/PydataPune)\r\nFacebook Page: [https://www.facebook.com/PyDataPune/](https://www.facebook.com/PyDataPune/)\r\nGithub Repo: [https://github.com/PyDataPune](https://github.com/PyDataPune)\r\nSee you soon\r\nTeam PyData Pune', 'uploads/python.jpg', '2025-01-26 15:12:13', 1, 11, 'Atharva', 1, 'Pune, Maharashtra', 4),
(4, 'JavaSphere', 'A community associated with the Java language is a collective group of developers, learners, and enthusiasts who come together to share knowledge, solve problems, and foster growth in the world of Java programming. This community provides a platform for members to ask questions, contribute to open-source projects, and discuss the latest trends and updates in Java development. It promotes collaboration and learning through events, workshops, online forums, and mentorship. By leveraging the power of networking, this community ensures that individuals at all skill levels can grow their expertise in Java.\r\nJavaSphere represents a global ecosystem where Java developers and enthusiasts can interact, exchange ideas, and stay updated on the latest innovations in the language, from core features to advanced frameworks.', 'uploads/java_community.png', '2025-01-27 03:26:23', 1, 11, 'JavaSphere', 1, 'Mumbai, Maharashtra', 4),
(5, 'ReactVerse', 'The React community is a dynamic and passionate group of developers, designers, and enthusiasts who work together to learn, build, and advance the React ecosystem. React, a popular JavaScript library for building user interfaces, has fostered a thriving community that encourages sharing best practices, contributing to open-source projects, and collaborating on a wide range of React-related topics. Members of this community regularly engage in discussions around performance optimization, component libraries, state management, and the latest updates in React and related tools. The community is known for hosting events like React conferences, meetups, and online forums, making it easy for developers to connect and support one another.\r\n\r\nReactVerse symbolizes the expansive universe of React, where developers and enthusiasts come together to explore new possibilities, push the boundaries of UI development, and support each other in mastering React.', 'uploads/react native.jpg', '2025-01-27 03:28:19', 1, 11, 'ReactVerse', 1, 'Bangalore, Karnataka', 4),
(6, 'PHP Connect', 'PHP Connect is a dynamic community dedicated to bringing together PHP enthusiasts, developers, and learners through engaging events and activities. This vibrant group focuses on enhancing skills and fostering collaboration by organizing regular workshops that cater to all levels, from beginners to advanced programmers. Members can participate in hackathons, where they work on real-world projects in a collaborative setting, promoting teamwork and innovation. Networking opportunities are a key feature, allowing participants to connect with industry professionals, share experiences, and explore potential collaborations. Additionally, PHP Connect hosts guest speakers and panel discussions featuring experienced developers who provide insights into the latest trends and best practices in PHP development. With an event management system to streamline organization and communication, along with a community forum for discussions and resource sharing, PHP Connect aims to create a supportive environment that encourages learning and growth within the PHP community.', 'uploads/php.avif', '2025-01-27 04:21:49', 1, 11, 'PHP Connect', 1, 'Chennai, Tamil Nadu', 4),
(7, 'MySQL Enthusiasts Network', 'The MySQL Enthusiasts Network is a vibrant community dedicated to fostering knowledge and collaboration among MySQL users, developers, and database administrators. This group organizes regular events, workshops, and meetups that cover a wide range of topics, from database optimization and performance tuning to the latest advancements in MySQL technology. Members benefit from hands-on sessions led by industry experts, networking opportunities, and the chance to share their experiences and challenges. The community aims to create an inclusive environment where both newcomers and seasoned professionals can enhance their skills and contribute to the growth of the MySQL ecosystem. Whether through local gatherings or larger conferences like the MySQL Belgian Days, the network serves as a hub for learning and innovation in the world of database management.', 'uploads/mysql.png', '2025-01-27 04:25:10', 1, 12, 'MySQL Enthusiasts Network', 1, 'Guwahati, Assam', 2),
(8, 'MERN Mavericks', 'MERN Mavericks is a vibrant and dynamic community dedicated to enthusiasts and professionals who are passionate about the MERN stack—MongoDB, Express.js, React, and Node.js. Our community hosts a variety of engaging events, including workshops, hackathons, and meetups, designed to foster collaboration, skill development, and innovation. Whether you are a beginner eager to learn the fundamentals or an experienced developer looking to share your knowledge and network with like-minded individuals, MERN Mavericks provides a supportive environment for all. Join us to explore the latest trends in web development, collaborate on exciting projects, and connect with industry experts who can help you elevate your skills in this powerful technology stack.', 'uploads/mern.png', '2025-01-27 04:26:14', 1, 12, 'MERN Mavericks', 1, 'Jaipur, Rajasthan', 2),
(9, 'CS Connect: Masters Network', 'The Masters in Computer Science Community is a vibrant network dedicated to fostering collaboration, knowledge sharing, and professional development among students and alumni pursuing or holding a Master’s degree in Computer Science. This community organizes a variety of events, including workshops, guest lectures, hackathons, and networking sessions, aimed at enhancing technical skills, exploring emerging technologies, and connecting members with industry leaders. By creating an inclusive environment that encourages innovation and mentorship, the community seeks to empower its members to thrive in their academic and professional journeys.', 'uploads/mcs.jpg', '2025-01-27 04:31:22', 1, 12, 'CS Connect: Masters Network', 1, 'Tirupati, Andhra Pradesh', 2);

-- --------------------------------------------------------

--
-- Table structure for table `community_members`
--

DROP TABLE IF EXISTS `community_members`;
CREATE TABLE IF NOT EXISTS `community_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `community_id` int NOT NULL,
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`community_id`),
  KEY `fk_community_id` (`community_id`),
  KEY `fk_member` (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `community_members`
--

INSERT INTO `community_members` (`id`, `user_id`, `community_id`, `joined_at`, `role_id`) VALUES
(2, 11, 7, '2025-02-09 14:56:45', 2);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `community_id` int NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_description` text,
  `event_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(255) DEFAULT NULL,
  `state_id` int NOT NULL,
  `city_id` int NOT NULL,
  `pincode_id` int NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  PRIMARY KEY (`event_id`),
  KEY `community_id` (`community_id`),
  KEY `fk_state` (`state_id`),
  KEY `fk_city` (`city_id`),
  KEY `fk_pincode` (`pincode_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `community_id`, `event_name`, `event_description`, `event_time`, `created_at`, `location`, `state_id`, `city_id`, `pincode_id`, `status`) VALUES
(5, 4, 'Java meetup', '<h2>Details</h2>\r\n\r\n<p>In collaboration with Nelkinda Software Craft and Equal Experts:</p>\r\n\r\n<p>This meetup is a combination of two talks<br />\r\n<em><strong>1. Why Learn Functional Programming by Anshul Chauhan.</strong></em><br />\r\nDuring this talk, we will learn about the Functional Programming paradigm. Anshul Chauhan will guide us through the basics and key vocabulary of functional programming. Through concrete examples, we will observe how Functional Programming can offer a compelling alternative to Object-Oriented Programming (OOP), potentially enhancing our daily coding tasks.</p>\r\n\r\n<p><em><strong>2. Singleton Paradox: Revisiting Advanced Java Through a Practical Lens by Nikhil Wanpal.</strong></em><br />\r\nSingleton design pattern ensures that only one object of a class exists at runtime. Is it really possible in Java though? During the course of this discussion, we attempt to implement a Singleton in Java and revisit aspects of Java like classes, static state, synchronisation, serialisation and class loading.</p>\r\n\r\n<p><strong>Agenda</strong></p>\r\n\r\n<ul>\r\n	<li>10:00 am - 10:15 am &ndash; Introduction</li>\r\n	<li>10:15 am - 11:00 am &ndash; Talk1: Why Learn Fucntional Programming.</li>\r\n	<li>11:00 am - 11:15 am &ndash; Q &amp; A session + Break 1</li>\r\n	<li>11:15 am - 12:25 pm &ndash; Talk 2: Singleton Paradox: Revisiting Advanced Java Through a Practical Lens.</li>\r\n	<li>12:25 pm - 01:30 pm &ndash; Talk 2 + Q&amp;A</li>\r\n	<li>01:30 pm - 02:00 pm &ndash; Networking</li>\r\n</ul>\r\n\r\n<p>(collaborating meetup groups: Nelkinda Software Craft Pune Meetup, Expert Talks)<br />\r\n<br />\r\n<img alt=\"\" src=\"https://d1jnx9ba8s6j9r.cloudfront.net/blog/wp-content/uploads/2019/07/EventHandling-In-Java.jpg\" style=\"height:175px; width:300px\" /></p>\r\n', '2025-01-28 09:32:00', '2025-01-28 04:03:03', 'Warje', 0, 0, 0, 'active'),
(4, 3, 'PyData Meetup 2025', '<h2><strong>Details</strong></h2>\r\n\r\n<p>Hello Pythonistas :)</p>\r\n\r\n<p>Happy New Year!</p>\r\n\r\n<p>This time we are having a free-flowing discussion about Python, Python libraries, projects, coding challenges, or anything else of your interest.</p>\r\n\r\n<p>The meetup welcomes Python novices as well as experts. If you need help with an existing project, bring it. If you want to interact with other Pythonistas, do join and say hello.</p>\r\n\r\n<p><strong>How to join?</strong></p>\r\n\r\n<p>Join this video conference link<br />\r\n[<a href=\"https://meet.jit.si/PythonPuneJan25\" target=\"_blank\">https://meet.jit.si/PythonPuneJan25</a>](https://meet.jit.si/PythonPuneJan25)</p>\r\n\r\n<p>Note: Every attendee should follow the PythonPune Code of Conduct during meetup [<a href=\"https://pythonpune.in/code-of-conduct\" target=\"_blank\">https://pythonpune.in/code-of-conduct</a>](https://pythonpune.in/code-of-conduct)</p>\r\n\r\n<p>----</p>\r\n\r\n<p>You are always welcome to talk about anything you are working on, be it a small Python program you wrote, or a new library you tried. Please create a GitHub issue.<br />\r\n[<a href=\"https://github.com/pythonpune/meetup-talks/issues/new/choose\" target=\"_blank\">https://github.com/pythonpune/meetup-talks/issues/new/choose</a>](https://github.com/pythonpune/meetup-talks/issues/new/choose)</p>\r\n', '2025-01-29 13:30:00', '2025-01-27 18:53:59', 'MITWPU, Pune', 0, 0, 0, 'active'),
(6, 7, 'MulticloudWorld 2025 - Pune', '<h2>Details</h2>\r\n\r\n<p>🌐&nbsp;<strong>MulticloudWorld 2025: Unlock the Future of Cloud Innovation!</strong>&nbsp;🌐<br />\r\nGet ready for the ultimate cloud event of the year! MulticloudWorld 2025 is coming to a city near you, bringing together world-class speakers from Oracle, Google, AWS, industry-leading customers, and many more.</p>\r\n\r\n<p><strong>Coming to a city near you:</strong><br />\r\n2-Feb-2025, Sunday -&nbsp;<a href=\"https://www.aioug.org/mc/2025/pnq\" target=\"_blank\">Pune</a></p>\r\n\r\n<p>🔥&nbsp;<strong>What to Expect:</strong><br />\r\n👉 Inspiring Keynotes: Visionary insights from top-notch speakers.<br />\r\n👉 Deep-Dive Sessions: Hands-on learning and in-depth technical talks on cutting-edge Oracle Multicloud Solutions.<br />\r\n👉 Networking Opportunities: Connect with industry leaders, peers, and experts.</p>\r\n\r\n<p>💡 Shape the Future with Oracle Multicloud Solutions! Whether you&#39;re a developer, architect, or IT leader, this is your chance to stay ahead in the multicloud revolution.</p>\r\n\r\n<p>🎟️ Don&#39;t Wait&mdash;register now at [<a href=\"https://www.aioug.org/mc/2025/pnq\" target=\"_blank\">https://www.aioug.org/mc/2025/pnq</a>](https://www.aioug.org/mc/2025/pnq) for the Early Bird Offer! Spots are limited, so secure your place today and be part of the next big thing in cloud innovation.</p>\r\n', '2025-01-31 13:00:00', '2025-01-28 05:27:58', 'Mumbai, Maharashtra', 0, 0, 0, 'active'),
(13, 3, 'Python Bootcamp', '<p>This is a python bootcamp event</p>\r\n', '2025-02-12 12:00:00', '2025-02-07 05:53:46', 'Pune, Magarpatta', 1, 2, 6, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

DROP TABLE IF EXISTS `event_attendees`;
CREATE TABLE IF NOT EXISTS `event_attendees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `domain` varchar(255) NOT NULL,
  `stream` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `pincode_id` int DEFAULT NULL,
  `mobile_number` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `pincode_id` (`pincode_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_attendees`
--

INSERT INTO `event_attendees` (`id`, `event_id`, `user_id`, `domain`, `stream`, `profile_photo`, `pincode_id`, `mobile_number`) VALUES
(9, 13, 12, 'Software Development', 'Database Admin', 'uploads/profile_pictures/12_PP.jpg', 2, '7842956310'),
(8, 5, 12, 'IT', 'Mobile Developer', 'uploads/profile_pictures/12_PP.jpg', 2, '7842956310');

-- --------------------------------------------------------

--
-- Table structure for table `event_photos`
--

DROP TABLE IF EXISTS `event_photos`;
CREATE TABLE IF NOT EXISTS `event_photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `community_id` int NOT NULL,
  `event_id` int NOT NULL,
  `photos` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `community_id` (`community_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_photos`
--

INSERT INTO `event_photos` (`id`, `community_id`, `event_id`, `photos`, `created_at`) VALUES
(1, 3, 4, 'uploads/community_event/photos/1738131505_meet2.jpg,uploads/community_event/photos/1738131505_meet1.jpg,uploads/community_event/photos/1738132959_img2.avif,uploads/community_event/photos/1738132959_img1.avif', '2025-01-29 06:18:25'),
(3, 3, 10, 'uploads/community_event/photos/1738133170_img3.jpg', '2025-01-29 06:46:10'),
(4, 3, 13, 'uploads/community_event/photos/1739248886_WhatsApp Image 2025-02-11 at 10.08.52_2db91279.jpg', '2025-02-11 04:41:26');

-- --------------------------------------------------------

--
-- Table structure for table `interest`
--

DROP TABLE IF EXISTS `interest`;
CREATE TABLE IF NOT EXISTS `interest` (
  `interest_id` int NOT NULL AUTO_INCREMENT,
  `interest_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`interest_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `interest`
--

INSERT INTO `interest` (`interest_id`, `interest_name`) VALUES
(1, 'Dance'),
(2, 'Music'),
(3, 'Education'),
(4, 'Sports'),
(5, 'E-Sports'),
(6, 'Fitness'),
(7, 'Travel'),
(8, 'Art & Crafts'),
(9, 'Food'),
(10, 'Career'),
(11, 'Theatre'),
(12, 'Pets & Animals'),
(13, 'Health & Medical'),
(14, 'Book Clubs'),
(15, 'Gardening'),
(16, 'Fashion'),
(17, 'Spirituality'),
(18, 'Business');

-- --------------------------------------------------------

--
-- Table structure for table `pincodes`
--

DROP TABLE IF EXISTS `pincodes`;
CREATE TABLE IF NOT EXISTS `pincodes` (
  `pincode_id` int NOT NULL AUTO_INCREMENT,
  `pincode` varchar(10) NOT NULL,
  `city_id` int DEFAULT NULL,
  PRIMARY KEY (`pincode_id`),
  KEY `city_id` (`city_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pincodes`
--

INSERT INTO `pincodes` (`pincode_id`, `pincode`, `city_id`) VALUES
(1, '400001', 1),
(2, '400002', 1),
(3, '400003', 1),
(4, '411001', 2),
(5, '411002', 2),
(6, '411003', 2),
(7, '440001', 3),
(8, '440002', 3),
(9, '440003', 3),
(10, '560001', 4),
(11, '560002', 4),
(12, '560003', 4),
(13, '570001', 5),
(14, '570002', 5),
(15, '570003', 5),
(16, '580001', 6),
(17, '580002', 6),
(18, '580003', 6),
(19, '600001', 7),
(20, '600002', 7),
(21, '600003', 7),
(22, '641001', 8),
(23, '641002', 8),
(24, '641003', 8),
(25, '625001', 9),
(26, '625002', 9),
(27, '625003', 9),
(28, '700001', 10),
(29, '700002', 10),
(30, '700003', 10),
(31, '711101', 11),
(32, '711102', 11),
(33, '711103', 11),
(34, '734001', 12),
(35, '734002', 12),
(36, '734003', 12),
(37, '700001', 10),
(38, '700002', 10),
(39, '700003', 10),
(40, '711101', 11),
(41, '711102', 11),
(42, '711103', 11),
(43, '734001', 12),
(44, '734002', 12),
(45, '734003', 12),
(46, '380001', 13),
(47, '380002', 13),
(48, '380003', 13),
(49, '395001', 14),
(50, '395002', 14),
(51, '395003', 14),
(52, '390001', 15),
(53, '390002', 15),
(54, '390003', 15);

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;
CREATE TABLE IF NOT EXISTS `request` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `community_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `skill_ids` varchar(255) DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `pincode_id` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `document_path` varchar(255) NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `community_id` (`community_id`),
  KEY `user_id` (`user_id`),
  KEY `skill_id` (`skill_ids`(250)),
  KEY `city_id` (`city_id`),
  KEY `pincode_id` (`pincode_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'Community Leader'),
(2, 'Community Coordinator'),
(3, 'Event Manager'),
(4, 'Community Media Manager'),
(5, 'Community Member'),
(6, 'Fundraising Head'),
(7, 'Community Support Team'),
(8, 'Trainer/Workshop Facilitator'),
(9, 'Community Secretary'),
(10, 'Community Advisor'),
(11, 'Community Resource Manager'),
(12, 'Community Welfare Manager');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE IF NOT EXISTS `skills` (
  `skill_id` int NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`skill_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `skill_name`) VALUES
(1, 'Public Speaking'),
(2, 'Driving'),
(3, 'Catering'),
(4, 'Accounting'),
(5, 'Event Handling'),
(6, 'Networking'),
(7, 'Problem Solving'),
(8, 'Design Thinking'),
(9, 'Graphic Designing'),
(10, 'Strategic Thinking'),
(11, 'Data Analysis'),
(12, 'Decision Making'),
(13, 'Analytical Thinking'),
(14, 'Team Building'),
(15, 'Digital Marketing'),
(16, 'Marketing');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
CREATE TABLE IF NOT EXISTS `states` (
  `state_id` int NOT NULL AUTO_INCREMENT,
  `state_name` varchar(100) NOT NULL,
  PRIMARY KEY (`state_id`),
  UNIQUE KEY `state_name` (`state_name`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`state_id`, `state_name`) VALUES
(1, 'Maharashtra'),
(2, 'Karnataka'),
(3, 'Tamil Nadu'),
(4, 'West Bengal'),
(5, 'Gujarat'),
(6, 'Andhra Pradesh'),
(7, 'Arunachal Pradesh'),
(8, 'Assam'),
(9, 'Bihar'),
(10, 'Chhattisgarh'),
(11, 'Goa'),
(12, 'Haryana'),
(13, 'Himachal Pradesh'),
(14, 'Jharkhand'),
(15, 'Kerala'),
(16, 'Madhya Pradesh'),
(17, 'Manipur'),
(18, 'Meghalaya'),
(19, 'Mizoram'),
(20, 'Nagaland'),
(21, 'Odisha'),
(22, 'Punjab'),
(23, 'Rajasthan'),
(24, 'Sikkim'),
(25, 'Telangana'),
(26, 'Tripura'),
(27, 'Uttar Pradesh'),
(28, 'Uttarakhand');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int DEFAULT NULL,
  `state_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `pincode_id` int DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone_number` varchar(15) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `state_id` (`state_id`),
  KEY `city_id` (`city_id`),
  KEY `pincode_id` (`pincode_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `age`, `state_id`, `city_id`, `pincode_id`, `profile_picture`, `phone_number`) VALUES
(11, 'Atharva Joshi', 'atharva@example.com', '$2y$10$Ag59YYHpH.E9I.gbDS.Z9.ehuyzjvmD1F6Ao9zafmMTUG70V5TRIK', 23, 1, 2, 4, 'uploads/profile_pictures/11_PP.jpg', '7350125844'),
(12, 'Aniket', 'aniket@example.com', '$2y$10$1QGoqcxR0SZMBvkq9/J3pO5o1/8tHkxAPzV.WewxDk4LoAf2Z2UHu', 18, 1, 1, 2, 'uploads/profile_pictures/12_PP.jpg', '7842956310'),
(21, 'demo user', 'demo@gmail.com', '$2y$10$yfPFoZSLDDhaqkWEYUoJtugkiaJBfubyrMuqDSJNICkMWgn1czEoy', 23, 1, 1, 1, 'uploads/profile_pictures/1738652718_signature.jpg', '1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `user_interests`
--

DROP TABLE IF EXISTS `user_interests`;
CREATE TABLE IF NOT EXISTS `user_interests` (
  `user_id` int NOT NULL,
  `interest_ids` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`user_id`, `interest_ids`) VALUES
(4, '1'),
(5, '1,2,3,4,5'),
(11, '5,9'),
(12, '1'),
(13, '8,11,12'),
(14, '2,7,9,10,12'),
(15, '7,9,12,16'),
(16, '1'),
(17, '1,2'),
(18, '9,12'),
(19, '1'),
(20, '1'),
(21, '1');

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

DROP TABLE IF EXISTS `user_skills`;
CREATE TABLE IF NOT EXISTS `user_skills` (
  `user_id` int NOT NULL,
  `skill_ids` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`user_id`, `skill_ids`) VALUES
(4, '9,10,11,12,13,14,15,16'),
(5, '1,3,4,6,7,8'),
(11, '1'),
(12, '1'),
(13, '4,10,14'),
(14, '1,2,3,4'),
(15, '2,3,4'),
(16, '4'),
(17, '1,2,3,4'),
(18, '5,6,11,13'),
(19, '1'),
(20, '1'),
(21, '1,2,3,4');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
