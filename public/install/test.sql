/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50553
 Source Host           : localhost:3306
 Source Schema         : wlyzdb

 Target Server Type    : MySQL
 Target Server Version : 50553
 File Encoding         : 65001

 Date: 31/12/2019 19:49:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for yz_buycard_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_buycard_record`;
CREATE TABLE `yz_buycard_record`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `pid` int(8) DEFAULT NULL,
  `sid` int(8) DEFAULT NULL,
  `sname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `cardId` int(1) DEFAULT NULL,
  `num` int(5) DEFAULT NULL,
  `money` decimal(6, 2) DEFAULT NULL,
  `email` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `status` int(1) DEFAULT 0,
  `authorid` int(8) DEFAULT NULL,
  `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_card_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_card_record`;
CREATE TABLE `yz_card_record`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) DEFAULT NULL,
  `sname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  `card_no` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` int(1) DEFAULT NULL,
  `cardValue` int(8) DEFAULT NULL,
  `user_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `depositTime` int(10) DEFAULT NULL,
  `beforTime` int(10) DEFAULT NULL,
  `ofterTime` int(10) DEFAULT NULL,
  `beforPoint` int(10) DEFAULT NULL,
  `ofterPoint` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_card_type
-- ----------------------------
DROP TABLE IF EXISTS `yz_card_type`;
CREATE TABLE `yz_card_type`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) DEFAULT NULL,
  `sname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  `type` int(1) DEFAULT NULL,
  `cardValue` int(8) DEFAULT NULL,
  `cardPrice` decimal(8, 2) DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_cards
-- ----------------------------
DROP TABLE IF EXISTS `yz_cards`;
CREATE TABLE `yz_cards`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) DEFAULT NULL,
  `sname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  `card_value` int(8) DEFAULT NULL,
  `card_no` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` int(1) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `user_account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  `bid` int(8) DEFAULT 0,
  `sell` int(1) DEFAULT 0,
  `proxyid` int(11) DEFAULT 0,
  `proxytime` int(10) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_feedback
-- ----------------------------
DROP TABLE IF EXISTS `yz_feedback`;
CREATE TABLE `yz_feedback`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `suid` int(11) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yz_forward_url
-- ----------------------------
DROP TABLE IF EXISTS `yz_forward_url`;
CREATE TABLE `yz_forward_url`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `uid` int(8) DEFAULT NULL,
  `sid` int(8) DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` int(255) DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_login_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_login_record`;
CREATE TABLE `yz_login_record`  (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `uid` int(6) DEFAULT NULL,
  `ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `login_time` int(10) DEFAULT NULL,
  `location` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_money_list
-- ----------------------------
DROP TABLE IF EXISTS `yz_money_list`;
CREATE TABLE `yz_money_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `money` decimal(11, 3) DEFAULT NULL,
  `after` decimal(11, 3) DEFAULT NULL,
  `uid` int(6) DEFAULT NULL,
  `type` int(1) DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  `charge` decimal(8, 3) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_msg_box
-- ----------------------------
DROP TABLE IF EXISTS `yz_msg_box`;
CREATE TABLE `yz_msg_box`  (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `uid` int(6) DEFAULT NULL,
  `type` int(1) DEFAULT 1,
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sendTime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_pay_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_pay_record`;
CREATE TABLE `yz_pay_record`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `uid` int(8) NOT NULL,
  `orderno` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `payno` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `money` decimal(6, 2) NOT NULL,
  `pay_mode` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  `type` int(1) NOT NULL,
  `order_time` int(10) NOT NULL,
  `pay_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_proxy_buycard_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_proxy_buycard_record`;
CREATE TABLE `yz_proxy_buycard_record`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `typeId` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `money` decimal(8, 2) DEFAULT NULL,
  `authorid` int(11) DEFAULT NULL,
  `buytime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for yz_proxy_nodes
-- ----------------------------
DROP TABLE IF EXISTS `yz_proxy_nodes`;
CREATE TABLE `yz_proxy_nodes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `money` decimal(8, 2) DEFAULT NULL,
  `applyReason` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yz_remote_function
-- ----------------------------
DROP TABLE IF EXISTS `yz_remote_function`;
CREATE TABLE `yz_remote_function`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) DEFAULT NULL,
  `uid` int(8) DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_reply
-- ----------------------------
DROP TABLE IF EXISTS `yz_reply`;
CREATE TABLE `yz_reply`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `tid` int(8) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `postTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yz_soft_list
-- ----------------------------
DROP TABLE IF EXISTS `yz_soft_list`;
CREATE TABLE `yz_soft_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(8) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `version` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `count` int(6) DEFAULT 0,
  `openReg` int(1) DEFAULT 0,
  `notice` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `data` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `regFreePoint` int(5) DEFAULT NULL,
  `regFree` int(1) DEFAULT 0,
  `timeFreePointEnd` int(2) DEFAULT NULL,
  `timeFreePointStart` int(2) DEFAULT NULL,
  `timeFree` int(1) DEFAULT NULL,
  `freeChangeBundled` int(2) DEFAULT NULL,
  `verifyMode` int(1) DEFAULT 0,
  `pointStep` int(1) DEFAULT NULL,
  `topLoginType` int(1) DEFAULT 0,
  `multiType` int(1) DEFAULT 0,
  `isModifyMac` int(1) DEFAULT 0,
  `multiTypeValue` int(3) DEFAULT NULL,
  `encryptType` int(1) DEFAULT 0,
  `privateSalt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `privateKey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sale_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `expireTime` int(11) DEFAULT NULL,
  `isProxy` int(1) DEFAULT 0,
  `proxy_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ipPublicProxy` int(1) DEFAULT 0,
  `maxProxyLevel` int(6) DEFAULT 0,
  `isSocket` int(1) DEFAULT 0,
  `regMacLimit` int(6) DEFAULT 0,
  `regIpLimit` int(6) DEFAULT 0,
  `detail` varchar(5000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `softLobby` int(1) DEFAULT NULL,
  `clicks` int(10) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_soft_users
-- ----------------------------
DROP TABLE IF EXISTS `yz_soft_users`;
CREATE TABLE `yz_soft_users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `qq` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  `sid` int(8) DEFAULT NULL,
  `sname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `maccode` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `out_time` int(10) DEFAULT NULL,
  `point` int(8) DEFAULT 0,
  `isblacklist` int(1) DEFAULT 0,
  `modif_num` int(5) DEFAULT 0,
  `heart_time` int(10) DEFAULT NULL,
  `isonline` int(1) DEFAULT 0,
  `status` int(1) DEFAULT 0,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `city` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `createtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_soft_ver
-- ----------------------------
DROP TABLE IF EXISTS `yz_soft_ver`;
CREATE TABLE `yz_soft_ver`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ver` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `checkUpdate` int(1) DEFAULT 0,
  `MD5` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `updateUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `notice` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `reamrk` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `addTime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_su_login_record
-- ----------------------------
DROP TABLE IF EXISTS `yz_su_login_record`;
CREATE TABLE `yz_su_login_record`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sid` int(8) DEFAULT NULL,
  `uid` int(8) DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  `sname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `maccode` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `softMD5` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `city` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `login_time` int(10) DEFAULT NULL,
  `heart_time` int(10) DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yz_task_list
-- ----------------------------
DROP TABLE IF EXISTS `yz_task_list`;
CREATE TABLE `yz_task_list`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `price` decimal(10, 2) DEFAULT NULL,
  `needCode` int(1) DEFAULT 0,
  `status` int(1) DEFAULT NULL,
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `uid` int(8) DEFAULT NULL,
  `authorId` int(8) DEFAULT NULL,
  `postTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for yz_users
-- ----------------------------
DROP TABLE IF EXISTS `yz_users`;
CREATE TABLE `yz_users`  (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `money` decimal(8, 2) DEFAULT 0.00,
  `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `regtime` int(10) DEFAULT NULL,
  `group_id` int(1) DEFAULT 1,
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'logo.png',
  `qq` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `alipay` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

INSERT INTO `yy_users`(`username`, `password`, `group_id`, `avatar`) VALUES ('yz_admin_username', 'yz_admin_password', 2, 'logo.png');
-- ----------------------------
-- Table structure for yz_variable
-- ----------------------------
DROP TABLE IF EXISTS `yz_variable`;
CREATE TABLE `yz_variable`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL,
  `authorid` int(8) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
