# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.35)
# Database: git_dev
# Generation Time: 2020-03-21 08:27:11 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ty_recharge
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ty_recharge`;

CREATE TABLE `ty_recharge` (
  `order_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '会员ID',
  `nickname` varchar(50) DEFAULT NULL COMMENT '会员昵称',
  `order_sn` varchar(30) NOT NULL COMMENT '充值单号',
  `account` float(10,2) DEFAULT '0.00' COMMENT '充值金额',
  `ctime` int(11) DEFAULT NULL COMMENT '充值时间',
  `pay_time` int(11) DEFAULT NULL COMMENT '支付时间',
  `pay_code` varchar(20) DEFAULT NULL,
  `pay_name` varchar(80) DEFAULT NULL COMMENT '支付方式',
  `pay_status` tinyint(1) DEFAULT '0' COMMENT '充值状态0:待支付 1:充值成功 2:交易关闭',
  `pay_sn` varchar(150) DEFAULT NULL COMMENT '支付返回流水号',
  `type` tinyint(1) DEFAULT '1' COMMENT '充值类型：1:金额,2:云豆',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

LOCK TABLES `ty_recharge` WRITE;
/*!40000 ALTER TABLE `ty_recharge` DISABLE KEYS */;

INSERT INTO `ty_recharge` (`order_id`, `user_id`, `nickname`, `order_sn`, `account`, `ctime`, `pay_time`, `pay_code`, `pay_name`, `pay_status`, `pay_sn`, `type`)
VALUES
	(340,17,'下午来','rechrp331583125961',0.01,1583125961,NULL,NULL,NULL,0,NULL,1),
	(341,39,'家用电器自营','recln19d1583810940',0.01,1583810940,NULL,NULL,NULL,0,NULL,1),
	(342,39,'家用电器自营','reclEkTS1583810941',0.01,1583810941,NULL,NULL,NULL,0,NULL,1),
	(343,39,'家用电器自营','reczkqem1583810942',0.01,1583810942,NULL,NULL,NULL,0,NULL,1),
	(344,39,'家用电器自营','rec9kNQ91583810969',0.01,1583810969,NULL,NULL,NULL,0,NULL,1),
	(345,39,'家用电器自营','recjgt6Q1583897908',0.01,1583897908,NULL,NULL,NULL,0,NULL,1),
	(346,39,'家用电器自营','reckdFSJ1583897909',0.01,1583897909,NULL,NULL,NULL,0,NULL,1),
	(347,39,'家用电器自营','recDtfgi1583897909',0.01,1583897909,NULL,NULL,NULL,0,NULL,1),
	(348,39,'家用电器自营','recbshdK1583897909',0.01,1583897909,NULL,NULL,NULL,0,NULL,1),
	(349,39,'家用电器自营','rec6lrns1583897909',0.01,1583897909,NULL,NULL,NULL,0,NULL,1),
	(350,63,'哥古古怪怪','recrBdbH1584257681',0.01,1584257681,NULL,NULL,NULL,0,NULL,1),
	(351,63,'哥古古怪怪','receEF8x1584257687',0.01,1584257687,NULL,NULL,NULL,0,NULL,1),
	(352,6,'136****8774','rec4Fx7u1584328050',100.00,1584328050,NULL,NULL,NULL,0,NULL,1),
	(353,6,'136****8774','recPvAMS1584328059',100.00,1584328059,NULL,NULL,NULL,0,NULL,1),
	(354,17,'下午来','recBtJEG1584328276',0.01,1584328276,NULL,NULL,NULL,0,NULL,1),
	(355,17,'下午来','rect5wsi1584328320',0.01,1584328320,NULL,NULL,NULL,0,NULL,1),
	(356,1604,'150****2097','recF5umr1584336799',1.00,1584336799,NULL,NULL,NULL,0,NULL,2),
	(357,26,'软装家纺自营','reciaHMx1584439312',0.01,1584439312,NULL,NULL,NULL,0,NULL,1);

/*!40000 ALTER TABLE `ty_recharge` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
