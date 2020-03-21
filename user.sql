# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.35)
# Database: git_dev
# Generation Time: 2020-03-21 08:57:10 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ty_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ty_users`;

CREATE TABLE `ty_users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `accid` char(32) DEFAULT '0' COMMENT '云信accid',
  `tokens` varchar(255) NOT NULL DEFAULT '' COMMENT '融云token',
  `tokens_business` varchar(255) NOT NULL DEFAULT '''''' COMMENT '融云token商家端',
  `email` varchar(60) DEFAULT '' COMMENT '邮件',
  `password` varchar(32) DEFAULT '' COMMENT '密码',
  `pay_password` varchar(32) DEFAULT '' COMMENT '支付密码',
  `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '0 保密 1 男 2 女',
  `birthday` int(11) DEFAULT '0' COMMENT '生日',
  `user_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户金额',
  `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `distribut_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累积分佣金额',
  `pay_points` int(10) unsigned DEFAULT '0' COMMENT '消费积分',
  `coin_yes` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '可提现饰币',
  `coin_no` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '不可提现饰币',
  `address_id` mediumint(8) unsigned DEFAULT '0' COMMENT '默认收货地址',
  `reg_time` int(10) unsigned DEFAULT '0' COMMENT '注册时间',
  `last_login` int(11) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(15) DEFAULT '' COMMENT '最后登录ip',
  `qq` varchar(20) DEFAULT NULL COMMENT 'QQ',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `mobile_validated` tinyint(3) unsigned DEFAULT '0' COMMENT '是否验证手机',
  `oauth` varchar(10) DEFAULT '' COMMENT '第三方来源 wx weibo alipay',
  `openid` varchar(100) DEFAULT NULL COMMENT '第三方唯一标示',
  `unionid` varchar(100) DEFAULT NULL,
  `head_pic` varchar(255) DEFAULT NULL COMMENT '头像',
  `province` int(6) DEFAULT '0' COMMENT '省份',
  `city` int(6) DEFAULT '0' COMMENT '市区',
  `district` int(6) DEFAULT '0' COMMENT '县',
  `email_validated` tinyint(1) unsigned DEFAULT '0' COMMENT '是否验证电子邮箱',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方返回昵称',
  `level` tinyint(1) DEFAULT '1' COMMENT '会员等级',
  `discount` decimal(10,2) DEFAULT '1.00' COMMENT '会员折扣，默认1不享受',
  `total_amount` decimal(10,2) DEFAULT '0.00' COMMENT '消费累计额度',
  `is_lock` tinyint(1) DEFAULT '0' COMMENT '是否被锁定冻结',
  `is_distribut` tinyint(1) DEFAULT '0' COMMENT '是否为分销商 0 否 1 是',
  `first_leader` int(11) DEFAULT '0' COMMENT '第一个上级',
  `second_leader` int(11) DEFAULT '0' COMMENT '第二个上级',
  `third_leader` int(11) DEFAULT '0' COMMENT '第三个上级',
  `token` varchar(64) DEFAULT '' COMMENT '用于app 授权类似于session_id',
  `token_business` varchar(64) DEFAULT NULL COMMENT '用于商家端app登录',
  `open_id` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `nick_name` varchar(255) DEFAULT NULL,
  `seat` int(255) DEFAULT '0',
  `call_cate` int(11) DEFAULT '0',
  `call_service` int(11) DEFAULT '0',
  `call_mf` int(11) DEFAULT '0',
  `call_s` int(11) DEFAULT '0',
  `wait_num` int(11) DEFAULT '0',
  `wait_type` int(255) DEFAULT '0',
  `join_store_id` int(10) DEFAULT '0' COMMENT '加入的店铺id',
  `description` varchar(255) DEFAULT '' COMMENT '个人简介',
  `longitude` varchar(100) NOT NULL DEFAULT '0' COMMENT '经度',
  `latitude` varchar(100) NOT NULL DEFAULT '0' COMMENT '纬度',
  `staff_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `staff` int(11) DEFAULT '0' COMMENT '0是普通用户，1是审核中。2是审核通过,3是审核失败',
  `rebate_money` decimal(10,2) DEFAULT '0.00' COMMENT '累计返佣金额，只做记录',
  `freeze` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态，是否冻结，0已冻结 1正常',
  `id_card` varchar(18) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `id_name` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证姓名',
  PRIMARY KEY (`user_id`),
  KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `ty_users` WRITE;
/*!40000 ALTER TABLE `ty_users` DISABLE KEYS */;

INSERT INTO `ty_users` (`user_id`, `accid`, `tokens`, `tokens_business`, `email`, `password`, `pay_password`, `sex`, `birthday`, `user_money`, `frozen_money`, `distribut_money`, `pay_points`, `coin_yes`, `coin_no`, `address_id`, `reg_time`, `last_login`, `last_ip`, `qq`, `mobile`, `mobile_validated`, `oauth`, `openid`, `unionid`, `head_pic`, `province`, `city`, `district`, `email_validated`, `nickname`, `level`, `discount`, `total_amount`, `is_lock`, `is_distribut`, `first_leader`, `second_leader`, `third_leader`, `token`, `token_business`, `open_id`, `country`, `gender`, `nick_name`, `seat`, `call_cate`, `call_service`, `call_mf`, `call_s`, `wait_num`, `wait_type`, `join_store_id`, `description`, `longitude`, `latitude`, `staff_id`, `staff`, `rebate_money`, `freeze`, `id_card`, `id_name`)
VALUES
	(39,'0','RvdMHmrakx6zMrk0IrrY8RYTTTgh5D9hrErtTkkt1oDXM5N+dCnUQCUEcD/ZbvD8YT/KcMibcWN6Q1LLuxYpvQ==','VZ22XTXLBpEahB35bl1UOHSS2KP5aW1V9uB/n51qiCzbLNZ4x9tqdN2an8g+usPFkfO029kdqtjwrEWGBrabGQ==','','519475228fe35ad067744465c42a19b2','519475228fe35ad067744465c42a19b2',0,0,864.00,0.00,0.00,0,0.00,0.00,0,1555564678,1584775434,'',NULL,'18239910719',0,'',NULL,NULL,'/Public/head/head_default.png',0,0,0,0,'家用电器',1,1.00,0.00,0,1,0,0,0,'50b0fff5beb9df3108ac36c6621593f9','',NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,0,'','0','0',0,2,0.00,1,'','');

/*!40000 ALTER TABLE `ty_users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
