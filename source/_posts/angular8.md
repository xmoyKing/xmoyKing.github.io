---
title: Angular2入门-服务与RxJS-1
categories: Angular
tags:
  - js
  - typescript
  - angular
date: 2017-10-15 17:04:58
updated:
---

在Angular中，服务用于书写可重用的公共功能（如日志处理、权限管理等）和复杂业务逻辑、对应应用程序的模块化有着很重要的意义。

介绍Angular服务的概念，优点、以及如何创建和使用服务，然后介绍内置服务HTTP服务，HTTP服务是基于RxJS异步库（基于响应式编程范式实现）编写的，最后介绍RxJS及其背后的响应式编程理念

### Angular服务
Angular服务一般封装了某种特定功能的独立模块，它可以通过注入的方式供外部调用，服务在Angular中使用非常广泛，比如：
- 多个组件中出现重复代码时，把重复代码提取到服务中实现代码复用
- 当组件中参杂了大量的业务代码和数据处理逻辑时，把这些逻辑封装成服务供组件使用，组件只负责UI相关的逻辑，有利于后续的更新和维护
- 把需要共享的数据存储在服务中，通过在多个组件中注入同一服务实例实现数据共享

常用的Angular服务为：
- 和服务器通讯的数据服务
- 检查用户输入的验证服务
- 方便跟踪错误的日志服务

#### 使用场景
通过2个示例学习Angular服务在业务逻辑封装和服务实例共享两种场景的使用。

##### 业务逻辑封装
结合通讯录例子中编辑联系人的功能来说明如何编写一个服务。
一般编辑联系人需要的步骤如下：
1. 从服务器拉去联系人信息
2. 验证用户修改的数据
3. 把修改后的数据提交到服务器

虽然可以把所有代码都写在组件里，但这样做会使组件的代码量非常大且杂乱，不利于后续代码维护。所以，最好从服务器拉去联系人信息和提交数据到服务器的代码封装到ContactService的类中。
```ts
// contact.service.ts
import { Injectable } from '@angular/core';
//...

@Injectable()
export class ContactService{
  // 从服务器拉去联系人信息
  getContactData(){
    //...
  }

  // 提交数据到服务器
  updateContacts(contact: Contact){
    //...
  }
}
```
@Injectable()装饰器用于说明被装饰的类依赖了其他服务，而这里ContactService没有依赖其他服务，所以@Injetable()是可以省略的，但Angular官方推荐无论是否有依赖其他服务，都使用@Injectable()来装饰服务，因为添加@Injectable()装饰器有利于提高代码的可读性、一致性、减少异常发生。

通讯录例子的EditComponent组件中，通过依赖注入使用ContactService服务，需要先将ContactService服务通过import导入，再在组件的构造函数中引入服务的实例，接着就可以在逻辑代码中调用服务的方法了。
```ts
import { Component, OnInit, Input } from '@angular/core';
import { ContactService } from 'shared/contact.service';

@Component({
  selector: 'my-operate',
  templateUrl: 'app/edit/edit.component.html',
  styleUrls: ['app/edit/edit.component.css']
})
export class EditComponent implements OnInit {
  constructor(
    // ...
    private _contactService: ContactService,
  ){}
  // ...
}
```
此处没有在@Component的providers元数据上显示声明ContactService服务，是因为通讯录例子的服务是采用模块注入的方式，在模块中注入服务，该模块下所有的组件都共享服务。

##### 共享服务示例
实际开发中，通常需要在多个组件之间进行通信，这种情况下除了组件通信的一些常用方法（父子组件间的输入输出属性、自定义事件、局部变量），还可以通过在组件间共享同一服务实例来实现通信。

以可在组件间共享数据的服务SharedService为例：
```ts
// shared.service.ts
import { Injectable } from '@angular/core';

@Injectable()
export class SharedService{
  list: string[] = [];
  append(str: string){
    this.list.push(str);
  }
}
```
该服务包含一个list数组对象和append()方法，组件可以调用该服务的append方法向list数组中添加数据。
使用时以父子组件为例，子组件ChildComponent接收用户输入并调用SharedService的append方法添加数据，父组件ParentComponent把SharedService的数据变化实时展示到模板中。
```ts
// parent.component.ts
import { Component } from '@angular/core';
import { SharedService } from './shared.service';
import { ChildComponent } from './child.component';

@Component({
  selector: 'parent-component',
  template: `
  <ul *ngFor="#item in list">
    <li>{{ item }}</li>
  </ul>
  <child-component></child-component>
  `,
  providers: [SharedService]
})
export class ParentComponent {
  list: string[] = [];
  constructor(private _sharedService: SharedService){}
  ngOnInit(): any {
    this.list = this._sharedService.list;
  }
}
```
为了父子组件都能获取到SharedService的同一个实例，需要在父组件中添加`providers: [SharedService]`，子组件不需要重复添加，否则父子组件获得的SharedService实例不是同一个。当然，也可以在父子组件所属的模块中统一配置`providers: [SharedService]`，那么父组件就不要配置了。
```ts
// child.component.ts
import { Component } from '@angular/core';
import { SharedService } from './shared.service';

@Component({
  selector: 'child-component',
  template: `
  <input type="text" [(ngModel)]="inputText" />
  <button (click)="add()">添加</button>
  `,
  providers: [SharedService]
})
export class ChildComponent {
  inputText: string = 'Testing data';
  constructor(private _sharedService: SharedService){}
  add() {
    this._sharedService.append(this.inputText);
    this.inputText = '';
  }
}
```
父子组件中注入同一个服务实例是实现的关键，也是层级注入的一个应用场景。

#### HTTP服务
HTTP服务是Angular中使用HTTP协议与远程服务器进行通讯的一个独立模块。在Angular应用中使用HTTP服务只需要3个简单步骤：
1. 在模块装饰器@NgModule中导入HttpModule
2. 在组件模块中导入HTTP服务
3. 在组件的构造函数中声明引入

注：上面的SharedService服务是通过@Component的providers属性（元数据）注入到组件中（即在组件中注入服务），而HTTP服务则是通过导入HttpModule注入到模块中（即在模块中注入服务）。这两种方式注入，服务都能被组件正常使用，但形成的作用域返回不同。

调用HTTP服务的一个例子：
```ts
// app.module.ts
import { HttpModule } from '@angular/http';
// ...

@NgModule({
  imports: [
    HttpModule // 1.在NgModule中导入HttpModule
  ],
  // ...
  bootstrap: [AppComponent]
})
export class AppModule{}
```
然后在组件中引入HTTP服务后，就可以用AJAX和JSONP两种方式发送数据请求了。
```ts
//contact.component.ts
import { Component } from '@angular/core';
import { bootstrap } from '@angular/platform-browser/browser';
import { Http } from '@angular/http'; // 2.导入HTTP服务

@Component({
  selector: 'contact',
  template: `<div>Http Service!</div>`
})
export class ContactComponent {
  constructor(http: Http){ // 3.声明引入
    // ...
  }
}
```

##### AJAX
AJAX是使用XMLHttpRequest对象向服务器发送请求并处理响应的通信技术，支持异步和同步2种方式。

一般来说使用异步方式，有3种方式处理异步操作：
**回调函数**
回调函数是最基本的异步操作方式，通过将函数A作为另一个函数B的行参，在B中调用函数A以此实现回调。这种方式简单好理解，但容易照成冗长的回调链（多层嵌套）问题，不利于代码维护。

**使用Promise**
Promise给异步操作提供了统一的接口，使得程序具备正常的同步运行流程，回调函数也不必层层嵌套，最终代码易于理解，可维护强。如下例使用Promise的方式：
```ts
// Promise写法
(new Promise(function(resolve, reject){}))
  .then(funcA)
  .then(funcB)
  .then(funcC);
```

**使用Observable**
Angular推荐使用Observable处理异步操作，HTTP服务的API接口返回的也是Observable对象。

Observable是响应式编程模型Rx的核心概念，Rx的全称是Reactive Extensions，是微软开发的一套响应式编程模型，RxJS是它的JS版本，Angular对RxJS做了封装处理，使得在Angular开发中更易用。

以通讯录获取远程联系人信息的数据为例(HTTP GET请求)：
```ts
import { NgModule } from '@angular/core';
import { HttpModule } from '@angular/http';
import { AppComponent } from './app.component';

@NgModule({
  imports: [
    HttpModule
  ],
  declarations: [AppComponent],
  bootstrap: [AppComponent]
})
export class AppModule{}
```
下面的数据服务以查找联系人为例：
```ts
// contact.service.ts
import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import { Observable } from 'rxjs/Rx';

const CONTACT_URL = `./app/contacts.json`;

@Injectable()
export class ContactService{
  constructor(private _http: http){}

  getContacts(): Observable<any[]> {
    return this._http.get(CONTACT_URL)
                .map(this.extractData)
                .catch(this.handleError);
  }

  private extractData(res: Response) {
    let body = res.json();
    return body.data || {};
  }

  private handleError(error: any){
    let errMsg = (error.message) ? error.message :
              error.status ? `${error.status} - ${error.statusText}` : `Server error`;
    console.error(errMsg); // 打印
    return Observable.throw(errMsg);
  }
}
```
_http.get()返回一个Observable对象，而map()方法是它的常用操作之一，在extractData()方法里需要通过json()方法把服务器返回的数据转换成JSON对象。

不建议在getContacts()方法直接返回Response对象，数据服务应该对使用者隐藏实现细节，使用者只需要调用数据服务的接口取得数据，并不需要关系数据是如何获得以及是何种数据格式。

最后处理异常情况，任何I/O操作都可能发生错误（如网络错误），所以在数据服务里做好异常处理非常必要，通过catch操作捕捉错误并答应，然后使用Observable.throw()方法重新返回一个包含错误信息的Observable对象。

在组件中使用ContactService服务：
```ts
// contact.component.ts
import { Component } from '@angular/core';
import { ContactService } from 'shared/contact.service.ts';

@Component({
  // ...
})
export class ContactComponent {
  // ...
  constructor(
    private _contactService: ContactService
  ){}

  getContacts(){
    return this._contactService.getContacts()
            .subscribe(
              contacts => this.contacts = contacts,
              error => this.errorMessage = <any>error
            );
  }
}
```
需注意：在ContactComponent的getContacts()方法中，_http.get()并没有发出请求，因为RxJS中的Observable实现的是“冷”模式，只有当它被getContacts().subscribe订阅之后才会发出请求。