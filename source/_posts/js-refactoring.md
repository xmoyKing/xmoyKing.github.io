---
title: JS单元重构&单元测试
categories: JavaScript
tags:
  - JavaScript
  - 重构
  - 单元测试
date: 2018-04-20 17:13:17
updated: 2018-04-20 17:13:17
---

展示重构基本的 JavaScript 代码的方法，来自《CSS重构》一书，由于内容和CSS无关，所以单独记录。

示例代码将计算电子商务订单的总价,所需的一些信息：
- 所购商品的单价
- 每种商品的购买数量
- 每种商品的单位运费
- 顾客的地址信息
- 可选择使用的、能降低订单价格的折扣码

```js
/**
 * 打过折、加入运费和税费之后，计算订单总价。
 *
 * @param {Object} customer——顾客信息，关于下订单者的一组信息。
 *
 * @param {Array.<Object>} lineItems——数组，包括所购商品、商品数量及每种商品的单位运费。
 *
 * @param {string} discountCode——可选择使用的折扣码，加入运费和税费之前使用该码。
 */
var getOrderTotal = function (customer, lineItems, discountCode) {
  var discountTotal = 0;
  var lineItemTotal = 0;
  var shippingTotal = 0;
  var taxTotal = 0;

  for (var i = 0; i < lineItems.length; i++) {
      var lineItem = lineItems[i];
      lineItemTotal += lineItem.price * lineItem.quantity;
      shippingTotal += lineItem.shippingPrice * lineItem.quantity;
  }

  if (discountCode === '20PERCENT') {
      discountTotal = lineItemTotal * 0.2;
  }

  if (customer.shiptoState === 'CA') {
    taxTotal = (lineItemTotal - discountTotal) * 0.08;
  }

  var total = (
      lineItemTotal -
      discountTotal +
      shippingTotal +
      taxTotal
  );

  return total;
};
```

为 getOrderTotal 函数编写的单元测试:
```js
var successfulTestCount = 0;
var unsuccessfulTestCount = 0;
var unsuccessfulTestSummaries = [];

/**
  * 断言getOrdertotal()函数计算正确。
  */
var testGetOrderTotal = function () {

    // 设定期望得到的结果

    var expectedTotal = 266;

    // 设定测试数据

    var lineItem1 = {
        price: 50,
        quantity: 1,
        shippingPrice: 10
    };

    var lineItem2 = {
        price: 100,
        quantity: 2,
        shippingPrice: 20
    };

    var lineItems = [lineItem1, lineItem2];

    var customer = {
        shiptoState: 'CA'
    };

    var discountCode = '20PERCENT';

    var total = getOrderTotal(customer, lineItems, discountCode);

    // 比较函数的计算结果与期望得到的结果

    if (total === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetOrderTotal: expected ' + expectedTotal + '; actual ' + total
      );
    }
};

// 运行测试

testGetOrderTotal();
document.writeln(successfulTestCount + ' successful test(s)<br/>');
document.writeln(unsuccessfulTestCount + ' unsuccessful test(s)<br/>');

if (unsuccessfulTestCount) {
    document.writeln('<ul>');
    for(var i = 0; i < unsuccessfulTestSummaries.length; i++) {
        document.writeln('<li>' + unsuccessfulTestSummaries[i] + '</li>');
    }
    document.writeln('</ul>');
}
```

#### 重构 getOrderTotal
仔细分析 getOrderTotal 函数，发现该函数内实现了多种计算：
- 从总价中减去的折扣
- 订单中所有商品的总价
- 总运费
- 总税额
- 订单总价

如果上述五项中任意一项的计算过程引入 bug，单元测试（testGetOrderTotal）将告诉我们出错了，但是不会明确指出 bug 的位置。这正是单元测试应该测试单一功能的主要原因。

为了让代码所实现功能的粒度更细，上面提到的这些计算都应该抽取出来，作为单独的一个函数，并且用能够描述其功能的名称作为函数名。

抽取代码片段，形成新函数
```js
/**
  * 计算所有line items的总价。
  *
  * @param {Array.<Object>} lineItems——数组，包括所购商品、商品数量及每种商品的单位运费。
  *
  * @returns {number}——所有line items的总价。
  */
var getLineItemTotal = function (lineItems) {
    var lineItemTotal = 0;
    　
    for (var i = 0; i < lineItems.length; i++) {
          var lineItem = lineItems[i];
          lineItemTotal += lineItem.price * lineItem.quantity;
    }
    　
    return lineItemTotal;
};
    　
/**
  * 计算所有line items的总运费。
  *
  * @param {Array.<Object>} lineItems——数组，包括所购商品、商品数量及每种商品的单位运费。
  *
  * @returns {number}——所有line items的运费。
  */
var getShippingTotal = function (lineItems) {
    var shippingTotal = 0;
    　
    for (var i = 0; i < lineItems.length; i++) {
        var lineItem = lineItems[i];
        shippingTotal += lineItem.shippingPrice * lineItem.quantity;
    }
    　
    return shippingTotal;
};
    　
/**
  * 计算一个订单的总价按照折扣减去了多少钱。
  *
  * @param {number} lineItemTotal——所有line items的总价。
  *
  * @param {string} discountCode——可选择使用的折扣码，加入运费和税费之前使用该码。
  *
  * @returns {number}——订单总价按照折扣减去了多少钱。
  */
var getDiscountTotal = function (lineItemTotal, discountCode) {
    var discountTotal = 0;
    　
    if (discountCode === '20PERCENT') {
        discountTotal = lineItemTotal * 0.2;
    }
    　
    return discountTotal;
};
    　
/**
  * 计算一个订单应缴纳的总税费。
  *
  * @param {number} lineItemTotal——所有line items的总价。
  *
  * @param {Object} customer——顾客信息，关于下订单者的一组信息。
  *
  * @returns {number}——一个订单应缴纳的总税费。
  */
var getTaxTotal = function () {
    var taxTotal = 0;
    　
    if (customer.shiptoState === 'CA') {
        taxTotal = lineItemTotal * 0.08;
    }
    　
    return taxTotal;
};
```

新抽取出来的函数编写的单元测试
```js
/**
  * 断言getLineItemTotal的计算结果符合预期。
  */
var testGetLineItemTotal = function () {
    var lineItem1 = {
        price: 50,
        quantity: 1
    };
    　
    var lineItem2 = {
        price: 100,
        quantity: 2
    };
    　
    var lineItemTotal = getLineItemTotal([lineItem1, lineItem2]);
    var expectedTotal = 250;
    　
    if (lineItemTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetLineItemTotal: expected ' + expectedTotal + '; actual ' +
          lineItemTotal
      );
    }
};
    　
/**
  * 断言getShippingTotal的计算结果符合预期。
  */
var testGetShippingTotal = function () {
    var lineItem1 = {
        quantity: 1,
        shippingPrice: 10
    };
    　
    var lineItem2 = {
        quantity: 2,
        shippingPrice: 20
    };
    　
    var shippingTotal = getShippingTotal([lineItem1, lineItem2]);
    var expectedTotal = 250;
    　
    if (shippingTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetShippingTotal: expected ' + expectedTotal + '; actual ' +
          shippingTotal
      );
    }
};
    　
/**
  * 确保使用有效的折扣码时，GetDiscountTotal的计算结果符合预期。
  */
var testGetDiscountTotalWithValidDiscountCode = function () {
    var discountTotal = getDiscountTotal(100, '20PERCENT');
    var expectedTotal = 20;
    　
    if (discountTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetDiscountTotalWithValidDiscountCode: expected ' + expectedTotal +
          '; actual ' + discountTotal
      );
    }
};
    　
/**
  * 确保使用无效的折扣码时，GetDiscountTotal的计算结果符合预期。
  */
    　
var testGetDiscountTotalWithInvalidDiscountCode = function () {
    var discountTotal = get_discount_total(100, '90PERCENT');
    var expectedTotal = 0;
    　
    if (discountTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetDiscountTotalWithInvalidDiscountCode: expected ' + expectedTotal +
          '; actual ' + discountTotal
      );
    }
};
    　
/**
  * 确保顾客住在加利福尼亚时，GetTaxTotal的计算结果符合预期。
  */
    　
var testGetTaxTotalForCaliforniaResident = function () {
    var customer = {
      shiptoState: 'CA'
    };
    　
    var taxTotal = getTaxTotal(100, customer);
    var expectedTotal = 8;
    　
    if (taxTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetTaxTotalForCaliforniaResident: expected ' + expectedTotal +
          '; actual ' + taxTotal
      );
    }
};
    　
/**
  *确保顾客不住在加利福尼亚时，GetTaxTotal的计算结果符合预期。
  */
var testGetTaxTotalForNonCaliforniaResident = function () {
    var customer = {
        shiptoState: 'MA'
    };
    　
    var taxTotal = getTaxTotal(100, customer);
    var expectedTotal = 0;
    　
    if (taxTotal === expectedTotal) {
      successfulTestCount++;
    } else {
      unsuccessfulTestCount++;
      unsuccessfulTestSummaries.push(
          'testGetTaxTotalForNonCaliforniaResident: expected ' + expectedTotal +
          '; actual ' + taxTotal
      );
    }
};
```

用新抽取出来的函数改写 getOrderTotal 函数
```js
/**
  *打过折、加入运费和税费之后，计算订单总价。
  *
  * @param {Object} customer——顾客信息，关于下订单者的一组信息。
  *
  * @param {Array.<Object>} lineItems——数组，包括所购商品、商品数量及每种商品的单位运费。
  *
  * @param {string} discountCode——可选择使用的折扣码，加入运费和税费之前使用该码。
  */
var getOrderTotal = function (customer, lineItems, discountCode) {
    var lineItemTotal = getLineItemTotal(lineItems);
    var shippingTotal = getShippingTotal(lineItems);
    var discountTotal = getDiscountTotal(lineItemTotal, discountCode);
    var taxTotal = getTaxTotal(lineTtemTotal, customer);
    　
    return lineItemTotal - discountTotal + shippingTotal + taxTotal;
};
```

通过如上的重构和测试，结果是：
- 函数比以前更多了；
- 单元测试比以前更多了；
- 每个函数实现一个特定功能；
- 每个函数都有一个单元测试；
- 多个函数组合起来可以实现更复杂的计算。

总体来讲，重构之后代码结构更加合理。getOrderTotal 函数内部计算各种价格的代码被抽取出来，作为一个个单独的函数，而且每个函数都有相应的单元测试。这意味着当代码中引入 bug 时，更容易定位受影响的功能。此外，如果总税额或运费需要更换计算方式，而现有功能已经提供了可用的单元测试，那么更换之后，可方便地用单元测试加以验证。