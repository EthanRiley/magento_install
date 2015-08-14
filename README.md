# magento_install

A command line installer for magento
currently in testing as is very new.

currently is split into three parts which will be eventually be put back together by a bash script

1. **Download** => uses magento's downloader file's functions to get latest version. -- **works**.

2. **Install** => uses magento's downloader framework to download and install magento. -- **works** but returns error
:php could not find dependencies for libVarien beacuse of usage on console, has no effect on installation.

3. **Configure** => uses magento's install framework to configure magento. -- **debugging** returns error:
*'could not connect to database'* seems like needs default socket file defined...

OS's tested On:

- [ ]  Linux

- [ ]  Windows

- [x]  Mac

