# async-cache-warmer
Asynchronous cache warmer for Magento 2

# Basic Usage:
run command:
```bash
$ php warm.php "http://magento.dev/sitemap.xml"
```
If you want to limit count of simultaneous requests, you can use second optional parameter:
```bash
$ php warm.php "http://magento.dev/sitemap.xml" 10 \\ requests will be split into chunks with 10 elements each
```
