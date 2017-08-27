---
title: angularjs入门笔记-24-动画&触控（swipe)手势
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-08-27 10:23:28
updated:
---

ng为在DOM中的动态内容变化的动画效果和处理触控事件提供了两种服务，一个是动态内容转变时使用指定的命名结构定义包含动画（animations）或转变（transitions）的CSS样式，并在指令上应用这些类，还有一个就是使用ng-swipe-left和ng-swipe-right监控触控手势。

### 动画
当在DOM中添加、移除、移动元素时，$animate服务能对这些操作加上动画，但$animate服务自身不定义动画，而是依靠css3的animation和transition，即不是js动画。css3的动画和转变与js完全是两种不同的机制。
注：动画应该是精妙、简短、快速的，目标是将用户的注意力集中在已被改变的事物上，所以需要一致、谨慎、甚至是保守的使用动画。

$animate服务是可选的，定义在模块ngAnimate中，用时需要导入ngAnimate模块文件，并定义module时需要声明对ngAnimate模块的依赖。

$animate服务并不是直接用于动画上的，相反，用css定义动画或转变，按照专门规范的命名，在元素应用这些class名。
ng动画的关键时，理解当指令改变内容时，与之相对应的class发生了那些变化，同时添加了那些class，如下列出支持动画的内置指令和与之对于相关的名称
- ng-repeat enter、leave、move
- ng-view enter、leave
- ng-include enter、leave
- ng-switch enter、leave
- ng-if enter、leave
- ng-class add、remove
- ng-show add、remove
- ng-hide add、remove
enter用于展示内容时，leave用于隐藏内容时，move用于内容在DOM中被移动时，add和remove用于从DOM中添加或移除时。

```html
<style type="text/css">
    .ngFade.ng-enter { transition: 0.1s linear all;  opacity: 0; }
    .ngFade.ng-enter-active { opacity: 1; }
</style>

<body ng-controller="defaultCtrl">
    <div class="panel panel-primary">
        <h3 class="panel-heading">Products</h3>
        <div ng-view class="ngFade"></div>
    </div>
</body>
```
`.ngFade.ng-enter`中的ngFade是自定义的，命名只要符合css命名语法就可以，ng-enter就不是可以自定义名称的了，其中ng-的前缀必不可少，对与上例来说，ng-enter是当ng-view指令中的内容添加时ngAnimate在div上自动添加的class名，而ng-enter-active时内容添加结束时在div上添加的class名
效果即为：视图加载前是透明的，然后在0.1s内完成动画，在结束时变为不透明。（即淡入效果）

一般来说，需要为旧内容的移除和新内容的添加同时设置动画，即：
```css
.ngFade.ng-enter { transition: 0.1s linear all;  opacity: 0; }
.ngFade.ng-enter-active { opacity: 1; }
.ngFade.ng-leave { transition: 0.1s linear all; opacity: 1;  }
.ngFade.ng-leave-active { opacity: 0; }
```

### 触控
ngTouch模块包含$swipe服务，在ngTouch模块中的事件提供触控手势并取代ng-click指令。ngTouch单击触控事件可被用于检测从左到右或从右到左的触控手势。
```html
<html ng-app="exampleApp">
<head>
    <title>Swipe Events</title>
    <script src="angular.js"></script>
    <script src="angular-touch.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script>
        angular.module("exampleApp", ["ngTouch"])
        .controller("defaultCtrl", function ($scope, $element) {
            $scope.swipeType = "<None>";
            $scope.handleSwipe = function(direction) {
                $scope.swipeType = direction;
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="well"
                 ng-swipe-right="handleSwipe('left-to-right')"
                 ng-swipe-left="handleSwipe('right-to-left')">
                <h4>Swipe Here</h4>
            </div>
            <div>Swipe was: {{swipeType}}</div>
        </div>
    </div>
</body>
</html>
```
上例先声明ngTouch模块的依赖，该事件处理器适用于ng-swipe-left和ng-swipe-right两个指令，在div元素上应用这些指令，并使用行内绑定表达式设置他们调用控制器行为，更新绑定的swipeType数据。

