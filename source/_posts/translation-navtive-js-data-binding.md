---
title: 原生JS数据绑定
categories: JavaScript
tags:
  - JavaScript
  - 翻译
date: 2018-02-21 20:48:59
updated: 2018-02-21 20:48:59
---

本文翻译自[Native JavaScript Data-Binding](https://www.sellarafaeli.com/blog/native_javascript_data_binding)，将会同时发布在众成翻译，地址：[原生JS数据绑定](http://zcfy.cc/article/native-javascript-data-binding)。

作者：[Sella Rafaeli](https://www.sellarafaeli.com/)

===============================================================================================

双向数据绑定是非常重要的特性 —— 将JS模型与HTML视图对应，能减少模板编译时间同时提高用户体验。我们将学习在不使用框架的情况下，使用原生JS实现双向绑定 —— 一种为Object.observe*（译注：现已废弃，作者写博客时为14年11月）*，另一种为覆盖get / set。PS: 第二种更好，详情请参阅底部的TL;DR*（译注：too long；don't read. 直译为“太长，不想看”，意译为“简单粗暴来吧”）*。

### 1: Object.observe 和 DOM.onChange

`Object.observe()`是[一种新特性][2]，其在ES7中实现，但在最新的Chrome中已可用 —— 允许对JS对象进行响应式更新。简单说就是 —— 只要对象（的属性）发生变化就调用回调函数。

一般用法为：

```
log = console.log
user = {}
Object.observe(user, function(changes){
    changes.forEach(function(change) {
        user.fullName = user.firstName + " " + user.lastName;
    });
});

user.firstName = 'Bill';
user.lastName = 'Clinton';
user.fullName // 'Bill Clinton'

```

这很方便，且能实现响应式编程 —— 保证所有内容都是最新的。

如下：

```
//<input id="foo">
user = {};
div = $("#foo");
Object.observe(user, function(changes){
    changes.forEach(function(change) {
        var fullName = (user.firstName || "") + " " + (user.lastName || "");
        div.text(fullName);
    });
});

user.firstName = 'Bill';
user.lastName = 'Clinton';

div.text() //Bill Clinton

```

_[JSFiddle][4]_

如上，我们自己实现了模型到数据的绑定！封装一下*（译注：此处原文为`Let’s DRY ourselves with a helper function.` DRY即 don't repeat yourself）*：

```
//<input id="foo">
function bindObjPropToDomElem(obj, property, domElem) {
  Object.observe(obj, function(changes){
    changes.forEach(function(change) {
      $(domElem).text(obj[property]);
    });
  });
}

user = {};
bindObjPropToDomElem(user,'name',$("#foo"));
user.name = 'William'
$("#foo").text() //'William'

```

_[JSFiddle][5]_

换一种方式 —— 将DOM元素与JS值绑定起来。简单的方法是使用[jQuery.change](http://api.jquery.com/change/)

```
//<input id="foo">
$("#foo").val("");
function bindDomElemToObjProp(domElem, obj, propertyName) {
  $(domElem).change(function() {
    obj[propertyName] = $(domElem).val();
    alert("user.name is now "+user.name);
  });
}

user = {}
bindDomElemToObjProp($("#foo"), user, 'name');
//enter 'obama' into input
user.name //Obama.

```

_[JSFiddle][6]_

简直不要太方便，在实际开发时，可以将两者结合，通过函数来创建一个双向数据绑定：

```
function bindObjPropToDomElem(obj, property, domElem) {
  Object.observe(obj, function(changes){
    changes.forEach(function(change) {
      $(domElem).text(obj[property]);
    });
  });
}

function bindDomElemToObjProp(obj, propertyName, domElem) {
  $(domElem).change(function() {
    obj[propertyName] = $(domElem).val();
    console.log("obj is", obj);
  });
}

function bindModelView(obj, property, domElem) {
  bindObjPropToDomElem(obj, property, domElem)
  bindDomElemToObjProp(obj, propertyName, domElem)
}

```

注意：在双向绑定时，需正确进行DOM操作，因为不同的DOM元素（input，div，textarea，select）有不同的取值方式（text，val）。同时注意：双向数据绑定并不是必须的 —— “输出型”元素一般不需要视图到模型的绑定，而“输入型”元素一般不需要模型到视图的绑定。

下面为第二种方式：

### 2: 深入'get'和'set'属性

上面的解决方法并不完美。比如直接的修改并不会自动触发jQuery的“change”事件 —— 例如，直接通过代码对DOM进行修改，比如以下代码不起作用：

```
$("#foo").val('Putin')
user.name //still Obama. Oops.

```

现在，我们来用一种更激进的方式实现 —— 重写getter和setter。因为我们不仅要监测变化，我们将重写JS最底层的功能，即get/setting变量的能力，所以不那么“安全”。后面我们将会看到，这种元编程的方式有多强大。

那么，如果我们可以重写get和set对象值的方法会怎么样呢？这也是数据绑定的实质。[用 `Object.defineProperty()` 即可实现][7].

其实，以前就有[已废弃且非标准实现方式][8]，但通过`Object.defineProperty`的实现方式更好（最重要的是标准），如下所示：

```
user = {}
nameValue = 'Joe';
Object.defineProperty(user, 'name', {
  get: function() { return nameValue },
  set: function(newValue) { nameValue = newValue; },
  configurable: true //to enable redefining the property later
});

user.name //Joe
user.name = 'Bob'
user.name //Bob
nameValue //Bob

```

现在`user.name`是`nameValue`的别名。但可做的不仅仅是创建新的变量名 - 我们可以通过它来保证模型和视图的一致。如下：

```
//<input id="foo">
Object.defineProperty(user, 'name', {
  get: function() { return document.getElementById("foo").value },
  set: function(newValue) { document.getElementById("foo").value = newValue; },
  configurable: true //to enable redefining the property later
});

```

`user.name`现在绑定到`#foo`元素。这种底层的方式非常简洁 —— 通过定义（或扩展）变量属性的get / set实现。由于实现非常简洁，因此可以根据情况轻松扩展/修改代码 —— 仅绑定或扩展get / set中的一个，比如绑定其他数据类型。

可封装如下：

```
function bindModelInput(obj, property, domElem) {
  Object.defineProperty(obj, property, {
    get: function() { return domElem.value; },
    set: function(newValue) { domElem.value = newValue; },
    configurable: true
  });
}

```

使用：
```
user = {};
inputElem = document.getElementById("foo");
bindModelInput(user,'name',inputElem);

user.name = "Joe";
alert("input value is now "+inputElem.value) //input is now 'Joe';

inputElem.value = 'Bob';
alert("user.name is now "+user.name) //model is now 'Bob';

```

_[JSFiddle][9]_

注意：上面的`domElem.value`只对`input`元素有效。（可在`bindModelInput`中扩展，对不同的DOM类型使用对应的方法来设置它的值）。

思考：
* [` DefineProperty `浏览器兼容性][10]良好 。
* 注意：上面的实现中，在某些场景下，`视图`可认为是符合`SPOT (single point of truth )`原则的，但该原则常常被忽视（因为双向数据绑定也就意味着等价）。然而，深究下去可能就会发现问题了，在实际开发中也会遇到。 —— 比如，当删除DOM元素时，关联的模型会自动注销么？答案是不会。`bindModelInput`函数在`domElem`元素上创建了一个闭包，使DOM元素常驻在内存中 —— 并保持模型与模型的绑定关系 —— 即使DOM元素被移除。即使视图被移除了，但模型依旧存在。反之一样 —— 若模型被移除了，视图依然能够正常显示。在某些刷新视图和模型无效的情况下，理解这些内部原理就能找到原因了。

*（译注：`SPOT`简单翻译为“单点原则”，即引起变化最好的是由单一入口引起的，而不是由多个入口引起的，比如一个函数，其返回结果最好仅由参数决定，这样输入和输出才能一致，而不会由于其他变化导致用一个输入会出现不同的输出）*

这种自己实现的数据绑定方法与Knockout或Angular等框架的数据绑定相比，有一些优点，例如：
* 理解：一旦掌握数据绑定的源码，不仅理解更深入，而且也能对其进行扩展和修改。
* 性能：不要将所有东西都绑定在一起，只绑定所需的，避免监测过多对象
* 避免锁定：若所用的框架不支持数据绑定，则自行实现的数据绑定更强大

缺点是由于不是`真正的`绑定（没有`脏检查`），有些情况会失败 —— 视图更新时不会`触发`模型中的数据，所以当试着`同步`视图中的两个DOM元素时将会失败。也就是说，将两个元素绑定到同一个模型上时，只有更新模型，则两个元素才会被正确更新。可以通过自定义一个更新函数来实现：

```
//<input id='input1'>
//<input id='input2'>
input1 = document.getElementById('input1')
input2 = document.getElementById('input2')
user = {}
Object.defineProperty(user, 'name', {
  get: function() { return input1.value; },
  set: function(newValue) { input1.value = newValue; input2.value = newValue; },
  configurable: true
});
input1.onchange = function() { user.name = user.name } //sync both inputs.

```

### TL;DR：
当需要使用原生JS创建模型和视图的双向数据绑定时，如下：

```
function bindModelInput(obj, property, domElem) {
  Object.defineProperty(obj, property, {
    get: function() { return domElem.value; },
    set: function(newValue) { domElem.value = newValue; },
    configurable: true
  });
}

//<input id="foo">
user = {}
bindModelInput(user,'name',document.getElementById('foo')); //hey presto, we now have two-way data binding.

```

感谢阅读，本文也发布在 [JavaScript Weekly][11], 可在[reddit][12]回复我


[2]: http://www.html5rocks.com/en/tutorials/es7/observe/
[3]: http://kangax.github.io/compat-table/es7/#Object.observe
[4]: http://jsfiddle.net/v2bw6658/
[5]: http://jsfiddle.net/v2bw6658/2/
[6]: http://jsfiddle.net/v2bw6658/4/
[7]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/defineProperty
[8]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/__defineGetter__
[9]: http://jsfiddle.net/v2bw6658/6/
[10]: http://kangax.github.io/compat-table/es5/#Object.defineProperty
[11]: http://javascriptweekly.com/issues/207
[12]: http://redd.it/2manfb