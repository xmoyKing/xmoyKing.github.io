---
title: effective-javascript7
date: 2017-03-9 08:34:20
tags:
  - js
  - effective javascript
  - note
---

## 并发
JS是一种嵌入式的脚本语言, JS程序不是作为独立的应用程序运行的,而是作为大型应用程序环境下的脚本运行的. 比如Web浏览器, 具有多个窗体(Window)和标签(Tab), 没个程序需要响应不同的输入和事件, 如键盘,鼠标,网络,定时任务等.

在JS中,编写响应多个并发事件的程序非常简单, 而且有时编写者甚至都不知道自己的代码是并发的. 这得益于JS的一个简单的执行模型, 即事件队列(事件循环并发) 和 异步API. 

但在官方ES标准中,并没有提及并发.

### 61. 不要阻塞I/O事件队列
在JS中,大多的I/O操作都提供了异步的或非阻塞的API, 给程序提供一个回调函数, 一旦输入完成就可以被系统调用,而不是将程序阻塞在等待结果的线程上. 比如浏览器在加载网页过程中下载资源.
```js
downloadAsync('http://example.com/file.txt', function(txt){
    ...
});
```
系统在程序调用的时候, 会适时的介入其中, 在完成操作的瞬间调用回调函数. 系统维护了一个按事件发生顺序排列的内部事件队列, 一次调用一个已注册的回调函数.

所以JS并发的最重要的规则是不要在应用程序事件队列中使用阻塞I/O的API.

异步的API在基于事件的环境中是安全的, 因为他们迫使应用程序逻辑在一个独立的事件循环"轮询"中继续处理. 

在上述下载文件的例子中, 假设下载资源需要一段时间, 在这段时间内, 有极其庞大的其他事件很可能发生. 在同步是实现中, 这些事件会堆积在事件队列中, 而事件循环将停留等待该JS代码执行完成, 这将阻塞任何其他事件的处理. 

但在异步版本中, JS代码注册一个事件处理程序并立即返回, 在下载完成之前, 允许其他事件处理程序处理这期间的事件.

比如,Web中的`Worker`的API使大量的并行计算成为可能. 不同于传统的线程执行, Workers在一个完全隔离的状态下执行, 没有获取全局作用域或主线程页面内容的能力. 因此,他们不会阻塞主事件队列中运行的代码的执行. 在一个Worker中, 使用`XMLHttpRequest`同步的版本很少出问题, 下载的操作会阻塞Worker的执行, 但并不阻止页面的渲染或事件队列中的事件响应.

在服务端环境, 阻塞的API在启动开始(在服务器开始接收响应输入的请求之前)是没问题的, 但在处理请求期间, 浏览器事件队列中存在阻塞API就是灾难.

1. **异步API使用回调函数来延缓处理代价高昂的操作以避免阻塞主应用程序**
2. **JS并发地接收事件,但会使用一个事件队列按序处理事件**
3. **在程序事件队列中不要使用阻塞的I/O**

### 62. 在异步序列中使用嵌套或命名的回调函数
借助闭包, 使用嵌套能将异步操作按照"顺序"执行. 但嵌套的异步操作很容易看懂, 但当扩展到更多的操作时, 序列会变得很笨拙.
```js
downloadAsync('url1', function(url){
    downloadAsync(url, function(file){
        downloadAsync('a.txt', function(a){
            downloadAsync('b.txt', function(b){
                ...
            });
        });
    });
});
```

减少过多嵌套的方法之一是将回调函数作为命名函数, 并将它们需要的附加数据作为额外的参数传递. 

# 此条笔记不完整,原因如下:
现在已经有`Promise`能结构良好的定义异步嵌套以及顺序调用的问题了.


### 63. 当心丢弃错误
管理异步编程的一个问题是对错误的处理, 对同步的代码, 通过`try`语句块包装一段代码很容易处理所有的错误.
```js
try{
    f();
    g();
    h();
} catch(e){
    // ...
}
```

对于异步的代码, 多步的处理通常被分割到事件队列的单独轮次中, 因此, 不可能将他们全部包在一个try语句中. 

事实上, 异步API甚至根本不抛出异常, 因为当一个异步错误发生时, 没有一个明显的执行上下文来抛出异常! 相反, 异步API倾向于将错误表示为回调函数的特定参数, 或使用一个附加的错误处理回调函数(也被称为errbacks).
```js
downloadAsync('url1', function(url){
    // ...
}, function(err){
    console.log(err);
});
```

还有一种错误处理的风格由Node.js而起, 将回调函数的第一个参数作为错误标识, 若有错误发生就表示为错误, 否则就是一个假值, 如`null`. 对这种错误, 可以定义一个通用的错误处理函数, 使用`if`语句来控制每个回调函数.
```js
function onErr(err){
    console.log(err);
}

downloadAsync('url1', function(err, url){
    if(err) return onErr(err);
    // ...
});
```

1. **通过编写共享的错误处理函数来避免复制和粘贴错误处理代码**
2. **确保明确的处理所有的错误条件以避免丢弃错误**

### 64. 对异步循环使用递归
将循环实现为一个函数
```js
function downloadOneAsync(urls, onsuccess, onfailure){
    var n = urls.length;
    function tryNextURL(i){
        if(i >= n){
            onfailure("all download failed");
            return;
        }

        downloadOneAsync(urls[i], onsuccess, function(){
            tryNextURL(i+1);
        });
    }
    tryNextURL(0);
}
```

通常情况下, 递归函数在调用自身太多后会产生运行错误, 由于耗尽栈空间, 最终抛出异常(栈溢出).

但异步回调函数不会耗尽栈空间, 因为异步API在其回调函数被调用前会立即返回, 其栈帧在任何递归调用将新的栈帧推入栈前, 会从调用栈中弹出.

事实上,回调函数总是在事件循环的单独轮次中被调用, 事件循环在每个轮次中调用其事件处理程序的调用栈最初是空的, 所以无论回调函数需要迭代调用多少次, 都不会耗尽栈空间.

1. **循环不能是异步的**
2. **使用递归函数在事件循环的单独轮次中执行迭代**
3. **在事件循环的单独轮次中执行递归, 并不会导致调用栈溢出**

### 65. 不要在计算时阻塞事件队列
为了保持客户端应用程序的高度交互性和确保所有传入的请求在服务器程序中得到了充分的服务,保持事件循环的每轮次尽可能短是很重要的. 否则,事件队列会滞销, 其增长速度会超过分发处理事件程序的速度.

当程序需要执行代价高昂的计算时如何办呢? 目前没有完全正确的答案, 但是一般是使用Worker API的并发机制.
```js
// 比如下面是一个用于搜索大量可移动距离的人工智能游戏
var ai = new Worker('ai.js');
```
使用ai.js源文件作为worker的脚本, 产生一个新的线程独立的事件队列的并发执行线程. 该worker运行在一个完全隔离的状态, 没有任何程序对象能直接访问. 但程序可与worker之间可以用字符串messages来交互.
```js
var userMove = '...';

ai.postMessage(JSON.stringify({userMove: userMove}));
```
`postMessage`的参数被作为一个消息增加到worker的事件队列中,为了处理worker的响应, 游戏需要注册一个事件处理程序.
```js
ai.onmessage = function(event){
    executeMove(JSON.parse(event.data).computerMove);
}
```

在ai.js文件中, 写了worker监听消息并执行计算下一步移动所需的工作.
```js
self.onmessage = function(event){
    var userMove = JSON.parse(event.data).userMove;
    var computerMove = computeNextMove(userMove);
    var message = JSON.stringify({
        computerMove: computerMove
    });

    self.postMessage(message);
};

function computerNextMove(userMove){
    ...
}
```
Worker这样的API有时传递消息的开销可能很昂贵. 而且若没有这样的API,则可以将算法分解为多个步骤, 每个步骤组成一个工作块. 
```js
// 搜索社交网络图的工作表
Member.prototype.inNetwork = function(other){
    var visited = {};
    var worklist = [this];
    while(worklist.length > 0){
        var member = worklist.pop();
        // ...
        if(member === other){
            return true;
        }
    }
    return false;
};
```
若while循环代价太高, 搜索时间会很长, 同时阻塞程序事件队列. 即使用Worker, 也不方便, 因为它需要复制整个网络图的状态或在worker中存储网络图的状态, 并需要频繁使用消息传递来更新和查询网络.

由于该算法是在whie循环内迭代,可以将它定义为步骤集的序列, 通过增加一个回调参数将`inNetWork`转换为一个匿名函数.

```js
// 将while循环替换为一个匿名的递归函数
Member.prototype.inNetwork = function(other, callback){
    var visited = {};
    var worklist = [this];
    function next(){
        if(worklist.length === 0){
            callback(false);
            return;
        }
        
        var member = worklist.pop();
        // ...
        if(member === other){
            callback(true);
            return true;
        }
        // ...
        setTimeout(next, 0); // 下一次迭代
    }
    setTimeout(next, 0); // 第一次迭代
};
```
以上代码中的`setTimeout`能立刻将回调函数添加到事件队列中, 但还可以用更好的方法替代. 比如`postMessage`.

同时, 若每轮次next只执行一次算法,则可能效率太低, 可以增加每轮次的迭代次数.
```js
// 在next函数的主体外围使用循环计数器
Member.prototype.inNetwork = function(other, callback){
    // ...
    function next(){
        for(var i = 0; i < 10; ++i){
            // ...
        }
        setTimeout(next, 0);
    }
    setTimeout(next, 0);
};
```

1. **避免在主事件队列中执行代价高昂的算法**
2. **在支持Worker API的平台, 该API可以用来在一个独立的事件队列中运行长计算程序**
3. **在Worker API不可用或代价昂贵的环境中, 考虑将计算程序分解到事件循环的多个轮次中**

### 66. 使用计数器来执行并行操作
并发事件是JS中不确定性的主要来源, 程序的执行顺序并不能保证与事件发生的顺序一致.

工具函数`downloadAllAsync`接收一个URL数组并下载所有文件, 返回一个存储了文件内容的数组, 每个URL对应一个字符串.downloadAllAsync不仅可以清理嵌套回调函数,而且能并行下载文件. 可以在一次事件循环中启动所有的文件的下载.

每次下载成功, 就将文件内容传入result数组, 若所有URL都成功下载,则调用onsuccess回调函数, 若有任何失败, 则调用onerror回调函数, `result = null`能保证若多次下载失败,onerror只被调用一次, 即第一次错误发生时.
```js
function downloadAllAsync(urls, onsuccess, onerror){
    var result = [], length = urls.length;

    if(length === 0){ // 若没有需要下载的url,则直接调用成功事件并返回结果.
        setTimeout(onsuccess.bind(null, result), 0);
        return;
    }

    urls.forEach(function(url){
        downloadAsync(url, function(text){
            if(result){
                // 存在竞争条件, 可能会出错
                result.push(text); 
                if(result.length === url.length){
                    onsuccess(result);
                }
            }
        }, function(error){
            if(result){
                result = null;
                onerror(error);
            }
        });
    });
}

// 使用
var filenames = [
    'huge.txt', // 大文件
    'tiny.txt', // 小文件
    'medium.txt' // 中等大小文件
];

downloadAllAsync(filnames, function(files){
    // 以下顺序无法保证
    console.log('huge.file', files[0].length);
    console.log('tiny.file', files[1].length);
    console.log('medium.file', files[2].length);
}, function(error){
    console.log('error: '+ error);
});

```
以上函数中, 当一个程序依赖于特定的事件顺序才能正常工作时, 程序就会出现数据竞争(data race), 数据竞争指多个并发操作可以修改共享的数据结构, 这取决于他们真正发生的顺序,而不是调用顺序.

当一个程序依赖于特定的时间顺序才能正常工作时, 这个程序会遭受数据竞争, 数据竞争是指多个并发操作可以修改共享的数据结构, 这取决于他们的发生顺序.


若想要不依赖事件的执行顺序而总是得到顺序的结果,我们需要将结果存储在原始索引的位置.而不是每次`push`到结果数组.
```js
function downloadAllAsync(urls, onsuccess, onerror){
    var result = [], length = urls.length;

    if(length === 0){ // 若没有需要下载的url,则直接调用成功事件并返回结果.
        setTimeout(onsuccess.bind(null, result), 0);
        return;
    }

    urls.forEach(function(url, i){
        downloadAsync(url, function(text){
            if(result){
                result[i] = text; // 将结果字符串存储在原始索引处
                if(result.length === url.length){
                    onsuccess(result);
                }
            }
        }, function(error){
            if(result){
                result = null;
                onerror(error);
            }
        });
    });
}
```
但以上程序还是会出错, 那就是若索引为`length-1`的文件先下载好, 比如共3个文件,索引为2的文件先下载好,这将导致`result.length`被更新为3, 用户的success回调函数将被过早的调用,其参数为一个不完整的数组.

正确的实现应该是使用一个计数器来追踪操作数量.
```js
function downloadAllAsync(urls, onsuccess, onerror){
    var result = [], pending = urls.length;

    if(pending === 0){ // 若没有需要下载的url,则直接调用成功事件并返回结果.
        setTimeout(onsuccess.bind(null, result), 0);
        return;
    }

    urls.forEach(function(url, i){
        downloadAsync(url, function(text){
            if(result){
                result[i] = text; // 将结果字符串存储在原始索引处
                --pending; // 表示完成一次操作
                if(pending === 0){
                    onsuccess(result);
                }
            }
        }, function(error){
            if(result){
                result = null;
                onerror(error);
            }
        });
    });
}
```

1. **JS程序中的事件发生是不确定的,即顺序是不可预测的**
2. **使用计数器避免并行操作中数据竞争**

### 67. 绝不要同步调用异步的回调函数
假设有一个downloadAsync的变种版本, 它能缓存已经下载的文件, 避免多次下载同一个文件. 在文件已经缓存的情况下, 立即调用回调函数.
```js
// 缓存使用Dict类
var cache = new Dict();

function downloadCachingAsync(url, onsuccess, onerror){
    if(cache.has(url)){
        onsuccess(cache.get(url)); // 直接调用
        return;
    }
    return downloadAsynce(url, function(file){
        cache.set(url, file);
        onsuccess(file);
    }, onerror);
}
```
通常情况下,downloadCachingAsync会立即提供缓存的数据, 但会有一些小问题. 首先它改变了操作的预期顺序, 比如对一个正常的异步API应该是用可预测的顺序来记录日志.
```js
downloadAsync('file.txt', function(file){
    console.log('finished');
});
console.log('starting');
```
而使用上面的downloadCachingAsync实现, 则上述的日志可能会以任意顺序记录事件, 因为文件是否被缓存对日志顺序有很大影响.

除了日志的顺序, 异步API的目的是维持事件循环中每轮的严格分离, 这简化了并发, 通过减轻每轮事件循环的代码量而不用担心其他代码并发修改共享的数据结构. 同步调用异步回调违反了分离, 导致在当前轮完成之前, 代码用于执行一轮隔离的事件循环.

比如, 下面程序用一个剩余文件队列给用户下载和显示消息
```js
downloadCachingAsync(remaining[0], function(file){
    remaining.shift();
    // ...
});

status.display('downloading '+ remaining[0] + '...');
```
若同步调用该函数, 那么将显示错误的文件名的消息, 若队列为空时, 会显示undefined.

同步的调用异步回调函数可能导致一些问题, 64条中解释了异步回调函数本质上是以空的调用栈来调用, 因此将异步的循环实现为递归函数是安全的, 完全没有累积超越调用栈空间的危险. 

同步的调用不能保证这点, 因而会使得一个表面上异步循环很可能会耗尽调用栈空间. 另一个问题是异常,对于上述的downloadCachingAsync实现, 若回调函数抛出一个异常, 它将会在每轮的事件循环中, 也就是开始下载时而不是期望的一个分离的回合抛出该异常.

为了确保总是异步调用回调函数, 可以使用已经存在的异步API, 使用通用的setTimeout在事件队列中增加一个回调函数.
```js
var cache = new Dict();

function downloadCachingAsync(url, onsuccess, onerror){
    if(cache.has(url)){
        var cached = cache.get(url);
        setTimeout( onsuccess.bind(null, cached), 0); // 使用bind将结果保存为onsuccess回调函数的第一个参数
        return;
    }
    return downloadAsynce(url, function(file){
        cache.set(url, file);
        onsuccess(file);
    }, onerror);
}
```

1. **即使可以立即得到数据,也绝不要同步地调用异步回调函数**
2. **同步地调用异步的回调函数扰乱了预期的操作序列, 并可能导致意向不到的交错代码**
3. **同步调用异步的回调函数可能导致栈溢出或错误的处理异常**
4. **使用异步的API, 比如setTimeout函数来调度异步回调函数,使其运行于另一个回合**

### 68. 使用Promise模式清洁异步逻辑

1. **promise代表最终值, 即并行操作完成时最终产生的结果**
2. **使用promise组合不同的并行操作**
3. **使用promise模式的API避免数据竞争**
4. **在要求有意的竞争条件时使用select(也被称为choose)**