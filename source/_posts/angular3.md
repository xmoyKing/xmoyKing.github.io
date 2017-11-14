---
title: Angular2入门-组件-2
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-03 18:28:11
updated:
---

### 组件生命周期
组件的生命周期由Angular内部管理，从组件的创建、渲染，到数据变动事件的触发，再到组件从DOM中移除，Angular都提供了钩子函数。

#### 生命周期钩子
通过实现一个或多个生命周期钩子（接口），从而在生命周期的各阶段做出适当的处理，这些钩子接口包含在@angular/core中，每个接口都对应一个名为"ng+接口名"的方法，例如OnInit接口有一个叫ngOnInit的钩子方法：
```js
class ExampleInitHook implements OnInit {
  constructor() {}

  ngOnInit(){
    console.log('OnOnit');
  }
}
```

以下为组件常用的生命周期钩子方法，Angular会按顺序依次调用：
1. ngOnChanges
2. ngOnInit
3. ngDoCheck
4. ngAfterContentInit
5. ngAfterContentChecked
6. ngAfterViewInit
7. ngAfterViewChecked
8. ngOnDestroy
除外，有些组件还有特殊的钩子，例如路由有routerOnActivate钩子。

##### ngOnChanges
ngOnChanges是用来响应组件输入值变化时触发的事件，该方法接收一个SimpleChanges对象，包含当前值和变化前的值，该方法在ngOnInit前触发，或当数据绑定输入属性的值发生变化时触发。

在AngualrJS1.x中，若需要监听数据的变化，需要设置$scope.$watch，在每次digest循环里判断数据是否有改变。Angular2中，ngOnChanges钩子简化了该过程，只要在组件里定义ngOnChanges方法，在输入数据发生变化时该方法就会被自动调用。

注意，这里的输入数据是指通过@Input装饰器显式指定的那些。

##### ngOnInit
ngOnInit钩子是用于数据绑定输入属性之后初始化组件，使用ngOnInit的原因为：
- 组件钩子后不久就要进行复杂的初始化
- 需要在输入属性设置完成之后才能构建组件
在组件中，一般通过ngOnInit获取数据而不通过组件构造函数获取数据的原因是：
- 构造函数做的事（例如成员变量初始化）应该尽可能简单
- 对Angular自动化测试的一些场景非常有用，将业务相关的初始化代码放到ngOnInit里可以很容易进行Hook操作，而钩子函数不能被显示调用，因此无法进行Hook操作。

##### ngDoCheck
用于变化监测，该方法会在每次变化发生时被调用。
每个变化监测周期内，不管数据值是否变化，ngDoCheck都会被调用，但这个钩子方法要慎用，例如mousemove事件，会被频繁触发，随之ngDoCheck也会被频繁调用，因此ngDoCheck不能处理复杂逻辑。

据大多数情况下，ngDoCheck和ngOnChanges不同时使用，ngOnChengs能做的ngDoCheck也能做，而ngDoCheck检测粒度更小，可以完成更灵活的变化监测逻辑。

##### ngAfterContentInit
在组件中使用ng-content将外部内容嵌入到组件视图后就会调用ngAfterContentInit，它在第一次ngDoCheck执行后调用，且只执行一次。

##### ngAfterContentChecked
Angular在这些外部内容嵌入到组件视图后，或每次变化时就会调用ngAfterContentChecked。

##### ngAfterViewInit
会在Angular创建了组件的视图及其子视图后被调用。

##### ngAfterViewChecked
在Angular创建了组件的视图及其子组件视图之后被调用一次，并在每次子组件变化时被调用。

##### ngOnDestroy
在销毁指令、组件之前触发。那些不会被垃圾回收器自动回收的资源，如已订阅的观察者事件、绑定过的DOM事件、通过setTimeout或setInterval设置过的计时器等，都应该在ngOnDestroy中手动销毁掉，从而避免发生内存泄漏等问题。

### 变化检测
异步事件的发生导致组件中数据的变化，Angular对于这并不会捕捉对象的变动，它采用的是在适当的时机去检验对象的值是否被改动，这个时机是由NgZone服务掌握的，它获取整个应用的执行上下文，能够对相关的异步事件发生、完成、异常进行捕捉，然后驱动Angular的变化监测机制执行。

#### 数据变化的源头
在应用程序中，大致有3中引起数据变化的场景：
1. 用户的操作，如click、change、hover等，
2. 前后端的交互，如从后端服务拉去页面所需的接口数据，如XHR/WebSocket，
3. 定时任务，在某个延时后再来响应对应的操作，如setTimeout、setInterval、requestAnimationFrame等。
它们的共同特征就是它们其实都是异步处理，通过异步回调函数句柄来处理相关数据操作，因此，任意一个异步操作，都有可能在数据层面上发生改变，这会导致应用状态改变。若可以在每一个异步回调函数执行结束后，通知Angular内核进行变化监测，那么任何数据的更改就可以在视图层实时的反馈出来。

例如在组件的模版元数据template中：
```html
<i [ngClass]="{collect: detail.collection}" (click)="collectTheContact()">收藏</i>
```
当用户单击收藏按钮，这个操作会改变组件里的收藏数据对象detail.collection，并将通知Angular去检查数据的变化，在视图层做出相应的改变，如改变DOM元素样式。

#### 变动通知机制
Angular本身不具备捕获异步事件的机制，所以引入了NgZone服务。NgZone服务基于Zones来实现，NgZone从Zone中fork了一份实例，是Zone派生出来的一个子Zone，在Angular环境内注册的异步事件都会运行在这个子Zone上（这个子Zone有用Angular运行环境的执行上下文）。NgZone扩展了一些API并添加了一些方法，如onUnstable和onMicrotaskEmpty事件，这些钩子方法会捕获对应的异步操作。

NgZone提供了一些可被订阅的自定义事件，这些自定义事件是Ovservable流，包括：
- onUnstable 在Angular单次事件启动前，触发消息通知订阅器
- onMicrotaskEmpty 在Zone完成当前Angular单次事件任务时，立即通知订阅者
- onStable 在完成onMicrotaskEmpty回调函数后，在视图变化之前立即通知订阅者，常用来验证应用的状态
这些自定义事件在跟踪定时任务和其他异步任务时非常又有哪个，而且Angular可以决定在Zone内需不需要执行变化监测，因为有时需要Angular每一次都去执行变化监测，例如NgZone的runOutsideAngular()方法可以让Angular不执行变化监测，即通知NgZone的父Zone在捕获异步事件时直接返回，不触发后续的onMicrotaskEmpty事件。

通过Angular源码中ApplicationRef类可帮助理解NgZone，在该类的钩子函数中监听NgZone中的onMicrotaskEmpty自定义事件，只要有任何异步任务发生将触发这个事件，其中的tick()方法用来通知Angular去执行变化检测：
```ts
// 精简后的源码
class ApplicationRef {
  changeDetectorRefs: ChangeDetectorRef[] = [];
  constructor(private zone: NgZone){
    this.zone.onMicrotaskEmpty.subsribe(() => this.zone.run(() => this.tick() ));
  }

  tick(){
    this.changeDetectorRefs.forEach((ref) => ref.detectChanges());
  }
}
```
#### 变化监测的响应处理
Angular应用由大大小小的组件组成，这些相互依赖的组件构成了一颗线性的组件树，然后每一个组件有自己的变化监测器，由此组成了一颗变化监测树。变化监测树的数据由上到下单向流动，因为变化监测的执行总是从根组件开始，从上而下地监测每一个组件的变化，单向的数据流让人清晰地了解视图中数据的来源，明白数据的变化是由那个组件引起的。

变化监测器的工作原理为：当组件中数据有变动时，NgZone通过钩子捕获到变化并通知到Angular去执行变化监测，变化监测是单向线性的，即从根组件开始，依次触发各个子组件的变化监测器去完成变化的对比工作。在每个组件的执行环境中，Angular都会创建一个变化监测类的实例，该实例能够准确的记录每个组件的数据模型，并以此作为下一轮变化监测的参考标准。

默认情况下，任何一个组件模型中的数据变化都会导致整个组件树的变化监测，但其实很多组件的输入属性是没有变化的，因此没有必要对这样的组件进行变化监测操作，减少不必要的监测操作可以提升性能。

##### 变化监测类（ChangeDetectorRef）
Angular会在运行期间为每一个组件创建变化监测类的实例，该实例提供了相关的方法来手动管理变化监测。虽然Angular并不知道那个组件发生了变化，但开发者知道，所以可以给这个组件做标记，依次来通知Angular仅仅监测这个组件所在的路径上的组件即可。

变化监测类（ChangeDetectorRef）提供的主要接口如下：
```ts
class ChangeDetectorRef {
  // ...
  markForCheck(): void // 把根组件到该组件之间的这条路径标记起来，通知Angular在下次触发变化监测时必须检查这条路径上的组件

  detach(): void // 从变化监测树中分离变化监测器，该组件的变换监测器将不在执行变化监测，除非再次手动执行reattach()方法
  detectChanges(): void // 手动触发执行该组件到各个子组件的一次变化监测
  reattach(): void // 把分离的变化监测器重新安装上，使得该组件及其子组件都能执行变化监测
}
```

通过示例说明一些使用场景，假如通讯录中联系人的数据时刻在变化，而产品需求又不需要实时的根据变化来展示数据，那么为了性能考虑，可以设置在一定事件范围内来执行变化监测，detach方法和detachChanges方法配合使用：
```ts
// ...
@Component({
  selector: 'list',
  template: `
  <ul class="list">
    <li *ngFor="let contact of contacs">
      <list-item [contact]="contact"></list-item>
    </li>
  </ul> 
  `
})
export class ListComponent implements OnInit {
  contacts: any = {};

  constructor(
    // ...
    private cd: ChangeDetectorRef
  ){
    cd.detach(); 
    
    // 定时执行变化监测
    setInterval(()=>{
      this.cd.detactChanges();
    }, 5000);
  }

  ngOnInit(){
    this.getContacts();
  }

  getContacts(){
    this.contacts = data; // data为联系人列表数据
  }
  // ...
}
```

##### 变化监测策略
在Angular中，每个组件都包含一些元数据，而其中一些是可选的，changeDetection是可选的组件的元数据，其作用是让开发者定义每个组件的变化监测策略，在使用时前需要导入ChangeDetectionStrategy对象：
```ts
import { Component, ChangeDetectionStrategy } from '@angular/core';
// ...

@Component({
  // ...
  changeDetection: ChangeDetectionStrategy.OnPush
})
// ...
```
ChangeDetectionStrategy是枚举类型，有2种值分别是Defult和OnPush，Default表示组件的每次变化监测都会检查其内部的所有数据，引用对象会深度遍历。而OnPush表示组件变化监测只检查输入属性（即@Input()修饰的变量），引用类型则只比对引用。

显然OnPush策略相比Default降低了变化监测的复杂度，尤其是子组件的更新只依赖输入属性的值时，在子组件上使用OnPush策略就非常好。但由于OnPush对引用类型仅比对引用，某些情况会出问题，比如，子组件通过输入属性获取父组件中的Object值为{a:1, b:2},但当父组件修改了该对象内的值为{a:11, b:22}，这时对象的引用没有发生变化，因此子组件的变化监测并不能感知对象已变化，此时解决方法有2个：
- 修改变化策略为Default，但牺牲性能
- 使用Immutable对象传值（推荐做法）
使用Immutable对象可以确保当对象值的引用地址不变时，对象内部的值或结构也会保持不变，反之，当对象内部变化时，对象引用必然变化。

例如子组件代码如下：
```ts
import { Component, Inpu, ChangeDetectionStrategy } from '@angular/core';

@Component({
  selector: 'list-item',
  template: `
  <div>
    <label>{{ contact.get('name') }}</label>
    <span>{{ contact.get('telName') }}</span>
  </div>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ListItemComponent {
  @Input() contact: any = {};
  // ...
}
```
子组件的数据更新只依赖输入属性contact的值，所以使用OnPush可以满足需求。父组件代码如下：
```ts
import { Component } from '@angular/core';
import Immutable from 'immutable';

@Component({
  // ... 
  template: `
    <lit-item [contact]="contactItem"></list-item>
    <button (click)="doUpdate()">更新</button>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ListComponent {
  contactItem: any;
  constructor(){
    this.contactItem = Immutable.map({
      name: '张三',
      telName: '12341234123'
    })
  }

  doUpdate(){
    this.contactItem = this.contactItem.set('telNum', '12312312345');
  }
}
```
父组件引入Immutable工具库，并把contactItem包装成Immutable对象，当点击更新按钮时，contactItem值被赋值为新的对象引用，子组件检测到contactItem的引用变化，从而触发数据更新。

### 元数据说明
除了上述已经讲解了的，还有其他一些元数据：

| 名称 | 类型 | 作用 |
| - | - | :- |
| selector | string | 自定义组件的标签，用于匹配元素 |
| inputs | string[] | 指定组件的输入属性 |
| outputs | string[] | 指定组件的输出属性 |
| host | {[key: string]: string;} | 指定指令/组件的事件、动作和属性等 |
| providers | any[] | 指定该组件及其所有子组件（含ContentChildren）可用的服务（依赖注入） |
| exportsAs | string | 给指令分配一个变量，使得可以在模版中调用 |
| moduleId | string | 包含该组件模块的id，它被用于解析模版和样式的相对路径 | 
| queries | {[key: string]: any;} | 设置需要被注入到组件的查询 |
| viewProviders | any[] | 指定该组件及其所有子组件（不含ContentChildren）可用的服务 |
| changeDetection | ChangeDetectionStrategy | 指定使用的变化监测策略 |
| templateUrl | string | 指定组件模版所在的路径 |
| template | string | 指定组件的内联模版 |
| styleUrls | string[] | 指定组件引用的外部样式文件 |
| styles | string[] | 指定组件使用的内联样式 |
| animations | AnimationEntryMetadata[] | 设置Angular动画 |
| encapsulation | ViewEncapsulation | 设置组件的视图包装选项 |
| interpolation | [string, string] | 设置自定义插值标记，默认是双大括号｛｛｝｝ |