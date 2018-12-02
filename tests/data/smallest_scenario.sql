SET FOREIGN_KEY_CHECKS=0;
INSERT INTO `scenario` VALUES (1,'Test scenario','',1,'schedule','* * * * *','[\"1\"]','[\"\"]',NULL,1,NULL,'{\"name\":\"\"}','','{\"timeDependency\":0,\"has_return\":0,\"logmode\":\"default\",\"allowMultiInstance\":\"0\",\"syncmode\":\"0\",\"timeline::enable\":\"0\"}','expert');
INSERT INTO `scenarioElement` VALUES (1,0,'action',NULL,NULL,NULL);
INSERT INTO `scenarioExpression` VALUES (1,0,1,'action',NULL,'log','{\"enable\":\"1\",\"background\":\"0\",\"message\":\"LAUNCHED\"}',NULL);
SET FOREIGN_KEY_CHECKS=1;