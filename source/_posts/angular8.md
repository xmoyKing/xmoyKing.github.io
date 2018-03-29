---
title: Angular2入门-服务
categories: Angular
tags:
  - TypeScript
  - Angular
  - 揭秘 Angular2
date: 2017-10-15 17:04:58
updated:
---

在Angular中，服务用于书写可重用的公共功能（如日志处理、权限管理等）和复杂业务逻辑、对应应用程序的模块化有着很重要的意义。

介绍Angular服务的概念，优点、以及如何创建和使用服务，然后介绍内置服务HTTP服务，HTTP服务是基于RxJS异步库（基于响应式编程范式实现）编写的。

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

为通讯录示例增加添加/编辑联系人的功能(HTTP POST)。
服务端的接口符合REST规范，添加联系人和拉取联系人的URL路径一样，至少METHOD不同，添加/编辑使用POST方法，并且在请求体中新增Contact对象的联系人数据。

服务端的接口在接收到数据并验证通过后会生成唯一id并保存到数据库中，然后以JSON格式放回带id的新联系人数据。因为要发起POST请求，并且在请求体中传递JSON数据，所以要设置HTTP请求头Content-Type为'application/json'。

首先导入Headers和RequestOptions对象：
```ts
import { Headers, RequestOptions } from '@angular/http';
```
然后在ContactService服务中新增一个addContact()方法：
```ts
// contact.service.ts
// ...
addContact(contact: Contact): Observable<Contact> {
  let body = JSON.stringify(contact);
  let headers = new Headers({'Content-Type': 'application/json'});
  let options = new RequestOptions({headers: headers});

  return this._http.post(CONTACT_URL, body, options)
            .map(this.extractData)
            .catch(this.handleError);
}
// ...
```
Headers是RequestOptions的一个属性，RequestOptions作为第三个参数传递给HTTP服务的post()方法，这样就可达到自定义请求头的目的。
即使Content-Type已经被指定为JSON类型，但服务端仍然只接收字符串，所以请求前需要将对象转换为JSON字符串。

在组件中使用addContact()方法和使用getContact方法一样：
```ts
// list.component.ts
// ...
addContact(contact: Contact){
  if(!contact) return;

  this._contactService.addContact(contact)
      .subscribe(
        contact => this.contacts.push(contact),
        error => this.errorMessage = <any>error
      );
}
```
在组件的addContact()方法中订阅ContactService中addContact()方法返回的Observable实例，请求返回时会把新联系人数据追加到contacts数组，然后Angular渲染更新展示。

另外HTTP服务返回的Observable对象可以方便的转换成Promise对象，以下为ContactService服务的Promise版本：
```ts
// Promise版本
// contact.service.ts
import { Injectable } from '@angular/core';
import { Http, RequestOptions } from '@angular/http';
import { Promise } from 'rxjs/Rx';

const CONTACT_URL = `./app/contacts.json`;

@Injectable()
export class ContactService{
  constructor(private _http: http){}

  getContacts(): Promise<any[]> {
    return this._http.get(CONTACT_URL)
                .toPromise()
                .then(this.extractData)
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
    return Promise.reject(errMsg);
  }
}
```

##### JSONP
这一技术的起因在浏览器同源策略的访问限制。关于同源策略不细讲，只谈解决方法。
若服务器和浏览器都支持CORS（Cross-Origin Resource Sharing）协议，则AJAX不受同源策略的限制。CORS是W3C的标准。若两端不方便实施CORS，则可以选择JSONP方案，它适用于任何浏览器。

因为script标签请求资源不会受同源策略限制，而JSONP就是利用script的特性来绕过同源策略，使用JSONP的关键在于利用script标签来发起GET请求，这个请求中传递callback参数给服务端，然后服务端返回一段JS代码，一般以callback函数包裹着JSON数据的形式返回，当script标签请求完成后会自动执行这段代码，所以可以在预先定义好的全局方法callbak中接收和处理JSON数据，注意JSONP只能发起GET请求，在需要发起POST请求的场景中并不适用。

HTTP服务中包含了JSONP服务，示例如下：
```ts
// contact.service.ts
import { Injectable } from '@angular/core';
import { Jsonp, URLSearchParams } from '@angular/http';

@Injectable()
export class ContactService{
  constructor(private _jsonp: Jsonp){}

  getContacts() {
    let URL = 'http://xxx.host.com/contacts';
    let params = new URLSearchParams();
    params.set('format', 'json');
    params.set('callback', 'JSONP_CALLBACK');

    return this._jsonp
                .get(URL, {search: params})
                .map(res => res.json())
                .subscribe(
                  contacts => this.contacts = contacts,
                  error => this.errorMessage = <any>error
                );
  }
}
```

##### HttpModule
HttpModule是在@angular/http中定义的用于封装HTTP相关功能的模块，它包含了HTTP服务，同时也包含HTTP所依赖的其他服务，HttpModule模块主要包含服务如下：
- HTTP: 封装了常用的HTTP请求方法
- BrowserXhr：用于创建XMLHTTPRequest实例的工厂
- XHRBackend：用于创建了XHRConnection实例，该实例会使用BrowserXhr对象来处理请求
- XSRFStrategy：接口，它定义了配置XSRF攻击保护的方式，目前Angular提供了CookieXSRFStrategy类帮助设置Request Header，用于防止XSRF攻击
- RequestOptions：封装了HTTP请求参数，BaseRequestOptions是它的子类，默认将请求设置为GET
方式
- ResponseOptions：封装了HTTP响应参数，BaseResponseOptions是它的子类，默认将响应设置为成功
以上服务为Angular默认提供的服务，实际开发用的较多的是HTTP服务，其他服务较少。

开发时，一般会对所有请求做统一处理，例如添加一些必要的HTTP自定义请求头域，或在后端返回某个错误时进行统一处理，如401错误码，或者统一在请求发出前显示“加载中”的状态并在请求返回后关闭该状态等。这种情况下，就可以通过实现ConnectionBackend类并重写createConnection()方法来实现。

首先编写一个HttpInterceptor服务，对请求发送前后进行处理：
```ts
// http-interceptor.ts
import { Injectable } from '@angular/core';
import { Request, Response } from '@angular/http';
import { Observable } from 'rxjs';

@Injectable()
export class HttpInterceptor{
  beforeRequest(request: Request): Request {
    // 请求发出前的处理逻辑
    console.log(request);
    return request;
  }

  afterResponse(res: Observable<Response>):Observable<any> {
    // 请求响应后的处理逻辑
    res.subscribe((data)=>{
      console.log(data);
    });
    return res;
  }
}
```
接着实现ConnectionBackend抽象类，目的是封装XHRBackend服务，在XHRBackend创建XHRConnection实例前后进行相应的逻辑处理：
```ts
// http-interceptor-backend.ts
import { Injectable } from '@angular/core';
import { ConnectionBackend, XHRConnection, XHRBackend, Request } from '@angular/http';
import { HttpInterceptor } from './http-interceptor';

@Injectable()
export class HttpInterceptorBackend implements ConnectionBackend {
  constructor(
    private _httpInterceptor: HttpInterceptor,
    private _xhrBackend: XHRBackend
  ){}

  createConnection(request: Request): XHRConnection {
    let interceptor = this._httpInterceptor;

    // 请求发出前，拦截请求并调用HttpInterceptor对象的beforeRequest()方法
    let req = interceptor.beforeRequest ? interceptor.beforeRequest(request) : request;
    // 通过XHRBackend对象创建XHRConnection实例
    let result = this._xhrBackend.createConnection(req);
    // 在得到响应后，拦截并调用HttpInterceptor对象的afterResponse方法
    result.response = interceptor.afterResponse ? interceptor.afterResponse(result.response) : result.response;

    return result;
  }
}
```

Angular的HttpModule源码中，HTTP服务默认是使用XHRBackend对象作为构造函数的第一个参数创建的，为了使HttpInterceptorBackend拦截生效，需要将创建HTTP服务时的第一个参数改为HttpInterceptorBackend对象，因此定义一个新的httpFactory工厂方法：
```ts
// HttpModule源码
// ...
export function httpFactory(xhrBackend: XHRBackend, requestOptions: RequestOptions): HTTP {
  return new Http(xhrBackend, requestOptions);
}
// ...
```
对应HttpModule源码，重写httpFactory方法
```ts
// http-factory.ts
import { RequestOptions, Http } from '@angular/http';
import { HttpInterceptorBackend } from './http-interceptor-backend';

export function httpFactory(httpInterceptorBackend: HttpInterceptorBackend, requestOptions: RequestOptions): HTTP {
  return new Http(httpInterceptorBackend, requestOptions);
}
```
最后在根模块中导入以上定义的服务即可：
```ts
// app.module.ts
import { Http, RequestOptions } from '@angular/http';
import { HttpInterceptorBackend } from './interceptor/http-interceptor-backend';
import { HttpInterceptor } from './interceptor/http-interceptor';
import { httpFactory } from './interceptor/http-factory';

// ...
  providers: [
    // ...
    HttpInterceptorBackend, HttpInterceptor,
    {
      provide: Http,
      useFactory: httpFactory,
      deps: [HttpInterceptorBackend, RequestOptions]
    }
  ]
// ...
```
如此之后，通过HTTP服务发出任何一个HTTP请求时，在控制台都能打印出Request对象和Response对象。
