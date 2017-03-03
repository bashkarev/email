Faster MIME Mail Parser
=======================

Faster MIME Mail Parser could be used to parse emails in MIME format.
 
[![Build Status](https://travis-ci.org/bashkarev/email.svg?branch=master)](https://travis-ci.org/bashkarev/email)

# Usage

Basic usage is the following:

```php
$file = fopen('path/to/file.eml', 'r');
$message = \bashkarev\email\Parser::email($file);

$message->textHtml();

$message->getParts();
$message->getAttachments();
```

## Settings

There are settings available. 

- `charset` - character set to use. Should be specified in uppercase only.
  Default is `UTF-8`.

  ```php
  \bashkarev\email\Parser::$charset = "WINDOWS-1251";
  ```

- `buffer` - read buffer size in bytes. Default is `500000`.

  ```php
  \bashkarev\email\Parser::$buffer = 4096;
  ```

## Attachments

There is attachments parsing support.

### Saving attachments to files

Saving to files could be done as follows:

```php
$file = fopen('path/to/file.eml', 'rb');
$message = \bashkarev\email\Parser::email($file);
foreach ($message->getAttachments() as $attachment) {
    $attachment->save('dir/' . $attachment->getFileName('undefined'));
}
```

### Streaming attachment to output

In order to stream attachment to output directly you need to do the following:

```php
$file = fopen('path/to/file.eml', 'rb');
$message = \bashkarev\email\Parser::email($file);
$attachment = $message->getAttachments()[0];
header("Content-Type: {$attachment->getMimeType()};");
header("Content-Disposition: attachment; filename=\"{$attachment->getFileName('undefined')}\"");
$attachment->getStream()->copy(fopen('php://output', 'c'));
```

## message/partial

```php
$block = \bashkarev\email\Parser::email([
    fopen('path/to/part.1.eml', 'rb'),
    fopen('path/to/part.2.eml', 'rb'),
]);
$block->getMessage();
```

## message/rfc822

```php
$file = fopen('path/to/file.eml', 'rb');
$container = \bashkarev\email\Parser::email($file);
$message = $container->getAttachments()[0]->getMessage();
```

## message/feedback-report
```php
$file = fopen('path/to/file.eml', 'rb');
$container = \bashkarev\email\Parser::email($file);
foreach ($container->getAttachments() as $attachment) {
    if ($attachment->getMimeType() === 'message/feedback-report') {
        /**
         * @var \bashkarev\email\messages\Feedback $feedback
         */
        $feedback = $attachment->getMessage();
        $feedback->getType(); // Feedback::TYPE_ABUSE ...
    }
}

```

## message/external-body

Supported types: url, local-file, ftp.
 
### FTP auth
```php
$file = fopen('path/to/file.eml', 'rb');
$container = \bashkarev\email\Parser::email($file);
foreach ($container->getAttachments() as $attachment) {
    if ($attachment->getStream() instanceof \bashkarev\email\transports\Ftp) {
        /**
         * @var \bashkarev\email\transports\Ftp $transport
         */
        $transport = $attachment->getStream();
        $transport->username = 'username';
        $transport->password = '******';
        $attachment->save('dir/' . $attachment->getFileName('undefined'));
    }
}
```