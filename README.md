[![Build Status](https://travis-ci.org/bashkarev/email.svg?branch=master)](https://travis-ci.org/bashkarev/email)

Faster Mime Mail Parser
=======================


# Usage


## Settings

`charset` uppercase only. Default UTF-8
```php
\bashkarev\email\Parser::$charset = "WINDOWS-1251";
```

`buffer` read buffer size in bytes. Default 500000 
```php
\bashkarev\email\Parser::$buffer = 4096;
```

## Message
```php
$file = fopen('path/to/file.eml', 'r');
$message = \bashkarev\email\Parser::email($file);

$message->textHtml();

$message->getParts();
$message->getAttachments();


```

## Attachment

### Save
```php
$file = fopen('path/to/file.eml', 'r');
$message = \bashkarev\email\Parser::email($file);
foreach ($message->getAttachments() as $attachment) {
    $attachment->save('dir/' . $attachment->getFileName('undefined'));
}
```

### Stream
```php
$file = fopen('path/to/file.eml', 'r');
$message = \bashkarev\email\Parser::email($file);
$attachment = $message->getAttachments()[0];
header("Content-Type: {$attachment->getMimeType()};");
header("Content-Disposition: attachment; filename=\"{$attachment->getFileName('undefined')}\"");
$attachment->getStream()->onFilter(fopen('php://output', 'c'));
```
