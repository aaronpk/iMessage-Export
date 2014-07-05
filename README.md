iMessage Archive
================

Archives all your iMessage history from the `chat.db` file.

Currently group conversations are not supported.


Contacts
--------

If you don't already have a contacts.txt file, you'll need to create one. This way the messages will be stored in folders by peoples' names rather than by their phone numbers.

The very first thing in the `contacts.txt` must be your own iMessage ID and name. This is used to set the name for your own sent messages in the logs.

First run `php contacts.php` which will write out a file of all the contacts listed in the chat log.
Fill in this file with peoples' names so that the chat logs look a little better.

```
+15035551212 Cool Dude
cooldude@gmail.com Cool Dude
```

You can have multiple entries per person, and they will be combined into a single log folder with that person's name.


Folder Structure
----------------

Messages are saved in separate files per month under a folder of each person's name. If you don't have an entry for them in your `contacts.txt` file, the folder name will be their iMessage ID (phone number or email address).

Photos that were sent in messages will also be archived in the folder.

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
<div class="h-entry">
  <time class="dt-published" datetime="2014-05-01T10:48:00+00:00">2014-05-01 10:48:00</time> 
  <a href="sms:+15035551212" class="p-author h-card">Cool Dude</a>
  <span class="e-content p-name">Message text here</span>
</div>
<div class="h-entry">
  <time class="dt-published" datetime="2014-05-01T10:49:00+00:00">2014-05-01 10:49:00</time> 
  <a href="mailto:aaron@parecki.com" class="p-author h-card">Aaron Parecki</a> 
  <span class="e-content p-name">Message text here</span>
</div>
```

Photos in the message thread are also included in the export and are stored in a subfolder with the same name as the file. They are embedded in the HTML with an img tag so they will be rendered by browsers.

