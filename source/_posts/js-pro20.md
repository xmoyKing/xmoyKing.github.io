---
title: JavaScript高级程序设计-20-事件
categories: js
tags:
  - js
  - js-pro
  - events
date: 2016-09-01 09:37:13
updated:
---

js与HTML之间的交互是通过事件实现的。事件，就是文档或浏览器窗口中发生的一些特定的交互瞬间。可以同监听器（处理程序）来预定事件，以便时间发生时执行相应的代码，这种模式也被称为“观察者模式”，支持页面行为（js代码）与页面视图（HTML和JS代码）之间的松散耦合。

浏览器的事件系统相对比较复杂，DOM2级规范标准化了一些DOM事件，但规范本身没有涵盖所有的事件类型。BOM也支持一些事件，且这些事件与DOM事件间的关系不清晰，因为BOM事件直到H5才有一些标准化规范可遵循。DOM3的出现更增强了DOM事件API和复杂性。

### 事件流
要明白页面的哪个部分获取哪个特定的事件，就要明白事件流，比如单击一个按钮，单击事件不仅仅发生在按钮上，在单击按钮时，也单击了按钮的容器，甚至单击了整个页面。

事件流描述的是从页面接收事件的顺序。但IE的事件流是事件冒泡流，而Netscape则是事件捕获流。所有现代浏览器（IE9+）都支持事件冒泡，所以一般使用事件冒泡。

#### 事件冒泡
IE的事件流叫**事件冒泡（event bubbling）**，即事件开始时是由最具体的元素（文档中嵌套最深的那个节点）接收，然后逐级上传到较为不具体的节点（直到window）。

#### 事件捕获
Netscape的事件流叫**事件捕获（event capturing）**，即与冒泡相反，最不具体的节点最先接收到事件，然后依次到最具体的节点。事件捕获的用意在于在事件到达预定目标前捕获它。

#### DOM事件流
DOM2级事件规定的事件流包括3个阶段：捕获阶段，目标阶段，冒泡阶段。虽然规范要求捕获阶段不会涉及到目标，但多数支持DOM事件流的浏览器都实现了一个特定的行为，即在捕获阶段也能触发事件对象上的事件，结果就有2次在目标对象上操作事件的机会。

### 事件处理程序
事件就是用户或浏览器自身执行的某种动作，如click、load、mouseover等都是事件。而响应某个事件的函数就是**事件处理程序（或事件监听器）**，一般事件处理程序的名字以`on`开头，因此click事件处理程序就是onclick。

#### HTML事件处理程序
某个元素支持的每种事件都可以使用一个与相应事件处理程序同名的HTML属性来指定，这个属性的值为js代码。例如单击按钮：
```js
<input type="button" value="click me" onclick="alert(1);"/>
```
注：此处的js代码由于在HTML代码中，所以不能使用未经转义的HTML语法字符，如&、双引号、大于、小于号等。

事件处理程序中的代码在执行时，有权访问全局作用域中的任何代码。所以可以将其指定为执行某个全局函数，如此，代码执行时会被传入一个event参数，该event就是事件对象，若是函数，则函数中的this等于事件的目标元素。

但在HTML中指定事件处理程序有缺点：
1. 存在时差问题，若用户在HTML元素触发事件时并不具备执行条件（如js文件为加载，变量还未定义等），此时可以将代码封在try-catch块中，将错误捕捉起来不要抛出。`onclick="try{msg();}catch(ex){}"`
2. 由于浏览器对标识符解析规则的差异，使用with扩展事件处理程序的作用域链会导致不同的结果（而且with语句不建议使用）
3. HTML与js代码紧密耦合，比如要更换事件处理程序则需要改动至少2个地方。所以一般使用js指定事件处理程序。

#### DOM0级事件处理程序
通过js指定事件处理程序的传统方式，就是将一个函数赋值给一个事件处理程序属性，这种方式非常简单，同时也具有跨浏览器的优势。

首先需要取得一个操作对象的引用，而每个元素（包括window和document）都有自己的事件处理程序，这些属性通常是小写的，如onclick，然后将这个属性值设置为一个函数即可，将属性值设置为null即可删除已指定的事件处理程序：
```js
var btn = document.getElementById('mybtn');
btn.onclick = function(){
  // ...
}

btn.onclick = null; // 删除
```
DOM0级方法指定的事件处理程序被认为是元素的方法，因此，这时的事件处理程序是在元素的作用域中运行，即this等于事件的当前元素。以这种方式添加的事件处理程序会在事件流的冒泡阶段被处理。

#### DOM2级事件处理程序
DOM2级事件处理程序定义了2个方法用于指定/删除事件处理程序：addEventListener/removeEventListener，所有的DOM节点都包含这两个方法，并且接收三个参数：要处理的事件名、事件处理函数、布尔值（true则表示在捕获阶段调用，false则表示在冒泡阶段调用）。
```js
var btn = document.getElementById('mybtn');
btn.addEventListener('click',function(){
  // ...
}, false);
```
DOM2级事件处理程序作用域也是当前元素（同DOM0级事件处理程序一样），其最大的好处是可以添加多个同名事件处理函数（如两个click事件），按照添加的顺序触发。

但通过addEventListener添加的事件处理程序只能使用对应的removeEventListener来移除，移除时传入的参数与添加时一样（3个参数都必须相同），这意味着若第二个参数是匿名函数则无法移除，所以一定要给每一个事件处理函数命名。

大多数情况下，都将事件处理程序添加到事件流的冒泡阶段，这样可以最大限度兼容各种浏览器，若不是特殊情况，不建议在事件捕获阶段注册事件处理函数。

#### IE事件处理程序
IE实现了与DOM类型的两个方法：attachEvent/detachEvent，这对方法接受相同的两个参数：事件处理程序名、事件处理函数。IE8-只支持事件冒泡，所以通过attachEvent添加的事件处理程序都会被添加到冒泡阶段。

与DOM类型的区别：
- 方法名，attachEvent系列方法的第一个参数名是带on前缀的，比如“onclick”，而addEventListener则是“click”。
- 函数的作用域，DOM0级方法情况中，作用域为所属元素。而IE情况下，作用域为全局作用域，因此this等于window。
- 触发顺序，以相反的顺序被触发

#### 跨浏览器的事件处理程序
要保证处理事件代码在大多数浏览器下保持一致则只能关注冒泡阶段，同时需要检测浏览器并视情况使用DOM0、DOM2、IE等方法来添加事件。
```js
var EventUtil = {
  addHandler: function(e, type, handler){
    if(e.addEventListener){
      e.addEventListener(type, handler, false);
    }else if(e.attachEvent){
      e.attachEvent('on'+type, handler);
    }else{
      e['on'+type] = handler;
    }
  },
  removeHandler: function(e, type, handler){
    if(e.removeEventListener){
      e.removeEventListener(type, handler, false);
    }else if(e.detachEvent){
      e.detachEvent('on'+type, handler);
    }else{
      e['on'+type] = null;
    }
  }
}
```

### 事件对象
在触发DOM上的某个事件时，会产生一个事件对象event，这个对象中包含着所有与事件有关的信息，包括导致事件的元素，事件的类型、以及其他与特定事件相关的信息。例如，鼠标位置、按键信息等，但每个浏览器实现不太一致。

同时由于event对象包含特定事件相关的属性和方法，触发的事件类型不一样，可用的属性和方法也不一样。[HTML DOM Event 对象](http://www.w3school.com.cn/jsref/dom_obj_event.asp)

#### DOM中的事件对象
兼容DOM的浏览器中，无论指定事件处理程序时使用什么方法（DOM0级或DOM2级）都会将一个event对象传入事件处理函数。
```js
<input type="button" value="click me" onclick="alert(event.type)"/> 

btn.onclick = function(event){ // 此处的event用其他变量名也可以
  event.type; // ...
}

btn.addEventListener('click',function(event){
   event.type; // ...
}, false);
```

在事件处理函数内部，对象this始终等于currentTarget的值，而target则只包含事件的实际目标，若直接将事件处理程序指定给目标元素，则this，currentTarget、target包含相同的值。

通过type属性，则可以在一个函数中处理多个事件。

要阻止默认行为（如a标签的跳转行为）则可以使用preventDefault方法（只有cancelable为true时才可使用）。

stopPropagation方法则可以阻止事件在DOM层次的传播，即取消进一步的事件捕获或冒泡，例如在按钮上调用，则会阻止触发document.body上注册的事件。

eventPhase属性可以用来确定事件当前所处的事件流的阶段，若在捕获阶段调用的事件处理程序则eventPhase为1，若在事件处理程序处于目标对象上则为2，若在冒泡阶段调用的事件处理程序则为3（注意，尽管“处于目标”阶段发生在冒泡阶段，但eventPhase仍然一直为2）：
```js
btn.onclick = function(event){
  event.eventPhase; // 2
}

document.body.addEventListener('click',function(event){
   event.eventPhase; // 1
}, true);

document.body.onclick = function(event){
   event.eventPhase; // 3
}
```
只有在事件处理程序执行期间event对象才会存在，一旦事件处理程序执行完则event对象就会被销毁。

#### IE中的事件对象
与访问DOM中的event对象不同，要访问IE中的event对象有几种不同的方式，取决于指定事件处理程序的方法。

使用DOM0级的方法添加事件处理程序时，event对象作为window对象的一个属性存在。

因为IE中事件处理程序的作用域是根据它的方法来确定的，所以不能认为this会始终等于事件目标，故最好使用event.srcElement比较好。
```js
<input type="button" value="click me" onclick="alert(event.type)"/> 

btn.onclick = function(){
  var event = window.event;
  event.type; // 'click'
  event.srcElement === this; // true
}

btn.attachEvent('onclick',function(event){
  event.type; // 也可以使用window.event
  event.srcElement === this; // false
});
```

#### 跨浏览器的事件对象
虽然IE和DOM中的event对象不同，但IE中的event对象的全部信息和方法在DOM中都有只是实现方式不同，因此实现两种事件模型之间的映射非常容易。主要是4个属性：event对象、target对象、preventDefault方法、stopPropagation方法。

### 事件类型
web浏览器中可能发生的事件有很多类型，不同的事件类型有不同的信息，DOM3级事件规定了如下几类事件：
- UI事件，当用户与页面上的元素交互时触发
- 焦点事件，当元素获得/失去焦点时触发
- 鼠标事件，当通过鼠标在页面上执行操作时触发
- 滚轮事件，当使用鼠标滚轮时触发
- 文本事件，当在文档中输入文本时触发
- 键盘事件，当用户通过键盘在页面上执行操作时触发
- 合成事件，当为IME（input method editor 输入法编辑器）输入字符时触发
- 变动（mutation）事件，当底层DOM结构发生变化时触发
- *变动名称事件，当元素或属性名变动时触发，此类事件已废弃*
除了上述事件，H5还定义了一些事件，同时一些浏览器在BOM，DOM中也实现了一些专有事件。

DOM3级事件模块在DOM2级事件模块基础上重新定义了这些事件，也添加了一些新事件，包括IE9在被的所有主流浏览器都支持DOM2和DOM3级事件。

#### UI事件
UI事件指的是那些不一定与用户操作有关的事件，这些事件在DOM规范出现前以各种形式存在，为了向后兼容所以在DOM规范中保留。现有UI事件如下：
- DOMActivate， 表示元素已经被用户操作激活（通过鼠标或键盘），此事件在DOM3级事件中被废弃，不建议使用
- load，当页面完全加载（包括图片、js、css文件）后在window上触发，当所有框架都记载完后在框架集上触发，当图像加载完后在img元素上触发，或当嵌入的内容加载完成后在object元素上触发
- unload，与load相对，为卸载完成后触发
- abort，当用户停止下载时，若嵌入的内容没有加载完则在object元素上触发
- error，当发生js错误时window上面触发，当无法加载图像时img上触发，当无法嵌入内容时object元素上触发，当有框架无法加载时在框架集上触发
- select，当用户选择文本框（input/textarea）中的一个或多个字符时触发
- resize，当窗口或框架的大小变化时在window或框架上触发
- scroll，当用户滚动内容时在该元素上触发，对整个页面而言则为body元素
这一类的事件多与window对象或表单控件有关。所有事件（DOMActivate除外）都归为DOM2级事件中的HTML事件。

##### load事件
load事件最常用的一种方式就是监听页面是否完成加载。通过js可以在window上监听此事件，DOM中，event.target值为document，但在IE中该事件的event对象中srcElement不存在。

一般的，因为HTML代码无法访问window元素，window上发生的任何事件都可以在body元素上通过相应的属性来指定，所以也可以在body元素上使用onload属性来触发，但这种方式仅仅只是为了保证向后兼容。首选还是js监听的方式。
```js
EventUtil.addHandler(window, 'load', function(event){
  // ...
});

<body onload="alert('Loaded');">
  // ...
</body>
```

除了window上使用load事件之外，还可以在img元素，Image对象（`new Image()`的实例），script/link（非标准）等元素上使用。

##### unload事件
与load对应的是unload事件，这个事件在文档被完全卸载后触发，只要用户从一个页面切换到另一个页面，就会发生unload事件，而此事件常用于清除引用，避免内存泄漏，与load事件类型，有两种指定事件处理程序的方式，一个是通过js注册unload事件，一个是body元素上的onunload属性。

注意，由于unload事件实在一切被卸载完成后才触发，因此页面加载后存在才生成的一些对象此时就不存在了，比如操作DOM节点或元素样式会报错。

*此事件有问题，测试时未成功触发*

##### resize事件
当浏览器窗口被调整时就会触发resize事件（包括最大化/最小化，用户手动拉伸），而关于何时触发，不用浏览器不同的机制，Firefox是在用户停止调整时触发，而其他浏览器则每变化1像素则触发1次，因此谨慎在该事件处理函数中加入大量任务，因为函数会被频繁执行从而降低效率。

##### scroll事件
scroll事件在window对象上发生，但实际表示页面元素的变化，在混杂模式下，通过body元素的scrollLeft/scrollTop可以监控变化，在标准模式下，浏览器通过html元素反映变化（safari基于body元素跟踪滚动位置）。
```js
EventUtil.addHandler(window, 'scroll', function(event){
  if(document.compatMode == 'CSS1Compat'){
    document.documentElement.scrollTop;
  }else{
    document.body.scrollTop;
  }
});
```

#### 焦点事件
焦点事件在页面获得/失去焦点时触发，与document.hasFocus方法document.activeELement属性配合可以知道用户在页面上的行踪。焦点事件有如下6种：
- focus，在元素获得焦点时触发，此事件不会冒泡，所有浏览器都支持
- blur
- DOMFocusIn，在元素获取焦点时触发，与HTML事件focus等价，但它会冒泡，且仅Opera支持，DOM3事件已废弃，选择focusin
- DOMFocusOut
- focusin，在元素获得焦点时触发，冒泡版本的focus，Firefox不支持
- focusout
当一个焦点从页面中的一个元素（元素1）移动到另一个元素（元素2），会依次触发下列事件：
1. focusout 元素1
2. focusin 元素2
3. blur 元素1
4. DOMFocusOut 元素1
5. focus 元素2
6. DOMFocusIn 元素2

注，即使focus/blur不冒泡，但仍然能在捕获阶段监听到。

#### 鼠标和滚轮事件
鼠标是主要外设，在DOM3级事件中定义了9个鼠标事件：
- click，单击鼠标左键或按下回车触发，即onclick事件处理程序既可以通过键盘也可以通不过个鼠标执行
- dblclick，双击鼠标左键
- mouseenter，在鼠标从元素外部首次移动到元素范围内时触发，不冒泡，且移动到子元素上不会触发。IE、Firefox，Opera支持
- mouseleave，与mouseenter对应
- mousemove，当鼠标在元素内部移动时触发，不能通过键盘触发
- mouseout，当鼠标位于元素上方，然后将其移入另一个元素（可为父元素/子元素）时触发，不能通过键盘触发
- mouseover，当鼠标位于一个元素外部，然后将其首次移入另一个元素边界内时触发，不能通过键盘触发
- mousedown，在用户按下鼠标按键时触发（不论左键/右键），不能通过键盘触发
- mouseup，用户释放鼠标时触发，不能通过键盘触发

除了mouseenter/mouseleave，所有鼠标事件都会冒泡，也能被取消。而取消鼠标事件会影响浏览器默认行为，也会影响到其他事件，比如：
只有在同一个元素上相继触发mousedown和mouseup事件才会触发click事件，若mousedown和mouseup其中一个被取消，则click不会被触发。类似的关系还有click与dblclick，这四个事件的顺序始终为：
1. mousedown
2. mouseup
3. click
4. mousedown
5. mouseup
6. click
7. dblclick

鼠标事件都是在浏览器视口中的特定位置上发生的，这个位置信息保存在event.clientX/clientY属性中，分别表示事件发生时鼠标在视口中的水平/垂直坐标。

pageX/pageY可获取页面坐标，页面坐标根据页面本身而非视口计算。当页面为滚动时，pageX和clientX的值相等。
IE8-不支持页面坐标，但通过scrollLeft + clientX可以计算出pageX。(混杂模式下scrollLeft为document.body的属性，而标准模式下为document.documentElement的属性)

screenX/screenY可以确定鼠标相对屏幕的坐标信息。

当按下鼠标同时按下键盘的某些键，如Shift、Ctrl、Alt、Meta（Mac中为Cmd键，Windows中为Win键），能修改鼠标事件行为（组合事件）。DOM中规定了4个属性，分别表示修改键的状态，shiftKey，ctrlKey，altKey，metaKey。这些属性都是布尔值，若相应的键按下则为true。当鼠标事件发生时检测这几个属性能确定用户是否同时键盘的对应键。

在mouseout/mouseover发生时，会涉及2个元素，一个主元素一个相关元素，比如对mouseover而言获得光标的元素就是主元素。DOM通过event.relatedTarget属性提供了相关元素的信息，此属性只对mouseout/mouseover事件有效，其他事件此属性值为null。

在click事件触发时（单击鼠标/按下回车键），对mousedown/mouseup事件而言，其event对象存在一个button属性表示按下/释放的按钮，DOM的button属性中0表示主键（鼠标左键），1表示中间按键（鼠标滚轮），2表示次键（鼠标右键）。

DOM2级事件规范提供了event.detail属性，用于给出有关事件的更多信息。比如对鼠标事件来说，detail中就包含了表示给定位置上发生多少次单击的数值。IE也提供了一些属性，但用处不大。

鼠标事件中还有一类滚轮事件，即mousewheel事件。当用户通过鼠标滚路与页面交互时就会触发该事件，该事件可以在任何元素上触发，且会冒泡到window对象（IE8下为document对象）上。