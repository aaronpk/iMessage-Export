iMessage Archive
================

Archives all your iMessage history from the `chat.db` file.

Currently group conversations are not supported.


Address Book
------------

First run `contacts.php` which will write out a file of all the contacts listed in the chat log.
Fill in this file with peoples' names so that the chat logs look a little better.

```
+15035551212 Cool Dude
cooldude@gmail.com Cool Dude
```

Make sure your own iMessage ID is the first thing in the list, since that will be used
as the identity of any messages you send.


Folder Structure
----------------

```
messages/

# Individual chats
messages/Cool Dude/
messages/Cool Dude/2014-04.html
messages/Cool Dude/2014-05.html
messages/Cool Dude/2014-05/photo.jpg
```

Log File
--------

Messages are stored as a minimal HTML page. Structured data is available by parsing out
the [microformats markup](http://microformats.org/wiki/microformats2).

Each message is an [h-entry](http://microformats.org/wiki/h-entry) containing the author, timestamp and text of the message.

```html
<div class="h-entry"><time class="dt-published" datetime="2014-05-01T10:48:00+00:00">2014-05-01 10:48:00</time> <span class="p-author h-card"><a href="sms:+15035551212">Cool Dude</a></span> <span class="e-content p-name">Message text here</span></div>
<div class="h-entry"><time class="dt-published" datetime="2014-05-01T10:49:00+00:00">2014-05-01 10:49:00</time> <span class="p-author h-card"><a href="mailto:aaron@parecki.com">Aaron Parecki</a></span> <span class="e-content p-name">Message text here</span></div>
```

Photos in the message thread are also included in the export and are stored in a subfolder with the same name as the file. They are embedded in the HTML with an img tag so they will be rendered by browsers.

