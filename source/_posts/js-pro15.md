---
title: JavaScript高级程序设计-15-BOM
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
date: 2016-08-15 14:57:49
updated:
---

ES是js的核心，但但web中使用js，那么BOM（Broswer Object Model 浏览器对象模型）是真正的核心。BOM提供了很多对象，用于访问浏览器的功能，一般这些功能和具体的网页内容无关。W3C将BOM纳入H5规范中将浏览器中的js最基本的部分标准化了。

### window对象
BOM的核心对象是window，它表示浏览器的一个实例，在浏览器中window对象既是通过js访问浏览器窗口的一个接口，也是ES规定的Global对象，即，在网页定义的任何一个对象、变量、函数，都是以window作为其Global对象的，因此有权访问全局函数，如parseInt等。

#### 全局作用域
抛开全局变量会成为window对象的属性不谈，定义全局变量与在window对象上直接定义属性还是有区别的：全局变量不能通过delete操作符删除，而直接在window对象上定义的属性可以。
```js
var age = 11;
window.color = 'red';

delete age; //false 或 报错
delete window.color; // true

console.log(age); // 11;
console.log(window.color); // undefined;
```
使用var语句添加的window属性有一个名为`[[Configurable]]`的特性，这个特性的值被设置为false，因此这样定义的属性不可以通过delete操作符删除。

同时，尝试访问未声明的变量会抛出错误，但通过查询window对象，可以知道某个变量是否未声明。
```js
var newValue = oldValue; // oldValue未定义，报错

var newValue = window.oldValue; // 不会报错，因为是查询属性
```

#### 窗口关系及框架（frameset）
若页面中包含框架，则每个框架都拥有自己的window对象，并且保存在frames集合中，在frames集合中，可通过索引（从0开始，将页面中的frame从左至右，从上至下依次定义索引值）或框架名称来访问相应的window对象，每个window对象都有一个name属性，其中包含框架的名称。
*注：除非最高层窗口是通过window.open打开的，否则其window对象的name属性不包含任何值*

top对象始终指向最高（最外层）的框架，也就是浏览器窗口。使用它可以确保一个框架中正确的访问另一个框架，因为对应一个框架中编写的任何代码来说，其中window对象指向的都是那个特定实例，而非最高层框架。

与top相对的另一个window对象是parent，其始终指向当前框架的直接上层框架，parent有可能等于top，但没有框架的情况下，parent一定等于top（此时都等于window）。

与框架有关的最后一个对象是self，始终指向window，实际上self和window对象可互相换用，引入self对象的目的只是为了与top和parent对应起来。因此它不格外包含其他值。

所有这些对象都是window对象的属性，可以通过window.parent，window.top等形式访问。同时，可以将不同层次的window对象连缀起来，例如`window.parent.parent.frames[0]`;

同时，在使用框架的情况下，浏览器中会存在多个Global对象，在每个框架中定义的全局变量会自动成为框架中window对象的属性。由于每个window都包含原生类型的构造函数，因此每个框架都有一套自己的构造函数，这些构造函数一一对应，但并不相等。例如top.Object并不等于top.frames[0].Object，这个问题会影响到对跨框架传递的对象使用instanceof操作符。

#### 窗口位置
用来确定和修改window对象位置的属性和方法有很多，且各浏览器API有区别。

比如：screenLeft/screenTop属性（IE、Safari、Opear、Chrome）及对应的screenX/screenY属性（Firefox、Chrome），分别用于表示窗口相对于屏幕左侧和上边的位置。但这两组其实数值并不相等。因为在IE、Opera、Chrome中，screenLeft/screenX属性的窗口指的是页面可视区（不包括浏览器头部等），而在Firefox和Safari中，指的是整个浏览器。

甚至Firefox、Safari、Chrome浏览器返回的是top的值，同时会忽略外边距设置的偏移。而IE和Opera则给出框架相对屏幕边界的精确坐标。

moveTo和moveBy可以将窗口移动到新位置，moveTo接收的是新位置的x和y坐标，而moveBy接收的是水平和锤子方向上移动的像素值。
```js
window.moveTo(0, 0); // 将窗口移动到屏幕左上角
window.moveTo(10, 30); // 将窗口移动到屏幕(10, 30)

window.moveBy(0, 100); // 将窗口向下移动100像素
window.moveBy(-50, 0); // 将窗口向左移动50像素
```
同时，这两个方法可能会被浏览器禁用，且不适用于框架，只能对最外层window对象使用。

#### 窗口大小
跨浏览器确定一个窗口大小不是简单的事儿，每个浏览器都定义了一些属性，IE9+、Firefox、Safari、Opera、Chrome提供了innerWidth、innerHeight、outerWidth、outerHeight。

而outer系列属性，IE9+、Firefox、Safari返回浏览器窗口本身的大小，Opera表示页面单个标签对应的浏览器窗口大小，Chrome中返回视口大小而非浏览器窗口大小，同时inner和outer系列值相同。

在IE、Firefox、Opera、Safari、Chrome中，document.documentElement.clientWidth和clientHeight保存页面视口的信息，在IE6的混杂模式中需通过body.clientWidth取得相同信息。

对于移动设备，window.innerWidth指的是可视区，document.documentElement是布局视口，即渲染后页面的实际大小（可视区是整个页面的一部分）。

另外resizeTo和resizeBy可以调整浏览器窗口大小，resizeTo接收浏览器窗口的新宽度和新高度，而resizeBy接收新窗口与原窗口的宽度和高度只差。

这两个方法与移动窗口位置的方法类型，可能会被浏览器禁用，且不适用于框架，只能对最外层window对象使用。

#### 导航和打开窗口
使用window.open方法既可以导航到一个特定的URL，也可以打开一个新的浏览器窗口，接收4个参数：要加载的URL、窗口目标、一个特性字符串以及一个表示新页面是否取代浏览器历史记录中当前加载页面的布尔值。通常只传递第一个参数，最后一个参数在不打开新窗口的情况下使用。

当传递了第二个参数，且该参数为已有窗口或框架名时，那么会在具有该名称的窗口或框架中加载指定的url，若没有存在，则新创建一个窗口并命名为第二个参数。
```js
window.open('http://www.king.com', 'topFrame');
// 等同 <a href="http://www.king.com" target="topFrame">
```
第二个参数可以是一些特殊的名称:`_self、_parent、_top、_blank`。

##### 弹出窗口
若open的第二个参数是一个不存在的窗口或框架时，会根据第三个参数来创建新窗口或新标签，若第三个参数不存在，则默认设置新窗口或新标签，而若不打开新窗口的话，则会忽略第三个参数。

第三个参数其实是一个逗号分隔的字符串，用于表示新窗口中显示那些特性：

  | 设置 | 值 | 说明 |
  | - | - | - |
  | height | 数值 | 表示窗口的高度，不能小于100 |
  | width | 数值 | 表示窗口的宽度，不能小于100 |
  | left | 数值 | 表示窗口的左坐标，不能是负值 |
  | top | 数值 | 表示窗口的上坐标，不能是负值 |
  | fullscreen | yes/no | 表示窗口是否最大化，仅限IE |
  | location | yes/no | 表示窗口是否显示地址栏，**无论任何浏览器，都无效** |
  | menubar | yes/no | 表示窗口是否显示菜单栏，默认no |
  | resizable | yes/no | 表示窗口是否可被拖动边框改变大小，默认no |
  | scrollbars | yes/no | 表示窗口是否可滚动，默认no |
  | toolbar | yes/no | 表示窗口是否显示工具栏，默认no |
  | status | yes/no | 表示窗口是否显示状态栏，默认no |

window.open会返回指向新窗口的引用，引用的对象与其他window对象大致相似，但可对其进行更多控制，而window对象可能会禁止一些功能。比如open创建的新窗口可调整大小或移动位置。

同时用close方法可以关闭打开的新窗口。关闭后，新窗口的引用依然存在，但已经没有用了，通过closed属性可检测其是否已经被关闭。

新窗口的window对象有一个opener属性，其为打开它的原始窗口对象，这个属性只在弹出窗口的最外层window对象（top）有定义，而且指向调用window.open的窗口或框架。
```js
var newwin = window.open('url', 'newwin', 'height=400,width=400');
newwin.resizeTo(100, 100); // 大小
newwin.moveTo(100, 100); // 位置

newwin.opener == window; // true

newwin.close(); //关闭,在newwin内部，通过top.close()也可以关闭自己
newwin.closed; // true
```

有些浏览器会在独立的进程中运行每个标签页，当一个标签页打开另一个标签页时，若两个window对象之间需彼此通信，那么新标签就不能运行在独立的进程中。在chrome下，若将opener设置为null，则表示在单独的进程中运行新标签页，同时一旦切断标签页之间的联系则无法恢复。

##### 安全限制
在某些浏览器中，对弹出窗口做了限制，比如始终显示地址栏，同时不能将弹出窗口移动到屏幕以外、不允许关闭状态栏等。

##### 弹出窗口屏蔽程序
大多数浏览器都内置了弹出窗口屏蔽程序，或者可安装第三方的屏蔽插件。所以当弹窗窗口被屏蔽时，有可能是被浏览器限制的，也有可能是被插件限制的。

若是浏览器限制的，那么window.open会返回null。若是插件限制的，则可能会报错。

#### 定时器：setTimeout超时和setInterval间歇
js能设置超时值和间歇值来调度代码在特定时刻指向，但由于js是单线程语言，所以，其实是伪定时器。

js在一定时间内只能执行一段代码，为了控制要执行的代码，js有一个任务队列，任务会依次被js执行。timeout表示指定时候后将当前的任务添加到队列中，若队列是空，则添加的代码会被立即执行，若不为空，则需要等待前面的代码执行完成后再执行，所以代码并不一定精确的经过指定时间后执行。

两个方法都接收两个参数，第一个表示要执行的代码（可以为字符串，也可以是一个函数对象），第二个是一个数值（单位是毫秒）。
其中第一个参数为字符串时会像eval一样去解析，但会导致性能损失。

调用setTimeout后，会返回一个数值ID，表示超时调用，这个ID是计划执行代码的唯一标识符，通过`clearTimeout(ID);`可以取消超时调用。当然，若调用已经发生，则在取消已经无效了。

同时，由于定时器调用的代码都是在全局作用域中执行的，因此函数中的this的值在非严格模式下指向window对象，在严格模式下是undefined。

而间歇调用与超时类似，不同的是，间歇调用会一直执行到页面卸载。一般使用超时调用模拟间歇调用，因为使用超时调用时不用跟踪ID，同时由于单线程执行问题，后一个间歇调用可能会在前一个间歇调用结束之前启动，而超时调用则完全避免了这一点。

#### 系统对话框
alert、confirm、prompt方法可以调用系统对话框向用户显示消息，系统对话框与浏览器中显示的网页没有关系，也不包含HTML。外观不由CSS控制，而是由浏览器决定，同时这几个方法打开的对话框是同步的、模态的，即这些对话框显示时代码会停止执行，关掉对话框后代码恢复执行。

alert是警告，向用户显示一些他们无法控制的消息，例如错误信息，而用户只能在看完消息后关闭对话框。
confirm是确认，confirm方法返回布尔值，true表示确认，false表示取消。
prompt是提示，用于提示用户输入文本，接收2个参数，第一个是提示文本，第二个是文本输入框的默认值。点击确定返回文本框内的值，点击取消或其他方式关闭对话则返回null。

其中若同一个脚本在执行过程中打开多个对话框，则会显示一个复选框用于提示用户是否要阻止后续的对话框显示。但独立的用户操作则不会有阻止复选框。

window对象的find和print方法可以显示“查找”，“打印”对话框，一些浏览器下find命令无效，同时这两个对话框是异步的，不影响页面js执行。

### location对象
location对象提供了当前窗口加载的文档的信息，以及导航功能。而且它非常特别，既是window的属性，也是document的属性。

location将URL解析为独立的片段，如href、hash、host、hostname、pathname、port、protocol、search等，非常有用。

#### 查询字符串参数
虽然location.search能返回查询字符串，但并不方便，它返回的只是字符串，还需要解析。
```js
function getQueryStringArgs(){

  var qs = (location.search.length > 0 ? location.search.substring(1) : ''), // 取得查询字符串并去掉开头问号
      args = {}, // 存储对象
      items = qs.length ? qs.split('&') : [], // 取得每一项
      item = null,
      name = null,
      value = null,
      i = 0,
      len = items.length;

    // 逐一添加到args对象中
    for(i = 0; i < len; i++){
      item = items[i].split('=');
      name = decodeURIComponent(item[0]);
      value = decodeURIComponent(item[1]);
      if(name.length){
        args[name] = value;
      }
    }
    return args;
}
```

#### 位置操作
location对象可以通过很多方式改变浏览器的位置，比如最常用的location.href

assign方法接收一个URL.
```js
location.assign('http://www.king.com');
```
这个方法会立即打开URL并在浏览器的历史记录中生成一条记录，而若设置location.href和window.location的值则会在内部调用assign方法，

修改location对象的其他属性也可以改变当前加载的页面，如hash、search、hostname、pathname、port，同时会在历史记录中生成新记录，而除了hash，其他的属性被修改后，页面会以新URL重新加载。

若不想生成新记录，则使用replace方法，该方法接收一个url字符串，同时会刷新页面到新url。

reload方法的作业则是重新加载当前页面，同时接收一个布尔参数，表示是否要强制重新加载，若不传参数则浏览器自动选择最合适的方法刷新(比如从缓存中获取)。

### navigator对象
该对象是识别客户端浏览器的重要“事实”标准，与BOM其他对象一样，每个浏览器的navigator对象有自己的属性：[navigator对象](http://www.w3school.com.cn/jsref/dom_obj_navigator.asp)

#### 检测插件
对于非IE浏览器，navigator对象的plugins属性是一个数组，用于保存浏览器中安装的插件信息的数组，数组中的每一项包含下列属性：
- name 插件名字
- description 插件描述
- filename 插件文件名
- length 插件处理MIME的类型数量
一般通过名字检测插件是否安装

#### 注册处理程序
H5新定义的registerContentHandler和registerProtocolHandler方法可以让一个站点知名它处理特定类型的信息，随着RSS阅读器和在线电子邮件的发展，注册处理程序为像使用桌面应用程序一样模式使用这些在线程序提供了一种方式。

其中registerContentHandler方法接收三个参数，分别表示要处理的MIME类型，可以处理该MIME类型的页面的url以及应用的名称。比如将站点注册为处理RSS源的处理程序：
```js
navigator.registerContentHandler('application/rss+xml', 'http://www.king.com?feed=%s','Some Reader');
```
第一个是RSS源的MIME类型，第二个参数是应该接收RSS源的URL,其中`%s`表示rss源url，由浏览器自动插入，当下一次请求rss源时，浏览器会打开指定的url，而相应的web应用程序将会处理该请求。

而registerProtocolHandler类似，但第一个参数指定的是处理的协议（如mailto或ftp）,比如将一个程序注册为默认的邮件客户端：
```js
navigator.registerProtocolHandler('mailto', 'http://www.king.com?cmd=%s','Some Mail Client')
```

### screen对象
js中的screen对象在编程时用处不大，用于表明客户端的能力，如显示器的信息，同时该对象所包含的属性也视浏览器而异。[screen对象](http://www.w3school.com.cn/jsref/dom_obj_screen.asp)

### history对象
history对象保存着用户上网的历史记录，从窗口被打开时算起，因为history是window对象的属性，因此每个浏览器窗口、每个标签页、甚至每个frame都有自己的history对象与特定的window对象关联。

出于安全考虑，开发者无法得之用户浏览过的url，但使用go方法可以实现后退和前进。

go方法可以在用户的历史记录中任意跳转，接收一个参数。

参数可以是整数，表示向前/后跳转的页面数，负数为向后（类似后退按钮），正数为向前（类型前进按钮）。简化的back和forward方法可以替代go方法。

参数也可以是字符串，浏览器会调整到历史记录中包含该字符串的第一个位置，可以是后退，也可能是前进，具体要看那个位置最近。若历史记录不包含该字符串，则什么也不做。

history还有一个length属性，保存历史记录的数量，这个数量包括所有的历史记录（包括向前和向后的），通过检测length是否等于0可以知道用户是否一开始就打开了当前页面。

### 小结
浏览器对象模型，即BOM，以window对象为依托，表示浏览器窗口以及页面可见区域，同时，window对象还是ES中的Global对象，因而所有的全局变量和函数都是它的属性，且所有原生的构造函数及其他函数也都存在于它的命名空间下。
- 在使用框架时，每个框架都有自己的window对象以及所有元素构造函数及其他函数的副本，每个框架都保存在frames集合胡总，可以通过位置或通过名称来访问
- 有一些窗口指针，可以用来引用其他框架，包括父框架
- top对象始终指向最外围的框架，包括整个浏览器窗口
- parent对象表示包含当前框架的框架，而self对象则回指window
- 使用location对象可以通过编程方式来访问浏览器的导航系统，设置相应的属性，可以逐段或整体修改浏览器的url
- 调用replace方法可以导航到一个新rul，同时该url会替换浏览器历史记录中当前显示的页面
- navigator对象提供了与浏览器有关的信息，除了公用的一些，具体取决于浏览器
BOM中的screen和history对象功能优先，screen对象保存着与客户端显示器有关的信息，这些信息一般只用于站点分析。history堆栈为访问浏览器历史记录开了口，可据此判断历史几率的数量，也可以在历史记录中任意导航。