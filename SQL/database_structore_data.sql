/*
 Navicat Premium Data Transfer

 Source Server         : Local
 Source Server Type    : MySQL
 Source Server Version : 50633
 Source Host           : localhost
 Source Database       : gt_eco_map

 Target Server Type    : MySQL
 Target Server Version : 50633
 File Encoding         : utf-8

 Date: 12/01/2016 13:11:33 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `area`
-- ----------------------------
DROP TABLE IF EXISTS `area`;
CREATE TABLE `area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `area`
-- ----------------------------
BEGIN;
INSERT INTO `area` VALUES ('3', 'Energy', '#ff6666'), ('4', 'Land Use', '#00e600'), ('5', 'Business', '#8533ff'), ('6', 'Waste', '#4dffd2'), ('7', 'Food', '#ffc266'), ('8', 'Other', '#66ffb3');
COMMIT;

-- ----------------------------
--  Table structure for `department`
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `parent` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=gbk ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `department`
-- ----------------------------
BEGIN;
INSERT INTO `department` VALUES ('107', 'Business School', null), ('105', 'Computational Science and Enigneering', null), ('106', 'Human Computer Interaction', null), ('104', 'Computer Science', null), ('108', 'Chemistry', null), ('109', 'Material Science', null), ('110', 'History and Sociology', null), ('111', 'Office of Campus Sustainability', null), ('112', 'GT Honors Program', null), ('113', 'GT Administration and Finance', null), ('114', 'AIA LEED AP', null);
COMMIT;

-- ----------------------------
--  Table structure for `person`
-- ----------------------------
DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deptID` int(3) DEFAULT NULL,
  `name` varchar(25) DEFAULT NULL,
  `area` int(3) DEFAULT '8',
  `role` int(1) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `pLink` varchar(255) DEFAULT NULL,
  `imglink` varchar(30) DEFAULT 'https://goo.gl/aHqWts',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `person`
-- ----------------------------
BEGIN;
INSERT INTO `person` VALUES ('16', '111', 'Anne Rogers', '8', '3', null, 'anne.rogers@sustain.gatech.edu', null, 'https://goo.gl/Kv4bTd'), ('17', '107', 'Elizabeth Schultz', '5', '1', '', 'elizabeth.s.schultz@gmail.com', 'linkedin.com/in/elizabethschultz5', 'https://goo.gl/Go5HkZ'), ('18', '112', 'Monica Halka', '4', '2', null, 'monica.halka@carnegie.gatech.edu', null, 'https://goo.gl/GbcsKg'), ('19', '108', 'Brian Schmatz', '3', '1', '', 'bschmatz@gatech.edu', 'linkedin.com/in/brianschmatz', 'https://goo.gl/vWAMUe'), ('20', '109', 'Nicole Kennard', '6', '1', '', 'nicolejjk.17@gmail.com', 'linkedin.com/in/nicolekennard', 'https://goo.gl/Pk595q'), ('21', '110', 'Rebecca Watts Hull', '7', '1', '', 'rwattshull@gatech.edu', null, 'https://goo.gl/tTgle6'), ('22', '113', 'Drew Cutright', '8', '3', '', 'drew.cutright@carnegie.gatech.edu', null, 'https://goo.gl/aHqWts'), ('23', '114', 'Nicolas Palfrey', '8', '3', '', 'nicolas.palfrey@facilities.gatech.edu', null, 'https://goo.gl/aHqWts'), ('24', '106', 'Felix Tener', '8', '1', null, 'felixtr@gmail.com', 'https://goo.gl/cgnj3l', 'https://goo.gl/rFFqJA'), ('25', '105', 'Xiaoqin Zhu', '8', '1', '', 'xzhu309@gatech.edu', '', 'https://goo.gl/aHqWts'), ('26', '104', 'Aoyi Li', '8', '1', '', 'liaoyi920828@gmail.com', null, 'https://goo.gl/RHNJBn'), ('27', '104', 'Yuyang He', '8', '1', null, 'yuyang.he@gatech.edu', null, 'https://goo.gl/aHqWts');
COMMIT;

-- ----------------------------
--  Table structure for `project`
-- ----------------------------
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `area` int(3) DEFAULT NULL,
  `uID` varchar(40) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `project`
-- ----------------------------
BEGIN;
INSERT INTO `project` VALUES ('5', 'GT Sustainability Eco Map', '8', '16,24,25,26,27', '127.0.0.1/GTEcoMap/Directory/table.php'), ('6', 'Examing Campus Sustainability and Food Surcing', '7', '21', 'www.iac.gatech.edu/students/graduate/graduate-student-profiles/rebecca-watts-hull'), ('7', 'Project ENGAGES', '6', '20', 'projectengages.gatech.edu'), ('8', 'Engineering Building Power Recycling', '3', '19', null), ('9', 'Greener Tech, Greener World', '8', '16', 'sustain.gatech.edu/our-mission-greener-tech-greener-world');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
