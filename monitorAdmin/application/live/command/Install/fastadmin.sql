/*
 FastAdmin Install SQL

 官网: http://www.fastadmin.net
 演示: http://demo.fastadmin.net

 Date: 08/08/2017 23:19:44 PM
*/

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `token` varchar(59) NOT NULL DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员表';

-- ----------------------------
--  Records of `admin`
-- ----------------------------
BEGIN;
INSERT INTO `admin` VALUES ('1', 'admin', 'Admin', '075eaec83636846f51c152f29b98a2fd', 's4f3', '/assets/img/avatar.png', 'admin@fastadmin.net', '0', '1502029281', '1492186163', '1502029281', 'd3992c3b-5ecc-4ecb-9dc2-8997780fcadc', 'normal'), ('2', 'admin2', 'admin2', '9a28ce07ce875fbd14172a9ca5357d3c', '2dHDmj', '/assets/img/avatar.png', 'admin2@fastadmin.net', '0', '1502015003', '1492186163', '1502029266', '', 'normal'), ('3', 'admin3', 'admin3', '1c11f945dfcd808a130a8c2a8753fe62', 'WOKJEn', '/assets/img/avatar.png', 'admin3@fastadmin.net', '0', '1501980868', '1492186201', '1501982377', '', 'normal'), ('4', 'admin22', 'admin22', '1c1a0aa0c3c56a8c1a908aab94519648', 'Aybcn5', '/assets/img/avatar.png', 'admin22@fastadmin.net', '0', '0', '1492186240', '1492186240', '', 'normal'), ('5', 'admin32', 'admin32', 'ade94d5d7a7033afa7d84ac3066d0a02', 'FvYK0u', '/assets/img/avatar.png', 'admin32@fastadmin.net', '0', '0', '1492186263', '1492186263', '', 'normal');
COMMIT;

-- ----------------------------
--  Table structure for `admin_log`
-- ----------------------------
DROP TABLE IF EXISTS `admin_log`;
CREATE TABLE `admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1218 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理员日志表';

-- ----------------------------
--  Table structure for `attachment`
-- ----------------------------
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imagetype` varchar(30) NOT NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(30) NOT NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) NOT NULL DEFAULT '' COMMENT '透传数据',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `uploadtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `storage` enum('local','upyun') NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附件表';

-- ----------------------------
--  Records of `attachment`
-- ----------------------------
BEGIN;
INSERT INTO `attachment` VALUES ('1', '/assets/img/qrcode.png', '150', '150', 'png', '0', '21859', 'image/png', '', '1499681848', '1499681848', '1499681848', 'local', '17163603d0263e4838b9387ff2cd4877e8b018f6');
COMMIT;

-- ----------------------------
--  Table structure for `auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE `auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text NOT NULL COMMENT '规则ID',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='分组表';

-- ----------------------------
--  Records of `auth_group`
-- ----------------------------
BEGIN;
INSERT INTO `auth_group` VALUES ('1', '0', '超级管理员', '*', '1490883540', '149088354', 'normal'), ('2', '1', '二级管理员', '1,2,4,6,7,8,9,10,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5', '1490883540', '1502205308', 'normal'), ('3', '2', '三级管理员', '1,4,9,10,11,13,14,15,16,17,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5', '1490883540', '1502205322', 'normal'), ('4', '1', '二级管理员2', '1,4,13,14,15,16,17,55,56,57,58,59,60,61,62,63,64,65', '1490883540', '1502205350', 'normal'), ('5', '2', '三级管理员2', '1,2,6,7,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34', '1490883540', '1502205344', 'normal');
COMMIT;

-- ----------------------------
--  Table structure for `auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `auth_group_access`;
CREATE TABLE `auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '会员ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限分组表';

-- ----------------------------
--  Records of `auth_group_access`
-- ----------------------------
BEGIN;
INSERT INTO `auth_group_access` VALUES ('1', '1'), ('2', '2'), ('3', '3'), ('4', '5'), ('5', '5');
COMMIT;

-- ----------------------------
--  Table structure for `auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='节点表';

-- ----------------------------
--  Records of `auth_rule`
-- ----------------------------
BEGIN;
INSERT INTO `auth_rule` VALUES ('1', 'file', '0', 'dashboard', '控制台', 'fa fa-dashboard\r', '', '用于展示当前系统中的统计数据、统计报表及重要实时数据\r', '1', '1497429920', '1497429920', '143', 'normal'), ('2', 'file', '0', 'general', '常规管理', 'fa fa-cogs', '', '', '1', '1497429920', '1497430169', '137', 'normal'), ('3', 'file', '0', 'category', '分类管理', 'fa fa-list\r', '', '用于统一管理网站的所有分类,分类可进行无限级分类\r', '1', '1497429920', '1497429920', '119', 'normal'), ('4', 'file', '0', 'addon', '插件管理', 'fa fa-rocket', '', '可在线安装、卸载、禁用、启用插件，同时支持添加本地插件', '1', '1502035509', '1502035509', '0', 'normal'), ('5', 'file', '0', 'auth', '权限管理', 'fa fa-group', '', '', '1', '1497429920', '1497430092', '99', 'normal'), ('6', 'file', '2', 'general/config', '系统配置', 'fa fa-cog', '', '', '1', '1497429920', '1497430683', '60', 'normal'), ('7', 'file', '2', 'general/attachment', '附件管理', 'fa fa-file-image-o', '', '主要用于管理上传到又拍云的数据或上传至本服务的上传数据\r\n', '1', '1497429920', '1497430699', '53', 'normal'), ('8', 'file', '2', 'general/profile', '个人配置', 'fa fa-user\r', '', '', '1', '1497429920', '1497429920', '34', 'normal'), ('9', 'file', '5', 'auth/admin', '管理员管理', 'fa fa-user', '', '一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成', '1', '1497429920', '1497430320', '118', 'normal'), ('10', 'file', '5', 'auth/adminlog', '管理员日志', 'fa fa-list-alt', '', '管理员可以查看自己所拥有的权限的管理员日志', '1', '1497429920', '1497430307', '113', 'normal'), ('11', 'file', '5', 'auth/group', '角色组', 'fa fa-group', '', '角色组可以有多个,角色有上下级层级关系,如果子角色有角色组和管理员的权限则可以派生属于自己组别下级的角色组或管理员', '1', '1497429920', '1497429920', '109', 'normal'), ('12', 'file', '5', 'auth/rule', '规则管理', 'fa fa-bars', '', '规则通常对应一个控制器的方法,同时左侧的菜单栏数据也从规则中体现,通常建议通过控制台进行生成规则节点', '1', '1497429920', '1497430581', '104', 'normal'), ('13', 'file', '1', 'dashboard/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '136', 'normal'), ('14', 'file', '1', 'dashboard/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '135', 'normal'), ('15', 'file', '1', 'dashboard/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '133', 'normal'), ('16', 'file', '1', 'dashboard/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '134', 'normal'), ('17', 'file', '1', 'dashboard/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '132', 'normal'), ('18', 'file', '6', 'general/config/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '52', 'normal'), ('19', 'file', '6', 'general/config/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '51', 'normal'), ('20', 'file', '6', 'general/config/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '50', 'normal'), ('21', 'file', '6', 'general/config/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '49', 'normal'), ('22', 'file', '6', 'general/config/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '48', 'normal'), ('23', 'file', '7', 'general/attachment/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '59', 'normal'), ('24', 'file', '7', 'general/attachment/select', '选择附件', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '58', 'normal'), ('25', 'file', '7', 'general/attachment/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '57', 'normal'), ('26', 'file', '7', 'general/attachment/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '56', 'normal'), ('27', 'file', '7', 'general/attachment/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '55', 'normal'), ('28', 'file', '7', 'general/attachment/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '54', 'normal'), ('29', 'file', '8', 'general/profile/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '33', 'normal'), ('30', 'file', '8', 'general/profile/update', '更新个人信息', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '32', 'normal'), ('31', 'file', '8', 'general/profile/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '31', 'normal'), ('32', 'file', '8', 'general/profile/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '30', 'normal'), ('33', 'file', '8', 'general/profile/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '29', 'normal'), ('34', 'file', '8', 'general/profile/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '28', 'normal'), ('35', 'file', '3', 'category/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '142', 'normal'), ('36', 'file', '3', 'category/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '141', 'normal'), ('37', 'file', '3', 'category/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '140', 'normal'), ('38', 'file', '3', 'category/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '139', 'normal'), ('39', 'file', '3', 'category/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '138', 'normal'), ('40', 'file', '9', 'auth/admin/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '117', 'normal'), ('41', 'file', '9', 'auth/admin/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '116', 'normal'), ('42', 'file', '9', 'auth/admin/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '115', 'normal'), ('43', 'file', '9', 'auth/admin/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '114', 'normal'), ('44', 'file', '10', 'auth/adminlog/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '112', 'normal'), ('45', 'file', '10', 'auth/adminlog/detail', '详情', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '111', 'normal'), ('46', 'file', '10', 'auth/adminlog/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '110', 'normal'), ('47', 'file', '11', 'auth/group/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '108', 'normal'), ('48', 'file', '11', 'auth/group/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '107', 'normal'), ('49', 'file', '11', 'auth/group/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '106', 'normal'), ('50', 'file', '11', 'auth/group/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '105', 'normal'), ('51', 'file', '12', 'auth/rule/index', '查看', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '103', 'normal'), ('52', 'file', '12', 'auth/rule/add', '添加', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '102', 'normal'), ('53', 'file', '12', 'auth/rule/edit', '编辑', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '101', 'normal'), ('54', 'file', '12', 'auth/rule/del', '删除', 'fa fa-circle-o', '', '', '0', '1497429920', '1497429920', '100', 'normal'), ('55', 'file', '4', 'addon/index', '查看', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('56', 'file', '4', 'addon/add', '添加', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('57', 'file', '4', 'addon/edit', '修改', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('58', 'file', '4', 'addon/del', '删除', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('59', 'file', '4', 'addon/local', '本地安装', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('60', 'file', '4', 'addon/state', '禁用启用', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('61', 'file', '4', 'addon/install', '安装', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('62', 'file', '4', 'addon/uninstall', '卸载', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('63', 'file', '4', 'addon/config', '配置', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('64', 'file', '4', 'addon/refresh', '刷新', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal'), ('65', 'file', '4', 'addon/multi', '批量更新', 'fa fa-circle-o', '', '', '0', '1502035509', '1502035509', '0', 'normal');
COMMIT;

-- ----------------------------
--  Table structure for `category`
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `flag` set('hot','index','recommend') NOT NULL DEFAULT '',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) NOT NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分类表';

-- ----------------------------
--  Records of `category`
-- ----------------------------
BEGIN;
INSERT INTO `category` VALUES ('1', '0', 'page', '官方新闻', 'news', 'recommend', '/assets/img/qrcode.png', '', '', 'news', '1495262190', '1495262190', '1', 'normal'), ('2', '0', 'page', '移动应用', 'mobileapp', 'hot', '/assets/img/qrcode.png', '', '', 'mobileapp', '1495262244', '1495262244', '2', 'normal'), ('3', '2', 'page', '微信公众号', 'wechatpublic', 'index', '/assets/img/qrcode.png', '', '', 'wechatpublic', '1495262288', '1495262288', '3', 'normal'), ('4', '2', 'page', 'Android开发', 'android', 'recommend', '/assets/img/qrcode.png', '', '', 'android', '1495262317', '1495262317', '4', 'normal'), ('5', '0', 'page', '软件产品', 'software', 'recommend', '/assets/img/qrcode.png', '', '', 'software', '1495262336', '1499681850', '5', 'normal'), ('6', '5', 'page', '网站建站', 'website', 'recommend', '/assets/img/qrcode.png', '', '', 'website', '1495262357', '1495262357', '6', 'normal'), ('7', '5', 'page', '企业管理软件', 'company', 'index', '/assets/img/qrcode.png', '', '', 'company', '1495262391', '1495262391', '7', 'normal'), ('8', '6', 'page', 'PC端', 'website-pc', 'recommend', '/assets/img/qrcode.png', '', '', 'website-pc', '1495262424', '1495262424', '8', 'normal'), ('9', '6', 'page', '移动端', 'website-mobile', 'recommend', '/assets/img/qrcode.png', '', '', 'website-mobile', '1495262456', '1495262456', '9', 'normal'), ('10', '7', 'page', 'CRM系统 ', 'company-crm', 'recommend', '/assets/img/qrcode.png', '', '', 'company-crm', '1495262487', '1495262487', '10', 'normal'), ('11', '7', 'page', 'SASS平台软件', 'company-sass', 'recommend', '/assets/img/qrcode.png', '', '', 'company-sass', '1495262515', '1495262515', '11', 'normal'), ('12', '0', 'test', '测试1', 'test1', 'recommend', '/assets/img/qrcode.png', '', '', 'test1', '1497015727', '1497015727', '12', 'normal'), ('13', '0', 'test', '测试2', 'test2', 'recommend', '/assets/img/qrcode.png', '', '', 'test2', '1497015738', '1497015738', '13', 'normal');
COMMIT;

-- ----------------------------
--  Table structure for `config`
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
--  Records of `config`
-- ----------------------------
BEGIN;
INSERT INTO `config` VALUES ('1', 'name', 'basic', '站点名称', '请填写站点名称', 'string', 'FastAdmin', '', 'required', ''), ('2', 'beian', 'basic', '备案号', '粤ICP备15054802号-4', 'string', '', '', '', ''), ('3', 'cdnurl', 'basic', 'CDN地址', '如果使用CDN云储存请配置该值', 'string', '', '', '', ''), ('4', 'version', 'basic', '版本号', '如果静态资源有变动请重新配置该值', 'string', '1.0.1', '', 'required', ''), ('5', 'timezone', 'basic', '时区', '', 'string', 'Asia/Shanghai', '', 'required', ''), ('6', 'forbiddenip', 'basic', '禁止访问IP', '一行一条记录', 'text', '', '', '', ''), ('7', 'languages', 'basic', '模块语言', '', 'array', '{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}', '', 'required', ''), ('8', 'fixedpage', 'basic', '后台默认页', '请尽量输入左侧菜单栏存在的链接', 'string', 'dashboard', '', 'required', ''), ('9', 'categorytype', 'dictionary', '分类类型', '', 'array', '{\"default\":\"默认\",\"page\":\"单页\",\"article\":\"文章\",\"test\":\"测试\"}', '', '', ''), ('10', 'configgroup', 'dictionary', '配置分组', '', 'array', '{\"basic\":\"基础配置\",\"email\":\"邮件配置\",\"dictionary\":\"字典配置\",\"user\":\"会员配置\",\"example\":\"示例分组\"}', '', '', ''), ('11', 'mail_type', 'email', '邮件发送方式', '选择邮件发送方式', 'select', '1', '[\"请选择\",\"SMTP\",\"mail()函数\"]', '', ''), ('12', 'mail_smtp_host', 'email', 'SMTP[服务器]', '错误的配置发送邮件会导致服务器超时', 'string', 'smtp.qq.com', '', '', ''), ('13', 'mail_smtp_port', 'email', 'SMTP[端口]', '(不加密默认25,SSL默认465,TLS默认587)', 'string', '465', '', '', ''), ('14', 'mail_smtp_user', 'email', 'SMTP[用户名]', '（填写完整用户名）', 'string', '10000', '', '', ''), ('15', 'mail_smtp_pass', 'email', 'SMTP[密码]', '（填写您的密码）', 'string', 'password', '', '', ''), ('16', 'mail_verify_type', 'email', 'SMTP验证方式', '（SMTP验证方式[推荐SSL]）', 'select', '2', '[\"无\",\"TLS\",\"SSL\"]', '', ''), ('17', 'mail_from', 'email', '发件人邮箱', '', 'string', '10000@qq.com', '', '', '');
COMMIT;

-- ----------------------------
--  Table structure for `test`
-- ----------------------------
DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID(单选)',
  `category_ids` varchar(100) NOT NULL COMMENT '分类ID(多选)',
  `week` enum('monday','tuesday','wednesday') NOT NULL COMMENT '星期(单选):monday=星期一,tuesday=星期二,wednesday=星期三',
  `flag` set('hot','index','recommend') NOT NULL DEFAULT '' COMMENT '标志(多选):hot=热门,index=首页,recommend=推荐',
  `genderdata` enum('male','female') NOT NULL DEFAULT 'male' COMMENT '性别(单选):male=男,female=女',
  `hobbydata` set('music','reading','swimming') NOT NULL COMMENT '爱好(多选):music=音乐,reading=读书,swimming=游泳',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `images` varchar(1500) NOT NULL DEFAULT '' COMMENT '图片组',
  `attachfile` varchar(100) NOT NULL DEFAULT '' COMMENT '附件',
  `keywords` varchar(100) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '省市',
  `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击',
  `startdate` date DEFAULT NULL COMMENT '开始日期',
  `activitytime` datetime DEFAULT NULL COMMENT '活动时间(datetime)',
  `year` year(4) DEFAULT NULL COMMENT '年',
  `times` time DEFAULT NULL COMMENT '时间',
  `refreshtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '刷新时间(int)',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `switch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开关',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `state` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT '状态值:0=禁用,1=正常,2=推荐',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='测试表';

-- ----------------------------
--  Records of `test`
-- ----------------------------
BEGIN;
INSERT INTO `test` VALUES ('1', '12', '12,13', 'monday', 'hot,index', 'male', 'music,reading', '我是一篇测试文章', '<p>我是测试内容</p>', '/assets/img/avatar.png', '/assets/img/avatar.png,/assets/img/qrcode.png', '/assets/img/avatar.png', '关键字', '描述', '广西壮族自治区/百色市/平果县', '0.00', '0', '2017-07-10', '2017-07-10 18:24:45', '2017', '18:24:45', '1499682285', '1499682526', '1499682526', '0', '1', 'normal', '1');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;



-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-09-04 18:20:39
-- 服务器版本： 5.7.18-log
-- PHP Version: 7.1.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `admin.express.com`
--
CREATE DATABASE IF NOT EXISTS `admin.express.com` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `admin.express.com`;

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `loginfailure` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录时间',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `token` varchar(59) NOT NULL DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='管理员表' ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `admin`
--

TRUNCATE TABLE `admin`;
--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `username`, `nickname`, `password`, `salt`, `avatar`, `email`, `loginfailure`, `logintime`, `createtime`, `updatetime`, `token`, `status`) VALUES
(1, 'admin', 'Admin', '075eaec83636846f51c152f29b98a2fd', 's4f3', '/assets/img/avatar.png', 'admin@fastadmin.net', 0, 1504511288, 1492186163, 1504511288, 'e148822a-2836-48fa-a139-b0f536214fc2', 'normal'),
(2, 'admin2', 'admin2', '9a28ce07ce875fbd14172a9ca5357d3c', '2dHDmj', '/assets/img/avatar.png', 'admin2@fastadmin.net', 0, 1502015003, 1492186163, 1502029266, '', 'normal'),
(3, 'admin3', 'admin3', '1c11f945dfcd808a130a8c2a8753fe62', 'WOKJEn', '/assets/img/avatar.png', 'admin3@fastadmin.net', 0, 1501980868, 1492186201, 1501982377, '', 'normal'),
(4, 'admin22', 'admin22', '1c1a0aa0c3c56a8c1a908aab94519648', 'Aybcn5', '/assets/img/avatar.png', 'admin22@fastadmin.net', 0, 0, 1492186240, 1492186240, '', 'normal'),
(5, 'admin32', 'admin32', 'ade94d5d7a7033afa7d84ac3066d0a02', 'FvYK0u', '/assets/img/avatar.png', 'admin32@fastadmin.net', 0, 0, 1492186263, 1492186263, '', 'normal');

-- --------------------------------------------------------

--
-- 表的结构 `admin_log`
--

DROP TABLE IF EXISTS `admin_log`;
CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COMMENT='管理员日志表' ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `admin_log`
--

TRUNCATE TABLE `admin_log`;
--
-- 转存表中的数据 `admin_log`
--

INSERT INTO `admin_log` (`id`, `admin_id`, `username`, `url`, `title`, `content`, `ip`, `useragent`, `createtime`) VALUES
(1, 1, 'admin', '/admin/index/login?url=/', '登录', '{\"url\":\"\\/\",\"__token__\":\"72a68e3838dfd591e068d0c270d86ada\",\"username\":\"admin\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:55.0) Gecko/20100101 Firefox/55.0', 1504511134),
(2, 1, 'admin', '/admin/index/login?url=/admin/general/profile?ref=addtabs', '登录', '{\"url\":\"\\/admin\\/general\\/profile?ref=addtabs\",\"__token__\":\"910c9264ac7d28f3c07902b8cded16d9\",\"username\":\"admin\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504511288),
(3, 1, 'admin', '/admin/general.config/edit', '常规管理 系统配置 编辑', '{\"row\":{\"name\":\"\\u5171\\u4eab\\u5feb\\u9012\\u5b9d\\u5b9d\",\"beian\":\"\",\"cdnurl\":\"\",\"version\":\"1.0.1\",\"timezone\":\"Asia\\/Shanghai\",\"forbiddenip\":\"\",\"languages\":{\"field\":{\"backend\":\"backend\",\"frontend\":\"frontend\"},\"value\":{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}},\"fixedpage\":\"dashboard\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504511520),
(4, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"service\\/index\",\"title\":\"\\u5ba2\\u670d\\u7ba1\\u7406\",\"icon\":\"fa fa-user\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u5feb\\u9012\\u5b9d\\u5b9d\\u5ba2\\u670d\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504511878),
(5, 1, 'admin', '/admin/auth/rule/edit/ids/66?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"service\",\"title\":\"\\u5ba2\\u670d\\u7ba1\\u7406\",\"icon\":\"fa fa-ambulance\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u5feb\\u9012\\u5b9d\\u5b9d\\u5ba2\\u670d\",\"status\":\"normal\"},\"ids\":\"66\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504512130),
(6, 1, 'admin', '/admin/auth/rule/edit/ids/66?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"service\",\"title\":\"\\u5ba2\\u670d\\u7ba1\\u7406\",\"icon\":\"fa fa-user\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u5feb\\u9012\\u5b9d\\u5b9d\\u5ba2\\u670d\",\"status\":\"normal\"},\"ids\":\"66\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504512199),
(7, 1, 'admin', '/admin/auth/rule/multi/ids/66', '', '{\"action\":\"\",\"ids\":\"66\",\"params\":\"ismenu=0\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504512217),
(8, 1, 'admin', '/admin/auth/rule/multi/ids/66', '', '{\"action\":\"\",\"ids\":\"66\",\"params\":\"ismenu=1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504512233),
(9, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"66\",\"name\":\"service\\/index\",\"title\":\"\\u5ba2\\u670d\\u5217\\u8868\",\"icon\":\"fa fa-circle-thin\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u5ba2\\u670d\\u5217\\u8868\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514436),
(10, 1, 'admin', '/admin/auth/rule/edit/ids/67?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"66\",\"name\":\"service\\/index\",\"title\":\"\\u5ba2\\u670d\\u5217\\u8868\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u5ba2\\u670d\\u5217\\u8868\",\"status\":\"normal\"},\"ids\":\"67\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514476),
(11, 1, 'admin', '/admin/auth/rule/add/ids/1?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"67\",\"name\":\"service\\/edit\",\"title\":\"\\u7f16\\u8f91\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u7f16\\u8f91\",\"status\":\"normal\"},\"ids\":\"1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514566),
(12, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"service\\/add\",\"title\":\"\\u6dfb\\u52a0\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u6dfb\\u52a0\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514617),
(13, 1, 'admin', '/admin/auth/rule/edit/ids/69?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"67\",\"name\":\"service\\/add\",\"title\":\"\\u6dfb\\u52a0\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\\u6dfb\\u52a0\",\"status\":\"normal\"},\"ids\":\"69\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514650),
(14, 1, 'admin', '/admin/auth/rule/edit/ids/1?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"dashboard\",\"title\":\"\\u9996\\u9875\",\"icon\":\"fa fa-dashboard\",\"weigh\":\"143\",\"condition\":\"\",\"remark\":\"\\u7528\\u4e8e\\u5c55\\u793a\\u5f53\\u524d\\u7cfb\\u7edf\\u4e2d\\u7684\\u7edf\\u8ba1\\u6570\\u636e\\u3001\\u7edf\\u8ba1\\u62a5\\u8868\\u53ca\\u91cd\\u8981\\u5b9e\\u65f6\\u6570\\u636e\\r\\n\",\"status\":\"normal\"},\"ids\":\"1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514737),
(15, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,2,3,4,66\",\"changeid\":\"5\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514773),
(16, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,4,66,5,3,2\",\"changeid\":\"1\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514812),
(17, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,4,66,3,2\",\"changeid\":\"5\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514825),
(18, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,66,3,2,4\",\"changeid\":\"4\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514836),
(19, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"5,66,3,2,4,1\",\"changeid\":\"1\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514930),
(20, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"66,3,2,4,5,1\",\"changeid\":\"5\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504514940),
(21, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"4,66,3,2,5,1\",\"changeid\":\"4\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515037),
(22, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,4,66,3,2,5\",\"changeid\":\"1\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515100),
(23, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,4,66,3,2\",\"changeid\":\"5\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515109),
(24, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,2,4,66,3\",\"changeid\":\"2\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515119),
(25, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,2,66,3,4\",\"changeid\":\"4\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515171),
(26, 1, 'admin', '/admin/auth/rule/edit/ids/9?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"5\",\"name\":\"auth\\/admin\",\"title\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"icon\":\"fa fa-user\",\"weigh\":\"14\",\"condition\":\"\",\"remark\":\"\\u4e00\\u4e2a\\u7ba1\\u7406\\u5458\\u53ef\\u4ee5\\u6709\\u591a\\u4e2a\\u89d2\\u8272\\u7ec4,\\u5de6\\u4fa7\\u7684\\u83dc\\u5355\\u6839\\u636e\\u7ba1\\u7406\\u5458\\u6240\\u62e5\\u6709\\u7684\\u6743\\u9650\\u8fdb\\u884c\\u751f\\u6210\",\"status\":\"normal\"},\"ids\":\"9\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515640),
(27, 1, 'admin', '/admin/auth/rule/edit/ids/11?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"5\",\"name\":\"auth\\/group\",\"title\":\"\\u89d2\\u8272\\u7ba1\\u7406\",\"icon\":\"fa fa-group\",\"weigh\":\"23\",\"condition\":\"\",\"remark\":\"\\u89d2\\u8272\\u7ec4\\u53ef\\u4ee5\\u6709\\u591a\\u4e2a,\\u89d2\\u8272\\u6709\\u4e0a\\u4e0b\\u7ea7\\u5c42\\u7ea7\\u5173\\u7cfb,\\u5982\\u679c\\u5b50\\u89d2\\u8272\\u6709\\u89d2\\u8272\\u7ec4\\u548c\\u7ba1\\u7406\\u5458\\u7684\\u6743\\u9650\\u5219\\u53ef\\u4ee5\\u6d3e\\u751f\\u5c5e\\u4e8e\\u81ea\\u5df1\\u7ec4\\u522b\\u4e0b\\u7ea7\\u7684\\u89d2\\u8272\\u7ec4\\u6216\\u7ba1\\u7406\\u5458\",\"status\":\"normal\"},\"ids\":\"11\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515664),
(28, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,11,12,10,9,2,8,7,6,66,67,3,4\",\"changeid\":\"11\",\"pid\":\"5\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515680),
(29, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,11,9,12,10,2,8,7,6,66,67,3,4\",\"changeid\":\"9\",\"pid\":\"5\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515688),
(30, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,11,9,10,12,2,8,7,6,66,67,3,4\",\"changeid\":\"10\",\"pid\":\"5\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515695),
(31, 1, 'admin', '/admin/auth/group/roletree', '', '{\"pid\":\"1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515766),
(32, 1, 'admin', '/admin/auth/group/add?dialog=1', '权限管理 角色管理 添加', '{\"dialog\":\"1\",\"row\":{\"rules\":\"66,67,68,69\",\"pid\":\"1\",\"name\":\"\\u5ba2\\u670d\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515851),
(33, 1, 'admin', '/admin/auth/group/roletree', '', '{\"pid\":\"1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515859),
(34, 1, 'admin', '/admin/auth/group/add?dialog=1', '权限管理 角色管理 添加', '{\"dialog\":\"1\",\"row\":{\"rules\":\"\",\"pid\":\"1\",\"name\":\"\\u8d22\\u52a1\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515874),
(35, 1, 'admin', '/admin/auth/group/roletree', '', '{\"id\":\"7\",\"pid\":\"1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504515892),
(36, 1, 'admin', '/admin/auth/rule/del/ids/8', '权限管理 规则管理 删除', '{\"action\":\"del\",\"ids\":\"8\",\"params\":\"\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516086),
(37, 1, 'admin', '/admin/auth/rule/edit/ids/2?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"general\",\"title\":\"\\u7cfb\\u7edf\\u7ba1\\u7406\",\"icon\":\"fa fa-cogs\",\"weigh\":\"33\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"2\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516228),
(38, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,11,9,10,12,2,6,7,66,67,3,4\",\"changeid\":\"6\",\"pid\":\"2\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516240),
(39, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"2\",\"name\":\"general\\/service\",\"title\":\"\\u670d\\u52a1\\u8bf4\\u660e\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516504),
(40, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"70\",\"name\":\"general\\/service\\/edit\",\"title\":\"\\u7f16\\u8f91\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516620),
(41, 1, 'admin', '/admin/auth/rule/edit/ids/70?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"2\",\"name\":\"general\\/service\\/index\",\"title\":\"\\u67e5\\u770b\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"70\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516661),
(42, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"2\",\"name\":\"general\\/service\",\"title\":\"\\u670d\\u52a1\\u8bf4\\u660e\",\"icon\":\"fa fa-file-text-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516857),
(43, 1, 'admin', '/admin/auth/rule/edit/ids/70?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"72\",\"name\":\"general\\/service\\/index\",\"title\":\"\\u67e5\\u770b\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"70\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516869),
(44, 1, 'admin', '/admin/auth/rule/edit/ids/71?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"72\",\"name\":\"general\\/service\\/edit\",\"title\":\"\\u7f16\\u8f91\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"71\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504516883),
(45, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"0\",\"pid\":\"72\",\"name\":\"general\\/service\\/del\",\"title\":\"\\u5220\\u9664\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517055),
(46, 1, 'admin', '/admin/auth/rule/multi/ids/71', '', '{\"action\":\"\",\"ids\":\"71\",\"params\":\"ismenu=0\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517066),
(47, 1, 'admin', '/admin/auth/rule/edit/ids/73?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"0\",\"pid\":\"72\",\"name\":\"general\\/service\\/del\",\"title\":\"\\u5220\\u9664\",\"icon\":\"fa fa-circle-o\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"73\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517093),
(48, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"weixin\\/notify\",\"title\":\"\\u7cfb\\u7edf\\u901a\\u77e5\",\"icon\":\"fa fa-bell\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517322),
(49, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"1,5,2,74,66,3,4\",\"changeid\":\"74\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517376),
(50, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"feedback\",\"title\":\"\\u6295\\u8bc9\\u4e0e\\u5efa\\u8bae\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517474),
(51, 1, 'admin', '/admin/ajax/weigh', '', '{\"ids\":\"74,4,3,66,2,5,1,75\",\"changeid\":\"75\",\"pid\":\"0\",\"field\":\"weigh\",\"orderway\":\"desc\",\"table\":\"auth_rule\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517505),
(52, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"kuaididuo\",\"title\":\"\\u5feb\\u9012\\u8c46\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517603),
(53, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"coupon\",\"title\":\"\\u4f18\\u60e0\\u5238\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517782),
(54, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"order\",\"title\":\"\\u8ba2\\u5355\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517817),
(55, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"user\",\"title\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517841),
(56, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"account\",\"title\":\"\\u8d22\\u52a1\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517911),
(57, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"join\",\"title\":\"\\u52a0\\u76df\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504517983),
(58, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"site\",\"title\":\"\\u7ad9\\u70b9\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518029),
(59, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"logistics\",\"title\":\"\\u7269\\u6d41\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518136),
(60, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"forbiddenword\",\"title\":\"\\u654f\\u611f\\u8bcd\\u7ba1\\u7406\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518324),
(61, 1, 'admin', '/admin/auth/rule/add?dialog=1', '权限管理 规则管理 添加', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"report\",\"title\":\"\\u6570\\u636e\\u62a5\\u8868\",\"icon\":\"fa fa-dot\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518434),
(62, 1, 'admin', '/admin/auth/rule/edit/ids/85?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"report\",\"title\":\"\\u6570\\u636e\\u62a5\\u8868\",\"icon\":\"fa fa-table\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"85\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518564),
(63, 1, 'admin', '/admin/auth/rule/edit/ids/79?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"user\",\"title\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"icon\":\"fa fa-user\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"79\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518627),
(64, 1, 'admin', '/admin/auth/rule/edit/ids/84?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"forbiddenword\",\"title\":\"\\u654f\\u611f\\u8bcd\\u7ba1\\u7406\",\"icon\":\"fa fa-th-list\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"84\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518779),
(65, 1, 'admin', '/admin/auth/rule/edit/ids/83?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"logistics\",\"title\":\"\\u7269\\u6d41\\u7ba1\\u7406\",\"icon\":\"fa fa-ambulance\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"83\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504518827),
(66, 1, 'admin', '/admin/auth/rule/edit/ids/82?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"site\",\"title\":\"\\u7ad9\\u70b9\\u7ba1\\u7406\",\"icon\":\"fa fa-server\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"82\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519024),
(67, 1, 'admin', '/admin/auth/rule/edit/ids/81?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"join\",\"title\":\"\\u52a0\\u76df\\u7ba1\\u7406\",\"icon\":\"fa fa-user-plus\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"81\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519146),
(68, 1, 'admin', '/admin/auth/rule/edit/ids/85?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"report\",\"title\":\"\\u6570\\u636e\\u62a5\\u8868\",\"icon\":\"fa fa-line-chart\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"85\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519189),
(69, 1, 'admin', '/admin/auth/rule/edit/ids/80?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"account\",\"title\":\"\\u8d22\\u52a1\\u7ba1\\u7406\",\"icon\":\"fa fa-rmb\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"80\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519246),
(70, 1, 'admin', '/admin/auth/rule/edit/ids/78?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"order\",\"title\":\"\\u8ba2\\u5355\\u7ba1\\u7406\",\"icon\":\"fa fa-list\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"78\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519400),
(71, 1, 'admin', '/admin/auth/rule/edit/ids/77?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"coupon\",\"title\":\"\\u4f18\\u60e0\\u5238\\u7ba1\\u7406\",\"icon\":\"fa fa-cc-discover\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"77\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504519999),
(72, 1, 'admin', '/admin/auth/rule/edit/ids/76?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"kuaididuo\",\"title\":\"\\u5feb\\u9012\\u8c46\\u7ba1\\u7406\",\"icon\":\"fa fa-yen\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"76\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504520070),
(73, 1, 'admin', '/admin/auth/rule/edit/ids/75?dialog=1', '权限管理 规则管理 编辑', '{\"dialog\":\"1\",\"row\":{\"ismenu\":\"1\",\"pid\":\"0\",\"name\":\"feedback\",\"title\":\"\\u6295\\u8bc9\\u4e0e\\u5efa\\u8bae\",\"icon\":\"fa fa-file\",\"weigh\":\"0\",\"condition\":\"\",\"remark\":\"\",\"status\":\"normal\"},\"ids\":\"75\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/60.0.3112.113 Chrome/60.0.3112.113 Safari/537.36', 1504520138);

-- --------------------------------------------------------

--
-- 表的结构 `attachment`
--

DROP TABLE IF EXISTS `attachment`;
CREATE TABLE IF NOT EXISTS `attachment` (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imagetype` varchar(30) NOT NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(30) NOT NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) NOT NULL DEFAULT '' COMMENT '透传数据',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `uploadtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传时间',
  `storage` enum('local','upyun') NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='附件表' ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `attachment`
--

TRUNCATE TABLE `attachment`;
--
-- 转存表中的数据 `attachment`
--

INSERT INTO `attachment` (`id`, `url`, `imagewidth`, `imageheight`, `imagetype`, `imageframes`, `filesize`, `mimetype`, `extparam`, `createtime`, `updatetime`, `uploadtime`, `storage`, `sha1`) VALUES
(1, '/assets/img/qrcode.png', '150', '150', 'png', 0, 21859, 'image/png', '', 1499681848, 1499681848, 1499681848, 'local', '17163603d0263e4838b9387ff2cd4877e8b018f6');

-- --------------------------------------------------------

--
-- 表的结构 `auth_group`
--

DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE IF NOT EXISTS `auth_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text NOT NULL COMMENT '规则ID',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='分组表';

--
-- 插入之前先把表清空（truncate） `auth_group`
--

TRUNCATE TABLE `auth_group`;
--
-- 转存表中的数据 `auth_group`
--

INSERT INTO `auth_group` (`id`, `pid`, `name`, `rules`, `createtime`, `updatetime`, `status`) VALUES
(1, 0, '超级管理员', '*', 1490883540, 149088354, 'normal'),
(2, 1, '二级管理员', '1,2,4,6,7,8,9,10,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5', 1490883540, 1502205308, 'normal'),
(3, 2, '三级管理员', '1,4,9,10,11,13,14,15,16,17,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5', 1490883540, 1502205322, 'normal'),
(4, 1, '二级管理员2', '1,4,13,14,15,16,17,55,56,57,58,59,60,61,62,63,64,65', 1490883540, 1502205350, 'normal'),
(5, 2, '三级管理员2', '1,2,6,7,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34', 1490883540, 1502205344, 'normal'),
(6, 1, '客服', '66,67,68,69', 1504515851, 1504515851, 'normal'),
(7, 1, '财务', '', 1504515874, 1504515874, 'normal');

-- --------------------------------------------------------

--
-- 表的结构 `auth_group_access`
--

DROP TABLE IF EXISTS `auth_group_access`;
CREATE TABLE IF NOT EXISTS `auth_group_access` (
  `uid` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `group_id` int(10) UNSIGNED NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限分组表';

--
-- 插入之前先把表清空（truncate） `auth_group_access`
--

TRUNCATE TABLE `auth_group_access`;
--
-- 转存表中的数据 `auth_group_access`
--

INSERT INTO `auth_group_access` (`uid`, `group_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 5),
(5, 5);

-- --------------------------------------------------------

--
-- 表的结构 `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE IF NOT EXISTS `auth_rule` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COMMENT='节点表' ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `auth_rule`
--

TRUNCATE TABLE `auth_rule`;
--
-- 转存表中的数据 `auth_rule`
--

INSERT INTO `auth_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES
(1, 'file', 0, 'dashboard', '首页', 'fa fa-dashboard', '', '用于展示当前系统中的统计数据、统计报表及重要实时数据\r\n', 1, 1497429920, 1504514737, 1, 'normal'),
(2, 'file', 0, 'general', '系统管理', 'fa fa-cogs', '', '', 1, 1497429920, 1504516228, 30, 'normal'),
(3, 'file', 0, 'category', '分类管理', 'fa fa-list\r', '', '用于统一管理网站的所有分类,分类可进行无限级分类\r', 1, 1497429920, 1497429920, 56, 'normal'),
(4, 'file', 0, 'addon', '插件管理', 'fa fa-rocket', '', '可在线安装、卸载、禁用、启用插件，同时支持添加本地插件', 1, 1502035509, 1502035509, 62, 'normal'),
(5, 'file', 0, 'auth', '权限管理', 'fa fa-group', '', '', 1, 1497429920, 1497430092, 13, 'normal'),
(6, 'file', 2, 'general/config', '系统配置', 'fa fa-cog', '', '', 1, 1497429920, 1497430683, 22, 'normal'),
(7, 'file', 2, 'general/attachment', '附件管理', 'fa fa-file-image-o', '', '主要用于管理上传到又拍云的数据或上传至本服务的上传数据\r\n', 1, 1497429920, 1497430699, 29, 'normal'),
(9, 'file', 5, 'auth/admin', '管理员列表', 'fa fa-user', '', '一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成', 1, 1497429920, 1504515640, 40, 'normal'),
(10, 'file', 5, 'auth/adminlog', '管理员日志', 'fa fa-list-alt', '', '管理员可以查看自己所拥有的权限的管理员日志', 1, 1497429920, 1497430307, 44, 'normal'),
(11, 'file', 5, 'auth/group', '角色管理', 'fa fa-group', '', '角色组可以有多个,角色有上下级层级关系,如果子角色有角色组和管理员的权限则可以派生属于自己组别下级的角色组或管理员', 1, 1497429920, 1504515664, 35, 'normal'),
(12, 'file', 5, 'auth/rule', '规则管理', 'fa fa-bars', '', '规则通常对应一个控制器的方法,同时左侧的菜单栏数据也从规则中体现,通常建议通过控制台进行生成规则节点', 1, 1497429920, 1497430581, 49, 'normal'),
(13, 'file', 1, 'dashboard/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 55, 'normal'),
(14, 'file', 1, 'dashboard/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 54, 'normal'),
(15, 'file', 1, 'dashboard/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 52, 'normal'),
(16, 'file', 1, 'dashboard/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 53, 'normal'),
(17, 'file', 1, 'dashboard/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 51, 'normal'),
(18, 'file', 6, 'general/config/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 21, 'normal'),
(19, 'file', 6, 'general/config/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 20, 'normal'),
(20, 'file', 6, 'general/config/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 19, 'normal'),
(21, 'file', 6, 'general/config/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 18, 'normal'),
(22, 'file', 6, 'general/config/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 17, 'normal'),
(23, 'file', 7, 'general/attachment/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 28, 'normal'),
(24, 'file', 7, 'general/attachment/select', '选择附件', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 27, 'normal'),
(25, 'file', 7, 'general/attachment/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 26, 'normal'),
(26, 'file', 7, 'general/attachment/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 25, 'normal'),
(27, 'file', 7, 'general/attachment/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 24, 'normal'),
(28, 'file', 7, 'general/attachment/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 23, 'normal'),
(35, 'file', 3, 'category/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 61, 'normal'),
(36, 'file', 3, 'category/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 60, 'normal'),
(37, 'file', 3, 'category/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 59, 'normal'),
(38, 'file', 3, 'category/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 58, 'normal'),
(39, 'file', 3, 'category/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 57, 'normal'),
(40, 'file', 9, 'auth/admin/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 48, 'normal'),
(41, 'file', 9, 'auth/admin/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 47, 'normal'),
(42, 'file', 9, 'auth/admin/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 46, 'normal'),
(43, 'file', 9, 'auth/admin/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 45, 'normal'),
(44, 'file', 10, 'auth/adminlog/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 43, 'normal'),
(45, 'file', 10, 'auth/adminlog/detail', '详情', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 42, 'normal'),
(46, 'file', 10, 'auth/adminlog/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 41, 'normal'),
(47, 'file', 11, 'auth/group/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 39, 'normal'),
(48, 'file', 11, 'auth/group/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 38, 'normal'),
(49, 'file', 11, 'auth/group/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 37, 'normal'),
(50, 'file', 11, 'auth/group/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 36, 'normal'),
(51, 'file', 12, 'auth/rule/index', '查看', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 34, 'normal'),
(52, 'file', 12, 'auth/rule/add', '添加', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 33, 'normal'),
(53, 'file', 12, 'auth/rule/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 32, 'normal'),
(54, 'file', 12, 'auth/rule/del', '删除', 'fa fa-circle-o', '', '', 0, 1497429920, 1497429920, 31, 'normal'),
(55, 'file', 4, 'addon/index', '查看', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 2, 'normal'),
(56, 'file', 4, 'addon/add', '添加', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 3, 'normal'),
(57, 'file', 4, 'addon/edit', '修改', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 4, 'normal'),
(58, 'file', 4, 'addon/del', '删除', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 5, 'normal'),
(59, 'file', 4, 'addon/local', '本地安装', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 6, 'normal'),
(60, 'file', 4, 'addon/state', '禁用启用', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 7, 'normal'),
(61, 'file', 4, 'addon/install', '安装', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 8, 'normal'),
(62, 'file', 4, 'addon/uninstall', '卸载', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 9, 'normal'),
(63, 'file', 4, 'addon/config', '配置', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 10, 'normal'),
(64, 'file', 4, 'addon/refresh', '刷新', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 11, 'normal'),
(65, 'file', 4, 'addon/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 1502035509, 1502035509, 12, 'normal'),
(66, 'file', 0, 'service', '客服管理', 'fa fa-user', '', '快递宝宝客服', 1, 1504511878, 1504512199, 50, 'normal'),
(67, 'file', 66, 'service/index', '客服列表', 'fa fa-circle-o', '', '客服列表', 1, 1504514436, 1504514476, 14, 'normal'),
(68, 'file', 67, 'service/edit', '编辑', 'fa fa-circle-o', '', '编辑', 1, 1504514566, 1504514566, 15, 'normal'),
(69, 'file', 67, 'service/add', '添加', 'fa fa-circle-o', '', '添加', 1, 1504514617, 1504514650, 16, 'normal'),
(70, 'file', 72, 'general/service/index', '查看', 'fa fa-circle-o', '', '', 1, 1504516504, 1504516869, 67, 'normal'),
(71, 'file', 72, 'general/service/edit', '编辑', 'fa fa-circle-o', '', '', 0, 1504516619, 1504516883, 66, 'normal'),
(72, 'file', 2, 'general/service', '服务说明', 'fa fa-file-text-o', '', '', 1, 1504516857, 1504516857, 65, 'normal'),
(73, 'file', 72, 'general/service/del', '删除', 'fa fa-circle-o', '', '', 0, 1504517055, 1504517093, 64, 'normal'),
(74, 'file', 0, 'weixin/notify', '系统通知', 'fa fa-bell', '', '', 1, 1504517322, 1504517322, 63, 'normal'),
(75, 'file', 0, 'feedback', '投诉与建议', 'fa fa-file', '', '', 1, 1504517474, 1504520137, 0, 'normal'),
(76, 'file', 0, 'kuaididuo', '快递豆管理', 'fa fa-yen', '', '', 1, 1504517603, 1504520070, 0, 'normal'),
(77, 'file', 0, 'coupon', '优惠券管理', 'fa fa-cc-discover', '', '', 1, 1504517782, 1504519999, 0, 'normal'),
(78, 'file', 0, 'order', '订单管理', 'fa fa-list', '', '', 1, 1504517817, 1504519400, 0, 'normal'),
(79, 'file', 0, 'user', '用户管理', 'fa fa-user', '', '', 1, 1504517841, 1504518627, 0, 'normal'),
(80, 'file', 0, 'account', '财务管理', 'fa fa-rmb', '', '', 1, 1504517911, 1504519245, 0, 'normal'),
(81, 'file', 0, 'join', '加盟管理', 'fa fa-user-plus', '', '', 1, 1504517983, 1504519146, 0, 'normal'),
(82, 'file', 0, 'site', '站点管理', 'fa fa-server', '', '', 1, 1504518029, 1504519024, 0, 'normal'),
(83, 'file', 0, 'logistics', '物流管理', 'fa fa-ambulance', '', '', 1, 1504518136, 1504518827, 0, 'normal'),
(84, 'file', 0, 'forbiddenword', '敏感词管理', 'fa fa-th-list', '', '', 1, 1504518324, 1504518779, 0, 'normal'),
(85, 'file', 0, 'report', '数据报表', 'fa fa-line-chart', '', '', 1, 1504518434, 1504519188, 0, 'normal');

-- --------------------------------------------------------

--
-- 表的结构 `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `flag` set('hot','index','recommend') NOT NULL DEFAULT '',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) NOT NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='分类表' ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `category`
--

TRUNCATE TABLE `category`;
--
-- 转存表中的数据 `category`
--

INSERT INTO `category` (`id`, `pid`, `type`, `name`, `nickname`, `flag`, `image`, `keywords`, `description`, `diyname`, `createtime`, `updatetime`, `weigh`, `status`) VALUES
(1, 0, 'page', '官方新闻', 'news', 'recommend', '/assets/img/qrcode.png', '', '', 'news', 1495262190, 1495262190, 1, 'normal'),
(2, 0, 'page', '移动应用', 'mobileapp', 'hot', '/assets/img/qrcode.png', '', '', 'mobileapp', 1495262244, 1495262244, 2, 'normal'),
(3, 2, 'page', '微信公众号', 'wechatpublic', 'index', '/assets/img/qrcode.png', '', '', 'wechatpublic', 1495262288, 1495262288, 3, 'normal'),
(4, 2, 'page', 'Android开发', 'android', 'recommend', '/assets/img/qrcode.png', '', '', 'android', 1495262317, 1495262317, 4, 'normal'),
(5, 0, 'page', '软件产品', 'software', 'recommend', '/assets/img/qrcode.png', '', '', 'software', 1495262336, 1499681850, 5, 'normal'),
(6, 5, 'page', '网站建站', 'website', 'recommend', '/assets/img/qrcode.png', '', '', 'website', 1495262357, 1495262357, 6, 'normal'),
(7, 5, 'page', '企业管理软件', 'company', 'index', '/assets/img/qrcode.png', '', '', 'company', 1495262391, 1495262391, 7, 'normal'),
(8, 6, 'page', 'PC端', 'website-pc', 'recommend', '/assets/img/qrcode.png', '', '', 'website-pc', 1495262424, 1495262424, 8, 'normal'),
(9, 6, 'page', '移动端', 'website-mobile', 'recommend', '/assets/img/qrcode.png', '', '', 'website-mobile', 1495262456, 1495262456, 9, 'normal'),
(10, 7, 'page', 'CRM系统 ', 'company-crm', 'recommend', '/assets/img/qrcode.png', '', '', 'company-crm', 1495262487, 1495262487, 10, 'normal'),
(11, 7, 'page', 'SASS平台软件', 'company-sass', 'recommend', '/assets/img/qrcode.png', '', '', 'company-sass', 1495262515, 1495262515, 11, 'normal'),
(12, 0, 'test', '测试1', 'test1', 'recommend', '/assets/img/qrcode.png', '', '', 'test1', 1497015727, 1497015727, 12, 'normal'),
(13, 0, 'test', '测试2', 'test2', 'recommend', '/assets/img/qrcode.png', '', '', 'test2', 1497015738, 1497015738, 13, 'normal');

-- --------------------------------------------------------

--
-- 表的结构 `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='系统配置';

--
-- 插入之前先把表清空（truncate） `config`
--

TRUNCATE TABLE `config`;
--
-- 转存表中的数据 `config`
--

INSERT INTO `config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`) VALUES
(1, 'name', 'basic', '站点名称', '请填写站点名称', 'string', '共享快递宝宝', '', 'required', ''),
(2, 'beian', 'basic', '备案号', '粤ICP备15054802号-4', 'string', '', '', '', ''),
(3, 'cdnurl', 'basic', 'CDN地址', '如果使用CDN云储存请配置该值', 'string', '', '', '', ''),
(4, 'version', 'basic', '版本号', '如果静态资源有变动请重新配置该值', 'string', '1.0.1', '', 'required', ''),
(5, 'timezone', 'basic', '时区', '', 'string', 'Asia/Shanghai', '', 'required', ''),
(6, 'forbiddenip', 'basic', '禁止访问IP', '一行一条记录', 'text', '', '', '', ''),
(7, 'languages', 'basic', '模块语言', '', 'array', '{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}', '', 'required', ''),
(8, 'fixedpage', 'basic', '后台默认页', '请尽量输入左侧菜单栏存在的链接', 'string', 'dashboard', '', 'required', ''),
(9, 'categorytype', 'dictionary', '分类类型', '', 'array', '{\"default\":\"默认\",\"page\":\"单页\",\"article\":\"文章\",\"test\":\"测试\"}', '', '', ''),
(10, 'configgroup', 'dictionary', '配置分组', '', 'array', '{\"basic\":\"基础配置\",\"email\":\"邮件配置\",\"dictionary\":\"字典配置\",\"user\":\"会员配置\",\"example\":\"示例分组\"}', '', '', ''),
(11, 'mail_type', 'email', '邮件发送方式', '选择邮件发送方式', 'select', '1', '[\"请选择\",\"SMTP\",\"mail()函数\"]', '', ''),
(12, 'mail_smtp_host', 'email', 'SMTP[服务器]', '错误的配置发送邮件会导致服务器超时', 'string', 'smtp.qq.com', '', '', ''),
(13, 'mail_smtp_port', 'email', 'SMTP[端口]', '(不加密默认25,SSL默认465,TLS默认587)', 'string', '465', '', '', ''),
(14, 'mail_smtp_user', 'email', 'SMTP[用户名]', '（填写完整用户名）', 'string', '10000', '', '', ''),
(15, 'mail_smtp_pass', 'email', 'SMTP[密码]', '（填写您的密码）', 'string', 'password', '', '', ''),
(16, 'mail_verify_type', 'email', 'SMTP验证方式', '（SMTP验证方式[推荐SSL]）', 'select', '2', '[\"无\",\"TLS\",\"SSL\"]', '', ''),
(17, 'mail_from', 'email', '发件人邮箱', '', 'string', '10000@qq.com', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `test`
--

DROP TABLE IF EXISTS `test`;
CREATE TABLE IF NOT EXISTS `test` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID(单选)',
  `category_ids` varchar(100) NOT NULL COMMENT '分类ID(多选)',
  `week` enum('monday','tuesday','wednesday') NOT NULL COMMENT '星期(单选):monday=星期一,tuesday=星期二,wednesday=星期三',
  `flag` set('hot','index','recommend') NOT NULL DEFAULT '' COMMENT '标志(多选):hot=热门,index=首页,recommend=推荐',
  `genderdata` enum('male','female') NOT NULL DEFAULT 'male' COMMENT '性别(单选):male=男,female=女',
  `hobbydata` set('music','reading','swimming') NOT NULL COMMENT '爱好(多选):music=音乐,reading=读书,swimming=游泳',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `images` varchar(1500) NOT NULL DEFAULT '' COMMENT '图片组',
  `attachfile` varchar(100) NOT NULL DEFAULT '' COMMENT '附件',
  `keywords` varchar(100) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '省市',
  `price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '价格',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点击',
  `startdate` date DEFAULT NULL COMMENT '开始日期',
  `activitytime` datetime DEFAULT NULL COMMENT '活动时间(datetime)',
  `year` year(4) DEFAULT NULL COMMENT '年',
  `times` time DEFAULT NULL COMMENT '时间',
  `refreshtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '刷新时间(int)',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `switch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开关',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `state` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT '状态值:0=禁用,1=正常,2=推荐',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='测试表' ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `test`
--

TRUNCATE TABLE `test`;
--
-- 转存表中的数据 `test`
--

INSERT INTO `test` (`id`, `category_id`, `category_ids`, `week`, `flag`, `genderdata`, `hobbydata`, `title`, `content`, `image`, `images`, `attachfile`, `keywords`, `description`, `city`, `price`, `views`, `startdate`, `activitytime`, `year`, `times`, `refreshtime`, `createtime`, `updatetime`, `weigh`, `switch`, `status`, `state`) VALUES
(1, 12, '12,13', 'monday', 'hot,index', 'male', 'music,reading', '我是一篇测试文章', '<p>我是测试内容</p>', '/assets/img/avatar.png', '/assets/img/avatar.png,/assets/img/qrcode.png', '/assets/img/avatar.png', '关键字', '描述', '广西壮族自治区/百色市/平果县', 0.00, 0, '2017-07-10', '2017-07-10 18:24:45', 2017, '18:24:45', 1499682285, 1499682526, 1499682526, 0, 1, 'normal', '1');
COMMIT;
