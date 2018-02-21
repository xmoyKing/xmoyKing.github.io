---
title: 【译】原生JS数据绑定
categories: Translation
tags:
  - js
  - translation
date: 2018-02-21 20:48:59
updated: 2018-02-21 20:48:59
---


## 原生JS数据绑定

双向数据绑定是非常重要的特性 —— 将JS模型与HTML视图对应，能减少模板编译并提高用户体验。我们将学习如何在不使用框架的情况下，使用原生JS实现双向绑定 —— 一种为Object.observe*（译注：已废弃）*，另一种为覆盖get / set。PS: 第二种更好，详情请参阅底部的TL;DR。

### 1: Object.observe && DOM.onChange

`Object.observe()`是[一种新特性][2]。JS提供的新特性，其在ES7中实现，但在最新的Chrome中已可用 —— 允许对JS对象进行动态更新。简单说就是 —— 只要对象（的属性）发生变化就调用注册的回调函数。

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

这很方便，能进行动态编程 —— 既保证了所有内容都是最新的。

先看下一步：

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

如上，我们自己实现了模型到数据的绑定！封装一下：

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

现在换一种方式 —— 将DOM元素与JS值绑定起来。一种简单的方法是使用[jQuery.change](http://api.jquery.com/change/)

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

简直不要太方便，简单总结一下，在实际开发时，可以将两者结合，通过函数来创建一个双向数据绑定：

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

注意：在双向绑定时，DOM操作有不同的方式，例如不同的DOM元素（input，div，textarea，select）有不同的取值方式（text，val）。同时注意：双向数据绑定并不总是必须的 —— “输出型”元素一般不需要视图到模型的绑定，而“输入型”元素一般不需要模型到视图的绑定。但也有例外，例如：

### 2: 深入'get'和'set'属性

上面的解决方法并不完美。比如用`.change`并不会触发jQuery的“change”事件 —— 例如，直接通过代码对DOM进行修改，在上面的代码中，以下代码不起作用：

```
$("#foo").val('Putin')
user.name //still Obama. Oops. 

```

我们将用一种更激进的方式实现 —— 重写getter和setter。因为我们不仅要动态更新，同时我们将重写JS最底层的功能，既get/setting变量的能力，所以不那么“安全”。这种元编程能力非常强。

那么，如果我们可以重写getting和setting对象值会怎么样呢？其实，这也是数据绑定的实质。其实，[用 `Object.defineProperty()` 即可实现][7].

其实，以前就有[旧的，非标准的，不赞成使用的实现方式][8]，但现在我们有了更好的方式（最重要的是标准）`Object.defineProperty`，如下所示：

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

OK, so now user.name is an alias for nameValue. But we can do more than just redirect the variable to be used - we can use it to create an alignment between the model and the view. Observe:

现在`user.name`是`nameValue`的别名。但可做的不仅仅是换个变量名 - 我们可以通过它来保证模型和视图的一致。如下：

```
//<input id="foo">
Object.defineProperty(user, 'name', {
  get: function() { return document.getElementById("foo").value }, 
  set: function(newValue) { document.getElementById("foo").value = newValue; },
  configurable: true //to enable redefining the property later
});

```

`user.name` is now binded to the input `#foo`. This is a very concise expression of ‘binding’ at a native level - by defining (or extending) the native get/set. Since the implementation is so concise, one can easily extend/modify this code for custom situation - binding only get/set or extending either one of them, for example to enable binding of other data types.

`user.name`现在绑定到`#foo`元素。这是一个非常简洁的在本地级别的“绑定”表达式 - 通过定义（或扩展）本机get / set。由于实现非常简洁，因此可以轻松扩展/修改此自定义情况的代码 - 仅绑定获取/设置或扩展其中一个，例如启用其他数据类型的绑定。

As usual we make sure to DRY ourselves with something like:

```
function bindModelInput(obj, property, domElem) {
  Object.defineProperty(obj, property, {
    get: function() { return domElem.value; }, 
    set: function(newValue) { domElem.value = newValue; },
    configurable: true
  });
}

```

usage:

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

Note the above still uses ‘domElem.value’ and so will still work only on `<input>` elements. (This can be extended and abstracted away within the bindModelInput, to identify the appropriate DOM type and use the correct method to set its ‘value’).

Discussion:

*   DefineProperty is available in [pretty much every browser][10].
*   It is worth mentioning that in the above implementation, the _view_ is now the ‘single point of truth’ (at least, to a certain perspective). This is generally unremarkable (since the point of two-way data-binding means equivalency). However on a principle level this may make some uncomfortable, and in some cases may have actual effect - for example in case of a removal of the DOM element, would our model would essentially be rendered useless? The answer is no, it would not. Our `bindModelInput` creates a closure over `domElem`, keeping it in memory - and preserving the behavior a la binding with the model - even if the DOM element is removed. Thus the model lives on, even if the view is removed. Naturally the reverse is also true - if the model is removed, the view still functions just fine. Understanding these internals could prove important in extreme cases of refreshing both the data and the view.

Using such a bare-hands approach presents many benefits over using a framework such as Knockout or Angular for data-binding, such as:

*   Understanding: Once the source code of the data-binding is in your own hands, you can better understand it and modify it to your own use-cases.
*   Performance: Don’t bind everything and the kitchen sink, only what you need, thus avoiding performance hits at large numbers of observables.
*   Avoiding lock-in: Being able to perform data-binding yourself is of course immensely powerful, if you’re not in a framework that supports that.

One weakness is that since this is not a ‘true’ binding (there is no ‘dirty checking’ going on), some cases will fail - updating the view will not ‘trigger’ anything in the model, so for example trying to ‘sync’ two dom elements _via the view_ will fail. That is, binding two elements to the same model will only refresh both elements correctly when the model is ‘touched’. This can be amended by adding a custom ‘toucher’:

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

### TL;DR:

Create a two way data-binding between model and view with native JavaScript as such:

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

Thanks for reading. Previously published on [JavaScript Weekly][11], comments at [reddit][12] or at [\[email protected\]][13]

Feel free to [drop me a line][14]. I [write extensively][15] about advanced web development and am available for [consulting][16] anywhere in the world.

© [sellarafaeli.com][17], 2017

 

---

via: [https://www.sellarafaeli.com/blog/native\_javascript\_data_binding][18]

作者: [null][19] 选题者: [@undefined][20] 译者: [译者ID][21] 校对: [校对者ID][22]

本文由 [LCTT][23] 原创编译，[Linux中国][24] 荣誉推出

[1]: https://www.sellarafaeli.com/
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
[13]: https://www.sellarafaeli.com/cdn-cgi/l/email-protection
[14]: https://www.sellarafaeli.com/contact
[15]: https://www.sellarafaeli.com/blog
[16]: https://www.sellarafaeli.com/consulting.html
[17]: https://www.sellarafaeli.com/
[18]: https://www.sellarafaeli.com/blog/native_javascript_data_binding
[19]: undefined
[20]: https://github.com/undefined
[21]: https://github.com/译者ID
[22]: https://github.com/校对者ID
[23]: https://github.com/LCTT/TranslateProject
[24]: https://linux.cn/