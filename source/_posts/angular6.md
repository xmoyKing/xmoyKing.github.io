---
title: Angular2入门-模板-3
categories: Angular
tags:
  - TypeScript
  - Angular
  - 揭秘 Angular2
date: 2017-10-07 17:15:51
updated:
---

### 管道
在Angular中，管道（Pipes）可以按照指定的规则将模板内的数据进行转换。

使用管道，需要用管道操作符`|`来链接模板表达式中左边的输入数据和右边的管道：
```ts
@Component({
  selector: 'pipe-demo',
  template: `
  <p>My Birthday is {{ birthday | date }}</p>
  `
})
export class PipedemoComponent{
  birthday = new Date(1999, 3, 22);
}
```
输出结果为`My Birthday is Apr 22, 1999`

**管道参数**
管道可以使用参数，通过传入的参数来输出不同格式的数据，如日期需要以固定格式输出，可以给日期管道添加参数
```html
<p>My Birthday is {{ birthday | date:"MM/dd/y" }}</p>
```
输出结果为`My Birthday is 04/22/1999`

**链式管道**
一个模板表达式可以连续使用多个管道进行不同的处理，就是链式管道，语法格式为：
```
{{ expression | pipeName1 | pipeName2 | ...}}
```
模板表达式expression的值依次传递，直到最后一个管道处理完毕，输出最终结果。

#### 内置管道
Angular根据业务场景，封装了一些常用的内置管道，内置管道可以直接在任何模板表达式中被使用，不需要通过import导入和在模块中声明。

Angular提供的内置管道如下表：

| 管道 | 类型 | 功能 |
| - | - | - |
| DatePipe | 纯管道 | 日期管道，格式化日期 |
| UpperCasePipe | 纯管道 | 将文本所有小写字母转成大写字母 |
| LowerCasePipe | 纯管道 | 将文本所有大写字母转成小写字母 |
| DecimalPipe | 纯管道 | 将数值按特定的格式显示文本 |
| CurrencyPipe | 纯管道 | 将数值转换成本地货币格式 |
| PercentPipe | 纯管道 | 将数值转换成百分比格式 |
| JsonPipe | 非纯管道 | 将输入数据对象经过JSON.stringify()方法转换后输出对象字符串 |
| SlicePipe | 非纯管道 | 将数组或字符串裁剪成新子集 |

详情可查看[Angular2 Pipe文档](https://v2.angular.cn/docs/ts/latest/api/#!?query=pipe)

#### 自定义管道
虽然Angular提供了内置管道，但数据转换涉及各种各样的格式，内置管道显然无法满足全部需求，因此需要使用Angular提供的自定义管道功能来实现更多的需求。

以一个性别转换（female->女，male->男）的自定义管道为例。
**1.定义元数据**
使用@Pipe定义元数据前必须从@angular/core中引入Pipe和PipeTransform，示例代码如下：
```ts
// sexreform.pipe.ts
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: 'sexReform'})
export class SexReform implements PipeTransform {
  // ...
}
```
通过@Pipe装饰器来告诉Angular这个是一个管道类，@Pipe的元数据有一个name属性，用来指定管道名称，这个名称必须是有效的JS标识符，此处将管道命名为sexReform。

**2.实现transform方法**
自定义管道必须继承接口类PipeTransform，同时自定义管道必须实现PipeTransform接口的transform()方法,该方法的第一个参数为需要被转换的值，后面可以有若干个可选转换参数，该方法需要返回一个转换后的值。
```ts
// ...
export class SexReform implements PipeTransform {
  transform(val: string): string {
    switch(val) {
      case 'male' : return '男';
      case 'female' : return '女';
      default : return '未知性别';
    }
  }
}
```

**3.使用自定义管道**
在组件模板中使用自定义管道之前，必须在@NgModule的元数据declarations数组中添加自定义管道。
```ts
import { SexReform } from 'pipes/sexreform.pipe';
// ...

@NgModule({
  //...
  declarations: [SexReform]
})
```
添加到declarations数组后，然后就可以在模板中像内置管道一样使用自定义管道了
```ts
// ...
@Component({
  selector: 'pipe-demo-custom',
  template: `
  <p>{{ sexValue | sexReform }}</p>
  `
})
// ...
```

#### 管道的变化监测
Angular在每次点击、移动鼠标、定时器触发以及服务器响应等事件后都会对数据绑定进行变化监测，而频繁的变化监测会引起性能问题。但可以通过使用管道让Angular选择使用更简单、更快速的变换监测策略来提高性能。

以通过管道实现过滤联系人列表功能为例：
```ts
// ...
@Pipe({name: 'selectContact'})
export class SelectContactPipe implements PipeTransform {
  transform(allContacts: Array, prefix: string){
    return allContacts.filter(contact => contact.name.match('^'+prefix));
  }
}

@Component({
  selector: 'pipe-demo',
  template: `
  <input type="text" #box (keyup.enter)="addContact(box.value); box.value=''" placeholder="输入联系人后回车添加" />
  <div *ngFor="let contact of (contacts | selectContactPipe: '李')">{{ contact.name }}</div>
  `
})
export class PipeDemoComponent{
  contacts = [{name: '张三'},{name:"李四"}];
  addContact(name:string){
    this.contacts.push({name});
  }
}
```
上述代码，定义了一个过滤联系人的管道SelectContactPipe，并传入姓字符串“李”，联系人列表经过管道过滤，只显示姓“李”的联系人。若在文本框控件中输入一个新联系人“李无”，然后回车触发addContact()方法，将新联系人加入数组中，此时预料中联系人列表应该会实现显示新的“李无”联系人，但结果无变化，还是只有“李四”。

因为Angular管道的变换监测策略对性能进行了优化，这种检测策略会忽略检查列表内部数据的变化，上例中使用`this.contacts.push(contact)`新增一个联系人，数组对象引用没有发生变化，从Angular角度上，引用地址不变的数组不进入SelectContactPipe筛选管道，所以列表数据没有更新，页面不会实时显示更新后的联系人数组，这种筛选管道称为**纯管道**，虽然纯管道优化了性能，但有时却不符合需求，要实现需求就需要Angular的另外一个变化监测机制。

##### 纯管道
在模板表达式中使用纯管道（Pure Pipe）后，只有在监测到输入值发生纯变更时才会调用纯管道的transform()方法来实现数据转换，从而将数据更新到页面上，纯变更是指对基本数据类型（String、Number、Boolean等）输入值的变更或对对象引用（Date、Array、Function、Object等）的更改。

以DatePipe日期转换管道为例，分别用String类型和Date类型的对象作为输入值，对日期进行格式化，同时设定一个2s的定时器，用来动态改变日期的月份：
```ts
// ...
@Component({
  selector: 'pure-pipe-demo',
  template: `
  <div>
    <p>'{{ dateObj | date: "y-MM-dd HH:mm:ss EEEE" }}'</p>
    <p>'{{ dateStr | date: "y-MM-dd HH:mm:ss EEEE" }}'</p>
  `
})
export class PurePipeDemoComponent{
  dateObj: date = new Date('2016-06-08 20:05:05');
  dateStr: string = '2016-06-08 20:05:05';

  constructor(){
    setTimeout(() => {
      this.dateObj.setMonth(11);
      this.dateStr = '2016-12-08 20:05:05';
    }, 2000);
  }
}
```
结果，两个日期字符串开始日期一致，都是'2016-06-08 20:05:05'，2s后，dateStr变化，而dateObj则无变化。因为dateStr的引用发生了变化，被赋值为另外一个常量字符串，而dateObj的引用没有变化。
在模板表达式中使用纯管道DatePipe，只有当输入值发生纯变更后才会调用该管道并更新变化的值。

纯管道的变化监测策略是基于判断基本类型的数据值或对象的引用是否被改变来监测对象变化的，对象引用的监测方式比遍历对象内部所有属性值的监测方式要快得多，这样能快速的判断是否可以跳过执行管道并更新视图。

##### 非纯管道
使用非纯管道（Impure Pipe），Angular组件在每个变换监测周期都会调用非纯管道，并执行管道的transform()方法来更新页面数据，可以在管道元数据里将pure属性值设置为false来定义非纯管道。
```ts
@Pipe({
  name: 'selectContact',
  pure: false
})
```

在Angular的内置管道里，SlicePipe、AsyncPipe、JsonPipe属于非纯管道。
以非纯异步管道（AsyncPipe）为例，非纯异步管道需要接收Promise或Observable对象作为输入，并自动订阅这个输入，最终返回该异步操作产生的值：
```ts
import { Component, OnInit } from '@agular/core';
import { Observable, Subscriber } from '@rxjs/Rx';

@Component({
  selector: 'impure-pipe-demo',
  templat: `
  <p> 时间: {{ time | async }}
  `
})
export class ImpurePipeDemoComponent implements OnInit{
  time: Observable<string>;
  constructor(){}

  ngOnInit(){
    this.time = new Observable<string>((observer: Subscriber<string>) => {
      setInterval(()=> observer.next(new Date().toLocaleString()), 1000);
    })
  }
}
```
上例中，使用非纯异步管道将一个时间字符串time的Observable绑定到视图中，通过异步管道实现了每隔1s切换时间的时钟效果。


### 安全导航操作符
Angular的模板表达式在某些特定场景中允许使用一些特殊的链接操作符，如管道操作符`|`，安全导航操作符`?.`

```html
<p>{{ detail.telNum }}</p>
```
假设模板变量detail没被赋值，在Angular会因为报错而导致程序无法运行。一般解决方案为：
```html
<p>{{ detail && detail.telNum }}</p>
```
但当碰到多级属性路径时，继续使用这种&&判断方式将会导致代码较为臃肿，后期维护比较困难。Angular的安全导航操作符`?.`可以用来规避因为属性路径中出现null或undefined值而出现的错误。