---
title: smashing-stack
categories:
  - report
tags:
  - information security
  - C
  - assembly
date: 2017-06-23 09:25:05
updated: 2017-06-23 09:25:05
---

使用缓冲区溢出攻击获取Root Shell的原理

参考译文：[smashing the stack for fun and profit 译文](http://www.itwendao.com/article/detail/397706.html)
PDF原件:[smashing-stack.pdf](./smashing-stack.pdf)

什么是`routine`【参考[百度百科：例程](http://baike.baidu.com/link?url=PsdMY81WJEytzMnjV_JU_rvPGwdp3klIwsEE917Fq6JZtjBlUTuhEfN9-D8QXVmG9ZkNF6dMWsfSGQ_NcM8uhGAKiDYNsD_GhHO6Fr9CU1a)】，简单理解就是对外提供服务的API，实际就是一个个可复用的函数。



