---
title: JavaScript框架设计笔记-6-节点模块
categories: js
tags:
  - js
  - js-framework
date: 2016-12-26 15:24:04
updated: 2016-12-26 15:24:04
---

DOM操作占前端工作的很大一部分，而节点操作又占了DOM操作的一半左右，由于选择器引擎让繁琐的元素选择变得简单，jQuery更是让节点操作简单到极致，一下子返回很多元素，能够操作一组元素。

而节点操作其实和数据库中的CRUD操作是一致的，来来回回也就四大类。

#### 节点的创建
浏览器提供了多种创建元素节点的API，从使用频率来看，依次是document.createEl6ment,innerHTML,insertAdjacentHTML,createContextualFragment。

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

#### 节点的插入
原生DOM接口很简单，参数类型确定，不会重载，每次只处理一个元素节点。而jquery则相反，参数类型复杂，过度重载，为了简化处理逻辑，jquery的做法时统统转换为文档碎片，然后将其复制到与当前jquery对象里面包含的节点集合相同的个数，一个个插入。

由于jquery式的插入方法，如append、prepend、after、before、replace，太常用而且非常受欢迎，W3C在DOM4中会原生支持这些方法，参数的类型也相同，可以是字符串或DOM节点。

mass framework通过统一调用manipulate的方式，接口变成了空心化的方法，用于提供语义化且便捷的名字而已，实现全部转至内部，由manipulate方法分配真正的执行方案，而且非常容易实现对应的反转方法：
```js
'append,prepend,before,after,replace'.replace($.rword, function(method){
  $.fn[method] = function(item){
    return manipulate(this, method, item, this.ownerDocument);
  };
  $.fn[method+'To'] = function(item){
    $(item, this.ownerDocument)[method](this);
    return this;
  };
});

function manipulate(nodes, name, item, doc){
  // 只运行向元素节点内部插入东西，因此需要转换为纯正的元素节点集合
  var elems = $.filter(nodes, function(el){
    return el.nodeType === 1;
  }),
    handler = insetHooks[name];
  if(item.nodeType){
    // 若传入元素节点，文本节点或文档碎片
    insertAdjacentNode(elems, item, handler);
  }else if(typeof item === 'string'){
    // 若传入的是字符串片段
    // 若方法名不是replace，完美支持insertAdjacentHTML，并且不存在嵌套关系的标签
    var fast = (name !== 'replace')&& $.support(adjacent) && !rnest.test(item);
    if(!fast){
      item = $.parseHTML(item, doc);
    }
    insertAdjacentHTML(elems, item, insertHooks[name+'2'], handler);
  }else if(item.length){
    // 若传入的是HTMLCollection nodeList mass实例，将转换为文档碎片
    insertAdjacentFragment(elems, item, doc, handler);
  }
  return nodes;
}

function insertAdjacentNode(elems, item, handler){
  // 使用appendChild， insertBefore实现，item为普通节点
  for(var i = 0, el; el = elems[i]; i++){
    // 第一个不需要复制，其他的要复制
    handler(el, i ? cloneNode(item, true, true) : item);
  }
}

function insertAdjacentHTML(elems, item, fastHandler, handler){
  for(var i = 0, el; el = elems[i++];){
    // 尝试使用insertAjacentHTML
    if(item.nodeType){ // 若是文档碎片
      handler(el, item.cloneNode(true));
    }else{
      fastHandler(el, item);
    }
  }
}

function insertAdjacentFragment(elems, item, doc, handler){
  var fragment = doc.createDocumentFragment();
  for(var i = 0, el; el = elems[i++];){
    handler(el, makeFragment(item, fragment, i>1));
  }
}

function makeFragment(nodes, fragment, bool){
  // 只有非NodeList的情况下i才递增
  var ret = fragment.cloneNode(false),
    go = !nodes.item;

  for(var i = 0, node; node = nodes[i]; go && i++){
    ret.appendChild(bool && cloneNode(node, true, true) || node);
  }
  return ret;
}

var insertHooks = {
  prepend: function(el, node){
    el.insertBefore(node, el.firstChild);
  },
  append: function(el, node){
    el.appendChild(node);
  },
  before: function(el, node){
    el.parentNode.insertBefore(node, el);
  },
  after: function(el, node){
    el.parentNode.insertBefore(node, el.nextSibling);
  },
  replace: function(el, node){
    el.parentNode.replaceChild(node, el);
  },
  prepend2: function(el, html){
    el[adjacent]('afterBegin', html);
  },
  append2: function(el, html){
    el[adjacent]('beforeEnd', html);
  },
  before2: function(el, html){
    el[adjacent]('beforeBegin', html);
  },
  after2: function(el, html){
    el[adjacent]('afterEnd', html);
  }
};
```

makeFragment函数需要注意两点，一个是NodeList的遍历，一个是文档碎片的复制。
由于NodeList的动态性，每当其插入节点时会立即反映到length属性上，所以需要在循环之前将length保存到一个变量上。
碎片对象的复制可以使用原生的cloneNode(true),但在IE下attachEvent绑定事件会跟着复制，而移除时无法找到对应的引用，所以需要特别注意。

jquery除了提供上述的几种方法外，还提供了wrap、wrapAll、wrappInner三种特殊插入操作：
- wrap,为当前元素提供一个新父节点，此新父节点将动态插入到原节点的父节点下，IE下`neo.applyElement(old, 'outside')`可以非常轻松的实现
- wrapAll,为一堆元素提供一个共同的新父节点，插入到第一个原元素的父节点下，其他元素在依次挪到新节点下。
- wrappInner,为当前元素插入一个新节点，然后将它之前的孩子挪到新节点下，IE下`neo.applyElement(old, 'inside')`可以非常轻松的实现

#### 节点的复制
IE下对元素的复制与innerHTML一样，有很多BUG，比如IE会复制attachEvent事件，标准浏览器的cloneNode只会复制元素写在标签内的属性或通过setAttribute设置的属性，而IE6~8还支持`node.aaa = 'xxx'`设置的属性复制。

jquery的处理方法就是，支持2个参数，第一个是只复制节点，但不复制数据和事件，默认为false，第二个参数决定如何复制它的子孙，默认是参数一的决定。
```js
// 接口只对参数进行处理，具体操作由cloneNode执行
$.fn.clone = function(dataAndEvents, deepDataAndEvents){
  dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
  deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;
  return this.map(function(){
    return cloneNode(this, dataAndEvents, deepDataAndEvents);
  });
};

function cloneNode(node, dataAndEvents, deepDataAndEvents){
  if(node.nodeType === 1){
    // 在标准浏览器下，fixCloneNode只是标准的cloneNode(true)，
    // 否则内部需要加载node_fix模块，做大量的补丁工作
    var neo = $.fixCloneNode(node), // 复制元素的attributes
      src, neos, i;
    if(dataAndEvents){
      $.mergeData(neo, node); // 复制数据与事件
      if(deepDataAndEvents){ // 处理子孙的复制
        src = node[TAGS]('*');
        neos = neo[TAGS]('*');
        for(i = 0; src[i]; i++){
          $.mergeData(neos[i], src[i]);
        }
      }
    }
    src = neos = null;
    return neo;
  }else{
    return node.cloneNode(true);
  }
}
```

#### 节点的移除
浏览器提供了多种移除节点的方法，如removeChild、removeNode。

其中removeNode是IE的私有实现，它的作用是将目标节点从文档树删除返回目标节点，同时接收一个布尔值的参数，即是否只删除目标节点，保留子节点，true时作用同removeChild。

在IE6，7下需要注意removeChild的内存泄漏问题。

#### 一些特殊的元素
有3个元素需要特殊注意，由于每个元素的内容都不同，而且篇幅都不短，所以暂时不给出具体的处理办法
- iframe
- object
- video