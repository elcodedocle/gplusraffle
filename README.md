gplusraffle by elcodedocle
==========================
#####*A Google OAuth 2.0 and FusionTables PHP API client based raffle manager*

 Copyright (C) 2014 Gael Abadin<br/>
 License: [MIT Expat][1]<br />
 Version: 0.1.4-beta<br />
 [![Build Status](https://travis-ci.org/elcodedocle/gplusraffle.svg?branch=master)](https://travis-ci.org/elcodedocle/gplusraffle)<br />
 [![Code Climate](https://codeclimate.com/github/elcodedocle/gplusraffle.png)](https://codeclimate.com/github/elcodedocle/gplusraffle)
 
[![Test site snapshot](http://i.imgur.com/Hy1LfLf.png "http://www.geovolutions.com/gplusraffle/webapp Check it out live!")](http://www.geovolutions.com/gplusraffle/webapp)


### (De)Motivation

Doing an in-site "manual" raffle may be way easier, but doing it online is 
waaay cooler! (for nerds like me :-))

(Also, I needed to get to know better the latest google PHP client API: What 
better way than doing a simple and cool app? ;-))

This is a proof of concept app, which means it works fine but it lacks anything
else but a rough basic functionality, although it should be easily extensible.

Anyway, the main thing I've learned from developing this project is that Fusion 
Tables is not at all a valid replacement for even the most modest DB needs. 
This project might as well have been called "A study on how and why Fusion 
Tables Suck":

 - Fusion tables is not a replacement for an SQL database (Duh!) But even 
 basic, fundamental features such as JOIN on a SELECT query are not allowed 
 (You can still merge two tables on a view and then query that view, which is 
 utterly slow, inefficient and annoying); Even a simple UPDATE or DELETE query 
 is a mess because they can only accept a ROWID = <ROWID> as a WHERE clause
 condition...
 
 - Fusion Tables is an experimental project, which means it could be cancelled 
 tomorrow, the next day, or anytime soon. It has been like that since June 
 2009.
 
 - Fusion Tables is an experimental project, which means it doesn't even offer 
 the chance to pay to increase you app's API access quota. Once you get pass 
 25000 requests/day (a single INSERT/UPDATE counts as 5), your app will be down
 until the next day. The same argument applies to a number of restrictions 
 such as table size, request and upload file size, etc.
 
In a nutshell, Fusion Tables may be nice to store slowly changing map data and 
things like that, but if you want to get some simple and yet powerful and 
scalable Cloud DB service I suggest you try AWS or Google Cloud SQL or 
something like that instead of Fusion Tables. I'll be the last one encouraging 
anybody to make serious use of this project without replacing the DAOs first to 
use any other service, at the very least. (I intend to do so myself if I ever 
release an update beyond patching the bugs I will find on this version)

### Requirements

 - PHP >=5.3 with curl extensions enabled
 - (mostly recommended) Apache 2 with mod_rewrite enabled for .htaccess URI 
 rewriting, although you can use any other web server by porting the required 
 rewrite rules, (and the webapp doesn't depend on them anyway)
 - (highly recommended) Linux server, with SSL enabled (but not the 
 heartbleed version :P)
 
(So, basically, any updated default LAMP stack, even without the M)

### Installation

This assumes you have already created a google app and the required oAuth 2.0 
credentials and activated the Fusion Tables API on 
[console.developers.google.com](http://console.developers.google.com). If 
you have trouble with that process, check out
[this tutorial](https://developers.google.com/+/api/oauth).

#### Using composer:

 - Get [composer](http://getcomposer.org):
 
```
curl -sS https://getcomposer.org/installer | php
```

 - Install the app from [packagist](https://packagist.org/packages/elcodedocle/gplusraffle) repo:
 
```
php composer.phar create-project -s "beta" elcodedocle/gplusraffle
```

 
#### Without composer:

- Download and unzip the 
[latest version](https://github.com/elcodedocle/gplusraffle/archive/master.zip) 
of this project from the git repo on a public folder on your web server, e.g.:

```bash
wget https://github.com/elcodedocle/gplusraffle/archive/master.zip -O gplusraffle.zip
unzip gplusraffle.zip -d /var/www
```

 - Download and extract the latest version (1.0.X) of the google api php client
 to /gplusraffle/vendor/google, renaming the dir to apiclient e.g.:

```bash
wget https://github.com/google/google-api-php-client/archive/master.zip -O apiclient.zip
unzip apiclient.zip -d /var/www/gplusraffle/vendor/google
mv /var/www/gplusraffle/vendor/google/google-api-php-client /var/www/gplusraffle/vendor/google/apiclient
```

 - Download and extract the latest version (2.2.1) of tabletools for datatables
 to /gplusraffle/vendor/google, renaming the dir to datatables-tabletools e.g.:

```bash
wget https://github.com/drmonty/datatables-tabletools/archive/master.zip -O datatables-tabletools.zip
unzip datatables-tabletools.zip -d /var/www/gplusraffle/components
mv /var/www/gplusraffle/components/datatables-tabletools-master /var/www/gplusraffle/components/datatables-tabletools
```

 - Download and extract the latest version (1.0.0) of my uuid class distro
 to /gplusraffle/vendor/elcodedocle, renaming the dir to uuid e.g.:

```bash
wget https://github.com/elcodedocle/uuid/archive/master.zip -O uuid.zip
unzip uuid.zip -d /var/www/gplusraffle/vendor
mv /var/www/gplusraffle/vendor/uuid-master /var/www/gplusraffle/vendor/uuid
```
 
### Set up: 

 - Edit config.php.dist, filling the required fields with your app's credentials, 
 and save it as config.php
 
 - Point your browser to `/admin/login` (`/main.php?collection=admin&action=login`) 
 and then to `/admin/install` (`/main.php?collection=admin&action=install`) 

This will set the google account offline token the app will use to manage 
the Fusion Tables.

You don't need to use the same google account you used for creating the app on 
the developers console: You may even create a new google account just to keep 
the app's tables if you want. 

The offline token, stored in `adminConfig.php`, will only give the app offline 
access to the Fusion Tables managing and basic profile info of that account (to
retrieve the account id).

Users are only required basic profile info access (for the userid). 

For your users' safety and your own I suggest using SSL on the server. And, 
since I am at the moment too lazy and cheap to follow my own advice on the demo site 
I've set up, if you are concerned about what somebody could do with access to your 
account's basic profile info I urge you not to use it on a public/open 
non encrypted connection.

### How to use

 - You can now `/admin/logout` (`main.php?collection=admin&action=logout`) and go to 
 `/webapp` to log in as a regular user and check the app out.

The web app, `/webapp`, provides an HTML5 client interface to handle 
requests and present JSON responses required to manage and participate on raffles.

If you want to toy around with the API (maybe even implement a slicker client ;-)), 
these are the HTTP request actions available (all responses are JSON encoded, with 
resposne column names array and rows array contained on `columns` and `rows` fields 
of the `data` response field):

`/admin/login` - logs the user with admin scopes (FusionTables handling, basic 
profile access).

`/admin/install` - sets the current admin google account id and a new token to 
handle fusion tables operations.

`/admin/uninstall` - removes the current admin google account id and token (the 
fusion tables will remain stored on the account, but new ones will be created on 
reinstall).

`/, /user/login` - logs in the user (html output with `authUrl` link), redirects 
to `/webapp` on success if a web session has been opened (by visiting /webapp).

`/user/logout` - logs out the user (an auto logout is performed when the session 
expires, in which case a 401 error code is issued instead of the requested 
response).

`/raffle/create/description` - creates a raffle, returning its id, description, 
creator (you), date of creation (now), privacy (public) and status (closed).

`/raffle/delete/raffleid` - deletes `raffleid` (only the user who created it 
can do this. WARNING: All data will be lost, and no confirmation is required!).

`/raffle/list`, `/raffle/list/all` - lists (public) raffles (id, description,
creator, status and date of creation).

`/raffle/list/me` or `/raffle/list/user/joined/me` - lists raffles the current 
user has joined (admin can list raffles joined by any userid).

`/raffle/list/mine` or `/raffle/list/user/created/me` - lists raffles the 
current user has created (admin can list raffles created by any userid).

`/raffle/list/raffle/raffleid` - lists raffle's participants (and winner, if 
raffled). Any raffle, public or private, can be listed, and any user can join
or leave a raffle while it's open by only knowing its id. Only creator and 
admin have managing rights.

`/raffle/list/open` - lists raffles (id, description, creator, status and date 
of creation)

`/raffle/list/closed` - lists closed raffles

`/raffle/list/raffled` - lists raffled raffles

`/raffle/open/raffleid` - opens the raffle, so users can join it

`/raffle/close/raffleid`  - closes the raffle. No more users will be allowed to 
join it, unless it's reopened.

`/raffle/raffleid/limit` - raffles `raffleid` (only the user who created the raffle 
can do this, and the raffle must be closed and have more than 0 participants. 
It picks min(participants,limit) winners or returns the winner(s) if the raffle has 
already been raffled).

`/raffle/join/raffleid` - user joins `raffleid` (the raffle must be opened).

`/raffle/leave/raffleid` - user joins `raffleid` (the raffle must be opened).

`/raffle/check/raffleid` - check who won the raffle (the raffle must be raffled).

Regarding the DB schema, it's actually the simplest possible:

- The raffles list is stored on a fusion table 'raffles' 
(raffleid, description, creatorid, created, privacy, status).

- The winners list is stored on a fusion table 'winners' (raffleid, userid, raffled).

- The raffles' participants lists are stored on a fusion table 'participants' 
(userid, raffleid, comment, joined).

(All dates are stored in UTC, reformatted to the local timezone in the web app 
client)

If you are into developing there are some phpunit tests you can perform:

```php
cd tests
phpunit --testsuite gplusraffle
```

(If you are a developer and find a bug not cached by the tests and want to 
help me fix it, opening an issue by pull requesting a non passing test would 
be swell :-))

### TODO:

- Switch from Fusion Tables to something more flexible and less experimental.

- Take some time to put together a nice GUI (Or at least one I'm not embarrased
 of :-P)

- v0.2 will implement private raffles that will not be listed to non 
participants and will only be accessible through the provided link, and users 
who want to participate on those will join a requests table and the 
creator/admin will have to accept them before they are pushed into the 
participants table.

### Acks

[The Google team](https://github.com/google/google-api-php-client).

The [twitter bootstrap team](https://github.com/twbs) (bootstrap).

[Allan Jardine](https://github.com/DataTables), the developer behind 
[datatables](https://github.com/DataTables/DataTables).

[Mathias Rohnstock](https://github.com/drmonty), the developer behind 
[tabletools](https://github.com/drmonty/datatables-tabletools).

[Andrew Moore](http://www.php.net/manual/en/function.uniqid.php#94959), the 
developer behind [the uuid class](https://github.com/elcodedocle/uuid).

[Felix Gnass](https://github.com/fgnass/), the developer behind 
[spin.js](http://fgnass.github.io/spin.js/).

[Pablo Cantero](https://github.com/phstc), the developer behind 
[jquery-dateFormat](https://github.com/phstc/jquery-dateFormat)


Enjoy!

(

And, if you're happy with this product, donate! 

bitcoin: 13qZDBXVtUa3wBY99E7yQXBRGDMKpDmyxa 

dogecoin: D8ZE9piiaj3aMZeToqyBmUMctDMfmErJCd 

paypal: http://goo.gl/ql69W2

)

[1]: https://raw.githubusercontent.com/elcodedocle/gplusraffle/master/LICENSE
