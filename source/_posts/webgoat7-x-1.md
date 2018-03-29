---
title: WebGoat7.x使用笔记1-环境搭建与基本解释
categories:
  - 安全
tags:
  - 安全
  - webgoat7
  - Java
date: 2017-04-24 11:20:04
updated: 2017-04-24 11:20:04
---

WebGoat是OWASP开发的用于进行Web漏洞实验的应用平台，用来说明Web应用中存在的安全漏掉.基于Java，服务器是tomcat

本文搭建的环境是WebGoat7.1 + JDK1.8
##独立版本环境搭建步骤：
1. Java JDK：网络上的搭建教程很多，随意选择即可，与JDK版本无关，但是Linux/Windows平台的搭建环境步骤不同
  1. 下载JDK
  2. 安装JAVA
  3. 设置环境变量
  4. 检查JAVA环境
2. WebGoat：有两种版本的，一个是开发版，一个是稳定版，本文使用稳定版
  1. 下载jar包
  2. 使用命令执行
  3. 通过浏览器输入`localhost:8080/WebGoat`访问项目
  4. 接下来按照课程一步一步完成即可


##开发版环境搭建：
**开发版最重要的就是可以修改源码，即各种java文件，但是需要注意，修改的java文件的位置都是在webgoat-lessions下，所以每次修改了java文件需要重新执行9-13步**

虽然每次重新编译java文件，生成jar包，然后重新部署到webgoat下，但是并不会将已经完成的实验的状态改变（即实验列表右侧小绿勾），个人估计这些状态都是记录在本地的某个文件内，只要该文件不变就ok。


###Windows 平台配置过程：
1.下载安装jdk，git，maven并配置环境变量，这个不用多说，网上一大堆。

2.最好全程开vpn，因为编译的过程要下载一些东西

3.找一个目录，使用git下载webgoat服务器代码和课程代码，命令如下：
```
git clone https://github.com/WebGoat/WebGoat.git
git clone https://github.com/WebGoat/WebGoat-Lessons.git
```

4.输入`cd WebGoat`目录进入webgoat文件夹下

5.输入`git checkout 7.1`命令，如果按照原来的输入，编译的时候是按照8.0编译的，结果报错（checkout命令用于在多个tag版本间切换）

6.输入`mvn clean compile install`， 刚开始的时候，它会先下载一些东西，时间很长，所以需要保持良好网络，之后开始编译，第二次之后的编译就会非常快了 ，

7.进入到WebGoat-Lessons

8，此处要输入`git checkout develop`，输入git checkout 7.1报错,因为webgoat-lessons下并没有7.1tag，所以会报错，其实模式即为develop

9.输入`mvn package` 编译，第一次还是很慢

10.输入xcopy "target\plugins\*.jar" "..\WebGoat\webgoat-container\src\main\webapp\plugin_lessons\"将编译好的课程文件复制到webgoat中，如下：(这一步其实就是负责所有的webgoat-lession目录下的生成的jar包到webgoat中，所以可以按照路径自己手动复制)

11.输入`cd ..` 返回上一目录。这里文档提供了三种方法运行服务，第一种使用Maven Tomcat插件做示范(因为webgoat内置tomcat，所以可以直接使用mvn开启这个内置的tomcat服务)

12.输入`cd WebGoat`进入WebGoat目录，

13.输入`mvn -pl webgoat-container tomcat7:run-war`安装插件，并开启服务，第一次时很慢，

14.然后可以开始按照实验步骤和提示做安全实验了。

**开发版修改java源码文件的位置是WebGoat-Lessons内，而不是WebGoat**
以cross-site-scripting为例，源码位置在：
`WebGoat-Lessons\cross-site-scripting\src\main\java\org\owasp\webgoat\plugin\crosssitescripting`

修改完后就需要重新执行上面9-13步，然后输入上次的用户名就可以继续实验了。

