CREATE TABLE `AppInfo` (
  `platform` varchar(16) NOT NULL,
  `version` varchar(16) NOT NULL,
  `url` varchar(128) NOT NULL,
  `date` varchar(16) NOT NULL,
  `environment` varchar(16) NOT NULL,
  `copyrightYear` varchar(16) NOT NULL,
  PRIMARY KEY (`platform`)
);

INSERT INTO AppInfo(`platform`,`version`,`url`,`date`) VALUES('ios','1.0','itms-services://?action=download-manifest&url=https://gitcafe.com/kiddx/sandbox/raw/master/production.plist','2015-10-10');
INSERT INTO AppInfo(`platform`,`version`,`url`,`date`) VALUES('android','1.0','http://www.gobeta.com.cn/sandbox/SandBox_pro.apk','2015-10-10');
