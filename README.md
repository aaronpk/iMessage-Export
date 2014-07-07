iMessage Archive
================

Archives all your iMessage history from the `chat.db` file.

Currently group conversations are not supported.


Contacts
--------

If you don't already have a contacts.txt file, you'll need to create one. This way the messages will be stored in folders by peoples' names rather than by their phone numbers.

The very first thing in the `contacts.txt` must be your own iMessage ID and name. This is used to set the name for your own sent messages in the logs.

First run `php contacts.php` which will output all the contacts listed in the chat log.
Fill in this file with peoples' names so that the chat logs look a little better. 
The first time you run it, you can run the script and output the txt file in one command:

```bash
php contacts.php >> contacts.txt
```

```
+15031234567 Your Name
+15035551212 Cool Dude
cooldude@gmail.com Cool Dude
```

You can have multiple entries per person, and they will be combined into a single log folder with that person's name.

Running `php contacts.php` subsequently will output only new contacts that were not yet in the file.


Export Formats
--------------

Running `php archive.php` will export to HTML files sorted by contact.

Running `php export-csv.php` will export to a single CSV file. See below for the structure of the file.


HTML Folder Structure
---------------------

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

HTML Log Files
--------------

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


CSV Log File
------------

Only one file is created when exporting as csv. The csv file will have the following columns:

```
Timestamp, Date, Time, From, From Name, To, To Name, Message, Emoji, Attachments
```

* `Timestamp`: The unix timestamp of the message (seconds since 1970-01-01)
* `Date`: The date will be in the format YYYY-mm-dd
* `Time`: The time will be HH:mm:ss
* `From`, `To`: The iMessage ID of the sender and recipient
* `From Name`, `To Name`: The name of the person as defined in your `contacts.txt` file (see above)
* `Message`: This is the actual text of the message
* `Emoji`: The number of emoji characters in the message
* `Attachments`: The number of attachments (usually photos) sent in the message

The messages are usually in chronological order, but because of delays in when your computer actually receives the messages, they might be slightly out of order.


SQL Database
------------

If you want to quickly query your data it may be faster to load the messages into a SQL database so you can write SQL queries.

First create a table with the following SQL:

```sql
CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `from` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4,
  `num_emoji` int(11) DEFAULT NULL,
  `num_attachments` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `from,to` (`from`,`to`),
  KEY `timestamp` (`timestamp`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

To import into the database, first make sure you define the database name in `export-sql.php`, then run:

```
php export-sql.php
```

Here are some example queries you can run!


### Who sends me the most messages in the past year?

```sql
SELECT from_name, COUNT(1) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY from_name
ORDER BY COUNT(1) DESC
```

(The top result will be you, so just ignore that)

### Who do I send the most messages to?

```sql
SELECT to_name, COUNT(1) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY to_name
ORDER BY COUNT(1) DESC
```

### Most contacted people in the past year

```sql
SELECT IF(from_name="Aaron Parecki", to_name, from_name) AS name, COUNT(1) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY IF(from_name="Aaron Parecki", to_name, from_name)
ORDER BY COUNT(1) DESC;
```

Obviously you should replace my name with yours. This will count both sent and received messages.


### Number of messages sent and received per day

```sql
SELECT date, COUNT(1) AS num
FROM messages
GROUP BY date
```

### Days with the most messages sent in the past year

```sql
SELECT date, COUNT(1) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY date
ORDER BY COUNT(1) DESC
```

### Number of messages per month

```sql
SELECT date, COUNT(1) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY YEAR(date), MONTH(date)
ORDER BY date DESC
```

### Number of emoji used per month

```sql
SELECT date, SUM(num_emoji) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
GROUP BY YEAR(date), MONTH(date)
ORDER BY date DESC
```

### Who sent me the most emoji in the past year?

```sql
SELECT from_name, SUM(num_emoji) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
  AND num_emoji > 0
GROUP BY from_name
ORDER BY SUM(num_emoji) DESC
```

### Who did I send the most emoji to in the past year?

```sql
SELECT to_name, SUM(num_emoji) AS num
FROM messages
WHERE date > "2013-07-07"
  AND date < "2014-07-07"
  AND num_emoji > 0
GROUP BY to_name
ORDER BY SUM(num_emoji) DESC
```

### Do you send or receive more emoji?

```sql
SELECT * FROM
(SELECT "received" AS type, SUM(num_emoji) AS num
FROM messages
WHERE to_name = "Aaron Parecki") AS received
UNION
(SELECT "sent" AS type, SUM(num_emoji) AS num
FROM messages
WHERE from_name = "Aaron Parecki")
```

### What hour is most active?

```sql
SELECT HOUR(DATE_ADD(time, INTERVAL 24-7 HOUR)) % 24 AS local_hour, COUNT(1) AS num
FROM messages
GROUP BY HOUR(time)
ORDER BY time
```

Change the -7 to your local timezone offset. Also this ignores DST so that could use some work.

