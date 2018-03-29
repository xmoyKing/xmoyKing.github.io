---
title: JavaScript框架设计笔记-8-样式模块
categories: JavaScript
tags:
  - JavaScript
  - js-framework
date: 2017-01-02 23:10:26
updated: 2017-01-02 23:10:26
---

样式模块大致分为两大块，一个是精确获取样式值，另一个是设置样式。由于样式分为外部样式、内部样式、行内样式，再加上important对选择器权重的干涉，实际上很难看出到底运用了那些样式，因此样式难点在于如何获取，包括offset、滚动条等。

大体上在标准浏览器中使用getComputedStyle方法，IE6~8使用currentStyle方法。
getComputedStyle方法为window对象下的方法，而不是document下，它返回一个对象，可以通过getPropertyValue方法传入连字符/驼峰风格的样式名获取样式值，而currentStyle方法使用驼峰样式名。一般考虑到兼容性，统一使用驼峰风格。
```js
var getStyle = function(el, name){
  // 此判断用于过滤伪类，此处只判断元素节点
  // getComputedStyle可以接收第二个参数，用于伪类，如滚动条，placeholder，IE9不支持
  if(el.style){
    name = name.replace(/\-(\w)/g, function(all, letter){
      return letter.toUpperCase();
    });

    if(window.getComputedStyle){
      return el.getComputedStyle(el, null)[name];
    }else{
      return el.currentStyle[name];
    }
  }
}
```

设置样式没什么难度，直接用`el.style[name] = value`即可设置。

但，一个框架需要考虑到其他很多因数，如兼容性，易用性，扩展性等：
- 样式名需要支持连字符风格（CSS风格），驼峰风格（DOM标准风格）
- 样式名需要特殊处理，如float样式、CSS3私有前缀样式
- 若仿jquery，则要考虑set、all、get、first等
- 设置样式时，对长度直接处理，智能补充单位`px`
- 设置样式时，对长度考虑相对值，如`-=20`
- 对个别样式特殊处理，如IE下z-index, opacity, user-select, background-position, top, left
- 基于setStyle、getStyle的扩展，如height、width、offset等

#### 主体框架
mass Framework的[css](https://github.com/RubyLouvre/mass-Framework/blob/master/css.js)和[css_fix](https://github.com/RubyLouvre/mass-Framework/blob/master/css_fix.js)模块。

其中css_fix用来兼容旧版本IE，放在$上的为静态方法，放在$.fn上的为原型方法，其他为私有方法或对象。
```
$
 - css(node, name, value)
 - cssName(name, host, camelCase)
 - parseDisplay(nodeName)
 - cssHooks
 - cssNumber
 - fn
    - css(name, value)
    - height(val)
    - width(val)
    - hide()
    - innerHeight()
    - innerWidth()
    - outerHeight()
    - outerWidth()
    - offset(options)
    - position()
    - scrollTop(val)
    - scrollLeft()
    - hide()
    - show()
    - toggle(state)
    - offsetParent()
    - scrollParent()
```

几个重要方法的依赖关系如下，其他方法基本依赖于$.fn.css,$.css,$.cssHooks['default:get'],
```
$.fn.css -> $.css -> $.cssName
```

在css模块中，几乎所有操作都能追溯到cssHooks这个钩子对象上，通过他里面的方法处理不同浏览器的样式兼容问题。根据传参的不同，分为读和写方法，对于不必特殊设置或获取的样式值，由默认的写方法（cssHooks['default:set']）和读方法（cssHooks['default:get']）处理。写方法一致，但读方法需要区分W3C和IE。

#### 样式名的修正
不是所有的样式名都直接用正则简单处理就行的，比如float对应的JS属性存在问题，CSS3的私有前缀，IE的私有前缀

其中float是关键字，不能直接用，IE的替代品是styleFloat，W3C的是cssFloat。

私有前缀的问题主要是-webkit-(Opera, Safari, Chrome), -ms-(IE), -moz-(Firefox), -o-(Opera)。

IE前缀的问题主要是其他的都可以转化为首字母大写驼峰式，而IE私有前缀是首字母小写驼峰式，比如： -webkit-transform转化为WebkitTransform，而-ms-transform转为msTransform。

#### 个别样式特殊处理

**opacity**
CSS中透明度的设置主要是opacity属性，其能让背景和内容透明，若想让内容不透明则必须设置rgba或hsla的背景色。

IE下设置透明度的方式是最多最复杂的，方式随版本不同而变化。

**user-select**
这个样式用于控制文本内容的可选择性，在旧版IE中，没有此样式，使用unselectable代替（而unselectable没有继承性，必须为所有子元素同时设置）

**background-position**
IE6、7中没有backgroundPosition属性，而是backgroundPositionX和backgroundPositionY，需要手动处理合并

**z-index**
z-index的特殊之处在于，若元素没有被定位（absolute、fixed、relative）需要回溯其祖先定位元素，若有则返回这个祖先元素的z-index值，否则为0

**盒子模型**
除了普通的margin，padding，border，content的区别之外，还有如下特点：
- 背景颜色、背景图片和边框之间是无法设置空白的
- 背景图片在背景颜色上，即背景图片会覆盖背景颜色
- 元素背景是指content和padding区域

由于border-shadow、outline属性的引入，让盒子模型在z轴上层次划分更加的多了：
边界->虚线框->外阴影->背景颜色->背景图片->内阴影->边框->填充->内容区

同时需要注意，W3C盒子模型和IE怪异模式盒子模型的区别，W3C盒子模型为content-box，即内容区的宽为el.style.width，而IE在怪异模式下为border-box，内容区其实包括了border-padding-content。在设置百分比值时，IE盒子模式更加合理，而这也是后面W3C推出box-sizing的原因。

**元素的尺寸**
元素宽高取法一致，以宽为例，一般情况下width指内容区的宽，用getComputedStyle可以获取，但若元素的display为none，或祖父元素的display为none，或元素脱离了DOM树，则getComputedStyle无法获取。而旧版IE下还width还有border-box的问题。

所以获取width，则需要一些列的处理步骤：将元素显示出来，判断是否display为none，判断盒子模型。

设置width，则需要先判断盒子模型，然后对应再处理。

判断盒子模型的代码：
```js
var cssBoxSizing = $.cssName('box-sizing');
adapter['boxSizing:get'] = function(node, name){
  return cssBoxSizing ? getter(node, name) :
    document.compatMpde === 'BackCompat' ? 'border-box' : 'content-box';
}
```

窗口和页面这两个的尺寸也非常重要，一般情况下使用document.innerWidth就可以获取，但旧版IE对于body处理不同，其认为body是最顶层元素，HTML元素是一直隐藏的，而现代浏览器认为body是普通的块状元素,HTML则是包含整个浏览器窗口的可视元素。所以IE下有一套clientXXX的属性用于取得元素的可视区的尺寸，不包含滚动条及被隐藏的部分。

若不打算兼容怪异模式，则可直接使用document.documentElement.clientWidth。
若是只用在移动端则可直接使用innerWidth。
最后获取窗口尺寸的兼容代码如下：
```js
windowWidth = document.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
```

关于页面的宽，即文档的宽，一般出现横向滚动条，就需要考虑加上被隐藏的部分。标准浏览器有一套outerXXX的属性，但那个取得浏览器的尺寸的，因此不想innerWidth那样，IE提供了scrollXXX、offsetXXX属性，而标准浏览器则对这两个IE的属性进行了参考“改进”，导致了各种兼容性问题。
- offsetWidth
  IE、Opera认为 offsetWidth = clientWidth + 滚动条 + 边框
  NS、FF认为offsetWidth是网页实际宽度，可小于clientWidth
- scrollWidth
  IE、Opera认为scrollWidth是网页实际宽度，可小于clientWidth
  NS、FF认为scrollWidth是网页内容宽度、最小值是clientWidth
因此最好兼容办法就是直接取上面一系列属性值中的最大值即可，即页面的宽度
```js
var pargeWidth = Math.max(
document.documentElement.scrollWidth,
document.documentElement.offsetWidth,
document.documentElment.clientWidth,
document.body.scrollWidth,
document.body.offsetWidth
)
```
而之所以不比较document.body.clientWidth是因为其一定是最小的，不用比较。

对于webkit系列的浏览器，直接通过document.width/height即可获取页面的尺寸。

**元素的显隐**
对于将一个元素隐藏，一般采用display的方法，直接设置为none即可，显示就设置为block。

但若涉及到多个元素，而且混着块状元素、内联元素、还有thead、tbody、tr等有特殊默认display的元素时，就需要特别注意了，一般草率设置block会导致部分页面的错乱。

所以必须用正确的display值才行，比如内联元素要用inline，块状元素用block等等，所以引发了另外一个问题，如何判断是内联元素还是块状元素，当涉及到到H5时，还不仅仅是块状元素和内联元素的问题，还有其他非常非常多的display属性。

CSS1中display的值有：block、inline、list-item、none。
CSS2.1中添加inline-block、inherit、run-in、table、inline-table、table-row-group、table-header-group、talbe-XXX等一系列的值。
CSS3引入Felxible Box布局模型，添加了了ruby、ruby-base、ruby-text、ruby-base-group、ruby-text-group、flex、grid等等，而且还非常的不稳定。

因此，除了一些元素我们能确认的默认样式值，我们需要获取一些元素的实时样式值以及默认样式值，通过getDefaultComputedStyle方法可以获取（浏览器需要支持），在iframe沙箱中取。

**元素的坐标**
元素的坐标，即元素的top和left值，但这两个属性只有被定位后才有效，否则为auto。而这时就需要获取offsetTop和offsetLeft值了（即使没有定位，也会有），它们是相对与offsetParent的距离，所以，一级一级的向上计算，就能获取到其的坐标（而且是绝对坐标）。

而相对于可视区的坐标非常有价值，比如弹窗窗口居中对齐，此时需要用到IE的getBoundingClientRect方法（W3C接受了它，没有了兼容问题），此方法用于获取页面中某个元素（border-box）相对浏览器视窗的位置，返回一个object对象，属性有有top、left、right、bottom，其中right是指右边距窗口最左边的距离，bottom是下边距窗口最上面的距离，标准浏览器还有width和height表示元素本身的尺寸值。

此处需要了解一下CSS Object Model (CSSOM)的东西:
参考[CSSOM视图模式(CSSOM View Module)相关整理](http://www.zhangxinxu.com/wordpress/?p=1907)
参考[Measuring Element Dimension and Location with CSSOM in Windows Internet Explorer 9](https://msdn.microsoft.com/en-us/library/hh781509(v=vs.85).aspx)

#### 元素的滚动条坐标
这组属性非常重要，浏览器本身就提供了很多方法来修改它们，比如window下的scroll、scrollTo、scrollBy方法，元素下的scrollLeft、scrollTop、scrollIntoView。

对于一般的元素，修改元素的top、left可能会遮盖其他元素，而scrollTop和scrollLeft不会，而且其读写也没什么特殊的。

对于浏览器的滚动条就需要特别注意，设置时使用window的scrollTo即可，而读取时，若在标准浏览器中从pageXOffset、pageYOffset，在IE中直接取html元素的scrollLeft和scrollTop属性。