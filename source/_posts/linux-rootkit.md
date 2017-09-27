---
title: Linux Rootkit
categories:
  - linux
tags:
  - linux
  - security
  - rootkit
date: 2017-06-25 23:48:55
updated: 2017-06-25 23:48:55
---

### Definition of a Rootkit
- “Trojan Horse” into a Computer System
- Malicious Programs that pretend to be normal programs
- May also be programs:
  1. that masquerade as “possible” programs
  2. with names that approximate existing program
  3. already running and not easily identifiable by user
- Installing a Rootkit on a Target System
  1. Hacker MUST already have root level access on target system
  2. Gain root level access by compromising system via buffer overflow, password attack, social engineering
  3. Rootkit allows hacker to get back onto system with root level privilege
- Rootkits are a comparatively recent phenomenon
- Developed by hackers to conceal their activities
- One method is to replace existing binary system files that continue to function as normal but allow hacker back door access
- Can be developed by skilled hacker with programming expertise

