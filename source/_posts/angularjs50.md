---
title: AngularJS巩固实践-50-常见“坑”-3-锚点导航、ngRepeat问题、指令优先级
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - AngularJS深度剖析
date: 2017-09-12 09:15:50
updated:
---

在ng路由中有两种实现方式，分别为HTML5 history和Hashbang，他们对URL解析是有区别的：
![解析url](1.png)

#### 锚点导航问题
当URL的锚点被Hashbang占用了，那么如何继续实现URL锚点导航定位呢？ng提供了`$anchorScroll`服务来处理URL锚点的定位,示例如下：
```html
<div id="scrollArea" ng-controller="ScrollCtrl">
  <a ng-click="gotoBottom()">go to bottom</a>
  <span id="bottom">Bottom</span>
</div>
```
JS:
```js
angualr.module('com.ngnice.app').controller('DemoController', function($location, $anchorScroll){
  var vm = this;
  vm.gotoBottom = function(){
    // location.hash参数为：导航目标节点的id
    $location.hash('bottom');

    $anchorScroll(); // 调用$anchorScroll
  };

  return vm;
});
```

#### ngRepeat验证失败问题
若Form表单控件是利用ngRepeat指令动态生成的，那么可能会遇到无法处理表单验证的问题，因为一旦控件是由ngRepeat动态生成的，那么就无法在Form对象上引用该控件。示例如下：
```html
<div ng-form="" name="demoForm">
  <div ng-repeat="item in [1,2,3]">
    No.{{ item }}:
    <input type="number" ng-model="demo.data[$index]" name="amount" min="10" />
    <div ng-show="demoForm.amount.$error.min">
      Should more than 10
    </div>
  </div>
</div>
```
期望的结果是：当输入小于10时，应该显示错误信息。但此处无法得到预期结果，因ngRepeat中产生的控件并不会把ngModelController注册到ngFormController中，因此验证无法生效。

解决的方法有两种，分别针对简单和复杂的验证：

##### 简单的验证显示
对于简单的验证，可以通过在ngRepeat内嵌套一层ngForm组件来引入新的ngFormController：
```html
<div ng-form="" name="demoForm">
  <div ng-repeat="item in [1,2,3]">
    <div ng-form="" name="demoForm">
      No.{{ item }}:
      <input type="number" ng-model="demo.data[$index]" name="amount" min="10" />
      <div ng-show="demoForm.amount.$error.min">
        Should more than 10
      </div>
    </div>
  </div>
</div>
```

###### 复杂的验证显示
若遇到需要在外部的区域引入表单控件对象时，只能手动将ngModelController注册到ngFormController中去：
```js
angular.module('com.ngnice.app').directive('dyName', function(){
  return{
    require: 'ngModel',
    link: function(scope, elm, iAttrs, ngModelCtrl){
      ngModelCtrl.$name = scope.$eval(iAttrs.dyName);
      var formController = elm.controller('form') || {
        $addControl: angular.noop
      };
      formController.$addControl(ngModelCtrl);

      scope.$on('$destroy', function(){
        formController.$removeControl(ngModelCtrl);
      });
    }
  };
});
```
然后使用dyName指令：
```html
<input type="number" dy-name="item.field" ng-model="demo.deta[item.field]" min="10" max="500" ng-required="true" />
```

#### ngRepeat报重复内容错误
有的时候，使用ngRepeat时会遇到报重复key的问题，例如：
```html
<body ng-controller="DemoController as demo">
  <div ng-repeat="item in demo.items">
    {{item}}
  </div>
</body>
```
JS:
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.items = [1,2,3,1,1];
  return vm;
});
```
因为ngRepeat会选择一个key值来关联每一个item对象，并且要求这个key是唯一的，但若使用的基础类型值重复了，则就会出错。

此时ng提供的ngRepeat的自定义key值方法可以解决问题，即`track by [key]`比如使用$index为key值：
```html
<body ng-controller="DemoController as demo">
  <div ng-repeat="item in demo.items track by $index">
    {{item}}
  </div>
</body>
```



#### 指令优先级
在设计ng指令时，若在同一个DOM元素上标注多个ng指令，有时需要特殊考虑这些指令的执行顺序，不同的执行顺序可能会导致不同的结果。

比如在ng内置指令中ngRepeat和ngIf指令，当将他们混用时，ngRepeat为了cloneNode产生多条相似的列表记录，而ngIf则按照特定的状态来控制单条记录的显示和隐藏。假设ng在解析指令时线解析了ngIf，那么有可能当前标记的DOM就会被移除，因此ngRepeat也就无法产生多条记录列表了。

因此ng为指令设计了优先级属性（priority）。比如：ngRepeat的priority为1000，ngIf的priority为600。ng会按照优先级来倒序执行它们。在默认情况下，指令优先级为0，可以通过设置`terminal`属性来指定当前指令的权重为结束界限，若为true，则意味着节点中优先级小于当前指令的其他指令都不会被执行，相同优先级指令不包含。

常用优先级：
- ngRepeat 1000
- ngSwitchWhen 800
- ngIf 600
- ngInclude 400
- ngView 400