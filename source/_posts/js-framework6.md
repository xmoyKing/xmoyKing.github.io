---
title: JavaScript框架设计笔记-6-节点模块
categories: js
tags:
  - js
  - js-framework
date: 2017-12-26 15:24:04
updated: 2017-12-26 15:24:04
---

DOM操作占前端工作的很大一部分，而节点操作又占了DOM操作的一半左右，由于选择器引擎让繁琐的元素选择变得简单，jQuery更是让节点操作简单到极致，一下子返回很多元素，能够操作一组元素。

而节点操作其实和数据库中的CRUD操作是一致的，来来回回也就四大类。

#### 节点的创建
浏览器提供了多种创建元素节点的API，从使用频率来看，依次是document.createElement,innerHTML,insertAdjacentHTML,createContextualFragment。

document.createElement方法传入标签名然后返回此类型的元素节点,`document.createElement('div')`，并且对于浏览器不支持的标签类型，它也能成功返回，所以很多老旧浏览器（IE6-8）能支持H5新标签就靠这个方法，同时它还能连同属性一块儿生成，即如jquery那样，传入带属性的标签，`document.createElement('<div id=aaa></div>')`,这样可以更方便生成标签，同时在老旧浏览器下（IE6,7）, name属性是只读的，所以没办法动态修改，只能这样连同标签一起生成。

innerHTML属性非常的厉害，通过它能一次性创建出一大堆的节点，而不是一个一个创建，所以其效率比createElement高了很多倍，`document.createElement('div').innerHTML = "<span class="left">aaa</span><span class="right">bbb</span>"`。
但这个属性在一些老版本的IE下存在兼容性问题，比如某些元素的innerHTML属性是只读的，只能用appendChild或inserBefore来出来。同时innerHTML中插入的script标签不会被执行，而是当做普通文本处理，但某些浏览器支持script标签的defer属性，这样它被插入到innerHTML中会被执行。一种解决方法是利用正则将script中的内容抽取出来然后用eval执行，或者等innerHTML赋值完成后script标签的内容重新抽取出来包裹在一个新的document.createElement('script')生成的script标签中。
其次就是某些标签不能独立存在，必须有外部的合法标签容器才行，比如tr、td、th节点必须在table thead/tbody内，area节点必须在map内，option节点必须在select内...当这些标签没有正确嵌套的时候，不同浏览器解析会有差异，大部分浏览器会自动补全，有一些会报错。

insertAdjacentHTML这个方法也非常强大，而且其插入方式非常灵活，它可以插入一个元素的内部的最前面（afterBegin）、内部最后面（beforeEnd），外部前面（beforeBegin），外部后面（afterEnd），对应着jQuery的方法就是prepend，append，before，after。但其内插入节点的规则的限制同innerHTML一样。

而jquery为了实现方便简单的创建节点，其实内部做了很多处理，比如先经过append方法进入domManip方法，在到buildFragment方法，再到clean方法，期间有字符串加工，script内容抽取，innerHTML序列化，文档随便对象生成，插入DOM，全局eval等多个操作。

mass Framework与jquery的结构类似，通过两个构造器与一个原型实现无new实例化，这样链式操作就不会被new关键字打断。
```js
function $(a, b){ // 第一个构造器
  return new $.fn.init(a, b); // 第二个构造器
}
// 将原型对象放到一个名字更短更好记的属性名中
// 方便扩展原型方法，类似jquery,同时是实现链式操作的关键
$.fn = $.prototype = {
  init: function(a, b){
    this.a = a;
    this.b = b;
  }
}
// 公用一个原型
$.fn.init.prototype = $.fn;

var a = $(1, 2);
console.log(a instanceof $);
console.log(a instanceof $.fn.init);
```

jquery的重载非常丰富灵活，其$的用法包含9种不同的传参方式：
- jQuery(selector, [context])
- jQuery(element)
- jQuery(elementArray)
- jQuery(object)
- jQuery(jQuery Object)
- jQuery()
- jQuery(html ,[ownerDocument])
- jQuery(callback)
大致分为3种，选择器、domParser、domReady，除了domReady，其他的都是为了获取操作的节点集合。而这些节点与实例通过索引可以联系起来，构成一个类数组对象，

若不支持IE，则可以使用`__proto__`属性，这样能更加方便的实现无new实例化，同时，jquery对象其实是一个类数组对象，所以可以考虑用数组代替。
```js
var $ = function(expr, context){
  // 这个dom数组通过选择器引擎或domparser得到，是节点集合
  var dom = [];
  return DomArray(dom, expr, context);
}
// DomArray为内部函数
function DomArray(dom, expr, context){
  dom = dom || [];
  dom.context = context;
  dom.expr = expr;
  dom.__proto__ = DomArray.prototype;
  return dom;
}
DomArray.prototype = $.fn = []; // 为了使用数组的方法
$.fn.get = function(){ // 添加原型方法
  alert(this.expr);
}

var a = $('div');
a.push('a', 'b', 'c');
a.get(); // div
a.lenght; // 3
a.forEach(function(i){
  i; // 依次a, b, c
})
```

接下来对其这个类数组对象进行扩展，
```js
$.fn.extend({
  init: function(expr, context){
    // 分支1，处理空白字符，null， undefined参数
    if(!expr){
      return this;
    }

    // 分支2，让$实例与元素节点一样拥有ownerDocument属性
    var doc, nodes; // 用于节点搜索的起点
    if($.isArrayLike(context)){ // typeof context === 'string'
      return $(context).find(expr);
    }

    // 分支3，处理节点参数
    if(expr.nodeType){
      this.ownerDocument = expr.nodeType === 9 ? expr : expr.ownerDocument;
      return $.Array.merge(this, [expr]);
    }
    this.selector = expr + '';

    // 分支4，处理css3选择器
    if(typeof epxr === 'string'){
      doc = this.ownerDocument = !context ? document : getDoc(context, context[0]);
      var scope = context || doc;
      expr = expr.trim();

      // 分支5，动态生成新节点
      if(expr.charAt(0) === '<' && expr.charAt(expr.length - 1) === '>' && expr.length >= 3){
        nodes = $.parseHTML(expr, doc);
        nodes = nodes.childNodes;
      }else if(rtag.test(expr)){
        // 分支6，getElementsByTagName
        nodes = scope[TAGS](expr);
      }else{
        // 分支7，进入选择器模块
        nodes = $.query(expr, scope);
      }
      return $.Array.merge(this, nodes);

    }else{
      // 分支8，处理数组、节点集合、mass对象或window对象
      this.ownerDocument = getDoc(expr[0]);
      $.Array.merge(this, $.isArrayLike(expr)?expr:[expr]);
      delete this.selector;
    }
  },

  mass: $.mass,
  length: 0,
  valueOf: function(){ // 转换为纯数组对象
    return Array.prototype.slice.call(this);
  },
  size: function(){
    return this.length;
  },
  toString: function(){ // 收集tagName属性，做成纯数组返回
    var i = this.length, 
        ret = [],
        getType = $.type;

    while(i--){
      ret[i] = getType(this[i]);
    }
    return ret.join(', ');
  },
  labor: function(nodes){ // 用于构建一个与对象具有相同属性，但节点集不用的mass对象
    var neo = new $;
    neo.context = this.context;
    neo.selector = this.selector;
    neo.ownerDocument = this.ownerDocument;
    return $.Array.merge(neo, nodes || []);
  },
  slice: function(a, b){ // 截取原来某部分组成mass对象
    return this.labor($.slice(this, a, b));
  },
  get: function(num){ // 传入索引值获取节点，支持负数,若不传返回节点集的纯数组
    return !arguments.length ? this.valueOf() : this[num<0 ? this.length+num : num];
  },
  eq: function(i){ // 传入索引值获取节点，
    return i === -1 ? this.slice(i) : this.slice(i, +i+1);
  },
  gt: function(i){ // 取得大于索引值的所有节点
    return this.slice(i+1, this.length);
  },
  lt: function(i){ 
    return this.slice(0, i);
  },
  first: function(){

  },
  last: function(){

  },
  even: function(){
    return this.labor($.filter(this, function(_, i){
      return i % 2 === 0;
    }));
  },
  odd: function(){

  },
  each: function(fn){
    return $.each(this, fn);
  },
  map: function(fn){
    return this.labor($.map(this, fn));
  },
  clone: function(){

  },
  html: function(){

  },
  text: function(){

  },
  outerHTML: function(){

  }
});

$.fn.init.prototype = $.fn;
'push,unshift,pop,shift,splice,sort,reverse'.replace($.rword, function(method){
  $.fn[method] = function(){
    Array.prototype[method].apply(this, arguments);
    return this;
  }
});
```

