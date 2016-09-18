LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'1000001','202CB962AC59075B964B07152D234B70',NULL,'+86','18621316860',0,0,NULL,NULL,NULL,NULL,'2016-09-18 13:57:22','2016-09-18 13:57:22',NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_client`
--

LOCK TABLES `user_client` WRITE;
/*!40000 ALTER TABLE `user_client` DISABLE KEYS */;
INSERT INTO `user_client` VALUES (1,NULL,NULL,NULL,NULL,'2016-09-18 13:57:22','2016-09-18 13:57:22');
/*!40000 ALTER TABLE `user_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
INSERT INTO `user_profiles` VALUES (1,1,'',NULL,'other',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-09-18 13:57:22','2016-09-18 13:57:22');
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
INSERT INTO `user_token` VALUES (1,1,1,'366d7a03ee14d6f806a3454cb62eaf18','6ad11d128d6bc07e963117ded423fc23',1,'2016-09-18 13:57:22','2016-09-18 13:57:22');
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;