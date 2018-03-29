---
title: Angular2入门-模板-1
categories: Angular
tags:
  - TypeScript
  - Angular
  - 揭秘 Angular2
date: 2017-10-07 09:23:32
updated:
---

模板是一种自定义的标准化页面，通过模板和模板中的数据结合，可以生成各种各样的网页。

在Angular中，模板的默认语言是HTML，几乎所有的HTML语法在模板中都适用，script标签除外，主要是为了防止XSS，同时一些HTML元素在模板中并不起作用，如html、body、base等标签。Angular可以通过组件和指令对模板的HTML元素进行扩展，这些扩展将以新的元素或属性的形式出现。

### 模板语法概览
初步认识模板语法。 具体查看[Angular 2 模板语法](http://www.runoob.com/angularjs2/angularjs2-template-syntax.html)

| 示例 | 名称 | 说明 | 语法 |
| - | - | - | - |
| `<p>｛｛detail.telNum ｝｝ </p>` | 插值 | 绑定属性变量的值到模板中 | `｛｛模板表达式｝｝` |
| `<div [title]="name">hello world </div>` | DOM元素属性绑定 | 将模板表达式name的值绑定带元素div的属性title上 | `[DOM元素属性]="模板表达式"`或`bind-DOM元素属性="模板表达式"` |
| `<td [attr.colspan]="｛｛ 1 + 2 ｝｝">合并单元格</div>` | HTML标签特性绑定 | 将模板表达式的返回值绑定到元素td标签特性colspan上 | `[attr.HTML标签特性]="模板表达式"` |
| `<div [class.isblue]="isBlue()">单击</div>` | Class类绑定 | 当isBlue()函数值为true时为div添加类名为isBlue的样式 | `[class.css类名]="模板表达式"` |
| `<button [style.color]="isRed?'red':'green'">红</div>` | Style样式绑定 | 当表达式isRed的值为true时设置button的文字颜色为红色，否则为绿色 | `[style.css样式属性名]="模板表达式"` |
| `<a class="edit" (click)="editContact()">编辑</a>` | 事件绑定 | 单击元素时会触发click事件，需要时也可以传递$event对象，如`(click)="editContact($event)"` | `(事件)="模板语法"`或`on-事件="模板语句"` |
| `<div [(title)]="name"></div>` | 双向绑定 | 组件和模板间双向数据绑定，等价于`<div [title]="name" (titleChange)="name=$event"></div>` | `[(绑定目标)]="模板表达式"`或`bindon-绑定目标="模板表达式"` |
| `<input type="text" ##name name="name" id="name" /><p>｛｛name.value｝｝</p>` | 模板局部变量 | 在当前模板中创建一个对id值为name的input元素的引用变量name，相当于`document.getElementById('name')` | `##变量名`或`ref-变量名` |
| `<p>张三的生日是｛｛birthdat 管道操作符 date ｝｝</p>` | 管道操作符 | 元素数据birthday经管道转换后输出期望数据并显示在模板中 | `输入数据 管道操作符 管道名:管道参数` |
| `<p>｛｛detail?.telNum ｝｝</p>` | 模板表达式操作符 | 模板表达式操作符表明detail.telNum属性不是必须存在的，若其值为undefined，那么后面的表达式将被忽略，不会引发异常 | `?.` |
| `<p *myUnless="boolValue">myUnless is false now.</p>` | 星号前缀 | 使用星号前缀可以简化对结构指令的使用，Angular会将带有星号的指令引用替换成带有`<template>`标签的代码，等价于`<template [myUnless]="boolValue"><p>myUnless is false now.</p></template>` | `*指令` |

### 数据绑定
Angular提供了多种数据绑定的方式，根据数据流动的方向分为3种：

| 数据流向 | 示例 | 绑定类型 |
| - | - | - |
| 单向：数据源到视图目标（属性绑定） | `<p>｛｛detail.telNum｝｝</p>`, `<div [title]="name">hello world</div>`, `<div [style.color]="color">hello world</div>` | 插值DOM元素属性绑定HTML标签特性绑定 |
| 单向：从视图目标到数据源（事件绑定） | `(click)="editContact()"`,`on-click="editContact()"` | 事件绑定 |
| 双向 | `<div [(title)]="name"></div>`, `<div bindon-title="name"></div>` | 双向绑定 |

上表中，除了插值外，在“=”的左侧都有一个目标名称，它可以被`[]`,`()`包裹，或者加上一个前缀（`bind-`,`on-`,`bindon-`）,这被称为绑定目标，而在“=”的右侧或者插值符号中的部分被称为绑定源。

有一对非常重要的概念，即DOM对象属性（Property）与HTML标签特性（Attribute），在英语中Property和Attribute都可以翻译为"属性"，名字虽然相同，但在模板中意义却不同。理解它们的不同是理解Angular数据绑定的关键。

| 定义方式 | 说明 |
| - | - |
| DOM对象属性（Property） | 以DOM元素为对象，其附加的内容，是在文档对象模型里定义的，如childNodes，firstChild等 |
| HTML标签特性（Attribute） | 是DOM节点自带的属性，是在HTML里定义的，即只要是HTML标签中出现的属性（HTML代码）都是Attribute，例如HTML中常用的colspan，align等 |

大多数情况下，DOM对象属性与HTML标签特性不是一一对应的，但有少量属性既是DOM对象属性又是HTML标签特性，如id、title、class（CSS类）等。
通常HTML标签特性代表着初始值，初始化后就不再发生改变，而DOM对象属性代表当前值，默认为初始值，它它会随着属性值而变化。
数据绑定是借助元素和指令的DOM对象属性和事件来运作的，而不是HTML标签特性。在Angular中，HTML标签特性唯一的作用就是用来进行元素和指令状态的初始化。

#### 模板表达式
模板表达式类似JS原生表达式，但不可以使用一些会引起副作用的JS表达式：
- 带有new运算符的表达式
- 赋值表达式（=，+=，-=等）
- 带有;或,的链式表达式
- 带有自增和自减操作（++和--）的表达式
- 不支持位运算符 | 和 &
- 部分模板表达式操作符被赋予了新的含义，如管道操作符，和安全导航操作符`?.`

模板表达式上下文通常就是它所在组件的实例，也可以包括组件之外的对象，如模板局部变量，但需注意，模板表达式不能引用任何全局命名空间中的成员，如window、document，console，Math等。

模板表达式书写原则：
- 避免视图变化的副作用
  一个模板表达式只能改变目标属性的值，不应改变应用的任何状态，Angular的单向数据流模式正是基于这条原则来的，在单独的渲染过程中，视图应该是可以预测到的，不必担心在读取组件值时会不小心改变其他的一些展示值
- 高效执行
  Angular执行表达式的频率很高，触发任何一次键盘或鼠标事件都可能执行，当计算成本很大时，可以考虑缓存那些计算得出的值。
- 使用简单的语句
  避免编写一些比较复杂的模板表达式
- 幂等性优先
  表达式应该遵循幂等性优先原则，幂等的表达式总是返回完全一致的东西，这样就没有副作用，并能提升Angular变化监测的性能，但表达式的返回值还是会随着它所依赖的值变化而变化。

#### 属性绑定
属性绑定是一种单向的数据绑定，数据从组件类流向模板，当要把一个视图元素的属性设置为模板表达式时，就需要用到属性绑定。

一般的，属性绑定不能用来从目标元素获取值，或者调用目标元素上的方法，即目标元素的值只能被设置，不能被读取，但可以使用@ViewChild和@ContentChild来读取目标元素的属性或调用它的方法。

在属性绑定中，“=”左侧中括号的作用是让Angular执行“=”右侧的模板表达式，并将结果赋值给该目标属性，若没有中括号，Angular会把右侧的模板表达式当做一个普通的字符串常量而不会计算该表达式，所以若赋值给目标属性的值是一个固定的字符串，中括号推荐省略。

Angular推荐使用DOM元素属性绑定，但当元素没有对应的属性可绑定的时候，则可以使用HTML标签特性绑定来设置值，例如table标签中的colspan或rowspan等，没有相对的DOM属性可供绑定，若直接用模板表达式赋值将会出现模板解析错误：
```html
<td colspan"{{ 1 + 2 }}">合并单元格</td>
```
因为colspan在td元素中并不是DOM元素属性，而是HTML标签特性，插值和属性绑定只能设置DOM元素属性，不能设置HTML标签特性。
HTML标签特性绑定在语法上类似于属性绑定，但中括号中的部分不是一个元素的属性名，而是由`attr.`的前缀和HTML标签特性名称组成的形式，然后通过一个模板表达式来设置HTML标签特性的值：
```html
<td [attr.colspan]="{{ 1 + 2 }}">合并单元格</td>
```

CSS类既属于HTML标签特性，又属于DOM对象属性，所以可以使用以上两种方式来完成属性绑定:
```html
<!-- 标准HTML样式类设置 -->
<div class="font14">14号字体</div>

<!-- 通过绑定重设或覆盖类 -->
<div class="red font14" [class]="changeGreen">14号绿字体</div>
```
当使用DOM对象属性绑定给`[class]`绑定值时，changeGreen对象会重写这个div元素的全部class。
另外Angular也提供了类似`[class.class-name]`的语法来完成属性绑定，当赋值为true时，将class-name这个类添加到该绑定的标签上，否则将移除这个类：
```html
<div [class.color-blue]="isBlue()">
  若isBlue返回true，则变为蓝色
</div>

<div class="footer" [class.footer]="showFooter">
  若showFooter为false，则footer这个CSS类将被移除
</div>
```

HTML标签内联样式可以通过Style样式绑定的方式来设置，也可以带上样式单位,支持驼峰式命名和连字符式命名
```html
<div [style.font-size.px]="isLarge ? 18 : 13">
  若isLarge返回true，则变为18px
</div>
```

属性绑定和插值本质上没有区别，在渲染视图之前，Angular会将插值表达式转换成属性绑定的形式，插值只是属性绑定的一个语法糖，推荐插值表达式。同时渲染之前，Angular会对内容进行安全处理，不允许带有script标签的HTML展示到浏览器中。

#### 事件绑定
事件绑定也是一种单向数据绑定形式，数据从模板流向组件类。
模板语法的上下文可以包含组件之外的对象，如模板局部变量和事件绑定语句中的$event。

目标事件可以是常见的事件（如click），也可以是自定义指令的事件。Angular在解析目标事件时，会优先判断是否匹配已知指令的事件，若事件名既不是某个已知指令的事件，也不是元素事件，那么会报“未知指令”的错误。

$event事件对象可以获取该事件的相关信息，目标事件的类型决定了事件对象的形态，目标事件是DOM元素事件则$event将是一个包含target和target.value属性的DOM事件对象。
```html
<input [value]="currentUser.firstName" (input)="currentUser.firstName=$event.target.value" />
```

组件要触发自定义事件可以借助于EventEmitter，在组件中可以创建一个EventEmitter实例对象，并将其输出属性的形式暴露出来，父组件通过绑定这个输出属性来自定义一个事件，在组件中调用`EventEmitter.emit(payload)`来触发这个自定义事件，其中payload可以传入任何值，父组件绑定的事件可以通过$event对象来访问payload的数据。

以通讯录为例，联系人列表页面，单击对应联系人区域，可以看到联系人的详细信息：
```ts
// item.component.ts
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'list-item',
  templateUrl: 'app/list/item.component.html',
  styleUrls: ['app/list/item.component.css']
})
export class ListItemComponent {
  @Input() contact: any = {};
  @Output() routerNavigate = new EventEmitter<number>();

  goDetail(num: number) {
    this.routerNavigate.emit(num);
  }
}
```
```html
<!-- item.component.html -->
<a (click)="goDetail(contact.id)">
  <!-- ... -->
</a>
```
组件ListItemComponent定义了一个EventEmitter实例routerNavigate，点击a标签组件调用goDetail方法，其再去执行EventEmitter.emit()方法，传递一个数字，并跳转到对应的联系人详情页面，另外，作为宿主的父组件绑定了ListItemComponent的routerNavigate事件。
```html
// list.component.html
<ul class="list">
  <li *ngFor="let contact of contacts">
    <list-item [contact]="contact" (routerNavigate)="routerNavigate($event)"></list-item>
  </li>
</ul>
```
当routerNavigate事件触发时，Angular就会调用父组建的routerNavigate方法，在$event中传入对应的联系人id。执行routerNavigate()方法使得页面内容更新，显示联系人详细信息。

#### 双向数据绑定
双向数据绑定可以利用属性绑定和事件绑定结合的形式来处理：
```html
<input [value]="currentUser.phone" (input)="currentUser.phone=$event.target.value" />
```
Angular提供NgModel指令可以更方便的进行双向绑定：
```html
<input
  [ngModel]="currentUser.phone"
  (ngModelChange)="currentUser.phone=$event"
/>
```
通过ngModel输入属性和ngModelChange输出属性隐藏了一些繁琐的细节，但更简洁的是通过[()]语法实现,[]实现了数据流从组件类到模板，()实现了数据流从模板到组件类，两者结合的[()]就可以简单实现实现双向绑定了。
```html
<input [(ngModel)]="currentUser.phone" />

<!-- 等价于 -->
<input bindon-ngModel="currentUser.phone" />
```
但[()]语法只能简单的设置一个数据绑定数据，若想完成更多任务，就得采用分离的形式来实现，例如在联系人手机号码前加上区号：
```html
<input
  [ngModel]="currentUser.phone"
  (ngModelChange)="addCodeForPhone($event)"
/>
```

##### 双向绑定的原理
[(ngModel)]可以拆分为ngModel和ngModelChange两部分，其中，ngModel是作为NgModel指令的输入属性用来设置元素的值，ngModelChange作为NgModel指令的输出属性用来监听元素值是否变化。

ngModelChange属性并不会生成DOM事件，实际上它是一个EvenEmiiter类型对象，[(ngModel)]的具体实现为：
```ts
@Directive({
  selector: "[ngModel]",
  host: {
    "[value]": "ngModel",
    "(input)": "ngModelChange.next($event.target.value)"
  }
})
class NgModelDirective{
  @Input() ngModel: ang;
  @Output() ngModelChange: EventEmitter = new EventEmitter();
}
```
上述代码中设计指令相关知识，host属性用来描述和指令元素相关的输入输出属性变化，即当[ngModel]的ngModelChange事件发生时就会触发input事件，当[ngModel]的ngModel值变化时就会更新value属性。

Angular提供了一种双向数据绑定的语法，即[(x)],也就是说当Angular解析一个[(x)]的绑定目标时，相当于为这个x指令绑定一个名为x的输入属性和一个名为xChange的输出属性,例如：
```html
<span [(x)]="e"></span>
<!-- 等价于 -->
<span [x]="e" (xChange)="e=$event"></span>
```
总的来说，双向数据绑定实际上就是通过输入属性存储数据，同时通过一个与之对应的输出属性(输入属性+Change后缀)监听输入属性的数据变化来触发相应的事件。

以创建一个支持双向绑定的组件为例，绑定一个number的输入输出属性，同时在组件中需要定义一个@Output输出属性来匹配@Input输入属性：
```ts
// amount.component.ts
import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'amount',
  template: `
  <span>
    子组件当前值：{{value}} - <button (click)="increment()">增加</button>
  </span>
  `
})
export class AmountComponent {
  @Input() value: number = 0;
  @Output() valueChange: EventEmitter<number> = new EventEmitter<number>();

  increment(){
    this.value++;
    this.valueChange.emit(this.value);
  }
}
```
```ts
// app.component.ts
import { Component, Input } from '@angular/core';
import { AmountComponent } from './amount.component';

@Component({
  selector: 'app',
  template: `
  <div>
    <div>
      <span>Number 1: </span>
      <amount [(value)]="number1"></amount>
    </div>
    <div>
      <span>Number 2: </span>
      <amount [value]="number2" (valueChange)="number2=$event"></amount>
    </div>
    <ul>
      <li>Number 1: 父组件当前值：{{ number1 }}</li>
      <li>Number 2: 父组件当前值：{{ number2 }}</li>
    </ul>
  </div>
  `
})
export class Parent {
  number1: number = 0;
  number2: number = 1;
}
```

#### 输入、输出属性
有时，输入、输出属性名的语义不是很明确，可能描述不清这个属性的作用或者功能，因此，给输入、输出属性名定义一个有语义的别名是非常有必要的，定义别名有2种方式：
1. 通过@Input和@Output装饰其为属性指定别名，语法形如：`@Output(别名) 事件属性名="..."`， 例如给一个自定义事件定义了一个别名goto，`@Output('goto') clicks= new EventEmitter<number>();`
2. 采用组件（指令）元数据的inputs或outputs数组可以为属性指定别名，语法形如：`outputs: ['组件属性名: 别名']`




### 内置指令
在Angular中，指令作用在特定的DOM元素上，可以扩展这个元素的功能，为元素增加新的行为，Angular框架本身自带一些指令，如NgClass、NgStyle、NgIf、NgFor、NgSwitch等。

#### NgClass
在属性绑定中，CSS类绑定的方式能够为标签元素添加或移除单个类，在实际开发中，通过动态添加或移除CSS类的方式，可以控制元素展示，在Angular中，通过NgClass指令，可以同时添加或移除多个类。NgClass绑定一个对象，其中数据为`CSS类名:value`的键值对，value是一个布尔类型的数据值，当value为true时则添加对应的类名到模板元素上，反之则移除。

在组件中设置一个管理类状态的对象，用来控制在模板元素中是否添加red、font14、title类。
```ts
// ...
setClasses(){
  let classes = {
    red: this.red, // true
    font14: !this.font14, // false
    title: this.isTitle, // true
  };
  return classes;
}
// ...
```
通过添加一个ngClass属性绑定，调用组件setClasses()方法来设置该元素的类样式：
```html
<div [ngClass]="setClasses()">红色标题</div>
```

#### NgStyle
在属性绑定中，Style样式绑定的方式能够给模板元素设置单一的样式，而采用NgStyle指令可以为模板元素设置多个内联样式，与NgClass类似，NgStyle绑定一个形如`CSS属性名:value`的对象，其中value为具体的CSS样式。

在组件中设置一些内联的CSS样式，用来在模板元素中设置color，font-size，font-weight：
```ts
// ...
setStyles(){
  let styles = {
    'color': this.red ? 'red' : 'blue', // red
    'font-size': !this.font14 ? '14px' : '16px', // 16px
    'font-weight': this.isSpecial ? 'bold' : 'normal', // bold
  };
  return styles;
}
// ...
```
使用：
```html
<div [ngStyle]="setStyles()">红色16px加粗</div>
```

#### NgIf
NgIf指令绑定一个布尔类型的表达式，当表达式返回true时，可以在DOM树节点上添加一个元素及其子元素，反之将被移除：
```html
<h3 *ngIf="collect.length === 0" class="no-collection">未收藏</h3>
```
写法`*ngIf`是一种语法糖，与NgSwitchCase、NgSwitchDefault、NgFor类似。

NgIf与类、样式绑定的方式区别在于：
样式、类绑定的方式也可以设置模板元素的显示与隐藏，如通过class.hidden属性的绑定方式可以控制是否显示该模板元素:
```html
<h3 [class.hidden]="collect.length === 0" class="no-collection">未收藏</h3>
```
但与NgIf不同，它们仅仅只是设置了元素是否显示，而该元素还保留在DOM树的节点上，类似`display:none`,而NgIf则是当表达式返回值为false时，元素会从DOM树上移除。

#### NgSwitch
NgSwitch指令需要结合NgSwitchCase和NgSwitchDefault指令来使用，根据NgSwitch绑定的模板表达式的返回值来决定添加哪个模板元素到DOM节点上，并移除其他备选模板元素：
- ngSwitch ： 绑定到一个返回控制条件的值的表达式
- ngSwitchCase ：绑定到一个返回匹配条件的值的表达式
- ngSwitchDefault ：用于标记默认元素的属性
注：ngSwitch前不加*、而ngSwitchCase和ngSwitchDefault前加

根据组件的contactName属性来确定展示对应的中文名:
```html
<span [ngSwitch]="contactName">
  <span *ngSwitchCase="'TimCook'">蒂姆 库克</span>
  <span *ngSwitchCase="'BillGates'">比尔盖茨</span>
  <span *ngSwitchDefault>无名</span>
</span>
```
每个子指令ngSwitchCase根据ngSwitch属性绑定的条件值来进行相关匹配，看是否符号某个子指令的判断条件，若符合则把该元素添加到DOM树节点上，并移除其兄弟元素，若匹配不到所有的条件值则显示子指令ngSwitchDefault对应的模板元素。

#### NgFor
NgFor指令可以实现重复执行某些步骤来展示数据，例如用来展示多列的模板列表，这些模板元素结构及布局一致，只是展示具体数据不一样：
```html
<li *ngFor="let contact of contacts">
  <list-li [contact]="contact" (routerNavigate)="routerNavigate($event)"></list-li>
</li>
```
赋值给*ngFor的字符串不是一个模板表达式，前面的星号不能省略，这个Angular提供的语法糖。Angular会遍历出contacts对象数据中的每个contact，并将它存储在局部变量contact中，使其在每个循环迭代中对模板HTML可用。

##### NgFor中的索引
NgFor指令支持一个可选的index索引，在循环迭代过程中，其下表范围是0 <= index < 数组长度。可以通过模板输入变量来捕获这个index，并应用在模板中。如把index赋值给变量i后，在当前的元素及其子元素中都可以使用该变量：
```html
<div *ngFor="let contact of contacts; let i = index">{{ i + 1 }} - {{ contact.id }}</div>
```

##### NgForTrackBy
在一些包含复杂列表的项目中，每次更改都会引发很多相互关联的DOM操作，这里使用NgFor指令会让性能变得很差，在通讯录例子中，当重新从服务器拉取列表数据，拉取到的数据可能包含很多（部分数据）之前显示过的数据，虽然这些数据中的联系人标号（contact.id）没有发生变化，但Angular并不知道那些列表数据在数据更新前已经渲染过，只能清理旧列表的DOM元素，并用新列表数据填充DOM元素来重新建立一个新列表。

这种情况下，可以通过追踪函数来避免这种重复渲染的性能浪费，追踪函数可以让Angular将具有相同id的对象处理成同一个联系人：
```ts
trackByContacts(index: number, contact: Contact){
  return contact.id;
}
```
然后通过NgForTrackBy指令设置追踪函数：
```html
<div *ngFor="let contact of contacts; trackBy: trackByContacts">{{ contact.id }}</div>
```
若检查出同一个联系人的属性发生了变化，Angular会更新DOM元素，反之就会留下这个DOM元素，使用NgForTractkBy指令的最终效果就是让列表界面变得更加顺畅，响应更及时。