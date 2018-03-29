---
title: AngularJS入门笔记-18-自定义指令2
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-05-28 10:17:34
updated:
---

除了在上一节已经介绍的自定义指令特性之外，还有一些其他特性。
- $render方法， 处理外部数据的变化
- $setViewValue方法， 处理内部数据的变化
- $formatters数组，格式化值
- $parsers数组，$setValidity，校验值

### 嵌入包含
潜入包含的意思是将一个文档的一部分通过引用插入到另一个文档中，在指令的上下文中，当需要创建一个自定义指令，而该指令模版内为外部控制器指定时，这就很有用了,使用ng-transclude达到这种目的。

```html
<html ng-app="exampleApp">
<head>
    <title>Transclusion</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script type="text/ng-template" id="template">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>This is the panel</h4>
            </div>
            <div class="panel-body" ng-transclude>
            </div>
        </div>
    </script>
    <script type="text/javascript">
        angular.module("exampleApp", [])
            .directive("panel", function () {
                return {
                    link: function (scope, element, attrs) {
                        scope.dataSource = "directive";
                    },
                    restrict: "E",
                    scope: true,
                    template: function () {
                        return angular.element(
                            document.querySelector("#template")).html();
                    },
                    transclude: true
                }
            })
            .controller("defaultCtrl", function ($scope) {
                $scope.dataSource = "controller";
            });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <panel>
        The data value comes from the: {{dataSource}}
    </panel>
</body>
</html>
```
![transclude](1.png)

可以看到，原本指令模版中带ng-transclude属性的标签的内部被替换为了外部defaultCtrl的panel内容，同时，dataSource绑定的值为控制器作用域内的值，而不是指令作用域内的值。

要使用嵌入包含，需要设置两个值，一个是自定义指令选项中设置`transclude: true`,然后是在指令模版中使用`ng-transclude`指定包含的位置。

当将scope设置为false时，dataSource将使用隔离作用域中的值，即directive



### 编译函数
编译函数是当指令特别复杂或需要处理大量数据时，使用编译函数操作DOM并让链接函数执行其他任务，除了性能，使用编译函数可通过嵌入包含来重复生成内容。

一般情况下可以通过简化代码或者优化待处理数据的方法来解决性能问题。

```html
<html ng-app="exampleApp">
<head>
    <title>Compile Function</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script type="text/javascript">
        angular.module("exampleApp", [])
            .controller("defaultCtrl", function ($scope) {
                $scope.products = [{ name: "Apples", price: 1.20 },
                    { name: "Bananas", price: 2.42 }, { name: "Pears", price: 2.02 }];

                $scope.changeData = function () {
                    $scope.products.push({ name: "Cherries", price: 4.02 });
                    for (var i = 0; i < $scope.products.length; i++) {
                        $scope.products[i].price++;
                    }
                }
            })
            .directive("simpleRepeater", function () {
                return {
                    scope: {
                        data: "=source",
                        propName: "@itemName"
                    },
                    transclude: 'element',
                    compile: function (element, attrs, transcludeFn) {
                        return function ($scope, $element, $attr) {
                            $scope.$watch("data.length", function () {
                                var parent = $element.parent();
                                parent.children().remove();
                                for (var i = 0; i < $scope.data.length; i++) {
                                    var childScope = $scope.$new();
                                    childScope[$scope.propName] = $scope.data[i];
                                    transcludeFn(childScope, function (clone) {
                                        parent.append(clone);
                                    });
                                }
                            });
                        }
                    }
                }
            });
    </script>
</head>
<body ng-controller="defaultCtrl" class="panel panel-body" >
    <table class="table table-striped">
        <thead><tr><th>Name</th><th>Price</th></tr></thead>
        <tbody>
            <tr simple-repeater source="products" item-name="item">
                <td>{{item.name}}</td><td>{{item.price | currency}}</td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-default text" ng-click="changeData()">Change</button>
</body>
</html>
```

上例即通过simpleRepeater指令实现了ng-repeat指令，但由于是自己实现的ng-repeat，对dom的操作较多，性能上远远比不上内置的ng-repeat指令。

<p data-height="265" data-theme-id="0" data-slug-hash="QMqRGv" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="编译函数" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/QMqRGv/">编译函数</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

`transclude: 'element'`表示元素本身被包含于嵌入包含中，而不仅仅只是其内容。
compile编译函数在执行时将会被传入三个参数，分别是指令所应用的元素，该元素的属性，以及一个可用于创建潜入包含元素的拷贝的函数。

编译函数的关键是，会返回一个链接函数（会忽略外部指定了link链接函数），因为编译函数的目的是修改DOM，所以返回一个链接函数是理所当然的，这样能更方便的将数据从指令的一部分传递到下一个部分。

由于编译函数仅仅操作DOM，所以参数中没有作用域对象，但返回的链接函数可以声明对$scope，$element, $attrs参数的依赖，分别对应到普通链接函数中的各个参数。

链接函数的作业是添加监听器和事件，所以上述例子对data.length属性使用$watch方法监听，当数据发生变化时需要响应更新其他绑定的数据：
1. 先删除父元素内的所有子元素
2. 遍历数据对象，使用$new方法创建新的作用域
3. 对于每一个嵌入包含内容的实例，将克隆的数据传入并赋值到新作用域中的item属性

```js
transclude(childScope, function(clone){
  parent.append(clone);
});
```
上述的函数调用非常重要，因为这个传给编译函数的嵌入包含函数执行时才是真正进行DOM操作的时候，函数参数分别为：包含item属性的子作用域，item属性设置为当前数据项，以及一个传日了嵌入包含内容的一组拷贝的函数，使用jqLite将这份拷贝添加到父元素行下。结果即对于每个数据对象生成了指令所应用到的tr元素的一份拷贝（及其内容），并且创建了一个新的作用域，在这个作用域中允许嵌入包含内容使用item来引用当前数据对象。


### 在指令中使用控制器
指令能够创建出被其他指令所用的控制器，即允许组合不同的指令创建更复杂的组件。

下面的例子基于两个指令，指令productItem用于表格内，通过ng-repeat生成表格各行。productTable指令被用在table元素上并且使用嵌入包含,同时productTable指令能够提供一个被productItem指令所使用的函数，该函数能够用于标记输入框元素的值的变化情况。
```html
<html ng-app="exampleApp">
<head>
    <title>Directive Controllers</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <script type="text/ng-template" id="productTemplate">
        <td>{{item.name}}</td>
        <td><input ng-model='item.quantity' /></td>
    </script>
    <script type="text/ng-template" id="resetTemplate">
        <td colspan="2"><button ng-click="reset()">Reset</button></td>
    </script>
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.products = [{ name: "Apples", price: 1.20, quantity: 2 },
                { name: "Bananas", price: 2.42, quantity: 3 },
                { name: "Pears", price: 2.02, quantity: 1 }];
        })
        .directive("productItem", function () {
            return {
                template: document.querySelector("#productTemplate").outerText,
                require: "^productTable",
                link: function (scope, element, attrs, ctrl) {
                    scope.$watch("item.quantity", function () {
                        ctrl.updateTotal();
                    });
                }
            }
        })
        .directive("productTable", function () {
            return {
                transclude: true,
                scope: { value: "=productTable", data: "=productData" },
                controller: function ($scope, $element, $attrs) {
                    this.updateTotal = function () {
                        var total = 0;
                        for (var i = 0; i < $scope.data.length; i++) {
                            total += Number($scope.data[i].quantity);
                        }
                        $scope.value = total;
                    }
                }
            }
        })
        .directive("resetTotals", function () {
            return {
                scope: { data: "=productData", propname: "@propertyName" },
                template: document.querySelector("#resetTemplate").outerText,
                require: "^productTable",
                link: function (scope, element, attrs, ctrl) {
                    scope.reset = function () {
                        for (var i = 0; i < scope.data.length; i++) {
                            scope.data[i][scope.propname] = 0;
                        }
                        ctrl.updateTotal();
                    }
                }

            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-striped" product-table="totalValue"
                   product-data="products" ng-transclude>
                <tr><th>Name</th><th>Quantity</th></tr>
                <tr ng-repeat="item in products" product-item></tr>
                <tr><th>Total:</th><td>{{totalValue}}</td></tr>
                <tr reset-totals product-data="products" property-name="quantity"></tr>
            </table>
        </div>
    </div>
</body>
</html>
```
在productTable指令中，使用controller属性指定一个控制器，这个控制器在参数中声明对作用域$scope，元素$element, 元素属性$attrs的依赖，同时在其内定义了一个updateTotal的函数。

在productItem指令中，使用require属性指定对控制器的依赖，前缀`^`表示在指令所应用的元素的父元素上查找另一个指令，除了`^`前缀，还有`?`前缀和`None`值：
- `?`前缀，表示若找不到指令则默认忽略
- `None`值，表示两个指令应用于同一元素

为了使用控制器中定义的方法，在链接函数中指定一个参数ctrl（注意这里不是依赖注入的方式），然后就可以在本地的链接函数中调用其内的方法了`ctrl.updateTotal()`

在指令中定义控制器函数的目的是对功能进行分离和重用，从而无需对整个庞大的组件进行测试和再构建。productTable的控制器并不需要知道productItem控制器的实现和功能，即只要productTable控制器仍然提供updateTotal函数即可独立测试并任意修改。

如下演示中添加了reset按钮，将所有的数量清零，在一个隔离的作用域上提供数据数组和要清零的属性名称，该指令即可通过数据绑定查找要清零的位置，之后调用productTable的updateTotal方法。

<p data-height="265" data-theme-id="0" data-slug-hash="OjxKjG" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="OjxKjG" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/OjxKjG/">OjxKjG</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>


### 创建自定义表单元素
ng-model指令能以多种方法捕获输入数据，允许我们定义组件时创建出特有的表单元素。

```html
<html ng-app="exampleApp">
<head>
    <title>CustomForms</title>
    <script src="angular.js"></script>
    <link href="bootstrap.css" rel="stylesheet" />
    <link href="bootstrap-theme.css" rel="stylesheet" />
    <style>
        *.error { color: red; font-weight: bold; }
    </style>
    <script type="text/ng-template" id="triTemplate">
        <div class="well">
            <div class="btn-group">
                <button class="btn btn-default">Yes</button>
                <button class="btn btn-default">No</button>
                <button class="btn btn-default">Not Sure</button>
            </div>
            <span class="error" ng-show="myForm.decision.$error.confidence">
                You need to be sure
            </span>
        </div>
    </script>
    <script>
        angular.module("exampleApp", [])
        .controller("defaultCtrl", function ($scope) {
            $scope.dataValue = "Not Sure";
        })
        .directive("triButton", function () {
            return {
                restrict: "E",
                replace: true,
                require: "ngModel",
                template: document.querySelector("#triTemplate").outerText,
                link: function (scope, element, attrs, ctrl) {

                    var validateParser = function (value) {
                        var valid = (value == "Yes" || value == "No");
                        ctrl.$setValidity("confidence", valid);
                        return valid ? value : undefined;
                    }

                    ctrl.$parsers.push(validateParser);

                    element.on("click", function (event) {
                        setSelected(event.target.innerText);
                        scope.$apply(function () {
                            ctrl.$setViewValue(event.target.innerText);
                        });
                    });

                    var setSelected = function (value) {
                        var buttons = element.find("button");
                        buttons.removeClass("btn-primary");
                        for (var i = 0; i < buttons.length; i++) {
                            if (buttons.eq(i).text() == value) {
                                buttons.eq(i).addClass("btn-primary");
                            }
                        }
                    }

                    ctrl.$render = function () {
                        validateParser(ctrl.$viewValue);
                    }
                }
            }
        });
    </script>
</head>
<body ng-controller="defaultCtrl">
    <form name="myForm" novalidate>
        <div><tri-button name="decision" ng-model="dataValue" /></div>
    </form>
</body>
</html>
```

<p data-height="265" data-theme-id="0" data-slug-hash="JyrgqB" data-default-tab="result" data-user="xmoyking" data-embed-version="2" data-pen-title="自定义表单元素" class="codepen">See the Pen <a href="https://codepen.io/xmoyking/pen/JyrgqB/">自定义表单元素</a> by XmoyKing (<a href="https://codepen.io/xmoyking">@xmoyking</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>

当dataValue属性在指令外被修改时，能够改变显示的按钮状态，通过替换ngModel控制器所定义的$render函数，原来的函数调用setSelected函数，当值在指令外被修改并且需要更新内容时，$render方法会被ng-model指令调用，通过读取$viewValue属性可拿到最新的值。
```js
ctrl.$render = function () {
    setSelected(ctrl.$viewValue || "Not Sure");
}
```
ngModel控制器提供了一些基本方法和属性：
- $render() 当数据绑定的值发生变化时ngModel控制器调用更新UI的函数，通常会用自定义控制器覆盖该函数。
- $setViewValue(value) 更新数据绑定值
- $viewValue 返回格式化后的值
- $modelValue 从作用域返回未格式化的值
- $formatters 将$modelValue转成$viewValue的格式化函数数组

当用户单击按钮时将变化通过ng-model指令传播到作用域
```js
element.on("click", function (event) {
    setSelected(event.target.innerText);
    scope.$apply(function () {
        ctrl.$setViewValue(event.target.innerText);
    });
});
```
$setViewValue方法调用时，ngModel控制器不会自动调用$render方法，所以需要在click事件中显式调用setSelected。

表单还需要一个很重要的功能就是验证，在本例中定义了一个名为confidence的验证错误属性，同时定义了一个叫validateParser的函数用于解析数据绑定的值，验证值并返回结果，通过`ctrl.$parser.push(validateParser)`注册该解析器。

ngModel控制器提供的验证方法和属性：
- $setPristine() 将校验状态重置，同时会阻止继续校验
- $isEmpty() 可设置指令表示该控件无值，默认实现为查找空字符串`''`,`null`,`undefined`等值
- $parsers 一个校验函数组成的数组
- $error 返回一个对象，其各个属性对应各个校验错误信息
- $pristine 若控件未修改，返回true
- $dirty
- $valid
- $invalid