---
title: 学习JavaScript数据结构与算法笔记
date: 2017-02-21 20:05:32
updated: 2017-02-21
categories: [fe]
tags: [js, data structure, algorithms, note]
---

《学习JavaScript数据结构与算法》 笔记，以下为使用ES5的语法
1. 数组
2. 栈
3. 队列
4. 链表
5. 集合
6. 字典和散列表
7. 树
8. 图
9. 排序和搜索算法
10. 其他：动态规划，贪心算法

## 数组
1. 添加和删除元素
    1. 使用`array[array.length] = 11;`或`array.push()`即可添加到最后，js中的数组是动态增长的，push方法可以添加任意多个元素，比如`array.push(1,2,3)`同时添加三个元素
    2. 方法`array.unshift(1，2)`可以直接把元素添加到数组首位
    3. 删除最后的元素用`array.pop()`方法，结合`array.push()`方法可以模拟栈
    4. 删除第一个元素用`array.shift()`方法，结合`array.unshift()`方法可以模拟基本队列
    5. 使用`array.splice(5,3)`方法可以删除数组相应位置和数量的元素，表示从索引5开始，删除3个元素
    6. 若`array.splice(5,3,0,1,2)`后面的三个参数表示用0，1，2代替原来被删除的3个元素
2. 二维和多维数组  
    ```js
    matrix = []; 
    matrix[0] = [];
    matrix[1] = [];
    ```
3. 数组方法参考
    ```
    方法	              描述
    concat()	        连接两个或更多的数组，并返回结果。
    join()	            把数组的所有元素放入一个字符串。元素通过指定的分隔符进行分隔。
    pop()	            删除并返回数组的最后一个元素
    push()	            向数组的末尾添加一个或更多元素，并返回新的长度。
    reverse()	        颠倒数组中元素的顺序。
    shift()	            删除并返回数组的第一个元素
    slice()	            从某个已有的数组返回选定的元素
    sort()	            对数组的元素进行排序
    splice()	        删除元素，并向数组添加新元素。
    toSource()	        返回该对象的源代码。
    toString()	        把数组转换为字符串，并返回结果。
    toLocaleString()    把数组转换为本地数组，并返回结果。
    unshift()	        向数组的开头添加一个或更多元素，并返回新的长度。
    valueOf()	        返回数组对象的原始值
    ```


## 栈
特点为先进后出，利用js数组实现一个栈类，具体代码为：
```js
function Stack(){
    var items = [];
    this.push = function(element){
        items.push(element);
    }
    this.pop = function(){
        return items.pop();
    }
    this.peek = function(){
        return items[items.length -1];
    }
    this.isEmpty = function(){
        return items.length == 0;
    }
    this.size = function(){
        return items.length;
    }
    this.clear = function(){
        items = [];
    }
    this.print = function(){
        console.log(this.toString());
    }
}
//使用
var stack = new Stack();
stack.print(); //输出true
...
``` 

### 利用栈将十进制转换为二进制
将十进制数与2整除，直到结果为0，将每次获取的余数入栈，输出的时候一一出栈即可。
```js
function dec2bin(dec){
    var rs = new Stack(), //余数数组    
        rem,    //余数
        s = ''; //二进制字符串
    while(dec > 0){
        //由于js的数字类型不区分整数or浮点数
        //所以需要使用Math.floor仅返回除数的整数部分
        rem = Math.floor(dec % 2); 
        rs.push(rem);
        dec = Math.floor(dec / 2);
    }
    while(!rs.isEmpty()){
        //将数组中的元素出栈并转换为字符串连接
        s += rs.pop().toString();
    }
    
    return s;
}

//若需转化为任意进制，则需添加一个参数表示基数
function dec2base(dec,base){
    var rs = new Stack(), //余数数组    
        rem,    //余数
        s = '', //二进制字符串
        digits = '0123456789ABCDEF'; //将数字转化为对应字符
    while(dec > 0){
        //由于js的数字类型不区分整数or浮点数
        //所以需要使用Math.floor仅返回除数的整数部分
        rem = Math.floor(dec % base); 
        rs.push(rem);
        dec = Math.floor(dec / base);
    }
    while(!rs.isEmpty()){
        //将数组中的元素出栈并转换为字符串连接
        s += digits[rs.pop()];
    }
    
    return s;
}
```

## 队列
特点为先进先出,利用js数组实现一个队列类，具体代码为：
```js
function Queue(){
    var items = [];
    this.enqueue = function(element){
        items.push(element);
    }
    this.dequeue = function(){
        return items.shift();
    }
    this.front = function(){
        return items[0];
    }
    this.isEmpty = function(){
        return items.length == 0;
    }
    this.size = function(){
        return items.length;
    }
    this.clear = function(){
        items = [];
    }
    this.print = function(){
        console.log(this.toString());
    }
}
//使用
var q = new Queue();
q.print();
```

### 优先队列
元素的添加和删除是基于优先级的，所以需要传入第二个参数表示优先级，其他方法和普通队列一样
```js
function PriorityQueue(){
    var items = [];
    //内部类，将优先级和元素数值保存为一个类
    function QueueElement(element, priority){
        this.element = element;
        this.priority = priority; //值越大，优先级越低
    }

    this.enqueue = function(element,priority){
        var qe = new QueueElement(element,priority); //新生成一个元素对象，保存数值和优先级
        if(this.isEmpty()){ 
            items.push(qe); //数组为空则直接添加到数组中
        }else{
            var added = false;
            for(var i =0; i < items.length; ++i){
                //优先级相同的，也是先进先出
                if(qe.priority < items[i].priority){ //比较优先级
                    items.splice(i, 0, qe); //插入到找到元素之前
                    added = true;
                    break;
                }
            }
            if(!added){ //若added还是false，表示当前的元素优先级是最低的，
                items.push(qe);
            }
        }
    }
    //...其他相同
}

//使用
var pq = new PriorityQueue();
pq.enqueue('John',2);
pq.enqueue('Jack',1);
pq.enqueue('Came',1);
pq.print(); //Jack,Came,John
```

### 循环队列-击鼓传花
击鼓传花，一个圈，当停止时，传到谁就出局，直到剩下1个
```js
function hotPotato(list, num){
    var q = new Queue();
    for(var i = 0; i < list.length; ++i){
        q.enqueue(list[i]); //将名字数组中的名字加入队列
    }

    var eliminated = '';
    while(q.size() > 1){
        for(i = 0; i < num; ++i){
            q.enqueue(q.dequeue()); //未淘汰则继续加入队列
        }
        eliminated = q.dequeue(); //将淘汰的记录下来
        console.log(eliminated + '淘汰了');
    }
    return q.dequeue(); //最后返回剩下的1个人
}

//测试
var names = ['A','B','C','D','E'];
var win = hotPotato(names, 7);
console.log(win + ' Win'); // A Win
```


## 链表
链表无法用数组实现，需要定一个一个节点类，用`next`属性模拟指针，指向下一个节点，同时注意删除和添加时的指针指向



## 动态规划 DP（Dynamic Programing）
### 最少硬币找零
给出要找零的钱数，以及可用的硬币面额d1...dn极其数量，找到所需最少的硬币个数  
例如：d1 = 1， d2 = 5， d3 = 10， d4 = 25，要找36美分的零钱，结果是1*25 + 1*10 + 1*1  

思想：设有n需要换零钱，找到n所需最少的硬币数量，在n - 1解的基础上求建立n的解，对前面的每一个数而言亦是如此，需要依次找到所有 x (x < n) 的解
```js
function MinCoinChange(coins){
    var coins = coins; //coins为面额数组，如[1,5,10,25]
    var cache = {}; //保存已经计算过的，不重复计算

    this.makeChange = function(amount){
        var self = this;
        if(!amount){ //若amount非正，则返回空数组
            return [];
        }
        if(cache[amount]){ //若存在缓存，则直接返回缓存
            return cache[amount];
        }
        var min = [], newMin, newAmount;
        for(var i = 0; i < coins.length; ++i){ //对每一面额都计算newAmount
            var coin = coins[i];
            newAmount = amount - coin;
            if(newAmount >= 0){ //若为正，则也计算newAmount的找零结果
                newMin = self.makeChange(newAmount);
            }
            if(newAmount >= 0 && 
            (newMin.length < min.length - 1 || !min.length) &&
            (newMin.length || !newAmount)){ //判断newAmount是否有效
                min = [coin].concat(newMin);
                console.log('new min ' + min + ' for ' + amount);
            }
        }
        return (cache[amount] = min);
    }
}

//使用
var m = new MinCoinChange([1,5,10,25]);
console.log(m.makeChange(36));
```

最少硬币找零的问题若使用贪心算法，则在某些情况下会出错，因为贪心总是尽可能先兑换最大面额，  
所以，当分别给出[1,3,4]和6参数的时候，贪心的结果是[4,1,1],而DP的结果是[3,3]