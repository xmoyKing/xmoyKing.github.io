---
title: Angular2入门-组件-1
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-03 09:42:08
updated:
---

组件(Component)是构成Angular应用的基础和核心，通俗的讲，组件用来包装特定的功能，应用程序的有序运行依赖于组件之间的协同工作。本部分内容主要有：了解组件化的发展和Web Component标准是如何形成的，以及Angular如何向Web Component靠齐。从如何创建组件到组件的构成、以及组件和模块的关系、从基础深入学习组件的元数据、生命周期、组建交互以及变化监测机制等内容。

### 组件基础
创建Angular组件有3个步骤：
1. 从@angular/core中引入Component装饰器
2. 建立一个普通的类，并用@Component装饰它
3. 在@Component中，设置selector自定义标签和template模版

比如创建一个显示名称和电话的联系人卡片来说明组件的创建方法，联系人卡片ContactItemComponent组件的示例代码如下：
```ts
// contactItem.component.ts
import { Component } from '@angular/core';

@Component({ // 1
  selector: 'contact-item', // 2
  template: `
  <div>
    <p>张三</p>
    <p>13812341234</p>
  </div>
  `  // 3
})
export class ContactItemComponent {}  // 4
```
使用组件需要在HTML中添加`<contact-item>`自定义标签，然后Angular便会在此标签中插入ContactItemComponent组件中指定的模版。

在组件的基础构成中：
- 组件装饰器（Component Decorator）：每个组件类必须用@Component进行装饰才能成为Angular组件
- 组件元数据（Component Metadata）：selector、template、...（以及其他）
- 模版：每个组件都会关联一个模版，这个模版最终会渲染到页面上，页面的这个DOM元素就是此组件实例的宿主元素
- 组件类：组件实际上也就是一个普通的类，组件的逻辑都在组件类里定义并实现

#### 组件装饰器
@Component是TypeScript的语法，它是一个装饰器，任何一个Angular组件都会用这个装饰器修饰，若移除这个装饰器，它将不再是Angular组件。
由于浏览器不能直接解释TypeScript代码，最终组件的代码会通过TypeScript解析器转换成JS代码，转换后的代码如下：
```js
var ContactItemComponent = (function(){
  function ContactItemComponent(){}
    ContactItemComponent = __decoratre([
      core_1.Component({
        selector: 'contact-item',
        template: `
        <div>
          <p>张三</p>
          <p>13812341234</p>
        </div>
        `
      })
      __metadata('design:paramtypes', [])
    ], ContactItemComponent);

    return ContactItemComponent;
}());
```
转换后，Angular的@Component会被转换成一个`__decorate()`方法，元数据的定义通过`core_1.Component`传入，将`ContactItemComponent`这个类装饰起来，使得ContactItemComponent有装饰器里定义的元数据属性，所以装饰器可以理解为对组件封装的语法糖，方便编写Angular的组件。

#### 组件元数据
在ContactItemComponent这个组件里的@Component装饰器部分，使用了大部分需要的元数据 —— 用于定义组件的标签名selector、用于定义组件宿主元素模版的template（template用于定义内联模版，templateUrl用于引用外联模版），styles用于提供内联样式，styleUrls用于引用外联样式。

其中styles和styleUrls可以同时指定，同时指定时，styles中的样式会先被解析，然后才会解析styleUrls中的样式，即styles会被styleUrls样式覆盖。而若直接在模版的DOM节点上写样式，则是作为优先级最高的模版内联样式（Template Inline Style）。一般使用styleUrls，方便管理，代码更清晰。

通过styles和styleUrls指定的样式时，Angular会在模版DOM中添加自定义的节点属性，依次来形成属于这些样式在组件中独有的作用域，避免CSS样式命名污染。

同时，也可以使用一些CSS预处理器来编写CSS代码，如SASS, 只要在webpack.config.js中的loaders配置一下：
```js
{
  module: {
    loaders: [
      {test: /\.scss/, loader: 'raw-loader!sass-loader', exclude: /node_modules/}
    ]
  }
}
```
然后通过`npm install sass-loader`安装sass-loader后即可在styleUrls中用require方法引入sass文件，

```ts
@Component({
  selector: 'contact-item',
  template: `
  <div>
    <p>张三</p>
    <p>13812341234</p>
  </div>
  `,
  styles: [
    `li:last-child{
      border-bottom: none;
    }`
  ],
  styleUrls: ['app/list/item.component.css', require('app/list/item.component.sass')]
})
```

#### 模版
每个组件都必须设置一个模版，Angular才能将组件内容渲染到DOM上，这个DOM元素被称为宿主元素，与宿主元素交互的形式包括：
- 显示数据，用插值语法`｛｛｝｝`来显示组件的数据
- 双向数据绑定，使用形如`[(ngModel)]="property"`
- 监听宿主元素事件以及调用组件方法，形如`(click)="addContact()"`这样的就表示绑定单击事件，单击时触发`addContact()`方法

```ts
import { Component } from '@angular/core';

@Component({ 
  selector: 'contact-item', 
  template: `
  <div>
    <input type="text" value="{{name}}" [(ngModel)]="name"/>
    <p (click)="addContact()">{{name}}</p>
    <p>{{phone}}</p>
  </div>
  `
})
export class ContactItemComponent {
  name: string = '张三';
  phone: string = '12341234123';
} 
```

#### 组件与模块
通常组件是不会独立存在的，而是与其他组件写作，完成一个功能，在Angular中，这样的功能一般会封装到一个模块里，模块是在组件之上的一层抽象，组件以及指令、管道、服务、路由等都能通过模块去组织。

Angular提供了@NgModule装饰器来创建模块，一个应用可以有多个模块，但有且只有一个根模块（Root Module）,其他模块叫做特性模块（Feature Module）。根模块是启动应用的入口模块，根模块必须通过bootstrap元数据来指定应用的根组件，然后通过bootstrapModule()方法来启动应用。
```ts
// app.module.ts
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { ContactItemComponent } from './contactItem.component';

@NgModule({
  imports: [BrowserModule], 
  declarations: [ContactItemComponent],
  bootstrap: [ContactItemComponent]
})
export class AppModule{}
```
然后创建一个app.ts，利用platformBrowserDynamic().bootstrapModule()方法来启动这个根模块，这样Angular应用就能运行起来，并将ContactItemComponent组件的内容展示到页面上：
```js
// app.ts
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import { AppModule } from './app.module';

platformBrowserDynamic().bootstrapModule(AppModule);
```
NgModule主要的元数据如下：
- declarations： 用于指定属于这个模块的视图类（View Class），即指定那些部分组成了这个模块，Angular有组件、指令和管道三种视图类，这些视图类只能属于一个模块，所以要注意不要再次声明属于其他模块的类。
- imports：引入到该模块依赖的其他模块或路由，引入后模块里的组件模版才能引用外部对应的组件、指令、管道。
- exports：导出视图类，当该模块被引入到外部模块时，这个实现指定了外部模块可以使用该模块的那些类视图类，所以它的值类型跟declarations一致（组件、指令、管道）。
- providers：指定模块依赖的服务，引入后该模块中的所有组件都可以使用这些服务。
- bootstrap：用于指定应用的根组件。

##### 视图类引入
以通讯录示例的app.module.ts文件中的declarations元数据为例：
```js
// ...
@NgModule({
    declarations: [
        AppComponent,
        ListComponent, ListItemComponent,
        DetailComponent,
        CollectionComponent,
        EditComponent,
        HeaderComponent, FooterComponent, 
        PhonePipe, BtnClickDirective
    ]
    // ...
})
export class AppModule { }
```
其中PhonePipe是管道、BtnClickDirective是指令，其他都是组件。比如ListComponent组件的模版代码list.component.html中使用到了HeaderComponent、FooterComponent以及ListItemComponent等3个组件，这时候必须在ListComponent所属的模块（即AppModule）中，通过declarations引入这3个组件。
```html
<!-- list.component.html -->
<!-- 组件中指定了HeaderComponent, 才能使用my-header标签 -->
<my-header title="所有联系人" [isShowCreateButton]="true"></my-header>
<ul class="list">
  <li *ngFor="let contact of contacts">
    <!-- 组件中指定了ListItemComponent才能使用list-item标签 -->
    <lit-item [contact]="contact" (rooterNavigate)="routerNavigate($event)"></list-item>
  </li>
</ul>
<!-- 组件中指定了FooterComponent, 才能使用my-footer标签 -->
<my-footer></my-footer>
```
在引入BrowserModule的时已经引入了常用的内置指令，其中的ngFor就是Angular的内置指令。

##### 导出视图类以及导入依赖模块
有时候模块中的组件、指令、管道可能会在其他模块中使用，可使用exports元数据对外暴露这些组件、指令、管道。

比如通讯录模块ContactModule中联系人信息组件可能需要被短信模块MessageModule使用：
```ts
// contact.module.ts
import { NgModule } from '@angular/core';
import { ContactItemComponent } from './contactItem.component';

@NgModule({
  declarations: [ContactItemComponent],
  exports: [ContactItemComponent] // 导出组件
})
export class ContactModule {}
```
在短信模块，需要将依赖的ContactModule模块引入，让就可以在MessageMudule中的其他模版使用ContactModule导出的ContactItemComponent组件。
```ts
// message.module.ts
import { NgModule } from '@angular/core';
import { ContactModule } from './contact.module';
import { SomeOtherComponent } from './someother.component';

@NgModule({
  declarations: [SomeOtherComponent], // 在SomeOtherComponent组件的模版中就可以使用contact-item组件了。
  imports: [ContactModule] // 导出组件
})
export class MessageModule {}
```

##### 服务引入
服务通常用于处理业务逻辑及其相关的数据，引入服务有两种方式：一是通过@NgModule的providers，另一个是通过@Component的providers。
以app.module.ts文件中，通过providers元数据注入了自定义的ContactService服务，ContactService服务是维护联系人数据的主服务，负责对联系人信息的相关操作：
```ts
// app.module.ts
import { ContactService, UtilService, FooterComponent, 
        HeaderComponent, PhonePipe, BtnClickDirective } from './shared';
// ...
@NgModule({
  providers: [ContactService],
  bootstrap: [AppComponent]
})
export class AppModule {
  // ...
}
```
通过@NgModule的providers来注入服务，所有被包含在AppModule中的组件，都可以使用到这些服务，同样，在组件中也可以用providers来引入服务，该组件及其子组件都可以公用这些引入的服务。

### 组件交互
Angular应用由各式各样的组件组成，这些组件形成了一颗组件树，数据可以在组件树里完成交互，组件间的交互包括父子组件交互和一些非父子组件的交互，组件交互就是组件通过一定的方式来访问其他租价你的属性或方法，从而实现数据双向流动。

#### 组件的输入输出属性
Angular提供了输入（@Input）和输出（@Output）语法来处理组件数据的流入流出，参照通讯录例子中item.component.ts以及list.component.html的代码：
```ts
// item.component.ts
export class ListItemComponent implements OnInit {
  @Input() contact:any = {};
  @Output() routerNavigate = new EventEmitter<number>();
  //...
}
```
```html
<!-- list.component.html -->
<li *ngFor="let contact of contacts">
  <!-- 组件中指定了ListItemComponent才能使用list-item标签 -->
  <lit-item [contact]="contact" (rooterNavigate)="routerNavigate($event)"></list-item>
</li>
```
上述ListItemComponent组件的作业是显示单个联系人的信息，由于联系人列表数据是在ListComponent组件中维护的，在显示单个联系人时，需要给ListItemComponent传入单个联系人数据，另外在单击单个联系人时，需要跳转到此联系人的明细信息，需要子组件通知父组件进行跳转，因此上述代码分别自定义了[contact]和(routerNavigate)的输入输出变量，用于满足上述功能。

被@Input修饰的contact变量属于输入属性，而被@Output修饰的routerNavigate则是输出属性，这里的输入、输出是以当前组件角度说的。除了@Input和@Output修饰外，还可以在组件的元数据中使用inputs、outputs来设置输入输出属性，设置的值必须为字符串数组，元素的名称需要和成员变量相对应：
```ts
// 等价为上述代码
@Component({
  // ...
  inputs: ['contact'], // 'contact'匹配成员变量contact
  outputs: ['routerNavigate']
})
export class ListItemComponent implements OnInit {
  contact:any = {};
  routerNavigate = new EventEmitter<number>();
  //...
}
```

#### 父组件向子组件传递数据
父组件的数据通过子组件的输入属性流入子组件，在子组件完成接收或拦截，以此实现数据由上而下的传递。

父组件ListComponent将获取到联系人的数据，通过属性绑定的方式流向子组件ListItemComponent：
```ts
// list.component.ts
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'list',
  template: `
    <ul class="list">
      <li *ngFor="let contact of contacts">
        <list-item [contact]="contact"></list-item>
      </li>
    </ul>
  `
})
export class ListComponent implements OnInit {
  // ...
  this.contact = data; // data为获取到的联系人数据
}
```
在app.module.ts中已经通过@NgModule的元数据declarations将子组件ListItemComponent的实例引入到AppModule中，使得所有属于AppModule中的其他组件都可以使用ListItemComponent组件，因此父组件ListComponent中可以直接引用该子组件，将每个联系人对象通过属性绑定的方式绑定到属性contact中来提供子组件的引用，数据由上而下流入子组件，在子组件中通过@Input装饰器完成数据的接收，子组件的示例代码如下：
```ts
// item.component.ts
import { Component, OnInit, Input } from '@angular/core'

@Component({
  selector: 'list-item',
  template: `
  <div class="contact-info">
    <lable class="contact-name">{{ contact.name }}</label>
    <span class="contact-tel">{{ contact.telNum }}</span>
  </div>
  `
})
export class ListItemComponent implements OnInit{
  @Input() contact: any = {};
}
```
ListItemComponent组件主要展示联系人姓名（name）和电话（telNum）,这两个属性包含在contact对象下，其数据是通过装饰器@Input来获取来自父组件的contact对象，数据由父组件流出，在子组件中通过输入属性@Input完成数据的接收。

Angular应用是由各式各样的组件组成的，Angular会从根组件开始启动，并解析整颗组件树，数据从上而下流入下一级子组件，但目标属性必须通过输入属性@Input明确的标记修饰才能接收到来自父组件的数据。

##### 拦截输入属性
父组件向子组件传递数据，子组件可以拦截输入属性的数据并进行处理，2种方式拦截，分别是setter拦截输入属性和ngOnChanges监听数据变化。

**setter拦截输入属性**
getter和setter通常配套使用，用来对属性进行相关约束，setter可以对属性进行再封装处理，对复杂的内部逻辑通过访问权限控制来隔离外部调用，以避免外部的错误调用影响到内部的状态，同时把内部复杂逻辑结构封装成高度抽象且能被简单调用的属性，再通过getter返回要设置的属性值，方便调用。

对ListItemComponent进行修改：
```ts
@Component({
  selector: 'list-item',
  template: `
  <div class="contact-info">
    <lable class="contact-name">{{ contactObj.name }}</label>
    <span class="contact-tel">{{ contactObj.telNum }}</span>
  </div>
  `
})
export class ListItemComponent implements OnInit{
  _contact: object = {};

  @Input()
  set contactObj(contact: object){
    this._contact.name = (contact.name && contact.name.trim()) || 'no name set';
    this._contact.telNum = contact.telNum || '000-000';
  };

  get contactObj(){ return this._contact};
}
```
如上处理的作业是使得联系人不会出现null或undefined的情况，一般来说，getter和setter其实是在该组件类的原型对象上设置的contactObj属性的方法：
```js
Object.defineProperty(ListItemComponent.prototype, 'contactObj', {
  // ...
});
```

**ngOnChanges监听数据变化**
ngOnChanges用于及时响应Angular在属性绑定中发生的数据变化，该方法接收一个对象参数，包含当前值和变化前的值，在ngOnInit之前，或者当数据绑定的输入属性的值发生变化时会触发，ngOnChanges是组件的生命周期钩子之一。

在通讯录例子详情页中，当父组件DetailComponent编辑联系人信息后，在子组件ChangeLogComponent中通过ngOnChanges来监听并处理数据的变化，将变化前后信息通过日志输出。
```ts
// detail.component.ts
import { Component } from '@angular/core';

@Component({
  selector: 'detail',
  template: `
  <a class="edit" (clicj)="editContact()">编辑</a>
  <change-log [contact]="detail"></change-log>
  `
})
export class DetailComponent implements OnInit {
  detail: any = {};
  editContact() {
    // ...
    this.detail = data; //修改后的数据
  }
}
```
子组件ChangeLogComponent中，SimpleChanges类是Angular的一个基础类，用于处理数据的前后变化，其包含两个重要成员变量，分别是previousValue和currentValue。
```ts
//  changelog.component.ts
import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
@Component({
  selector: 'change-log',
  template: `
    <h4>Change log: </h4>
    <ul>
      <li *ngFor="let change of changes">{{ change }}</li>
    </ul>
  `
})
export class ChangelogComponent implements OnChanges {
  @Input() contact: any = {};
  changs: string[] = [];

  ngOnChanges(changes: {[propKey: string]: SimpleChanges}) {
    let log: string[] = [];
    for( let propName in changes){
      let changedProp = changes[propName],
          from = JSON.stringify(changedProp.previousValue),
          to = JSON.stringify(changedProp.currentValue);
      log.push(`${propName} changed from ${from} to ${to}`);
    }
    this.changes.push(log.join(','));
  }
}
```

#### 子组件向父组件传递数据
使用事件传递是子组件向父组件传递数据最常用的方式。子组件需要示例化一个用来订阅和触发自定义事件的EventEmitter类，这个实例对象是一个由装饰器@Output修饰的输出属性，当有用户操作行为发生时，该事件会被触发，父组件则通过事件绑定的方式来订阅来自子组件触发的事件，即子组件触发的具体事件会被其父组件订阅。

通过事件传递的方式来实现联系人详情页中收藏联系人的例子，父组件CollectionComponent以及子组件ContactCollectComponent，单击收藏按钮后将完成联系人的收藏操作，即在子组件中通过数据绑定的方式实现单击收藏功能，具体的收藏操作统一在父组件中实现：

CollectionComponent示例代码：
```ts
import { Component } from '@angular/core';
@Component({
  selector: 'collection',
  template: `
  <contact-collect [contact]="detail" (onCollect)="collectTheContact($event)"></contact-collect>
  `
})
export class CollectionComponent implements OnInit{
  detail: any = {};
  collectTheContact() {
    this.detail.collection == 0 ? this.detail.collection = 1 : this.detail.collection = 0;
  }
}
```
子组件ContactCollectComponent示例代码：
```ts
import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'contact-collect',
  template: `
  <i [ngClass]="{collected: contac.collection}" (click)="collectTheContact()">收藏</i>
  `
})
export class ContactCollectComponent {
  @Input contact: any = {};
  @Output onCollect = new EventEmitter<boolean>();

  collectTheContact(){
    this.onCollect.emit();
  }
}
```
上述代码中单击收藏按钮后将触发自定义的事件`onCollect = new EventEmitter<boolean>()`, 通过输出属性@Output将数据流向父组件，在父组件完成事件的监听，依次实现从子组件到父组件的数据交互，这样的数据通信主要依赖@Output，它声明事件绑定的输出特性，当输出属性发出一个事件，在模版中绑定的对应事件处理句柄（Event Handler）将会被调用。

#### 其他组件交互方式
父子组件间数据的传递还有其他方法：
- 父组件通过局部变量获取子组件引用
- 父组件使用@ViewChild获取子组件的引用

##### 通过局部变量引用实现数据交互
通过输入输出属性绑定的方式来实现数据双向流动，但父组件仅仅是将数据源流向下级子组件，它不拥有读取子组件的相关成员变量和方法的权限，因此也不能调用子组件的相关成员变量和方法。

在Angular中的“模版局部变量”,可以获取子组件的实例引用，即在父组件的模版中为子组件创建一个局部变量，通过此变量来获取子组件公共成员变量和函数的权限，模版局部变量的作用域范围仅存在于定义该模版局部变量的子组件。
修改CollectionComponent，在父组件模版中的`<contact-collect>`子组件标签上绑定一个局部变量collect（以#号标记），依次来获取子组件类的示例对象。
```ts
import { Component } from '@angular/core';
@Component({
  selector: 'collection',
  template: `
  <contact-collect (click)="collect.collectTheContact()" #collect></contact-collect>
  `
})
export class CollectionComponent{}
```
子组件ContactCollectComponent修改代码：
```ts
import { Component } from '@angular/core';

@Component({
  selector: 'contact-collect',
  template: `
  <i [ngClass]="{collected: detail.collection}">收藏</i>
  `
})
export class ContactCollectComponent {
  detail: any = {};
  collectTheContact(){
    this.detail.collection == 0 ? this.detail.collection = 1 : this.detail.collection = 0;
  }
}
```

##### 使用@ViewChild实现数据交互
使用模版局部变量的方式简单，但有局限，即只能在模版中是哟个，而不能孩子接在父组件类里使用，@ViewChild的方式则更好。

通过@ViewChild注入的方式也可以获取子组件中变量或方法的读写权限。组件中元数据ViewChild的作用是声明子组件元素的示例引用，它提供了一个参数来选择将要引用的组件元素，这个参数可以是一个类的实例，也可以是一个字符串。具体如下：
- 参数为类实例，表示父组件将绑定一个指令或子组件实例
- 参数为字符串类型，表示将起到选择器的作用，相当于在父组件中绑定一个模版局部变量，获取到子组件的一份实例对象的引用。

对CollectionComponent代码修改：
```ts
import { Component, AfterViewInit, ViewChild } from '@angular/core';

@Component({
  selector: 'collection',
  template: `
    <contact-collect (click)="collectTheContact()"></contact-collect>
  `
})
export class CollectionComponent {
  @ViewChild(ContactCollectComponent) contactCollect: ContactCollectComponent;

  ngAfterViewInit(){
    // ...
  }

  collectTheContact(){
    this.contactCollect.collectTheContact();
  }
}
```
通过@ViewChild装饰器将ContactCollectComponent子组件注入进来，并赋值给contactCollect变量，此变量是对子组件实例的引用。

### 组件内容嵌入
内容嵌入（ng-content）是组件的一个高级功能，它和AngularJS1.x中指令的transclude属性非常相似。

通常用来创建可复用的组件，典型的例子是模态对话框或导航栏，使得这些组件具有一致的样式，但内容又可以自定义。
例如定义一个NgContentExampleComponent组件，其内容可以动态变化：
```ts
import { Component } from '@angular/core';

@Component({
  selector: 'example-content',
  template: `
    <div>
      <h4>ng-content 示例</h4>
      <div style="padding: 5px;">
        <ng-content select="header"></ng-content>
      </div>
    </div>
  `
})
class NgContentExampleComponent {}
```
模版中有一个ng-content标签，其作用是渲染组件嵌入内容，select="header"用于匹配内容并填充到ng-content。这里的header是一个css选择器。

在NgContentAppComponent根组件中使用：
```ts
import { Component } from '@angular/core';

@Component({
  selector: 'app',
  template: `
    <example-content>
      <header>HEADER内容~ </header>
      <!-- 自定义内容放在example-content之间 -->
    </example-content>
  `
})
class NgContentAppComponent {}
```
