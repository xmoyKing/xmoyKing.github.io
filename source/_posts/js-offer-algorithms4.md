---
title: 剑指Offer笔记-JS语法 - 4 优化时间和空间效率
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-27 09:19:41
updated: 2018-03-27 09:19:41
---

### 问题29 数组中出现次数超过一半的数字
**问题描述：数组中有一个数字出现的次数超过数组长度的一半，请找出这个数字。例如输入一个长度为9的数组{1,2,3,2,2,2,5,4,2}。由于数字2在数组中出现了5次，超过数组长度的一半，因此输出2。如果不存在则输出0。**

解法一：基于快排Partition函数的O(n)算法
当一个数组排好序后，数组中间的数一定就是出现次数超过一半的数，因此任意选中一个数k，利用快排的一次划分，将小于k的数都移动到左边，大于k的数移动到右边，若最后k的下标为n/2，那么就是中位数，否则再从左边或右边划分选取中位数。

注意，最后一定要检查所找到的数是否数量大于数组的一半。

```js
function MoreThanHalfNum_Solution(numbers)
{
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return 0;
    }else if(n == 1){ // 仅有一个值
        return numbers[0];
    }



    let middle = n >> 1, // 取中间值
        start = 0, // 划分起点
        end = n - 1, // 划分终点
        idx = Partition(numbers, n, start, end);

    while(idx != middle){
        if(idx > middle){
            end = idx - 1;
            idx = Partition(numbers, n, start, end);
        }else{
            start = idx + 1;
            idx = Partition(numbers, n, start, end);
        }
    }

    let rst = numbers[middle];
    if(!isMoreThanHalf(numbers, n, rst)){
        return 0;
    }

    return rst;
}

// 快排划分
function Partition(numbers, n, start, end){
    if(!numbers || n <= 0 || start < 0 || end >= n){
        return false;
    }

    let idx = end - 1; // 暂时以最后一个的前一个为key， RandomInRange(start, end);
    Swap(numbers, idx, end);

    let small = start - 1;
    for(idx = start; idx < end; ++idx){
        if(numbers[idx] < numbers[end]){
            ++small;
            if(small != idx){
                Swap(numbers, idx, small);
            }
        }
    }

    ++small;
    Swap(numbers, small, end);

    return small;

    function Swap(arr, n1, n2) {
        let temp = arr[n1];
        arr[n1] = arr[n2];
        arr[n2] = temp;
    }
}

// 检查结果值数量是否确实有超过一半
function isMoreThanHalf(numbers, n, rst){
    let times = 0;
    for(let i = 0; i < n; ++i){
        if(numbers[i] == rst){
            times++;
        }
    }

    let check = true;
    if(times * 2 <= n){
        check = false;
    }
    return check;
}
```

解法二：根据数组特点找出的O(n)算法
数组中有一个数出现的次数超过数组长度一半，那么也就是说它的次数比其他数字次数加起来的总和还多，

所以，可以在遍历的时候保存一个键值对。键是数字，值为该数字出现的次数，若当前数字与key相等，则key数量++，否则key数量减一，若key数量减为0时，则将当前数设置为key并将数量设置为1。直到遍历结束，那么最后一个次数不为0的一定就是需要找的数。

```js
function MoreThanHalfNum_Solution(numbers)
{
    let n = numbers.length;
    // 检查输入
    if(!numbers || !n){
        return null;
    }else if(n == 1){ // 仅有一个值
        return numbers[0];
    }

    let key = numbers[0];
    let times = 1;

    for(let i = 0; i < n; ++i){
        if(times == 0){
            key = numbers[i];
            times = 1;
        }else if(numbers[i] == key){
            times++;
        }else{
            times--;
        }
    }

    if(!isMoreThanHalf(numbers, n, key)){
        return 0;
    }

    return key;
}
```

### 问题30 最小的K个数
**问题描述：输入n个整数，找出其中最小的K个数。例如输入4,5,1,6,2,7,3,8这8个数字，则最小的4个数字是1,2,3,4,。**

解法一：类似“问题29 数组中出现次数超过一半的数字”的解法，但这次不是找中位数，而是找最后索引为k的数，其前面的数就是结果，这种方式会改变数组，同时无法直接获取有序的结果。

```js
function GetLeastNumbers_Solution(numbers, k)
{
    // 结果需要排序
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return [];
    }else if(k > n || k <= 0){ // 若k越界，则输出空数组
        return []
    }

    let start = 0, // 划分起点
        end = n - 1, // 划分终点
        idx = Partition(numbers, n, start, end);
        k--; // 由于k是数量，而idx是下标，所以需要将k减一才能对应上，防止k为1，而其实idx是0的情况

    while(idx != k){
        if(idx > k){
            end = idx - 1;
            idx = Partition(numbers, n, start, end);
        }else{
            start = idx + 1;
            idx = Partition(numbers, n, start, end);
        }
    }

    let rst = numbers.slice(0, k+1);

    return rst.sort();
}

// 快排划分
function Partition(numbers, n, start, end){
    if(!numbers || n <= 0 || start < 0 || end >= n){
        return false;
    }

    let idx = end - 1; // 暂时以最后一个的前一个为key， RandomInRange(start, end);
    Swap(numbers, idx, end);

    let small = start - 1;
    for(idx = start; idx < end; ++idx){
        if(numbers[idx] < numbers[end]){
            ++small;
            if(small != idx){
                Swap(numbers, idx, small);
            }
        }
    }

    ++small;
    Swap(numbers, small, end);

    return small;

    function Swap(arr, n1, n2) {
        let temp = arr[n1];
        arr[n1] = arr[n2];
        arr[n2] = temp;
    }
}
```

解法二：O(nlogk)复杂度，适合处理海量数据
先建立一个k大小的数据容器存储最小的k个数字，然后每次从输入的n个整数中读入一个数，若容器已有的数字少于k个，则直接把这次读入的证书放入容器中，若容器已有k个，则将容器内最大值与小于这个最大值的当前值交换。

当容器满了的情况，需要做三件事：
1. 在容器中找到最大值
1. 可能删除这个最大值
1. 可能插入一个新的数字

由于每次需要在k个整数中查找、删除、插入，若采用二叉树来作为容器，则能在O(logk)内实现这三步。

而查最大数字，所以很容易想到用大根堆，能在O(1)时间内取出最大值，但插入删除为O(logk)；用红黑树也可以实现这个容器，其保证树是平衡的，所以查找、删除、插入都是O(logk)。

其实很多库提供的排序算法或数据结构就是基于红黑树的。因为其平均性能较好。

下面是解法：
```js
function GetLeastNumbers_Solution(numbers, k)
{
    // 结果需要排序
    let n = numbers.length;
    // 检查输入，数组不存在或为空
    if(!numbers || !n){
        return [];
    }else if(k > n || k <= 0){ // 若k越界，则输出空数组
        return []
    }

    let rst = []; // 简单的采用数组作为容器

    // 遍历找到k个最小值
    for(let i = 0; i < n; i++){
        if(rst.length < k){
            rst.push(numbers[i]);
        }else{
            let max = Math.max.apply(null, rst);
            if( max > numbers[i]){ // 若当前数小于rst数组内最大值，则替换
                rst[rst.indexOf(max)] = numbers[i];
            }
        }
    }

    return rst.sort();
}
```
```js
// 简单测试：需要检查边界值
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 0));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 1));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 4));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 8));
console.log(GetLeastNumbers_Solution([4,5,1,6,2,7,3,8], 10));
```

### 问题31 连续子数组的最大和
**问题描述：输入一个整型数组，数组里有正数也有负数，数组中一个或连续多个整数组成一个子数组，求所有子数组的和的最大值，要求时间复杂度为O(n)**

例如{6,-3,-2,7,-15,1,2,2},连续子数组的最大和为8(从第0个开始,到第3个为止)

暴力解法为：枚举所有数组的子数组并求和，长度为n的数组，公有 n*(n+1)/2 个子数组，计算所有子数组的和，最快也要$O(n^2)$

解法一：根据规律，从头到尾试着逐个累加数组中的每个数字，同时需要保存当前的最大和子数组。
```js
function FindGreatestSumOfSubArray(array)
{
    let n = array.length;
    // 检查输入
    if(!array || !n){
        return [];
    }

    let crtSum = 0,
        greatSum = Number.NEGATIVE_INFINITY; // 初始化为负无穷大

    for(let i = 0; i < n; i++){
        // 若当前总和小于0，则丢弃前面的子数组，直接从当前数开始从新计算
        if(crtSum <= 0){
            crtSum = array[i];
        }else{
            crtSum += array[i];
        }

        // 每轮循环介绍，更新最大值
        if(crtSum > greatSum){
            greatSum = crtSum;
        }
    }

    return greatSum;
}

console.log(FindGreatestSumOfSubArray([-2,-8,-1,-5,-9]));
```

解法二：用动态规划，f(i)表示第i个数字结尾的子数组的最大和，那么需要求出`max[f(i)]`,其中`0<=i<n`,递归公式如下：

f(i):
- array[i]： 若i=0或`f(i-1)<=0`
- f(i-1)+array[i]： i不为0且`f(i-1)>0`

当以第i-1个数字结尾的子数组中所有数字的和小于0时，若把这个负数与array[i]（第i个数）累加，得到的结果一定比array[i]小，所以这种情况，第i个数字结尾的子数组就是array[i]本身，若以第i-1个数字结尾的子数组中所有数字的和大于0，与第i个数字累加就得到以第i个数字结尾的子数组中所有数字的和。

其实，动态规划通常是用递归的思想，但实现却是用循环，所以代码其实和上面的代码没区别，f(i)对应的变量就是crtSum, 而max[f(i)]就是greatSum。

### 问题32 整数中1出现的次数（从1到n整数中1出现的次数）
**问题描述：输入一个整数n，求1到n这n个整数的十进制表示中1出现的次数，例如输入12，从1到12这些证书中包含1的数字有1，10，11和12，1一共出现了5次**


不考虑时间效率的算法即通过循环每次对某个数转换为字符串，然后求1的次数，循环的次数即n次。

而若能找到数字1出现的规律，那么就能明显提高时间效率。*PS：本解法暂时没想通，先给出解答：*

以n为21345为例，将1到21345分为两部分，第一部分为1到1345，第二部分为1346到21345。为何要分为这两部分，是因为除去最高位后为1345（第一部分1到1345），可直接递归求得。

第二部分1346到21345中1出现的次数需要分两种情况：
1. 1出现在最高位（本次即为万位），从10000 ~ 19999，一共1万次（10^4）
1. 1出现在其他位，从1346到21345共2万个数字，后4位中1出现的次数的2000次，由于最高位是2，将1346到21345再次分为2部分，1346到11345，和11346到21345两部分，每部分剩下的四位数字中，选择一位为1，其余三位在0~9这10个数字中任意选择，按照排列组合原则，总共出现`2*10^3 = 2000`次。

### 问题33 把数组排成最小的数
**问题描述：输入一个正整数数组，把数组里所有数字拼接起来排成一个数，打印能拼接出的所有数字中最小的一个。例如输入数组{3，32，321}，则打印出这三个数字能排成的最小数字为321323。**

暴力解法为将数组元素的所有排列求出，然后依次对比求最小值，n个数的全排列为n！，

更好的解法为找到一个排序规则，数组根据规则排序后能形成一个最小的数字，而要排序就需要对比规则，来确定m和n哪个在前，规则 如下：
1. 若mn < nm, 则m在前，
1. 若nm < mn, 则n在前，
1. 否则相等

其中需要注意，若将mn或nm转化为数字进行对比可能会溢出，所以将其转化为字符串对比更好,因为按照字符串对比中，`'123' < '321' == true`。

```js
function PrintMinNumber(numbers)
{
    // 检查输入
    if(!numbers || !numbers.length){
        return '';
    }

    numbers.sort(function(m, n) {
        return (''+m+n) - (''+n+m);
    })

    return numbers.join('');
}
```

### 问题34 丑数
**问题描述：把只包含因子2、3和5的数称作丑数（Ugly Number）。例如6、8都是丑数，但14不是，因为它包含因子7。 习惯上我们把1当做是第一个丑数。求按从小到大的顺序的第N个丑数。**

暴力求解：逐个判断该整数是否是丑数，简单但不够高效，主要问题是每一个数都需要去判断。

空间换时间解法：创建数组保存已经找到的丑数，然后依次对某个丑数乘以2/3/5即可获得后续的丑数，也就是说，这个数组是排好序的，每一个丑数都是前面的丑数乘以2/3/5得到的。

关键在于如何去报数组内的丑数是拍好序的，假设数组已经有若干丑数排好序，且当前最大丑数M在末尾，那么接下来如何生成新的一个丑数。

由于新的丑数X一定是前面某个丑数乘以2/3/5得到的，所以考虑直接把已有的丑数依次乘以2，如此获得的一定有很多数是小于或等于M的，而这些数都已经放在数组内了，同时还有一些大于M的数，但由于只需要一个结果，且要拍好序，所以需要一个最接近M的丑数，记为X2。

然后后面还有X3、X5分别表示乘以3和5得到的最近M的某个丑数，然后取X2、X3、X5中较小的丑数加入数组即可。

而其中有一个需要注意的，其实并不需要从头开始依次乘以2/3/5，因为前面的数组中一定有一个丑数（即获得X2的那个丑数），其前面的丑数乘以2后的结果小于M，而其后面的丑数乘以2后的结果大于M，这样记住这个分隔处的丑数索引idx，每轮更新即可，这样每次乘以2/3/5时就从这个数开始。

```js
function GetUglyNumber_Solution(index)
{
    // 检查输入
    if(!index || index <= 0){
        return 0;
    }

    let array = [1]; // 初始化第一个丑数为1
    let idx = 1; // 当前丑数数量，同时始终指向下一个丑数在数组中的索引
    let X2 = X3 = X5 = 0; //

    while(idx < index){
        let min = Math.min(array[X2]*2, array[X3]*3, array[X5]*5);
        array.push(min);

        while(array[X2]*2 <= array[idx]){
            ++X2;
        }
        while(array[X3]*3 <= array[idx]){
            ++X3;
        }
        while(array[X5]*5 <= array[idx]){
            ++X5;
        }

        idx++;
    }
    //console.log(array);
    return array[index - 1];
}

// console.log(GetUglyNumber_Solution(1500));
```

### 问题35 第一个只出现一次的字符
**问题描述：在一个字符串(1<=字符串长度<=10000，全部由字母组成)中找到第一个只出现一次的字符,并返回它的位置**
比如`'abaccdeff'`，结果为`'b'`，输出位置为1。

使用一个hash表，每一个key为出现过的字符，value为出现的次数，然后依次遍历将所有次数都记录下，然后第二次遍历找到第一个次数为1的字符即可。

```js
function FirstNotRepeatingChar(str)
{
    // 插件输入
    if(!str){
        return -1;
    }

    let hash = {};
    for(let i = 0, n = str.length; i < n; i++){
        if(hash[str[i]]){
            hash[str[i]]++;
        }else{
            hash[str[i]] = 1;
        }
    }


    for(let i = 0, n = str.length; i < n; i++){
        if(hash[str[i]] == 1){
            // console.log(str[i]);
            return i;
        }
    }
}

console.log(FirstNotRepeatingChar(''));
console.log(FirstNotRepeatingChar('abaccdeff'));
```

相关题目：
- 定义一个函数，输入两个字符串，从第一个字符串中删除在第二个字符串中出现过的所有字符。解法为创建第二个字符串中字符的hash表，然后依次遍历第一个字符串，删除在hash表中存在有的字符即可。
- 定义一个函数，删除字符串中所有重复出现的字符，例如输入"google"，结果为"gole"。


### 问题36 数组中的逆序对
**问题描述：在数组中的两个数字，如果前面一个数字大于后面的数字，则这两个数字组成一个逆序对。输入一个数组,求出这个数组中的逆序对的总数P。并将P对1000000007取模的结果输出。 即输出P%1000000007**

输入描述: 题目保证输入的数组中没有的相同的数字

数据范围：
- 对于%50的数据,size<=10^4
- 对于%75的数据,size<=10^5
- 对于%100的数据,size<=2*10^5

示例1
输入
> 1,2,3,4,5,6,7,0
输出
> 7

*PS：此问题暂时没有研究清楚*

毫无疑问不能暴力，遍历数组时，不要用它与后面每一个数比较，否则时间复杂度为$O(n^2)$，考虑先比较相邻的两个数字。

以数组{7,5,6,4}为例分析统计逆序对。
1. 将长度为4的数组分为2个长度为2的子数组
1. 把长度为2的数组分解为2个长度为1的子数组
1. 把长度为1的子数组合并、排序、统计逆序对
1. 把长度为2的子数组合并、排序、统计逆序对

之所以要合并排序是为了防止后面的统计过程重复计算，而其中的关键为合并、排序、统计逆序对的方式。

用两个指针分别指向两个子数组的末尾，并每次比较两个指针指向的数：
- 若第一个子数组中的数字大于第二个子数组中的数字，则构成逆序对，并且数目为第二个子数组中剩余数字的个数。
- 若第一个数组中的数字小于或等于第二个数组中的数字，则不构成逆序对，每一次比较的时候，我们都将较大的数字从后往前复制到一个辅助数组，确保辅助数组是有序的。在把较大数字复制到辅助数组后，把对应指针前移，然后进行下一轮比较，指导出现第一个逆序对数为0的数，这样其前面的所有数都不可能构成逆序对。

总结下来统计逆序对的过程为：现将数组分为子数组，统计子数组内部的逆序对数，同时排序，然后再统计相邻子数组之间的逆序对数，然后继续排序。其实统计的过程就是归并排序的过程。

如此，基于归并排序的解法如下：
```js
//数组中的逆序对
function InversePairs(array){
    if( !array || array.length<2 )
        return 0
    var copy = array.slice(),
        count = 0
    count = mergeSort(array, copy, 0, array.length-1)
    return count%1000000007
}

function mergeSort(array, copy, start, end){
    if ( start === end ) {
        return 0
    }
    var mid = (start+end)>>1,
        // 把copy和array的顺序颠倒是防止重复计算
        leftCount = mergeSort(copy, array, start, mid),
        rightCount = mergeSort(copy, array, mid+1, end),
        i = mid, //i初始化为前半段最后一个数字的下标
        j = end, //j初始化为后半段最后一个数字的下标
        index = end, //辅助数组复制的数组的最后一个数字的下标
        count = 0 //计数--逆序对的数目

    while( i>=start && j>=mid+1 ){
        if(array[i] > array[j]){
            copy[index--] = array[i--]
            count += j-mid // 比归并排序多的一步，计算逆序对
        }else{
            copy[index--] = array[j--]
        }
    }
    while(i >= start){
        copy[index--] = array[i--]
    }
    while(j >= mid+1){
        copy[index--] = array[j--]
    }
    return leftCount + rightCount + count
}
```

### 问题37 两个链表的第一个公共结点
**问题描述：输入两个链表，找出它们的第一个公共结点。**

由于有公共结点的两个链表都是单链表，那么从某个结点开始，它们的next都指向同一个结点，因此，不可能出现分叉。

同时又因为最后链表尾的结点一定是公共结点，从后往前遍历的话，就一定能找到开始的公共结点。

这样的话，依次将两个链表所有遍历的结点分别入栈，最后依次弹出栈顶结点，然后对比，若不同，则说明其下一个结点就是公共结点。

还有一种方法，就是得到两个链表的长度，然后求得长度差x，将比较长的一个先走x步，然后依次在对比两个链表剩下的结点即可，若有相等的则直接返回该结点即可，若没有相等则最后会返回链表尾的null。

```js
function FindFirstCommonNode(pHead1, pHead2)
{
    // 检查输入
    if(!pHead1 || !pHead2){
        return null;
    }

    // 两个栈中会在最后push一个null
    let stack1 = [],
        stack2 = [],
        pointer1 = pHead1,
        pointer2 = pHead2;

    while(pointer1){
        stack1.push(pointer1);
        pointer1 = pointer1.next;
    }
    while(pointer2){
        stack2.push(pointer2);
        pointer2 = pointer2.next;
    }

    // 从此处开始使用对比多走x步的方法
    let len1 = stack1.length,
        len2 = stack2.length;

    let x = Math.abs(len1 - len2);
    let i = x;

    if(len1 > len2){
        while(stack1[i] !== stack2[i - x]){
            i++
        }
        return stack1[i];
    }else{
        while(stack1[i - x] !== stack2[i]){
            i++
        }
        return stack2[i];
    }
}
```


<script src="//cdn.bootcss.com/mathjax/2.7.0/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>