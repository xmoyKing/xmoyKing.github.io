---
title: WebGoat7.x使用笔记1-环境搭建与基本解释
categories:
  - report
tags:
  - information security
  - webgoat7
  - java
date: 2017-04-24 11:20:04
updated: 2017-04-24 11:20:04
---

WebGoat是OWASP开发的用于进行Web漏洞实验的应用平台，用来说明Web应用中存在的安全漏掉.基于Java，服务器是tomcat

本文搭建的环境是WebGoat7.1 + JDK1.8
环境搭建步骤：
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
