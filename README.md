# Broken Link Detector
Detects and fixes (if possible) broken links in post_content. 

## Enable log mode 
Define the constant BROKEN_LINKS_LOG to true to enable extended logging. This will write curl messages and errors to the default logfile. 

## Bypass for domains
You can bypass checks (automatically consider valid) by adding domains to the 'brokenLinks/External/ExceptedDomains' filter. The filter requires that you provide your domains in the same format as parse_url($url, PHP_URL_HOST) returns. It's therefore recommended that you filter all your domains trough this function. 

```php
add_filter('brokenLinks/External/ExceptedDomains',function($array) {
    return array(
        parse_url("http://agresso/agresso/", PHP_URL_HOST),
        parse_url("http://qlikviewserver/qlikview/index.htm", PHP_URL_HOST),
        parse_url("http://serviceportalen/", PHP_URL_HOST),
        parse_url("http://a002163:81/login/login.asp", PHP_URL_HOST),
        parse_url("http://serviceportalen/Default.aspx", PHP_URL_HOST),
        parse_url("http://cmg/BluStarWeb/Start", PHP_URL_HOST),
        parse_url("http://surveyreport/admin", PHP_URL_HOST),
        parse_url("http://klarspraket/", PHP_URL_HOST),
        parse_url("http://guideochtips/", PHP_URL_HOST),
        parse_url("http://hbgquiz/index.php/category/?id=3", PHP_URL_HOST),
        parse_url("http://agresso/agresso/", PHP_URL_HOST),
        parse_url("http://a002490/efact/", PHP_URL_HOST),
        parse_url("http://a002064/Kurser/", PHP_URL_HOST),
        parse_url("http://a002064/kursbokning/", PHP_URL_HOST)
    ); 
}, 10);
```