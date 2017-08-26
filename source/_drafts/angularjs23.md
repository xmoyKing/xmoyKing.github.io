---
title: angularjs入门笔记-23-视图服务
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-30 10:28:37
updated:
---

ng的视图服务通过多个组件独立控制内容，能降低应用复杂度。
- 使用$routeProvider定义url路由，使路由导航由前端控制
- 使用ng-view指令显示视图
- 使用$location.path方法或使用href属性改变路由
- 配置controller属性将视图和控制器关联
- 配置resolve属性定义控制器的依赖

