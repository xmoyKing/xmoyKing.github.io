---
title: 自建CA并将网站改为HTTPS协议网站
categories:
  - report
tags:
  - report
  - ca
  - https
  - openssl
date: 2017-04-27 23:48:16
updated: 2017-04-27 23:48:16
---

本文讲解如何将http网站改为https网站，首先讲解HTTPS的基础SSL，CA认证以及OPENSSL的用法，以apache2 Web容器为例，

![ca验证过程](1.png)