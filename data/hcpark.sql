/*
 Navicat Premium Data Transfer

 Source Server         : 本地服务器
 Source Server Type    : MySQL
 Source Server Version : 50719
 Source Host           : localhost
 Source Database       : hcpark

 Target Server Type    : MySQL
 Target Server Version : 50719
 File Encoding         : utf-8

 Date: 08/09/2017 13:55:46 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `tb_action_log`
-- ----------------------------
DROP TABLE IF EXISTS `tb_action_log`;
CREATE TABLE `tb_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `action_name` varchar(50) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` bigint(20) NOT NULL COMMENT '执行行为者ip',
  `model` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  PRIMARY KEY (`id`),
  KEY `action_ip_ix` (`action_ip`),
  KEY `action_id_ix` (`action_id`),
  KEY `user_id_ix` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';

-- ----------------------------
--  Records of `tb_action_log`
-- ----------------------------
BEGIN;
INSERT INTO `tb_action_log` VALUES ('1', '0', '管理员登入', '1', '3232235530', 'Member', '0', '用户:admin12登入后台。', '1', '1473709579'), ('2', '0', '管理员登入', '1', '3232235786', 'Member', '0', '用户:admin12登入后台。', '1', '1473730015'), ('3', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1475995248'), ('4', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1476949387'), ('5', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1478673885'), ('6', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1479195786'), ('7', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1479202928'), ('8', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480298194'), ('9', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480298297'), ('10', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480381515'), ('11', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480467517'), ('12', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480555879'), ('13', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1480908149'), ('14', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1481245314'), ('15', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1481245492'), ('16', '0', '管理员登入', '2', '2130706433', 'Member', '0', '用户:test登入后台。', '1', '1481245766'), ('17', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1481245860'), ('18', '0', '管理员登入', '2', '2130706433', 'Member', '0', '用户:test登入后台。', '1', '1481245943'), ('19', '0', '管理员登入', '2', '2130706433', 'Member', '0', '用户:test登入后台。', '1', '1481245979'), ('20', '0', '管理员登入', '1', '2130706433', 'Member', '0', '用户:admin12登入后台。', '1', '1481249893'), ('21', '0', '管理员登入', '1', '3232235786', 'Member', '0', '用户:admin12登入后台。', '1', '1500969324'), ('22', '0', '管理员登入', '1', '3232235786', 'Member', '0', '用户:admin12登入后台。', '1', '1501054317'), ('23', '0', '管理员登入', '1', '1033094521', 'Member', '0', '用户:admin12登入后台。', '1', '1501058640'), ('24', '0', '管理员登入', '1', '1033094521', 'Member', '0', '用户:admin12登入后台。', '1', '1501063062'), ('25', '0', '管理员登入', '1', '1033094521', 'Member', '0', '用户:admin12登入后台。', '1', '1501118364'), ('26', '0', '管理员登入', '1', '3232235786', 'Member', '0', '用户:admin12登入后台。', '1', '1501119071'), ('27', '0', '管理员登入', '1', '3232235786', 'Member', '0', '用户:admin12登入后台。', '1', '1501553167');
COMMIT;

-- ----------------------------
--  Table structure for `tb_auth_extend`
-- ----------------------------
DROP TABLE IF EXISTS `tb_auth_extend`;
CREATE TABLE `tb_auth_extend` (
  `group_id` mediumint(10) unsigned NOT NULL COMMENT '用户id',
  `extend_id` mediumint(8) unsigned NOT NULL COMMENT '扩展表中数据的id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '扩展类型标识 1:栏目分类权限;2:模型权限',
  UNIQUE KEY `group_extend_type` (`group_id`,`extend_id`,`type`),
  KEY `uid` (`group_id`),
  KEY `group_id` (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组与分类的对应关系表';

-- ----------------------------
--  Table structure for `tb_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `tb_auth_group`;
CREATE TABLE `tb_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型，2默认',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='系统用户组';

-- ----------------------------
--  Records of `tb_auth_group`
-- ----------------------------
BEGIN;
INSERT INTO `tb_auth_group` VALUES ('1', 'admin', '1', '超级管理员', '', '1', ''), ('2', 'admin', '1', '管理员', '', '1', ''), ('3', 'admin', '1', '业务组', '', '1', ''), ('4', 'admin', '1', '财务组', '', '1', '20,39,42,44,45,46,52,53,55,56,58,59,61,66'), ('5', 'admin', '1', '默认组', '', '1', '1,2,3,4,5,6,7,8,12,13,14,16,17,18,19,21,22,23,24,25,26,27,34,36,38,41,42,43,53,55,56,58,59,61,66,70,76,77,78,79,80,81,82,83,84,85,86,87,95,96,97,98,99');
COMMIT;

-- ----------------------------
--  Table structure for `tb_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `tb_auth_group_access`;
CREATE TABLE `tb_auth_group_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='用户和组对应权限';

-- ----------------------------
--  Records of `tb_auth_group_access`
-- ----------------------------
BEGIN;
INSERT INTO `tb_auth_group_access` VALUES ('1', '1', '1'), ('2', '15', '4'), ('17', '2', '5');
COMMIT;

-- ----------------------------
--  Table structure for `tb_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `tb_auth_rule`;
CREATE TABLE `tb_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COMMENT='权限列表';

-- ----------------------------
--  Records of `tb_auth_rule`
-- ----------------------------
BEGIN;
INSERT INTO `tb_auth_rule` VALUES ('1', 'admin', '1', 'admin/User/add', '新增', '1', '1'), ('2', 'admin', '1', 'admin/User/edit', '编辑', '1', ''), ('3', 'admin', '1', 'admin/User/del', '删除', '1', ''), ('4', 'admin', '1', 'admin/User/changeStatus', '修改状态', '1', ''), ('5', 'admin', '1', 'admin/Action/add', '新增', '1', ''), ('6', 'admin', '1', 'admin/Action/edit', '编辑', '1', ''), ('7', 'admin', '1', 'admin/Action/del', '删除', '1', ''), ('8', 'admin', '1', 'admin/Action/changeStatus', '修改状态', '1', ''), ('9', 'admin', '1', 'admin/Auth/add', '新增', '-1', ''), ('10', 'admin', '1', 'admin/Auth/edit', '编辑', '-1', ''), ('11', 'admin', '1', 'admin/Auth/del', '删除', '-1', ''), ('12', 'admin', '1', 'admin/Auth/changeStatus', '修改状态', '1', ''), ('13', 'admin', '1', 'admin/Auth/user', '成员授权', '1', ''), ('14', 'admin', '1', 'admin/Auth/access', '访问授权', '1', ''), ('15', 'admin', '1', 'admin/Auth/group', '授权组', '-1', ''), ('16', 'admin', '1', 'admin/Auth/addToGroup', '保存成员授权', '1', ''), ('17', 'admin', '1', 'admin/Auth/removeFromGroup', '接触授权', '1', ''), ('18', 'admin', '1', 'admin/Action/view', '日志详情', '1', ''), ('19', 'admin', '1', 'admin/Action/remove', '删除日志', '1', ''), ('20', 'admin', '2', '1', '首页', '-1', ''), ('21', 'admin', '1', 'admin/User/index', '用户信息', '1', ''), ('22', 'admin', '2', 'admin/User/index', '后台用户', '1', ''), ('23', 'admin', '1', 'admin/Auth/index', '权限控制', '1', ''), ('24', 'admin', '2', 'admin/System/index', '系统配置', '1', ''), ('25', 'admin', '1', 'admin/Action/index', '用户行为', '1', ''), ('26', 'admin', '2', 'admin/Addons/index', '扩展插件', '1', ''), ('27', 'admin', '1', 'admin/Action/log', '行为日志', '1', ''), ('28', 'admin', '1', 'admin/Member/company', '游戏厂商', '-1', ''), ('29', 'admin', '1', 'admin/Project/index', '发布中的专案', '-1', ''), ('30', 'admin', '1', 'admin/Project/apply', '申请名单', '-1', ''), ('31', 'admin', '1', 'admin/Member/anchor', '视频主播', '-1', ''), ('32', 'admin', '1', 'admin/Member/companyDetail', '厂商详情', '-1', ''), ('33', 'admin', '1', 'admin/Member/anchorDetail', '主播详情', '-1', ''), ('34', 'admin', '1', 'admin/Config/group', '网站配置', '1', ''), ('35', 'admin', '2', 'admin/Project/index', '推广专案', '-1', ''), ('36', 'admin', '1', 'admin/Config/index', '配置属性', '1', ''), ('37', 'admin', '2', 'admin/Member/index', '前台用户', '-1', ''), ('38', 'admin', '1', 'admin/Menu/index', '菜单管理', '1', ''), ('39', 'admin', '2', 'admin/Finance/index', '财务结算', '-1', ''), ('40', 'admin', '1', 'admin/Channel/index', '业务导航', '-1', ''), ('41', 'admin', '1', 'admin/Database/export', '备份数据库', '1', ''), ('42', 'admin', '2', 'admin/Message/index', '消息管理', '1', ''), ('43', 'admin', '1', 'admin/Database/import', '还原数据库', '1', ''), ('44', 'admin', '1', 'admin/Finance/project', '结算专案', '-1', ''), ('45', 'admin', '1', 'admin/Finance/anchor', '支付主播', '-1', ''), ('46', 'admin', '1', 'admin/Finance/company', '厂商支付', '-1', ''), ('47', 'admin', '1', 'admin/Project/info', '详情', '-1', ''), ('48', 'admin', '1', 'admin/Project/check', '审核', '-1', ''), ('49', 'admin', '1', 'admin/Project/anchor', '管理视频', '-1', ''), ('50', 'admin', '1', 'admin/Project/video', '查看视频', '-1', ''), ('51', 'admin', '1', 'admin/Project/anchordetail', '主播详情', '-1', ''), ('52', 'admin', '1', 'admin/Finance/view', '查看详情', '-1', ''), ('53', 'admin', '1', 'admin/Message/view', '浏览', '1', ''), ('54', 'admin', '1', 'admin/Project/checkList', '提交审核的专案', '-1', ''), ('55', 'admin', '1', 'admin/Message/send', '发送消息', '1', ''), ('56', 'admin', '1', 'admin/Message/inbox', '收件箱', '1', ''), ('57', 'admin', '1', 'admin/Project/makeList', '制作中的专案', '-1', ''), ('58', 'admin', '1', 'admin/Message/index', '发件箱', '1', ''), ('59', 'admin', '1', 'admin/Message/draft', '草稿箱', '1', ''), ('60', 'admin', '1', 'admin/Project/finishList', '已完结的专案', '-1', ''), ('61', 'admin', '1', 'admin/Message/trash', '回收站', '1', ''), ('62', 'admin', '1', 'admin/Vote/video', '参赛视频', '-1', ''), ('63', 'admin', '1', 'admin/Vote/index', '投票统计', '-1', ''), ('64', 'admin', '1', 'admin/Vote/expert', '专家投票', '-1', ''), ('65', 'admin', '2', 'admin/Vote/index', '投票管理', '-1', ''), ('66', 'admin', '1', 'admin/Index/index', '首页', '1', ''), ('71', 'admin', '1', 'admin/Vote/show', '显示详情', '-1', ''), ('67', 'admin', '1', 'admin/Project/finish', '完结专案', '-1', ''), ('68', 'admin', '1', 'admin/Project/edit', '编辑专案', '-1', ''), ('69', 'admin', '1', 'admin/Project/addVideoData', '添加数据', '-1', ''), ('70', 'admin', '2', 'admin/Index/index', '首页', '1', ''), ('72', 'admin', '1', 'admin/Vote/vote', '投票', '-1', ''), ('73', 'admin', '1', 'admin/Finance/add', '添加支付', '-1', ''), ('74', 'admin', '1', 'admin/Finance/typeInfo', '类型信息', '-1', ''), ('75', 'admin', '1', 'admin/Finance/getInfo', '专案信息', '-1', ''), ('76', 'admin', '1', 'admin/Message/delete', '删除', '1', ''), ('77', 'admin', '1', 'admin/Message/moveToTrash', '移动到回收站', '1', ''), ('78', 'admin', '1', 'admin/User/updateHeader', '上传头像', '1', ''), ('79', 'admin', '1', 'admin/User/update', '更新信息', '1', ''), ('80', 'admin', '1', 'admin/Auth/createGroup', '创建组', '1', ''), ('81', 'admin', '1', 'admin/Auth/editGroup', '编辑组', '1', ''), ('82', 'admin', '1', 'admin/auth/updateRules', '更新节点', '1', ''), ('83', 'admin', '1', 'admin/Config/add', '新增配置', '1', ''), ('84', 'admin', '1', 'admin/Config/edit', '编辑', '1', ''), ('85', 'admin', '1', 'admin/Config/save', '批量保存', '1', ''), ('86', 'admin', '1', 'admin/Config/del', '删除配置', '1', ''), ('87', 'admin', '1', 'admin/Config/sort', '排序', '1', ''), ('88', 'admin', '1', 'admin/Menu/add', '新增', '1', ''), ('89', 'admin', '1', 'admin/Menu/edit', '编辑', '1', ''), ('90', 'admin', '1', 'admin/Menu/del', '删除', '1', ''), ('91', 'admin', '1', 'admin/Menu/toogleHide', '是否隐藏', '1', ''), ('92', 'admin', '1', 'admin/Menu/toogleDev', '是否开发', '1', ''), ('93', 'admin', '1', 'admin/Menu/sort', '排序', '1', ''), ('94', 'admin', '1', 'admin/Menu/getInfo', '菜单信息', '1', ''), ('95', 'admin', '1', 'admin/Database/optimize', '优化表', '1', ''), ('96', 'admin', '1', 'admin/Database/repair', '修复表', '1', ''), ('97', 'admin', '1', 'admin/Database/del', '删除备份', '1', ''), ('98', 'admin', '1', 'admin/Update/index', '补丁管理', '1', ''), ('99', 'admin', '1', 'admin/Update/update', '升级系统', '1', '');
COMMIT;

-- ----------------------------
--  Table structure for `tb_config`
-- ----------------------------
DROP TABLE IF EXISTS `tb_config`;
CREATE TABLE `tb_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `value` text COMMENT '配置值',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `type` (`type`),
  KEY `group` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COMMENT='动态配置信息';

-- ----------------------------
--  Records of `tb_config`
-- ----------------------------
BEGIN;
INSERT INTO `tb_config` VALUES ('1', 'web_site_title', '1', '网站标题', '1', '', '网站标题前台显示标题', '1378898976', '1379235274', '1', '超级后台管理系统', '0'), ('2', 'web_site_description', '2', '网站描述', '1', '', '网站搜索引擎描述', '1378898976', '1379235841', '1', '广告管理平台', '1'), ('3', 'web_site_keyword', '2', '网站关键字', '1', '', '网站搜索引擎关键字', '1378898976', '1381390100', '1', '', '8'), ('4', 'web_site_close', '4', '关闭站点', '1', '0:关闭,1:开启', '站点关闭后其他用户不能访问，管理员可以正常访问', '1378898976', '1379235296', '1', '1', '1'), ('9', 'config_type_list', '3', '配置类型列表', '4', '', '主要用于数据解析和页面表单的生成', '1378898976', '1379235348', '1', '0:数字\r\n1:字符\r\n2:文本\r\n3:数组\r\n4:枚举', '2'), ('10', 'web_site_icp', '1', '网站备案号', '1', '', '设置在网站底部显示的备案号，如“沪icp备12007941号-2', '1378900335', '1379235859', '1', '', '9'), ('11', 'document_position', '3', '文档推荐位', '2', '', '文档推荐位，推荐到多个位置key值相加即可', '1379053380', '1379235329', '1', '1:列表推荐\r\n2:频道推荐\r\n4:首页推荐', '3'), ('12', 'document_display', '3', '文档可见性', '2', '', '文章可见性仅影响前台显示，后台不收影响', '1379056370', '1379235322', '1', '0:所有人可见\r\n1:仅注册会员可见\r\n2:仅管理员可见', '4'), ('13', 'color_style', '4', '后台色系', '1', 'default_color:默认\r\nblue_color:紫罗兰', '后台颜色风格', '1379122533', '1379235904', '1', 'default_color', '10'), ('20', 'config_group_list', '3', '配置分组', '4', '', '配置分组', '1379228036', '1469757555', '1', '1:基本\r\n2:内容\r\n3:用户\r\n4:系统\r\n5:业务\r\n6:SMTP', '4'), ('21', 'hooks_type', '3', '钩子的类型', '4', '', '类型 1-用于扩展显示内容，2-用于扩展业务处理', '1379313397', '1379313407', '1', '1:视图\r\n2:控制器', '6'), ('22', 'auth_config', '3', 'auth配置', '4', '', '自定义auth.class.php类配置', '1379409310', '1379409564', '1', 'auth_on:1\r\nauth_type:2', '8'), ('23', 'open_draftbox', '4', '是否开启草稿功能', '2', '0:关闭草稿功能\r\n1:开启草稿功能\r\n', '新增文章时的草稿功能配置', '1379484332', '1379484591', '1', '1', '1'), ('24', 'draft_aotosave_interval', '0', '自动保存草稿时间', '2', '', '自动保存草稿的时间间隔，单位：秒', '1379484574', '1386143323', '1', '60', '2'), ('25', 'list_rows', '0', '后台每页记录数', '2', '', '后台数据每页显示记录数', '1379503896', '1380427745', '1', '10', '10'), ('26', 'user_allow_register', '4', '是否允许用户注册', '3', '0:关闭注册\r\n1:允许注册', '是否开放用户注册', '1379504487', '1379504580', '1', '0', '3'), ('27', 'codemirror_theme', '4', '预览插件的codemirror主题', '4', '3024-day:3024 day\r\n3024-night:3024 night\r\nambiance:ambiance\r\nbase16-dark:base16 dark\r\nbase16-light:base16 light\r\nblackboard:blackboard\r\ncobalt:cobalt\r\neclipse:eclipse\r\nelegant:elegant\r\nerlang-dark:erlang-dark\r\nlesser-dark:lesser-dark\r\nmidnight:midnight', '详情见codemirror官网', '1379814385', '1384740813', '1', 'ambiance', '3'), ('28', 'data_backup_path', '1', '数据库备份根路径', '4', '', '路径必须以 / 结尾', '1381482411', '1381482411', '1', './data/', '5'), ('29', 'data_backup_part_size', '0', '数据库备份卷大小', '4', '', '该值用于限制压缩后的分卷最大长度。单位：b；建议设置20m', '1381482488', '1381729564', '1', '20971520', '7'), ('30', 'data_backup_compress', '4', '数据库备份文件是否启用压缩', '4', '0:不压缩\r\n1:启用压缩', '压缩备份文件需要php环境支持gzopen,gzwrite函数', '1381713345', '1381729544', '1', '1', '9'), ('31', 'data_backup_compress_level', '4', '数据库备份文件压缩级别', '4', '1:普通\r\n4:一般\r\n9:最高', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '1381713408', '1381713408', '1', '9', '10'), ('32', 'develop_mode', '4', '开启开发者模式', '4', '0:关闭\r\n1:开启', '是否开启开发者模式', '1383105995', '1383291877', '1', '1', '11'), ('33', 'allow_visit', '3', '不受限控制器方法', '0', '', '', '1386644047', '1386644741', '1', '0:article/draftbox\r\n1:article/mydocument\r\n2:category/tree\r\n3:index/verify\r\n4:file/upload\r\n5:file/download\r\n6:user/updatepassword\r\n7:user/updatenickname\r\n8:user/submitpassword\r\n9:user/submitnickname\r\n10:file/uploadpicture', '0'), ('34', 'deny_visit', '3', '超管专限控制器方法', '0', '', '仅超级管理员可访问的控制器方法', '1386644141', '1386644659', '1', '0:addons/addhook\r\n1:addons/edithook\r\n2:addons/delhook\r\n3:addons/updatehook\r\n4:admin/getmenus\r\n5:admin/recordlist\r\n6:authmanager/updaterules\r\n7:authmanager/tree', '0'), ('35', 'reply_list_rows', '0', '回复列表每页条数', '2', '', '', '1386645376', '1387178083', '1', '10', '0'), ('36', 'admin_allow_ip', '2', '后台允许访问ip', '4', '', '多个用逗号分隔，如果不配置表示不限制ip访问', '1387165454', '1387165553', '1', '', '12'), ('37', 'show_page_trace', '4', '是否显示页面trace', '4', '0:关闭\r\n1:开启', '是否显示页面trace信息', '1387165685', '1387165685', '1', '0', '1'), ('38', 'web_company_name', '1', '公司名称', '1', '', '页面显示公司名字', '0', '0', '1', '虚空之翼', '0'), ('39', 'budget_list', '3', '预算额列表', '5', '', '', '0', '0', '1', '1:1,000以下\r\n2:1,000 -- 2,999\r\n3:3,000 -- 9,999\r\n4:10,000 -- 99,999\r\n5:100,000及以上', '0'), ('40', 'project_status_list', '3', '专案的状态', '5', '1:提交发布\r\n2:审核通过\r\n3:接案制作中\r\n4:完结', '', '0', '0', '1', '1:提交审核\r\n2:审核通过\r\n3:报名签约\r\n4:专案制作\r\n5:完结', '0'), ('41', 'message_type', '3', '消息发送对类型', '4', '', '', '0', '0', '1', '1:系统群发\r\n2:至厂商\r\n3:至主播', '0'), ('42', 'web_version', '0', '版本号', '1', '', '', '0', '0', '1', '0.8', '0'), ('43', 'mail_smtp', '1', 'stmp服务器', '6', '', '邮件的stmp服务器', '0', '0', '1', '', '0'), ('44', 'mail_username', '1', '邮箱帐号', '6', '', '发送服务器的帐号名', '0', '0', '1', '', '0'), ('45', 'mail_password', '1', '帐号密码', '6', '', '', '0', '0', '1', '', '0'), ('46', 'web_url', '1', '网站域名', '1', '', '网站的域名地址', '0', '0', '1', 'http://www.admin.com', '0');
COMMIT;

-- ----------------------------
--  Table structure for `tb_feedback`
-- ----------------------------
DROP TABLE IF EXISTS `tb_feedback`;
CREATE TABLE `tb_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '主题',
  `content` text COMMENT '反馈内容',
  `company` varchar(255) DEFAULT NULL COMMENT '反馈人-企业名称',
  `name` varchar(255) DEFAULT NULL COMMENT '反馈人-姓名',
  `mobile` varchar(255) DEFAULT NULL COMMENT '反馈人-手机号码',
  `reply` text,
  `create_user` varchar(200) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `reply_content` text,
  `reply_user` varchar(200) DEFAULT NULL,
  `reply_time` int(11) DEFAULT NULL,
  `park_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0未回复。1已回复',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='反馈';

-- ----------------------------
--  Table structure for `tb_file`
-- ----------------------------
DROP TABLE IF EXISTS `tb_file`;
CREATE TABLE `tb_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `savename` varchar(100) NOT NULL DEFAULT '' COMMENT '保存名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件保存路径',
  `thumbnail` varchar(255) DEFAULT '' COMMENT '图片缩略图',
  `ext` char(5) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `type` char(40) NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '文件保存位置',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '远程地址',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_md5` (`md5`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文件表';

-- ----------------------------
--  Records of `tb_file`
-- ----------------------------
BEGIN;
INSERT INTO `tb_file` VALUES ('1', 'logo.png', '94898592cf5133671555f5ab861ac3de.png', 'public/uploads/file/20161209/94898592cf5133671555f5ab861ac3de.png', null, 'png', 'image/png', '38592', 'ab5ad0c90ad269c3c199a0854874dc20', '8440199290a0f36f3dbb5885078c70129e0be6e7', '1', '', '1481266318'), ('2', 'logo_meitu_1.jpg', 'dc9194318c22aee62bb7a33101123807.jpg', 'public/uploads/file/20161209/dc9194318c22aee62bb7a33101123807.jpg', null, 'jpg', 'image/jpeg', '50069', '32a6f7854d40a9c6a1e2c99f8ec31ddb', '2a22671f69699fec2d23833b5ffbc660e3d16203', '1', '', '1481267441');
COMMIT;

-- ----------------------------
--  Table structure for `tb_mail`
-- ----------------------------
DROP TABLE IF EXISTS `tb_mail`;
CREATE TABLE `tb_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(100) DEFAULT '' COMMENT '发件人',
  `to` varchar(100) DEFAULT '' COMMENT '收件人',
  `subject` varchar(255) DEFAULT '' COMMENT '标题',
  `body` text COMMENT '内容',
  `attachment` varchar(255) DEFAULT NULL COMMENT '附件',
  `create_time` int(11) DEFAULT '0',
  `create_user` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `delete_time` int(11) DEFAULT '0',
  `send_time` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态，-1删除、0草稿、1发送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件发送记录';

-- ----------------------------
--  Table structure for `tb_mail_signed`
-- ----------------------------
DROP TABLE IF EXISTS `tb_mail_signed`;
CREATE TABLE `tb_mail_signed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_type` tinyint(4) DEFAULT '1' COMMENT '用户类型，1后台、2厂商、3主播',
  `signed` varchar(255) DEFAULT '' COMMENT '随机字符串验证信息',
  `effective_time` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `type` tinyint(4) DEFAULT '1' COMMENT '邮件类型，1激活帐号、2重置密码',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态，0无效、1有效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件验证信息';

-- ----------------------------
--  Table structure for `tb_member`
-- ----------------------------
DROP TABLE IF EXISTS `tb_member`;
CREATE TABLE `tb_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` char(100) DEFAULT '' COMMENT '用户名',
  `password` char(32) DEFAULT NULL,
  `park_id` int(11) DEFAULT '0' COMMENT '对应园区id',
  `nickname` char(32) DEFAULT '' COMMENT '昵称',
  `header` varchar(255) DEFAULT NULL,
  `email` char(32) DEFAULT NULL,
  `mobile` char(15) DEFAULT NULL,
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `qq` char(10) NOT NULL DEFAULT '' COMMENT 'qq号',
  `score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `login` int(11) DEFAULT NULL,
  `reg_time` int(11) DEFAULT NULL,
  `reg_ip` bigint(20) DEFAULT NULL,
  `last_login_time` int(11) DEFAULT NULL,
  `last_login_ip` bigint(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员状态，0禁用，1正常',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
--  Records of `tb_member`
-- ----------------------------
BEGIN;
INSERT INTO `tb_member` VALUES ('1', 'admin', '02571bc54a0a4c08451a187d1929b208', '0', 'admin12', '/uploads/header/2017-07-25/header_1500969355.png', '15161@qq.com', '18912345678', '0', '2016-05-23', '', '0', '114', null, null, '1501553167', '3232235786', '1'), ('2', 'test', 'ddfa43dd9b4565e5ad0b44e217421757', '0', 'test', null, '164166@qq.com', null, '0', null, '', '0', null, '1481245755', '2130706433', '1481245979', '2130706433', '1');
COMMIT;

-- ----------------------------
--  Table structure for `tb_menu`
-- ----------------------------
DROP TABLE IF EXISTS `tb_menu`;
CREATE TABLE `tb_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `icon` varchar(50) DEFAULT '',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `level` tinyint(4) DEFAULT '1' COMMENT '菜单等级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COMMENT='后台系统菜单';

-- ----------------------------
--  Records of `tb_menu`
-- ----------------------------
BEGIN;
INSERT INTO `tb_menu` VALUES ('1', '首页', 'fa-th-large', '0', '1', '1', 'Index/index', '0', '', '', '0', '1'), ('2', '后台用户', 'fa-user', '0', '1', '7', 'User/index', '0', '', '', '0', '1'), ('3', '系统配置', 'fa-cogs', '0', '1', '8', 'System/index', '0', '', '', '0', '1'), ('4', '扩展插件', 'fa-location-arrow', '0', '1', '9', 'Addons/index', '1', '', '', '0', '1'), ('5', '用户信息', '', '2', '2', '1', 'User/index', '0', '', '', '0', '1'), ('6', '权限控制', '', '2', '2', '2', 'Auth/index', '0', '', '', '0', '1'), ('7', '用户行为', '', '2', '2', '4', 'Action/index', '1', '', '', '0', '1'), ('8', '行为日志', '', '2', '2', '3', 'Action/log', '0', '', '', '0', '1'), ('9', '新增', '', '5', '3', '0', 'User/add', '0', '', '', '0', '1'), ('10', '编辑', '', '5', '3', '0', 'User/edit', '0', '', '', '0', '1'), ('11', '删除', '', '5', '3', '0', 'User/del', '0', '', '', '0', '1'), ('12', '修改状态', '', '5', '3', '0', 'User/changeStatus', '0', '', '', '0', '1'), ('13', '新增', '', '7', '3', '0', 'Action/add', '0', '', '', '0', '1'), ('14', '编辑', '', '7', '3', '0', 'Action/edit', '0', '', '', '0', '1'), ('15', '删除', '', '7', '3', '0', 'Action/del', '0', '', '', '0', '1'), ('16', '修改状态', '', '7', '3', '0', 'Action/changeStatus', '0', '', '', '0', '1'), ('17', '创建组', '', '6', '3', '0', 'Auth/createGroup', '0', '', '', '0', '1'), ('18', '编辑组', '', '6', '3', '0', 'Auth/editGroup', '0', '', '', '0', '1'), ('20', '修改状态', '', '6', '3', '0', 'Auth/changeStatus', '0', '', '', '0', '1'), ('21', '成员授权', '', '6', '3', '0', 'Auth/user', '0', '', '', '0', '1'), ('22', '访问授权', '', '6', '3', '0', 'Auth/access', '0', '', '', '0', '1'), ('87', '新增配置', '', '35', '3', '0', 'Config/add', '0', '', '', '0', '1'), ('24', '保存成员授权', '', '6', '3', '0', 'Auth/addToGroup', '0', '', '', '0', '1'), ('25', '接触授权', '', '6', '3', '0', 'Auth/removeFromGroup', '0', '', '', '0', '1'), ('26', '日志详情', '', '8', '3', '0', 'Action/view', '0', '', '', '0', '1'), ('27', '删除日志', '', '8', '3', '0', 'Action/remove', '0', '', '', '0', '1'), ('35', '配置属性', '', '3', '2', '2', 'Config/index', '0', '', '', '0', '1'), ('36', '网站配置', '', '3', '2', '1', 'Config/group', '0', '', '', '0', '1'), ('37', '菜单管理', '', '3', '2', '3', 'Menu/index', '0', '', '', '0', '1'), ('38', '备份数据库', '', '3', '2', '4', 'Database/export', '0', '', '', '0', '1'), ('39', '还原数据库', '', '3', '2', '5', 'Database/import', '0', '', '', '0', '1'), ('44', '消息管理', 'fa-envelope', '0', '1', '6', 'Message/index', '0', '', '', '0', '1'), ('45', '发送消息', '', '44', '2', '1', 'Message/send', '0', '', '', '0', '1'), ('46', '发件箱', '', '44', '2', '3', 'Message/index', '0', '', '', '0', '1'), ('47', '草稿箱', '', '44', '2', '4', 'Message/draft', '0', '', '', '0', '1'), ('48', '回收站', '', '44', '2', '5', 'Message/trash', '0', '', '', '0', '1'), ('49', '收件箱', '', '44', '2', '2', 'Message/inbox', '1', '', '', '0', '1'), ('62', '浏览', '', '46', '3', '0', 'Message/view', '0', '', '', '0', '1'), ('67', '首页', '', '1', '2', '0', 'Index/index', '1', '', '', '0', '1'), ('82', '删除', '', '46', '3', '0', 'Message/delete', '0', '', '', '0', '1'), ('83', '移动到回收站', '', '46', '3', '0', 'Message/moveToTrash', '0', '', '', '0', '1'), ('84', '上传头像', '', '5', '3', '0', 'User/updateHeader', '0', '', '', '0', '1'), ('85', '更新信息', '', '5', '3', '0', 'User/update', '0', '', '', '0', '1'), ('86', '更新节点', '', '6', '3', '0', 'auth/updateRules', '0', '', '', '0', '1'), ('88', '编辑', '', '35', '3', '0', 'Config/edit', '0', '', '', '0', '1'), ('89', '批量保存', '', '35', '3', '0', 'Config/save', '0', '', '', '0', '1'), ('90', '删除配置', '', '35', '3', '0', 'Config/del', '0', '', '', '0', '1'), ('91', '排序', '', '35', '3', '0', 'Config/sort', '0', '', '', '0', '1'), ('92', '新增', '', '40', '3', '0', 'Menu/add', '0', '', '', '0', '1'), ('93', '编辑', '', '40', '3', '0', 'Menu/edit', '0', '', '', '0', '1'), ('94', '删除', '', '40', '3', '0', 'Menu/del', '0', '', '', '0', '1'), ('95', '是否隐藏', '', '40', '3', '0', 'Menu/toogleHide', '0', '', '', '0', '1'), ('96', '是否开发', '', '40', '3', '0', 'Menu/toogleDev', '0', '', '', '0', '1'), ('97', '排序', '', '40', '3', '0', 'Menu/sort', '0', '', '', '0', '1'), ('98', '菜单信息', '', '40', '3', '0', 'Menu/getInfo', '0', '', '', '0', '1'), ('99', '优化表', '', '38', '3', '0', 'Database/optimize', '0', '', '', '0', '1'), ('100', '修复表', '', '38', '3', '0', 'Database/repair', '0', '', '', '0', '1'), ('101', '删除备份', '', '39', '3', '0', 'Database/del', '0', '', '', '0', '1'), ('103', '补丁管理', '', '3', '1', '6', 'Update/index', '0', '', '', '0', '1'), ('104', '升级系统', '', '3', '2', '7', 'Update/update', '0', '', '', '0', '1');
COMMIT;

-- ----------------------------
--  Table structure for `tb_message`
-- ----------------------------
DROP TABLE IF EXISTS `tb_message`;
CREATE TABLE `tb_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '消息标题',
  `content` text COMMENT '主体内容',
  `attachment_id` varchar(50) DEFAULT '0' COMMENT '附件ID',
  `type` tinyint(4) DEFAULT '1' COMMENT '消息类型，1系统群发、2发送给厂商、3发送给用户',
  `send_id` int(11) DEFAULT '0' COMMENT '发件人，0系统',
  `receive_id` int(11) DEFAULT '0' COMMENT '收件人，0所有对象',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `send_time` int(11) DEFAULT '0' COMMENT '发送时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态，-1删除、0草稿、1正常',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内部消息';

-- ----------------------------
--  Table structure for `tb_message_status`
-- ----------------------------
DROP TABLE IF EXISTS `tb_message_status`;
CREATE TABLE `tb_message_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) DEFAULT '0',
  `member_id` int(11) DEFAULT '0',
  `is_view` tinyint(4) DEFAULT '0' COMMENT '是否已经查看，1已查看',
  `is_delete` tinyint(4) DEFAULT '0' COMMENT '是否删除，1删除',
  `type` tinyint(4) DEFAULT '1' COMMENT '用户类型，1后台、2厂商、3主播',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消息内容的状态';

-- ----------------------------
--  Table structure for `tb_news`
-- ----------------------------
DROP TABLE IF EXISTS `tb_news`;
CREATE TABLE `tb_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `type` tinyint(4) DEFAULT '1' COMMENT '类型：1新闻速递、2园区通告、3好文分享、4相关活动、5政策、6法规',
  `front_cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT '封面图片，填写路径',
  `is_banner` tinyint(4) DEFAULT '0' COMMENT '是否轮播',
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT '发布人',
  `views` int(11) DEFAULT '0' COMMENT '浏览量',
  `create_time` int(11) DEFAULT '0',
  `comments` int(11) DEFAULT '0' COMMENT '评论量',
  `update_time` int(11) DEFAULT '0',
  `create_user` int(11) DEFAULT '0',
  `is_send` tinyint(4) DEFAULT '0' COMMENT '是否推送',
  `park_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`) USING BTREE,
  KEY `is_send` (`is_send`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_banner` (`is_banner`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻内容';

-- ----------------------------
--  Table structure for `tb_park`
-- ----------------------------
DROP TABLE IF EXISTS `tb_park`;
CREATE TABLE `tb_park` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `area_total` int(11) DEFAULT NULL COMMENT '总面积',
  `area_use` int(11) DEFAULT NULL COMMENT '在租',
  `area_other` int(11) DEFAULT NULL COMMENT '其他占用',
  `company_total` int(11) DEFAULT NULL,
  `company_in` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='园区';

-- ----------------------------
--  Table structure for `tb_sms`
-- ----------------------------
DROP TABLE IF EXISTS `tb_sms`;
CREATE TABLE `tb_sms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to` char(16) DEFAULT '' COMMENT '接收手机号码',
  `content` varchar(255) DEFAULT '' COMMENT '短信内容',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `template_id` int(11) DEFAULT '0' COMMENT '模板ID',
  `result` varchar(255) DEFAULT '' COMMENT '发送后的返回码',
  `resp_code` char(6) DEFAULT '' COMMENT '发送后的返回码',
  `create_date` char(16) DEFAULT '' COMMENT '短信平台记录的时间',
  `sms_id` char(32) DEFAULT '' COMMENT '短信平台记录的ID',
  `status` tinyint(4) DEFAULT '0' COMMENT '0:成功；1：提交失败，4：失败，5：关键字（keys），6：黑/白名单，7：超频（overrate），8：unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信发送记录';

-- ----------------------------
--  Table structure for `tb_sms_code`
-- ----------------------------
DROP TABLE IF EXISTS `tb_sms_code`;
CREATE TABLE `tb_sms_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` char(12) DEFAULT '' COMMENT '手机号码',
  `code` char(4) DEFAULT '' COMMENT '验证码',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `active_time` int(11) DEFAULT '0' COMMENT '有效期单位秒',
  `status` tinyint(4) DEFAULT '0' COMMENT '是否验证',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发送验证码验证信息';

-- ----------------------------
--  Table structure for `tb_union`
-- ----------------------------
DROP TABLE IF EXISTS `tb_union`;
CREATE TABLE `tb_union` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `contact` varchar(255) DEFAULT NULL COMMENT '联系人',
  `mobile` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `office_time_start` time DEFAULT NULL,
  `office_time_end` time DEFAULT NULL,
  `park_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工会';

-- ----------------------------
--  Table structure for `tb_wechat_department`
-- ----------------------------
DROP TABLE IF EXISTS `tb_wechat_department`;
CREATE TABLE `tb_wechat_department` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `parentid` int(11) DEFAULT NULL,
  `order` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信部门';

-- ----------------------------
--  Table structure for `tb_wechat_tag`
-- ----------------------------
DROP TABLE IF EXISTS `tb_wechat_tag`;
CREATE TABLE `tb_wechat_tag` (
  `tagid` int(11) NOT NULL,
  `tagname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='微信标签';

-- ----------------------------
--  Table structure for `tb_wechat_user`
-- ----------------------------
DROP TABLE IF EXISTS `tb_wechat_user`;
CREATE TABLE `tb_wechat_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户的标识，对当前公众号唯一',
  `unionid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段',
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户的昵称',
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `province` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `language` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户的语言，简体中文为zh_CN',
  `headimgurl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `subscribe_time` int(11) NOT NULL DEFAULT '0' COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间\n用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间\n用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间\n用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `unsubscribe_time` int(11) DEFAULT '0' COMMENT '退订时间',
  `subscribe` tinyint(4) DEFAULT '0' COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '粉丝的备注',
  `groupid` char(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户所在的分组ID',
  `tagid_list` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标签组',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '后期逻辑处理标识,0取消关注、1关注',
  `party_branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '党支部',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '姓名',
  `age` tinyint(4) DEFAULT NULL,
  `education` tinyint(4) DEFAULT '2' COMMENT '学历，1大专、2本科、3硕士、4博士',
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机号码',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '所属单位',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='微信用户信息';

SET FOREIGN_KEY_CHECKS = 1;
