-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 13, 2026 at 02:46 PM
-- Server version: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `EventsTeam`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('handler', 'Survivalmaster', '2026-05-20 22:05:45'),
('username', 'Survivalmaster', '2026-05-02 00:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `environmental_events`
--

CREATE TABLE `environmental_events` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_id` varchar(50) NOT NULL DEFAULT '',
  `faction_flags` varchar(255) NOT NULL DEFAULT '',
  `weight` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `type` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL DEFAULT '',
  `banner_url` varchar(1000) NOT NULL DEFAULT '',
  `banner_pos_x` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `banner_pos_y` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `banner_zoom` decimal(4,2) NOT NULL DEFAULT 1.00,
  `label` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `environmental_events`
--

INSERT INTO `environmental_events` (`id`, `event_id`, `faction_flags`, `weight`, `type`, `name`, `district`, `banner_url`, `banner_pos_x`, `banner_pos_y`, `banner_zoom`, `label`, `created_at`, `updated_at`) VALUES
(2, '2', 'LEO,MED,GOV,SANFIRE', 4, '', 'Incident #2', '', 'https://i.ibb.co/1fd9wTnb/image.png', 50, 62, 1.36, 'A large fallen tree seems to be blocking the dirt path.', NULL, NULL),
(4, '4', 'LEO,MED,GOV,SANFIRE', 5, '', 'Fallen Tree', '', 'https://i.ibb.co/vChykRrG/image.png', 50, 54, 1.64, 'Two large fallen trees seems to have rolled off the hill. They\'re now blocking the dirt path.', NULL, NULL),
(8, '8', 'GOV,LEO', 4, '', 'Incident #8', '', 'https://i.ibb.co/s9tVvf3V/image.png', 50, 50, 1.00, 'The phone booth has shattered glass with jagged edges clinging to the frame & shards scattered on the ground. The exposed interior looks abandoned, and the broken glass leaves the booth open.', NULL, NULL),
(9, '9', 'MED,LEO,GOV', 3, '', 'Incident #9', '', 'https://i.ibb.co/twYnQYvb/image.png', 50, 63, 1.51, 'The busted fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(10, '10', 'LEO,MED,GOV', 5, '', 'Incident #10', '', 'https://i.ibb.co/bg1QvCh8/image.png', 50, 61, 2.63, 'The busted fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(11, '11', 'LEO,MED,GOV', 1, '', 'Incident #11', '', 'https://i.ibb.co/4nWWX90B/image.png', 50, 64, 1.90, 'The busted fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(13, '13', 'LEO,MED,GOV', 4, '', 'Incident #13', '', 'https://i.ibb.co/0p28ykPY/image.png', 50, 73, 1.29, 'The bench has one of its legs snapped in half, making it unusable but not entirely collapsed.', NULL, NULL),
(14, '14', 'LEO,GOV,MED,SANFIRE', 3, '', 'Fallen Tree', '', 'https://i.ibb.co/ynStJHt1/image.png', 50, 59, 1.14, 'A tree seems to have collapsed on the sidewalk.', NULL, NULL),
(15, '15', 'LEO,MED,GOV,SANFIRE', 3, '', 'Fallen Tree', '', 'https://i.ibb.co/4ZQC4Pmy/image.png', 50, 59, 1.38, 'A tree seems to have collapsed on the road, blocking a lane completely.', NULL, NULL),
(16, '16', 'LEO,MED,GOV', 6, '', 'Rock Slide', '', 'https://i.ibb.co/XfqtZn89/image.png', 50, 65, 1.45, 'Larger sized rocks seems to have rolled off from the cliff. Dust and debris would cover the road, along with the rocks.', NULL, NULL),
(17, '17', 'LEO,MED,GOV,SANFIRE', 6, '', 'Fallen Tree', '', 'https://i.ibb.co/WpPb0SMN/image.png', 50, 60, 1.29, 'A large tree seems to have collapsed on the road, blocking the lanes. It also snapped in half.', NULL, NULL),
(18, '18', 'LEO,MED,GOV', 4, '', 'Incident #18', '', 'https://i.ibb.co/0y2tHRgk/image.png', 50, 62, 1.27, 'The dumpster\'s plastic lid is in pieces. The steel frame seems to have been severely vandalized, the overall shape is deformed, suggesting an urgent need for repair.', NULL, NULL),
(19, '19', 'LEO,MED,GOV,SANFIRE', 5, '', 'Fallen Tree', '', 'https://i.ibb.co/wF4TGrmj/image.png', 50, 50, 1.30, 'A giant tree collapsed on the road.', NULL, NULL),
(20, '20', 'LEO,MED,GOV', 5, '', 'Incident #20', '', 'https://i.ibb.co/vvkrzdCQ/image.png', 50, 58, 1.92, 'The fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(21, '21', 'LEO,MED,GOV', 5, '', 'Incident #21', '', 'https://i.ibb.co/gbm6DpNp/image.png', 50, 50, 1.00, 'The fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(22, '22', 'LEO,MED,GOV', 2, '', 'Incident #22', '', 'https://i.ibb.co/39pskNkm/image.png', 50, 50, 1.00, 'The old, rusty, unused pump would show some leaks. A tiny, very minimal amount of gasoline can be seen leaking from the nozzle.', NULL, NULL),
(23, '23', 'LEO,MED,GOV,SANFIRE', 3, '', 'Fallen Tree', '', 'https://i.ibb.co/qLKjCbKD/image.png', 50, 63, 1.35, 'One of the palm trees snapped in half and fell on the road, blocking some of the access.', NULL, NULL),
(24, '24', 'LEO,MED,GOV,SANFIRE', 4, '', 'Fallen Tree', '', 'https://i.ibb.co/4wXT0vk9/image.png', 50, 50, 1.00, 'A tree fell on the dirt path, blocking access.', NULL, NULL),
(25, '26', 'LEO,MED,GOV', 5, '', 'Incident #26', '', 'https://i.ibb.co/96XtXbm/image.png', 50, 65, 1.36, 'A large fallen tree is blocking the road. It snapped in half.', NULL, NULL),
(26, '27', 'LEO,MED,GOV', 4, '', 'Incident #27', '', 'https://i.ibb.co/KpsXPTdc/image.png', 50, 57, 2.42, 'The fire hydrant has a steady leak, with water trickling from a crack near the base. Small puddles form around it, as the gentle flow dampens the surrounding pavement.', NULL, NULL),
(27, '28', 'LEO,MED,GOV,SANFIRE', 5, '', 'Fallen Tree', '', 'https://i.ibb.co/Zzt4ZzjW/image.png', 50, 71, 1.32, 'A large tree seems to have collapsed on the ground. It snapped in half.', NULL, NULL),
(28, '29', 'LEO,GOV,SANFIRE', 3, '', 'Fallen Tree', '', 'https://i.ibb.co/S4b73vSh/image.png', 50, 64, 1.26, 'A tree nearby has fallen onto the access road / trail upto Arthur\'s Pass. Leaves are scattered everywhere, a dead bird and it\'s nest is found on the ground from the impact.', NULL, NULL),
(29, '30', 'LAW,LEO,GOV,DEPT,MDC,GDM', 4, '', 'Incident #30', '', 'https://i.ibb.co/mCStdJhy/image.png', 50, 93, 1.38, 'The sewer is blocked, and as a result, water has risen. You can see stinky, yellowish water next to the sewer hole; the odor is awful.', NULL, NULL),
(30, '31', 'LEO,MED,GOV', 7, '', 'Incident #31', '', 'https://i.ibb.co/Q7k4QCBc/image.png', 54, 23, 3.00, 'A black cat would be heard loudly meowing and whining from the tree. It seems terrified and in distress.', NULL, NULL),
(31, '32', 'LEO,MED,LSGOV', 3, '', 'Incident #32', '', 'https://i.ibb.co/5xXvSdv9/image.png', 50, 84, 1.47, 'There is trash and debris scattered across the road. It looks like an animal had gotten into it.', NULL, NULL),
(33, '35', 'LEO,MED,GOV', 6, '', 'Incident #35', '', 'https://i.ibb.co/GfpZd0kP/image.png', 50, 28, 2.12, 'The fire hydrant can be seen leaking water all over the road.', NULL, NULL),
(34, '36', 'LEO,MED,GOV', 5, '', 'Rock Slide', '', 'https://i.ibb.co/274LwYSV/image.png', 50, 89, 1.25, 'A number of well sized rocks rolled down onto the road, blocking some of the traffic.', NULL, NULL),
(35, '37', 'LEO,MED,GOV', 5, '', 'Incident #37', '', 'https://i.ibb.co/0Ry9VW8v/image.png', 50, 71, 2.14, 'The fire hydrant is broken and water is gushing out everywhere.', NULL, NULL),
(36, '38', 'LEO,MED,GOV', 6, '', 'Incident #38', '', 'https://i.ibb.co/Y4s5r0Jr/image.png', 50, 70, 1.74, 'The fire hydrant is busted, with water shooting out in all directions.', NULL, NULL),
(37, '39', 'GOV,LEO', 7, '', 'Incident #39', '', 'https://i.ibb.co/jkpXD6Ls/image.png', 50, 72, 1.15, 'There would be various temporary accommodations left lying around, trash would be seen everywhere and the smell would be pungent.', NULL, NULL),
(41, '45', 'GOV,GOVDUTY,MED,LEO', 5, '', 'Incident #45', '', 'https://i.ibb.co/7JCbrT7P/image.png', 50, 83, 1.38, 'A dumpster would have been left on it\'s side. There would be garbage bags surrounding it, smelling very badly.', NULL, NULL),
(42, '46', 'LEO,MED,GOV,SANFIRE', 3, '', 'Dead Carcass', '', 'https://i.ibb.co/5WKfv3qr/image.png', 50, 82, 1.64, 'There would be a deer corpse laying by the side of the road. It would appear to be a fresh kill though the aroma would not be pleasant.', NULL, NULL),
(43, '47', 'LEO,GOV', 5, '', 'Incident #47', '', 'https://i.ibb.co/LDvj3ZYS/image.png', 50, 72, 1.46, 'Some shopping carts were vandalized and left all over the place in the parking lot.', NULL, NULL),
(44, '48', 'LEO,GOV', 6, '', 'Incident #48', '', 'https://i.ibb.co/svn4zcwv/image.png', 50, 83, 1.53, 'Some shopping carts were left abandoned and kicked over.', NULL, NULL),
(45, '49', 'LEO,GOV', 6, '', 'Incident #49', '', 'https://i.ibb.co/dsVqK3Mh/image.png', 50, 69, 1.54, 'A number of trash bags were left on the sidewalk. They smell awful.', NULL, NULL),
(46, '50', 'LEO,GOV', 7, '', 'Incident #50', '', 'https://i.ibb.co/zT56jqjj/image.png', 50, 74, 1.98, 'A number of trash bags were left on the sidewalk. If approached, the stench would be almost unbearable.', NULL, NULL),
(47, '51', 'LEO,GOV,MED', 7, '', 'Fallen Tree', '', 'https://i.ibb.co/Wv5XwB1Q/image.png', 50, 69, 1.29, 'The large tree snapped in half and collapsed on the road.', NULL, NULL),
(48, '52', 'GOV', 4, '', 'Incident #52', '', 'https://i.ibb.co/Fqxb6JdZ/image.png', 50, 77, 1.03, 'A portion of the billboard has fallen down, there would be debris over the road.', NULL, NULL),
(49, '53', 'MED', 3, '', 'Incident #53', '', 'https://i.ibb.co/0RpndPqY/image.png', 50, 86, 1.27, 'There would be smoke seen coming from the dumpster.', NULL, NULL),
(50, '55', 'LEO,MED,GOV', 5, '', 'Incident #55', '', 'https://i.ibb.co/dJmhLgGf/image.png', 50, 54, 1.36, 'The encampment would appear to of been left alone, it would smell putrid and not pleasant. There would be mouldy food and tents that appear to have all been ripped.', NULL, NULL),
(51, '56', 'MED,GOV', 4, '', 'Incident #56', '', 'https://i.ibb.co/0pDMMJw5/image.png', 50, 100, 1.18, 'The billboard will have fallen, landing onto the electrical pole. They\'d be spark flying out.', NULL, NULL),
(52, '58', 'LEO,GOV,MED,FIRE', 7, '', 'Incident #58', '', 'https://i.ibb.co/6c6yJkmd/image.png', 50, 61, 1.13, 'A number of trash bags were left here. They stink, making it really hard for anyone to even approach them.', NULL, NULL),
(53, '59', 'LEO,MED,FIRE,GOV', 5, '', 'Rock Slide', '', 'https://i.ibb.co/FCGnRdz/image.png', 50, 92, 1.18, 'Several rocks have rolled off the hill.', NULL, NULL),
(54, '60', 'LEO,MED,GOV,SANFIRE', 8, '', 'Incident #60', '', 'https://i.ibb.co/HpPG4QWr/image.png', 50, 50, 1.00, 'A rusty old boat seems to be washed ashore. It\'s stuck in the sand.', NULL, NULL),
(55, '61', 'LEO,MED,SANFIRE,GOV', 7, '', 'Incident #61', '', 'https://i.ibb.co/tpzRyRth/image.png', 53, 88, 1.41, 'Various garbage bags were left on the side of the road. They radiate an unbearable stench.', NULL, NULL),
(56, '62', 'LEO,MED,GOV,SANFIRE', 8, '', 'Incident #62', '', 'https://i.ibb.co/Tq8sNMYc/image.png', 50, 95, 1.00, 'A rusty old row boat seems to have washed ashore. It\'s stuck in the sand.', NULL, NULL),
(57, '63', 'LEO,MED,GOV,SANFIRE', 7, '', 'Incident #63', '', 'https://i.ibb.co/zdQYNN3/image.png', 50, 61, 1.33, 'Several plastic chairs were left all over the place.', NULL, NULL),
(58, '64', 'LEO,MED,GOV,SANFIRE', 5, '', 'Fallen Tree', '', 'https://i.ibb.co/9HPQmn9p/image.png', 50, 50, 1.16, 'The palm tree seems to have collapsed and snapped in half.', NULL, NULL),
(59, '65', 'LEO,MED,GOV,SANFIRE', 6, '', 'Fallen Tree', '', 'https://i.ibb.co/9mZYmTQ4/image.png', 52, 58, 1.37, 'The tree collapsed on the road, blocking half the road.', NULL, NULL),
(60, '66', 'LEO,MED,GOV,SANFIRE', 6, '', 'Dead Carcass', '', 'https://i.ibb.co/vt5xGvm/image.png', 50, 50, 1.30, 'A deer carcass was left on the side of the road. It seems to have been ran over.', NULL, NULL),
(61, '67', 'LEO,MED,GOV,SANFIRE,FIRE', 7, '', 'Dead Carcass', '', 'https://i.ibb.co/HL3Dy0dk/image.png', 50, 96, 1.28, 'A deer carcass was left on the side of the road. It seems to have been here for a while, covered in dust.', NULL, NULL),
(62, '68', 'LEO,MED,LSGOV', 2, '', 'Fallen Tree', '', 'https://i.ibb.co/gL99zrJh/image.png', 50, 50, 1.10, 'A tree would off fallen, blocking the entire road. Dirt can be seen scattered across the road as well.', NULL, NULL),
(63, '69', 'GOV', 2, '', 'Dumped Appliances', 'Pillbox Hill', 'https://i.ibb.co/rfmFqg7D/image.png', 50, 58, 2.01, 'There would be a variety of different broken appliances that had been left on the side of the road.', NULL, NULL),
(64, '70', 'SANFIRE,FIRE', 6, 'Fire Risk', 'Smoldering Dry Brush/Grass', 'Mount Chillian', 'https://i.ibb.co/XrVS4qWd/Grand-Theft-Auto-V-Screenshot-2026-04-17-15-48-19-28.png', 50, 50, 1.93, 'Smoke visible from dry brush and grass off the roadside, smoldering due to the extreme heat. No open flames are visible at this time. The surrounding soil and vegetation are severely dried out.', NULL, NULL),
(65, '71', 'SANFIRE', 5, 'Environmental Risk', 'Illegal Dumping Site', 'Mount Chilliad', 'https://i.ibb.co/1GDc90d6/Grand-Theft-Auto-V-Screenshot-2026-04-17-16-29-05-77.png', 50, 60, 3.00, 'A cluster of bulging black trash bags, a smashed CRT monitor with a shattered screen and a hazardous scattering of broken glass shards across seen scattered across the dirt.', NULL, NULL),
(66, '72', 'SANFIRE,GOV', 6, 'Environmental Risk', 'Illegal Dumping Site Vehicle Parts', 'Mount Chilliad', 'https://i.ibb.co/75KtD8y/Grand-Theft-Auto-V-Screenshot-2026-04-17-16-54-11-83.png', 50, 26, 1.95, 'Beside the rusted vehicle wreck lies a stack of discarded tires and sun-bleached plastic canisters, their brittle casings cracked and sweating leftover chemical fluids into the dry pine needles.', NULL, NULL),
(67, '73', 'SANFIRE,LSGOV', 7, 'Environmental Risk', 'Littering at Viewpoint', 'Raton Canyon', 'https://i.imgur.com/UK8sc8Z.png', 50, 81, 1.62, 'A collection of greasy fast-food wrappers and soda cups lies dumped in the dirt directly beneath the wooden \'East Alamo View\' observation deck.', NULL, NULL),
(68, '74', 'SANFIRE,LSGOV', 7, 'Abandoned Site', 'Left alone campsite', 'Cassidy Creek', 'https://i.ibb.co/HfTfpzJZ/Grand-Theft-Auto-V-Screenshot-2026-04-17-17-09-43-49.png', 50, 34, 1.65, 'A weathered dome tent stands abandoned in the brush alongside two green folding chairs, a portable grill, and a battery-powered lantern resting near a discarded whiskey bottle in the dirt.', NULL, NULL),
(69, '75', 'SANFIRE,LSGOV,FIRE', 6, 'Fire Risk', 'Smoldering Dry Brush/Grass', 'Grand Senora Desert', 'https://i.ibb.co/5X1mxy7v/Grand-Theft-Auto-V-Screenshot-2026-04-17-18-27-29-33.png', 50, 50, 2.83, 'Two empty glass bottles lie in the parched grass, acting as magnifying lenses for the sun and causing the surrounding dry vegetation to smolder and release thin wisps of smoke.', NULL, NULL),
(70, '76', 'SANFIRE,LSGOV,FIRE', 5, 'Environmental Risk', 'Dead Fallen Tree', 'Raton Canyon', 'https://i.ibb.co/ccYbH5Pr/Grand-Theft-Auto-V-Screenshot-2026-04-17-17-35-12-66.png', 50, 75, 2.32, 'A fallen, weathered tree lies sprawled across the dirt road, its bare branches obstructing the trail and forcing vehicles to detour around it.', NULL, NULL),
(71, '77', 'SANFIRE,FIRE,LSGOV', 5, 'Environmental Risk', 'Dead Tree Collapsing', 'Great Chaparral', 'https://i.ibb.co/YB7kMTNq/Grand-Theft-Auto-V-Screenshot-2026-04-17-18-46-43-97.png', 50, 50, 1.65, 'A dead, barkless tree trunk leans dangerously over the trail at a sharp angle, held up only by its own weight and posing an immediate overhead hazard to passing vehicles and hikers.', NULL, NULL),
(72, '78', 'SANFIRE,FIRE,LSGOV', 6, 'Environmental Risk', 'Smoldering Grass/Vegetation', 'Great Chaparral', 'https://i.ibb.co/Rp58tZxQ/Grand-Theft-Auto-V-Screenshot-2026-04-18-01-17-06-09.png', 50, 72, 3.00, 'Smoke visible from dry grass and vegetation off the roadside, smoldering due to the extreme heat. No open flames are visible at this time. The surrounding soil and vegetation are severely dried out.', NULL, NULL),
(73, '79', 'SANFIRE,LSGOV,FIRE,LEO', 7, 'Environmental Risk', 'Deer Carcass', 'Mount Chilliad', 'https://i.ibb.co/MyMFRhkP/Grand-Theft-Auto-V-Screenshot-2026-04-18-01-28-24-82.png', 50, 75, 2.83, 'A large deer carcass lies stiff on the side of the dirt road, surrounded by heavy tire tracks and disturbed earth that suggest a recent vehicle collision.', NULL, NULL),
(74, '80', 'SANFIRE,LSGOV,FIRE,LEO', 7, 'Environmental Risk', 'Deer Carcass 2', 'Paleto Forest', 'https://i.ibb.co/MyMFRhkP/Grand-Theft-Auto-V-Screenshot-2026-04-18-01-28-24-82.png', 50, 74, 3.00, 'A bloated deer carcass lies rotting in the tall grass, its hide mangled by scavengers and emitting a powerful, putrid odor of decay that hangs heavy in the humid forest air.', NULL, NULL),
(75, '81', 'SANFIRE,LSGOV,FIRE,LEO', 6, 'Environmental Risk', 'Unattended Campfire', 'Cassidy Creek', 'https://i.ibb.co/5xzhqycL/Grand-Theft-Auto-V-Screenshot-2026-04-18-02-14-23-26.png', 50, 50, 3.00, 'A pair of plaid folding chairs are pulled up to a live, unattended campfire crackling on the rocky shoreline, flanked by a discarded beer carton and a metal bucket containing a fresh, raw fish.', NULL, NULL),
(76, '82', 'SANFIRE,LEO', 6, 'Environmental Risk', 'Illegal Dumping Site By River', 'Zancudo River', 'https://i.ibb.co/7d8xJWp5/Grand-Theft-Auto-V-Screenshot-2026-04-18-02-48-32-19.png', 50, 36, 3.00, 'An illegal dump site by the riverbank features a rusted washing machine, several discarded tires, a heavy black trash bag, and a collection of chemical drums and gas canisters abandoned in the grass.', NULL, NULL),
(77, '83', 'SANFIRE,FIRE', 6, 'Environmental Risk', 'Smoldering Dry Brush/Grass', 'Stab City', 'https://i.ibb.co/4ZBS1TYW/Grand-Theft-Auto-V-Screenshot-2026-04-18-02-53-47-40.png', 50, 75, 2.56, 'Thin wisps of grey smoke rise from a patch of parched, blackened grass where the dry vegetation has begun to smolder, threatening to ignite the surrounding brush.', NULL, NULL),
(78, '84', 'SANFIRE,LSGOV,FIRE,LEO', 6, 'Environmental Risk', 'Dead Fallen Tree', 'North Chumash', 'https://i.ibb.co/G3cbPMgh/Grand-Theft-Auto-V-Screenshot-2026-04-18-03-12-19-39.png', 50, 80, 2.36, 'A large, fallen tree trunk lies heavily across the trail, its dry bark peeling away and its jagged branches creating a complete blockade for any passing traffic.', NULL, NULL),
(79, '85', 'LSGOV,GOV', 6, 'Environmental Risk', 'Loose Snake', 'Mirror Place', 'https://i.ibb.co/bRM3CJYC/image.png', 48, 71, 3.00, 'A snake seems to have found its way here and appears agitated as you approach.', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'NEW',
  `handler` varchar(100) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `name` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL DEFAULT '',
  `discord` varchar(500) NOT NULL DEFAULT '',
  `banner_url` varchar(1000) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `property_id` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `banner_pos_x` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `banner_pos_y` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `banner_zoom` decimal(4,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `environmental_events`
--
ALTER TABLE `environmental_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_env_weight` (`weight`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `environmental_events`
--
ALTER TABLE `environmental_events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
