---
title: angularjs巩固实践-39-使用angualr-hint
categories:
  - angularjs
tags:
  - angularjs
  - angular-hint
date: 2017-08-19 17:33:35
updated:
---

angular-hint是在ng1.3后出现的第三方模块，目的在于帮助写出更好的ng代码，以及更容易定位ng中常见的错误。

angular-hint目前包括如下模块：
- angular-hint-controllers: 包含全局controller的警告、以及controller命名等最佳实践
- angular-hint-directives: 包含指令的attribute、tag命名方法、以及更多的ng指令最佳实践
- angular-hint-dom：当在ng controller中使用的DOM处理时发出警告
- angular-hint-events: 标记出事件表达式中职位undefined的变量
- angular-hint-interpolation: 关于`{ {} }`表达式的最佳实践和使用
- angualr-hint-modules: 标记出未使用的module,以及未声明的module，多处ng-app声明等关于module的最佳实践
- angular-hint-scope：包含关于$scope使用的最佳实践

可以通过batarang使用angular-hint，ng官方提供的chrome插件——batarang集成了angular-hint，安装了batarang后在ng页启用插件即可。

#### 手动集成angular-hint
如果没法在chrome中进行开发，就只能手动集成angular-hint了，通过npm将该插件下载到项目，然后添加模块依赖，并在应用的ngApp节点上加上ng-hint指令即可。
```
npm install angular-hint --save
```
angular-hint 使用：
```html
<body ng-app="com.ngnice.app" ng-hint></body>
```
angular-hint会默认开启所有建议的module，但也可以只使用其中一些，比如仅开启dom和directives：
```html
<body ng-app="com.ngnice.app" ng-hint-include="dom directives"></body>
```
注意：当发布项目额时候需要移除这些hint。

