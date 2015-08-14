#!/bin/bash

: << 'END'
a utility for magento: command line installe
uses magento functions to create a clean magento install which is vaild
The MIT License (MIT)

Copyright (c) 2015 Ethan Riley(https://gitlab.com/u/_UNKNOWN_) and verve(www.verveuk.eu)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

END


cp -v -R  ../Install/. $1

php ./Install.php

chmod -R a+wrx ./

cd ..

cp -R ./Downloader/Mage/Install/. ./app/code/core/Mage/Install/

chmod -R a+wrx ./

rm -r ./Downloader/Mage/

chmod -R a+wrx ./

cd ./app/code/core/Mage/Install/

php configure.php $1

cd $1

chmod -R a+wrx ./
