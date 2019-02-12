SET FOREIGN_KEY_CHECKS=0;
INSERT INTO `object` VALUES (1,'My Room',NULL,1,NULL,'{\"parentNumber\":0,\"tagColor\":\"#000000\",\"tagTextColor\":\"#FFFFFF\",\"desktop::summaryTextColor\":\"\",\"mobile::summaryTextColor\":\"\"}','[]','[]');
INSERT INTO `eqLogic` VALUES (1,'Test eqLogic',NULL,NULL,1,'plugin4tests','{\"createtime\":\"2019-02-10 22:10:30\",\"updatetime\":\"2019-02-01 20:21:16\"}',1,NULL,1,NULL,NULL,'[]','{\"showObjectNameOnview\":1,\"showObjectNameOndview\":1,\"showObjectNameOnmview\":1,\"height\":\"auto\",\"width\":\"auto\",\"layout::dashboard::table::parameters\":{\"center\":1,\"styletd\":\"padding:3px;\"},\"layout::mobile::table::parameters\":{\"center\":1,\"styletd\":\"padding:3px;\"}}',9999,NULL,NULL);
INSERT INTO `config` VALUES ('plugin4tests','active','1'),('plugin4tests','deamonAutoMode','1'),('core','log::level::plugin4tests','{\"100\":\"1\",\"200\":\"0\",\"300\":\"0\",\"400\":\"0\",\"1000\":\"0\",\"default\":\"0\"}');
INSERT INTO `cmd` VALUES (1,1,'plugin4tests',NULL,NULL,0,'Cmd 1','[]','[]','0','info','binary',NULL,'[]',1,NULL,'[]','[]');
SET FOREIGN_KEY_CHECKS=1;
