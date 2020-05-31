Package: ComicBookTools
Author: Abu Khadeejah Karl Holz <binholz|a|hotmail|d|com>
Licence: LICENCE

# About Me
I'm Abu Khadeejah Karl Holz, I'm a StageHand by trade and coding is my hobby.  I have diploma for Computer Systems Technologies from Seneca College in Toronto, this is where I learned the basics of UNIX, Linux, Shell Scripting, Perl, PHP, Windows, etc.  I'm a father, so i have been busy taking care of my girl and not spending a lot of time coding php on my off time.

# Comic Book Tools

this is a quick little tool to let you view your cbr files off of a webserver using PHP, like the built-in server.  This project uses the [rar pecl extention](https://pecl.php.net/package/rar) and it will need to be built by the user.  Not all file versons are read by the pecl plugin, I have added a setup.php file that will create an ini file with all your cbz/cbr files, this is used for the front page page so you can select your comic from what was found. i have tried added conversion to png support, not all images seem to be viewable so far.  the links that are generated are base64 encoded as a query string. you will need to increase the php memory usage for this class in your php.ini

## File type viewing status

My class will deal with both rar and zip based comic books, class will test for rar first, then zip for all requests.  7-zip (cb7) is not supported, i didn't see a pecl extention or anything in the documentation about 7-zip.  You can create a zip file arcive of selected images, like in MacOS Finder, then rename the extention to cbz.

```
<zipfile>.[zip|rar|cbr|cbz]
|_ File01.png
|_ File02.jpg
|_ File03.png
```
As you can see from above, this class can be used for more than just comics, take a look at the samples i made in the comic folder (check my github)

## You Need the RAR PECL EXTENTION for cbr files
you will need this pecl extention installed on your system.  i built it from source using the link bellow:

* [rar pecl extention](https://pecl.php.net/package/rar)

* extract, then from the dir ->

```
$ cd <src dir>
$ ./configure
$ make
$ make test
$ make install
```

I used the system version of PHP 7 in MacOS High Sieara and not MAMP (failed to build for me).

### php.ini

Enable pecl extention in the /etc/php.ini
```
extension=rar.so
```


you should add the code bellow to your php.ini file, if my scripts setting doesn't work. Most images in cbr/cbz files are high resolution.

```
memory_limit = 2048M

```

## use PHP built-in webserver

for this project, i'm just using the built in webserver for PHP since this is just a personal reader for comics that you can setup on a raspberry pi, pine64 or other SBC. take a look at the index.php and edit the $dir variable for your comic book directory.

```
$ cd <comic srv>
$ php -S localhost:8888 index.php
or
$ php -S <ip address|hostname>:8888 index.php
```

## Support My Work

If you like any of the work I have done in the past, I'm welcome to any support you would like to provide via PayPal or EMT.  I would like to focus on the projects with the most downloads, like the Excel2JSON, eKitab (code clean-up?), WebMap (upgrade) or others listed on PHP Classes website.  My current computer is an MacMini 2010.  I would like to get a new laptop (Mac perferablly). E-mail or make an issue on GitHub if you would like to ask questions or provide help.  I like to keep things open source so we can have these tools availale and educational.