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
```

Log File
--------

```
2014-05-26 10:49:00 \t "Cool Dude" <+15035551212> \t Message text here
2014-05-26 10:49:30 \t "Aaron Parecki" <aaron@parecki.com> \t Message text here
```


