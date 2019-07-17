/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50725
Source Host           : localhost:3306
Source Database       : permission

Target Server Type    : MYSQL
Target Server Version : 50725
File Encoding         : 65001

Date: 2019-05-28 14:00:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for model_has_permission
-- ----------------------------
DROP TABLE IF EXISTS `model_has_permission`;
CREATE TABLE `model_has_permission` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(16) unsigned NOT NULL COMMENT '用户id',
  `permission_id` int(16) unsigned NOT NULL COMMENT '权限id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_model_unique` (`user_id`,`permission_id`),
  KEY `delete_permission` (`permission_id`),
  CONSTRAINT `delete_permission` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for model_has_role
-- ----------------------------
DROP TABLE IF EXISTS `model_has_role`;
CREATE TABLE `model_has_role` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(16) unsigned NOT NULL COMMENT '角色id',
  `user_id` int(16) unsigned NOT NULL COMMENT '权限id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_model_unique` (`role_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for permission
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限节点名称(根据它来进行)',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '节点名称',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点备注信息',
  `p_id` int(2) NOT NULL DEFAULT '0' COMMENT '用于菜单渲染的节点',
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0 禁用 1 启用',
  `create_time` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_time` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for role_has_permission
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permission`;
CREATE TABLE `role_has_permission` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` int(16) unsigned NOT NULL COMMENT '关联的权限id',
  `role_id` int(16) unsigned NOT NULL COMMENT '关联的角色id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission_role` (`permission_id`,`role_id`),
  KEY `deleted_to_role` (`role_id`),
  CONSTRAINT `deleted_to_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
