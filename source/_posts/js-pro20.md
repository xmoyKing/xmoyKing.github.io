---
title: JavaScript高级程序设计-20-事件
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
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

鼠标事件中还有一类滚轮事件，即mousewheel事件。当用户通过鼠标滚路与页面交互时就会触发该事件，该事件可以在任何元素上触发，且会冒泡到window对象上（IE8下为document对象）。event.wheelDelta属性表示滚轮的数量（120的倍数，上+下-）。

Firefox支持一个类似的事件，DOMMouseScroll，该事件与mousewheel一样，滚轮信息保存在detail属性中（+-3的倍数）。

在触摸设备上（iPhone/iPad Safari），支持的事件有如下特性：
- 不支持dblclick事件，双击浏览器会放大窗口，且无法改变该行为。
- 轻击可单击元素（如链接，或注册了onclick的元素）会触发mousemove事件，若此操作导致内容变化则不再有其他事件发生，若无变化，则依次发生mousedown、mouseup、click事件。轻击不可单击元素不会触发任何事件。
- mousemove事件也会触发mouseover/mouseout事件
- 两个手机放在屏幕上且滚动会触发mousewheel/scroll事件

关于无障碍性：
- 使用click事件执行代码（屏幕阅读器无法触发onmousedown）
- 不使用onmouseover（屏幕阅读器无法触发）
- 不要用dblclick，键盘无法触发此事件

#### 键盘与文本事件
DOM3级事件为键盘事件制定了规范，所有元素都支持如下3个事件，但一般用在文本框上：
- keydown，按下任意键触发，若按住不放，则重复触发
- keypress，按下字符键触发，若按住部分，则重复触发
- keyup，释放按下的键时触发
当用户按了一个字符键时，首先触发keydown，然后是keypress，最后是keyup。其中keydown和keypress都是在文本框改变之前触发，而keyup则在文本框改变之后触发。

文本事件至于一个，textInput，该事件是keypress的补充，目的是为了在文本显示给用户之前更容易拦截文本，在文本插入文本框之前会触发textInput事件。此时event.data中保存着实际输入的字符。

当keydown和keyup发生时，event.keyCode表示按下的键值码，对字符键，值与ASCII码中的小写字母和数字对应。

当keypress发生时，event.charCode表示按下的字符键，然后基于可以用String.fromCharCode将其转换为实际的字符，浏览器之间有差异，需要先检测是否可用。

#### 变动事件
DOM2级的变动（mutation）事件能在DOM中的某一部分发生变化时触发，变动事件是为XML/HTML DOM设计的，与语言无关，DOM2级定义了如下变动事件：
- DOMNodeInserted，当一个节点作为子节点被插入到另一个节点中时触发
- DOMNodeInsertedIntoDocument，在一个节点被直接插入到文档或通过子树间接插入文档后触发，此事件在DOMNodeInserted之后触发
- DOMNodeRemoved，当节点从父节点被移除时触发
- DOMNodeRemovedFromDocument
- DOMAttrModified， 在属性被修改后触发
- DOMCharacterDataModified，在文本节点的值发生变化时触发
- DOMSubtreeModified，在DOM结构中发生任何变化时触发，此事件在其他任何事件触发后都会触发。

DOM3级事件支持的变动事件（废除了部分）
##### 删除节点
使用removeChild或replaceChild时，首先触发DOMNodeRemoved事件，事件的event.target是被删节点，event.relatedTarget是父节点，此时，节点尚未从其父节点删除，因此parentNode属性仍然有效（同event.relatedTarget），此事件会冒泡，因此可以在DOM任何层次上处理。

若被删除的节点包含子节点，则其所有子节点以及被删除的节点会相继触发DOMNodeRemovedFromDocument事件，但此事件不会冒泡，所以只有直接指定给其中一个子节点的事件处理程序才会被调用，此事件的目标是相应子节点或被删除的节点，除此之外event对象不包含其他信息。

然后触发的是DOMSubtreeModified，此事件的event.target是被删节点的父节点，此时event对象不包含其他信息。

##### 插入节点
在使用appendChild、replaceChild、insertBefore向DOM插入节点时，会先触发DOMNodeInserted事件，此事件的event.target是被插入的节点，relatedTarget是父节点，此事件被触发时，节点已经被插入到了新的父节点中，由于此事件会冒泡，因此可以在DOM的各个层次处理它。

然后会在新插入的节点上触发DOMNodeInsertedIntoDocument事件，此事件不冒泡，因此必须在插入节点之前对它添加这个事件处理程序，此事件的target是被插入节点，除此之外event对象不包含其他信息。

最后一个触发的事件是DOMSubtreeModified，此事件的event.target是新插入节点的父节点。

#### HTML5事件
H5详细列出了浏览器应该支持的所有事件，但并不是所有事件都被所有浏览器支持。
##### contextmenu事件
单击鼠标右键能调出上下文菜单，contextmenu事件用于表示何时应该显示上下文菜单，以便取消默认的上下文菜单而提供自定义的菜单。

由于contextmenu冒泡，因此可以为document指定一个事件处理程序。此事件的target是用户操作的元素，可以用preventDefault取消此事件，同时由于contextmenu属于鼠标事件，所以其事件对象中包含与光标位置有关的所有属性。

通常contextmenu事件用来显示自定义上下文菜单，使用onclick来隐藏该菜单。

##### beforeunload事件
beforeunload发生在window对象上，作用是在页面被卸载（关闭）前可以有一个提示框，能够阻止关闭页面，但不能彻底阻止，此事件将弹出提示框询问是否真的要关闭。

为了弹出对话框，必须将event.returnValue设置为显示给用户的字符串（IE/Firefox），同时将字符串作为函数的返回值（Chrome/Safari）。

##### DOMContentLoaded事件
与window的load事件（一切都加载完，包括js、css、img）不同，DOMContentLoaded事件是在形成完整的DOM树之后就触发，此时不管img、js、css、或其他资源是否加载完毕。即，DOMContentLoaded事件能让用户尽早的与页面进行交互。

DOMContentLoaded事件可以添加在document或window对象上（其target值为document，所以其实是发生在document上，冒泡到window上）。DOMContentLoaded事件的event对象不会提供额外信息。

对于不支持DOMContentLoaded事件的浏览器，可在页面加载期间设置一个0毫秒的定时器`setTimeout(func, 0)`，表示在当前js处理完成后立即运行这个函数，在页面下载和构建期间，只有一个js处理过程，因此这个定时器会在该过程结束后立即触发。

因为这个定时器能否在DOM加载完后立即执行（如DOMContentLoaded事件执行的时间点）取决于浏览器和页面中的其他代码，所以为了确保有效，必须将其作为页面中第一个定时器，同时最好能最先执行。但即使如此，也无法保证该定时器一定早于load事件。

##### readystatechange事件
readystatechange事件在IE、Firefox、Opera下可用。

这个事件的目的是提供与文档或元素的加载状态有关的信息，但此事件的行为有时候比较难以预测。支持此事件的对象都有一个readyState属性，该属性值有5种：
- uninitialized,为初始化，对象存在但未初始化
- loading，加载中，对象正在加载数据
- loaded，加载完成，对象加载数据完成
- interactive，可交互，可以操作对象，但还没完全加载完
- complete，完成，对象已经加载完毕
并非所有对象都会经历readyState这几个阶段，即，若某几个阶段不适用于对象，则该对象完全可能跳过该阶段，但并没有规定那个阶段适合于那个对象，所以，readystatechange事件可能会少于4次，且readyState属性值也不总是连续的。

对document而言，值为interactive的readyState会在与DOMContentLoaded大致相同的时刻触发readystatechange事件，此时，DOM树已经加载完成，可以安全操作，因此会进入interactive阶段，但图像或其他外部文件不一定可用。

此事件的event不会提供任何信息，包括target属性。

在与load事件一起使用时，无法预测两个事件触发的先后顺序，在页面包含较多或较大外部资源时，load事件触发之前会进入交互阶段，但若页面包含资源较小较少，则很难确定readystatechange事件和load事件发生的顺序。

同时interactive和complete阶段的顺序也如load和readystatechange事件一样，顺序无法精确定下，因此为了尽快执行代码，有必要同时检测交互和完成阶段：
```js
EventUtil.addHandler(document, 'readystatechange', function(event){
  if(document.readyState == 'interactive' || document.readyState == 'complete'){
    EventUtil.removeHandler(document, 'readystatechange', arguments.callee);
    // content loaded;
  }
});
```
如此，若已经进入交互阶段或完成阶段则removeHandler，是为了避免在其他阶段执行该事件处理程序。这样就达到与DOMContentLoaded相近的效果。

另外，script（IE/Opera）和link（IE）元素也会触发readystatechange事件，可用来确定外部的js和css文件是否加载完成。而基于元素触发的readystatechange事件也需要向对待document那样，同时检测loaded和complete阶段。

##### pageshow和pagehide事件
现在浏览器（IE9+）有一个特性，**往返缓存（back-forward cache, bfcache）**,可以在用户使用“后退”/“前进”按钮时加快页面的转换速度，这个缓存不仅保存着页面数据，还保存了DOM和js状态，实际上就是将整个页面都保存在了内存中。若页面位于bfcache，你们再次打开页面就不会触发load事件，因此提供了一些新事件用于支持bfcache的行为：

pageshow事件就是在页面显示时触发，无论该页面是否来自bgcache，在重新加载的页面中，pageshow会在load事件触发后触发，而对bfcache中的页面，pageshow会在页面状态完全回复时触发。

注意，此事件的target是document，但必须将事件处理程序添加到window。

pageshow事件的event对象中有一个布尔值属性，persisted，若页面被保存在bfcache中则为true，否则为false。通过检测persisted属性，可以根据页面在bfcache中的状态来确定是否需要采取其他操作。

与pageshow事件对应的是pagehide事件，对pagehide事件，persisted为true则表示页面卸载后会被保存在bfcache中，否则为false，因此第一次触发pageshow时，persisted值一定是false。

##### hashchange事件
hashchange事件是在url中的hash字符串发生变化时触发，在ajax应用中，经常会用到url参数来保存状态或导航信息。

hashchange事件需要添加到window对象上，此时event对象会额外有oldURL和newURL属性（IE/Safari不支持），分别表示hash变化前后的完整URL。

#### 设备事件
设备事件（device event）可以让开发者确定用户在如何使用设备，但某些API还是特定浏览器厂商的事件，而未成为标准：
1. orientationchange事件，确定用户何时将设备由横向查看切换为纵向查看模式，移动safari的window.orientation属性可能取3种值：0表示纵向，90表示左旋转横向（主屏幕按钮在右侧），-90相反。
2. MozOrientation事件，当设备的加速计检测到设备方向改变时触发，但与orientationchange事件不同，该事件提供一个平面的方向变化。
3. deviceorientation事件，与MozOrientation事件类似，但其目的是表示设备在空间中的朝向。
4. devicemotion事件，展示设备移动（不仅仅是设备方向改变）

#### 触摸与手势事件
触摸与手势事件都是由apple引入的，开始只有移动版的safari支持，后来移动版的webkit（包括android）也开始支持，只针对触摸设备的事件
##### 触摸事件
- touchstart，当手指触摸屏幕时触发，即使已经有一个手指放在屏幕上也会触发
- touchmove，当手指在屏幕上滑动时连续触发,取消此事件会阻止滚动
- touchend，当手指从屏幕上移开时触发
- touchcancel，当系统停止跟踪触摸时触发（确切的事件不太清楚）
上述几个事件都会冒泡，也都可通过preventDefault阻止取消，每个触摸事件的event对象都提供了鼠标事件中常见的属性。除了常见的DOM属性外，还有3个用于跟踪触摸的属性：
- touches，表示当前跟踪的触摸操作的Touch对象的数组
- targetTouches，特定于事件目标的Touch对象的数组
- changedTouches，表示自上次触摸依赖发生了什么改变的Touch对象的数组

每个Touch对象包含下列属性：
- clientX/clientY，触摸目标在视口中的x/y坐标
- pageX/pageY，触摸目标在页面中的x/y坐标
- screenX/screenY，触摸目标在屏幕中的x/y坐标
- identifier，标志触摸的唯一ID
- target，触摸的DOM节点目标

在触摸屏幕上的元素时，事件发生顺序如下（包括鼠标事件也会被触发）：
- touchstart
- mouseover
- mousemove(一次)
- mousedown
- mouseup
- click
- touchend

##### 手势事件
当两个手指触摸屏幕时会产生手势，手势通常会改变显示项的大小，或渲染显示项：
- gesturestart，当一个手指已经按在屏幕上，而另一个手指又触摸屏幕时触发
- gesturechange，当触摸屏幕的任何一个手指的位置发生变化时触发
- gestureend，当任何一个手指从屏幕上移开时触发
这些事件都会冒泡，同时这些事件的target是两个手指都位于其范围内的那个元素。

触摸事件和手势事件之间存在关联，当一个手指放在屏幕上时，会触发touchstart，若同时另一个手指也放在屏幕上，则会西安出发gesturestart事件，随后触发基于该手指的touchstart事件，若手指在屏幕上滑动则会触发gesturechange事件，但只要有一个手指移开就会触发gestureend事件，然后基于该手指触发touchend。

与触摸事件一样，每个手势事件的event对象包含标准的鼠标事件属性，同时额外还有rotation和scale。rotation表示手指变化引起的旋转角度，负值表示逆时针旋转。scale表示手指间距离的变化情况，从1开始随距离拉大而增长，距离缩短而减小。

### 内存和性能
由于事件处理程序为Web应用提供交互能力，所以很容易导致页面添加大量的处理程序，在js中，添加到页面上的事件处理程序数量会直接关系到页面的整体运行性能。原因之一是每个函数都是对象，都会占用内容，内存中对象越多，性能就越差。其次必须事先指定所有事件处理程序而导致的DOM访问次数，会延迟整个页面的交互就绪时间。

#### 事件委托
对“事件处理程序过多”问题的解决方案就是事件委托，事件委托利用事件冒泡，只指定一个事件处理程序，就可以管理某一类型的所有事件，如click事件会一直冒泡到document层次，即只需要为整个页面指定一个onclick事件处理程序，而不必为每个单击的元素单独添加。

若可行的话，考虑为document对象添加一个事件处理程序，用以处理页面上发生的某种特定类型的事件，优点如下：
- document对象很快就可以访问，而且可以在页面生命周期的任何时间点上为它添加事件处理程序（无需等DOMContentLoaded、load事件），即，只要可单击的元素出现在页面上即可执行注册的功能。
- 在页面中设置事件处理程序所需的事件更少，只添加一个事件处理程序所需的DOM引用更少，所花的时间更少。
- 整个页面占用的内存空间更少，能够提升整体性能
最适合采用事件委托的事件包括：click、mousedown、mouseup、keydown、keypress。mouseover/mousout事件也冒泡，但处理不易且一般需要计算元素位置（当鼠标从一个元素移到其他子节点时，或者当鼠标移出该元素时，都会触发mouseout事件）

#### 移除事件处理程序
当内存中保存着过时不用的“空事件处理程序（dangling event handler）”是造成web内存和性能的主要原因。

每当事件处理程序指定给元素时，运行中的浏览器代码与支持页面交互的js代码之间会建立一个连接。采用事件委托可以减少连接数量。在不需要的时候移除事件处理程序也可以解决此问题。

有两种情况下会造成空事件处理程序，第一种是从文档中移除带有事件处理程序的元素时，比如removeChild/replaceChild/innerHTML将页面中某一部分移除，此时，原来添加到元素中的事件处理程序内存就有可能无法被正常回收。此种情况，最好在移除元素之前先移除事件，其次，通过事件委托将事件注册到更高层元素上也可以避免这种问题。

另一个种就是卸载页面时，要解决这种情况，最好的方法就是在页面卸载之前，通过onunload将事件都移除，若以前是通过事件委托注册的事件那么此时移除的事件就能大大减少（跟踪的事件处理程序越少，移除就越容易），简单的说就是通过onload添加的最后都要通过onunload移除。

### 模拟事件
模拟事件就是通过js可以在任意时刻触发特定的事件，而此时的事件就如同浏览器原生的事件一样，该冒泡、该执行的都会继续执行。

在测试web应用时，模拟触发事件是非常有用的。DOM2级规范谓词规定了模拟特定事件的方式，（IE8-有特殊,暂时忽略）。

#### DOM中的事件模拟
可以在document对象上使用createEvent方法创建event对象，接收一个参数，表示要创建的事件类型的字符串，在DOM2级中，所有字符串都使用英文复数形式，在DOM3级改为了单数。
- UIEvents，一般化的UI事件，鼠标和键盘事件都继承自UI事件，DOM3中为UIEvent
- MouseEvents，一般化的鼠标事件
- MutationEvents，一般化的DOM变动事件
- HTMLEvents，一般化的HTML事件，无对应的DOM3级事件
创建了event对象后，还需要使用与事件有关的信息对其进行初始化，每种类型的event对象都有一个特殊的方法，为它传入适当的数据可以初始化该event对象，不同类型的方法名不同，取决于createEvent中使用的参数。
最后一步就是触发事件，需要使用dispatchEvent方法，传入表示要触发事件的event对象即可。

##### 模拟鼠标事件
`createEvent('MouseEvents')`返回的event对象有一个initMouseEvent方法，用于指定与该鼠标事件有关的信息，该方法接收15个参数，分别与鼠标事件中每个典型属性一一对应。如type、bubbles、cancelable、view（几乎都是设置为document.defaultView）等。

##### 模拟键盘事件
DOM2级事件中没有单独对键盘事件做出规定，而仅仅是在草案中提及，DOM3级事件中的键盘事件其实就是DOM2级草案中的事件。

传入"KeyboardEvent"可以创建一个键盘事件，返回的事件对象会包含一个initKeyEvent方法，该方法接收参数：
- type
- bubbles
- cancelable
- view
- key，表示按下的键的键码
- location,整数，表示按下哪里的键，0默认为主键盘，1表示左，2表示右，3表示数字键盘，4表示移动设置（虚拟键盘），5表示手柄
- modifiers，字符串，空格分隔的修改键列表，如"Shift Ctrl"
- repeat,整数，在一行中按了多少次这个键

Firefox中传入"KeyEvents"创建。

##### 模拟其他事件
此处指的是变动事件和HTML事件，一般用的较少，略过。

##### 自定义DOM事件
DOM3级定义了“自定义事件”，自定义事件不是DOM原生触发的，而是开发者创建的事件。

传入"CustomEvent"创建一个自定义DOM事件,返回的事件对象中有一个initCustomEvent方法。

#### IE8-中的事件模拟
思路与DOM的类似，但每一步骤采用了不一样的方法名好方式。

调用document.createEventObject方法在IE中创建event对象，但与DOM不同，此方法不接受参数，而是返回一个通用event对象，然后手动显式设置该对象中所有必要的信息。最后在目标元素上调用fireEvent方法，此方法接收2个参数，事件处理程序名和上一步的event对象。

### 小结
事件是将js同网页联系在一起的主要方式，DOM3级事件规范和H5定义了常见的大多数事件，但仅仅是基本事件，浏览器厂商同时也实现了自己的专有事件。
事件是js最重要的主题之一，深入理解事件的工作机制以及它们对性能的影响至关重要，在使用时需要考虑内存和性能问题：
- 有必要限制一个页面中事件处理程序的数量，数量太多会导致占用大量内存，而且会让用户感觉页面延迟高
- 建立在事件冒泡机制之上的事件委托技术，从而有效减少事件处理程序的数量
- 建议在浏览器卸载页面之前移除页面中移除事件处理程序

使用js模拟事件，DOM2级和DOM3级事件规范规定了模拟事件的方法，为模拟各种事件提供了方便。