PhpRedis
=============
The phpredis extension provides an API for communicating with the [Redis](http://redis.io/) key-value store. It is released under the [PHP License, version 3.01](http://www.php.net/license/3_01.txt).
This code has been developed and maintained by Owlient from November 2009 to March 2011.

You can send comments, patches, questions [here on github](https://github.com/nicolasff/phpredis/issues) or to n.favrefelix@gmail.com ([@yowgi](http://twitter.com/yowgi)).


Installing/Configuring
======================

<pre>
phpize
./configure
make && make install
</pre>

`make install` copies `redis.so` to an appropriate location, but you still need to enable the module in the PHP config file. To do so, either edit your php.ini or add a redis.ini file in `/etc/php5/conf.d` with the following contents: `extension=redis.so`.

You can generate a debian package for PHP5, accessible from Apache 2 by running `./mkdeb-apache2.sh` or with `dpkg-buildpackage` or `svn-buildpackage`.

This extension exports a single class, `Redis` (and `RedisException` used in case of errors).

Install on OSX
==============

If the install fails on OSX, type the following commands in your shell before trying again:
<pre>
MACOSX_DEPLOYMENT_TARGET=10.6
CFLAGS="-arch i386 -arch x86_64 -g -Os -pipe -no-cpp-precomp"
CCFLAGS="-arch i386 -arch x86_64 -g -Os -pipe"
CXXFLAGS="-arch i386 -arch x86_64 -g -Os -pipe"
LDFLAGS="-arch i386 -arch x86_64 -bind_at_load"
export CFLAGS CXXFLAGS LDFLAGS CCFLAGS MACOSX_DEPLOYMENT_TARGET
</pre>

See also: [Install Redis & PHP Extension PHPRedis with Macports](http://www.lecloud.net/post/3378834922/install-redis-php-extension-phpredis-with-macports).

Session handler (new)
==============

phpredis can be used to store PHP sessions. To do this, configure `session.save_handler` and `session.save_path` in your php.ini to tell phpredis where to store the sessions:
<pre>
session.save_handler = redis
session.save_path = "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2"
</pre>

`session.save_path` can have a simple `host:port` format too, but you need to provide the `tcp://` scheme if you want to use the parameters. The following parameters are available:

* weight (integer): the weight of a host is used in comparison with the others in order to customize the session distribution on several hosts. If host A has twice the weight of host B, it will get twice the amount of sessions. In the example, *host1* stores 20% of all the sessions (1/(1+2+2)) while *host2* and *host3* each store 40% (2/1+2+2). The target host is determined once and for all at the start of the session, and doesn't change. The default weight is 1.
* timeout (float): the connection timeout to a redis host, expressed in seconds. If the host is unreachable in that amount of time, the session storage will be unavailable for the client. The default timeout is very high (86400 seconds).
* persistent (integer, should be 1 or 0): defines if a persistent connection should be used. **(experimental setting)**
* prefix (string, defaults to "PHPREDIS_SESSION:"): used as a prefix to the Redis key in which the session is stored. The key is composed of the prefix followed by the session ID.
* auth (string, empty by default): used to authenticate with the Redis server prior to sending commands.

Sessions have a lifetime expressed in seconds and stored in the INI variable "session.gc_maxlifetime". You can change it with [`ini_set()`](http://php.net/ini_set).
The session handler requires a version of Redis with the `SETEX` command (at least 2.0).

Error handling
==============

phpredis throws a `RedisException` object if it can't reach the Redis server. That can happen in case of connectivity issues, if the Redis service is down, or if the redis host is overloaded. In any other problematic case that does not involve an unreachable server (such as a key not existing, an invalid command, etc), phpredis will return `FALSE`.

Methods
=========

## Redis::__construct
##### *Description*

Creates a Redis client

##### *Example*

<pre>
$redis = new Redis();
</pre>

## connect, open
##### *Description*

Connects to a Redis instance.

##### *Parameters*

*host*: string. can be a host, or the path to a unix domain socket  
*port*: int, optional  
*timeout*: float, value in seconds (optional, default is 0 meaning unlimited)  

##### *Return Value*

*BOOL*: `TRUE` on success, `FALSE` on error.

##### *Example*

<pre>
$redis->connect('127.0.0.1', 6379);
$redis->connect('127.0.0.1'); // port 6379 by default
$redis->connect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout.
$redis->connect('/tmp/redis.sock'); // unix domain socket.
</pre>

## pconnect, popen
##### *Description*

Connects to a Redis instance or reuse a connection already established with `pconnect`/`popen`.

The connection will not be closed on `close` or end of request until the php process ends.
So be patient on to many open FD's (specially on redis server side) when using persistent
connections on many servers connecting to one redis server.

Also more than one persistent connection can be made identified by either host + port + timeout
or host + persistent_id or unix socket + timeout.

This feature is not available in threaded versions. `pconnect` and `popen` then working like their non
persistent equivalents.

##### *Parameters*

*host*: string. can be a host, or the path to a unix domain socket  
*port*: int, optional  
*timeout*: float, value in seconds (optional, default is 0 meaning unlimited)  
*persistent_id*: string. identity for the requested persistent connection

##### *Return Value*

*BOOL*: `TRUE` on success, `FALSE` on error.

##### *Example*

<pre>
$redis->pconnect('127.0.0.1', 6379);
$redis->pconnect('127.0.0.1'); // port 6379 by default - same connection like before.
$redis->pconnect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout and would be another connection than the two before.
$redis->pconnect('127.0.0.1', 6379, 2.5, 'x'); // x is sent as persistent_id and would be another connection the the three before.
$redis->pconnect('/tmp/redis.sock'); // unix domain socket - would be another connection than the four before.
</pre>

## close
##### *Description*
Disconnects from the Redis instance, except when `pconnect` is used.

## setOption
##### *Description*
Set client option.

##### *Parameters*
*parameter name*  
*parameter value*  

##### *Return value*
*BOOL*: `TRUE` on success, `FALSE` on error.

##### *Example*
<pre>
$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);	// don't serialize data
$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);	// use built-in serialize/unserialize
$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);	// use igBinary serialize/unserialize

$redis->setOption(Redis::OPT_PREFIX, 'myAppName:');	// use custom prefix on all keys
</pre>


## getOption
##### *Description*
Get client option.

##### *Parameters*
*parameter name*  

##### *Return value*
Parameter value.

##### *Example*
<pre>
$redis->getOption(Redis::OPT_SERIALIZER);	// return Redis::SERIALIZER_NONE, Redis::SERIALIZER_PHP, or Redis::SERIALIZER_IGBINARY.
</pre>


## ping
##### *Description*

Check the current connection status

##### *Parameters*

(none)

##### *Return Value*

*STRING*: `+PONG` on success. Throws a RedisException object on connectivity error, as described above.


## get
##### *Description*

Get the value related to the specified key

##### *Parameters*

*key*

##### *Return Value*

*String* or *Bool*: If key didn't exist, `FALSE` is returned. Otherwise, the value related to this key is returned.

##### *Examples*

<pre>
$redis->get('key');
</pre>

## set
##### Description

Set the string value in argument as value of the key.

##### Parameters
*Key*  
*Value*  
*Timeout* (optional). Calling `SETEX` is preferred if you want a timeout.  

##### Return value
*Bool* `TRUE` if the command is successful.

##### Examples

<pre>
$redis->set('key', 'value');
</pre>

## setex
##### Description

Set the string value in argument as value of the key, with a time to live.

##### Parameters
*Key*
*TTL*
*Value*

##### Return value
*Bool* `TRUE` if the command is successful.

##### Examples

<pre>
$redis->setex('key', 3600, 'value'); // sets key → value, with 1h TTL.
</pre>
## setnx
##### Description
Set the string value in argument as value of the key if the key doesn't already exist in the database.

##### Parameters
*key*
*value*

##### Return value
*Bool* `TRUE` in case of success, `FALSE` in case of failure.

##### Examples
<pre>
$redis->setnx('key', 'value'); /* return TRUE */
$redis->setnx('key', 'value'); /* return FALSE */
</pre>

## del, delete
##### Description
Remove specified keys.
##### Parameters
An array of keys, or an undefined number of parameters, each a key: *key1* *key2* *key3* ... *keyN*
##### Return value
*Long* Number of keys deleted.
##### Examples
<pre>
$redis->set('key1', 'val1');
$redis->set('key2', 'val2');
$redis->set('key3', 'val3');
$redis->set('key4', 'val4');

$redis->delete('key1', 'key2'); /* return 2 */
$redis->delete(array('key3', 'key4')); /* return 2 */
</pre>

## multi, exec, discard.
##### Description
Enter and exit transactional mode.  
##### Parameters
(optional) `Redis::MULTI` or `Redis::PIPELINE`. Defaults to `Redis::MULTI`. A `Redis::MULTI` block of commands runs as a single transaction; a `Redis::PIPELINE` block is simply transmitted faster to the server, but without any guarantee of atomicity. `discard` cancels a transaction.  
##### Return value
`multi()` returns the Redis instance and enters multi-mode. Once in multi-mode, all subsequent method calls return the same object until `exec()` is called.
##### Example
<pre>
$ret = $redis->multi()
    ->set('key1', 'val1')
    ->get('key1')
    ->set('key2', 'val2')
    ->get('key2')
    ->exec();

/*
$ret == array(
    0 => TRUE,
    1 => 'val1',
    2 => TRUE,
    3 => 'val2');
*/
</pre>

## watch, unwatch
##### Description
Watches a key for modifications by another client. If the key is modified between `WATCH` and `EXEC`, the MULTI/EXEC transaction will fail (return `FALSE`). `unwatch` cancels all the watching of all keys by this client.
##### Parameters
*keys*: a list of keys
##### Example
<pre>
$redis->watch('x');
/* long code here during the execution of which other clients could well modify `x` */
$ret = $redis->multi()
    ->incr('x')
    ->exec();
/*
$ret = FALSE if x has been modified between the call to WATCH and the call to EXEC.
*/
</pre>

## subscribe
##### Description
Subscribe to channels. Warning: this function will probably change in the future.
##### Parameters
*channels*: an array of channels to subscribe to  
*callback*: either a string or an array($instance, 'method_name'). The callback function receives 3 parameters: the redis instance, the channel name, and the message.  
##### Example
<pre>
function f($redis, $chan, $msg) {
	switch($chan) {
		case 'chan-1':
			...
			break;

		case 'chan-2':
			...
			break;

		case 'chan-2':
			...
			break;
	}
}

$redis->subscribe(array('chan-1', 'chan-2', 'chan-3'), 'f'); // subscribe to 3 chans
</pre>


## publish
##### Description
Publish messages to channels. Warning: this function will probably change in the future.
##### Parameters
*channel*: a channel to publish to  
*messsage*: string  
##### Example
<pre>
$redis->publish('chan-1', 'hello, world!'); // send message.
</pre>


## exists
##### Description
Verify if the specified key exists.
##### Parameters
*key*
##### Return value
*BOOL*: If the key exists, return `TRUE`, otherwise return `FALSE`.
##### Examples
<pre>
$redis->set('key', 'value');
$redis->exists('key'); /*  TRUE */
$redis->exists('NonExistingKey'); /* FALSE */
</pre>

## incr, incrBy
##### Description
Increment the number stored at key by one. If the second argument is filled, it will be used as the integer value of the increment.
##### Parameters
*key*  
*value*: value that will be added to key (only for incrBy)
##### Return value
*INT* the new value
##### Examples
<pre>
$redis->incr('key1'); /* key1 didn't exists, set to 0 before the increment */
					  /* and now has the value 1  */

$redis->incr('key1'); /* 2 */
$redis->incr('key1'); /* 3 */
$redis->incr('key1'); /* 4 */
$redis->incrBy('key1', 10); /* 14 */
</pre>

## decr, decrBy
##### Description
Decrement the number stored at key by one. If the second argument is filled, it will be used as the integer value of the decrement.
##### Parameters
*key*  
*value*: value that will be substracted to key (only for decrBy)
##### Return value
*INT* the new value
##### Examples
<pre>
$redis->decr('key1'); /* key1 didn't exists, set to 0 before the increment */
					  /* and now has the value -1  */

$redis->decr('key1'); /* -2 */
$redis->decr('key1'); /* -3 */
$redis->decrBy('key1', 10); /* -13 */
</pre>

## getMultiple
##### Description
Get the values of all the specified keys. If one or more keys dont exist, the array will contain `FALSE` at the position of the key.
##### Parameters
*Array*: Array containing the list of the keys
##### Return value
*Array*: Array containing the values related to keys in argument
##### Examples
<pre>
$redis->set('key1', 'value1');
$redis->set('key2', 'value2');
$redis->set('key3', 'value3');
$redis->getMultiple(array('key1', 'key2', 'key3')); /* array('value1', 'value2', 'value3');
$redis->getMultiple(array('key0', 'key1', 'key5')); /* array(`FALSE`, 'value2', `FALSE`);
</pre>

## lPush
##### Description
Adds the string value to the head (left) of the list. Creates the list if the key didn't exist. If the key exists and is not a list, `FALSE` is returned.
##### Parameters
*key*  
*value* String, value to push in key
##### Return value
*LONG* The new length of the list in case of success, `FALSE` in case of Failure.
##### Examples
<pre>
$redis->delete('key1');
$redis->lPush('key1', 'C'); // returns 1
$redis->lPush('key1', 'B'); // returns 2
$redis->lPush('key1', 'A'); // returns 3
/* key1 now points to the following list: [ 'A', 'B', 'C' ] */
</pre>

## rPush
##### Description
Adds the string value to the tail (right) of the list. Creates the list if the key didn't exist. If the key exists and is not a list, `FALSE` is returned.
##### Parameters
*key*  
*value* String, value to push in key
##### Return value
*LONG* The new length of the list in case of success, `FALSE` in case of Failure.
##### Examples
<pre>
$redis->delete('key1');
$redis->rPush('key1', 'A'); // returns 1
$redis->rPush('key1', 'B'); // returns 2
$redis->rPush('key1', 'C'); // returns 3
/* key1 now points to the following list: [ 'A', 'B', 'C' ] */
</pre>

## lPushx
##### Description
Adds the string value to the head (left) of the list if the list exists.
##### Parameters
*key*  
*value* String, value to push in key
##### Return value
*LONG* The new length of the list in case of success, `FALSE` in case of Failure.
##### Examples
<pre>
$redis->delete('key1');
$redis->lPushx('key1', 'A'); // returns 0
$redis->lPush('key1', 'A'); // returns 1
$redis->lPushx('key1', 'B'); // returns 2
$redis->lPushx('key1', 'C'); // returns 3
/* key1 now points to the following list: [ 'A', 'B', 'C' ] */
</pre>

## rPushx
##### Description
Adds the string value to the tail (right) of the list if the ist exists. `FALSE` in case of Failure.
##### Parameters
*key*  
*value* String, value to push in key
##### Return value
*LONG* The new length of the list in case of success, `FALSE` in case of Failure.
##### Examples
<pre>
$redis->delete('key1');
$redis->rPushx('key1', 'A'); // returns 0
$redis->rPush('key1', 'A'); // returns 1
$redis->rPushx('key1', 'B'); // returns 2
$redis->rPushx('key1', 'C'); // returns 3
/* key1 now points to the following list: [ 'A', 'B', 'C' ] */
</pre>

## lPop
##### *Description*
Return and remove the first element of the list.
##### *Parameters*
*key*
##### *Return value*
*STRING* if command executed successfully
*BOOL* `FALSE` in case of failure (empty list)
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
$redis->lPop('key1'); /* key1 => [ 'B', 'C' ] */
</pre>

## rPop
##### *Description*
Returns and removes the first element of the list.
##### *Parameters*
*key*
##### *Return value*
*STRING* if command executed successfully
*BOOL* `FALSE` in case of failure (empty list)
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
$redis->rPop('key1'); /* key1 => [ 'A', 'B' ] */
</pre>

## blPop, brPop
##### *Description*
Is a blocking lPop(rPop) primitive. If at least one of the lists contains at least one element, the element will be popped from the head of the list and returned to the caller.
Il all the list identified by the keys passed in arguments are empty, blPop will block during the specified timeout until an element is pushed to one of those lists. This element will be popped.

##### *Parameters*
*ARRAY* Array containing the keys of the lists
*INTEGER* Timeout
Or
*STRING* Key1
*STRING* Key2
*STRING* Key3
...
*STRING* Keyn
*INTEGER* Timeout
##### *Return value*
*ARRAY* array('listName', 'element')

##### *Example*
<pre>
/* Non blocking feature */
$redis->lPush('key1', 'A');
$redis->delete('key2');

$redis->blPop('key1', 'key2', 10); /* array('key1', 'A') */
/* OR */
$redis->blPop(array('key1', 'key2'), 10); /* array('key1', 'A') */

$redis->brPop('key1', 'key2', 10); /* array('key1', 'A') */
/* OR */
$redis->brPop(array('key1', 'key2'), 10); /* array('key1', 'A') */

/* Blocking feature */

/* process 1 */
$redis->delete('key1');
$redis->blPop('key1', 10);
/* blocking for 10 seconds */

/* process 2 */
$redis->lPush('key1', 'A');

/* process 1 */
/* array('key1', 'A') is returned*/

</pre>

## lSize
##### *Description*
Returns the size of a list identified by Key. If the list didn't exist or is empty, the command returns 0. If the data type identified by Key is not a list, the command return `FALSE`.
##### *Parameters*
*Key*
##### *Return value*
*LONG* The size of the list identified by Key exists.  
*BOOL* `FALSE` if the data type identified by Key is not list

##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
$redis->lSize('key1');/* 3 */
$redis->rPop('key1'); 
$redis->lSize('key1');/* 2 */
</pre>

## lIndex, lGet
##### *Description*
Return the specified element of the list stored at the specified key.
0 the first element, 1 the second ...
-1 the last element, -2 the penultimate ...
Return `FALSE` in case of a bad index or a key that doesn't point to a list.
##### *Parameters*
*key*
*index*

##### *Return value*
*String* the element at this index  
*Bool* `FALSE` if the key identifies a non-string data type, or no value corresponds to this index in the list `Key`.
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
$redis->lGet('key1', 0); /* 'A' */
$redis->lGet('key1', -1); /* 'C' */
$redis->lGet('key1', 10); /* `FALSE` */
</pre>

## lSet
##### *Description*
Set the list at index with the new value.
##### *Parameters*
*key*
*index*
*value*
##### *Return value*
*BOOL* `TRUE` if the new value is setted. `FALSE` if the index is out of range, or data type identified by key is not a list.
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
$redis->lGet('key1', 0); /* 'A' */
$redis->lSet('key1', 0, 'X');
$redis->lGet('key1', 0); /* 'X' */ 
</pre>

## lRange, lGetRange
##### *Description*
Returns the specified elements of the list stored at the specified key in the range [start, end]. start and stop are interpretated as indices:
0 the first element, 1 the second ...
-1 the last element, -2 the penultimate ...
##### *Parameters*
*key*
*start*
*end*

##### *Return value*
*Array* containing the values in specified range. 
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C');
$redis->lRange('key1', 0, -1); /* array('A', 'B', 'C') */
</pre>

## lTrim, listTrim
##### *Description*
Trims an existing list so that it will contain only a specified range of elements.
##### *Parameters*
*key*
*start*
*stop*
##### *Return value*
*Array*  
*Bool* return `FALSE` if the key identify a non-list value.
##### *Example*
<pre>
$redis->rPush('key1', 'A');
$redis->rPush('key1', 'B');
$redis->rPush('key1', 'C');
$redis->lRange('key1', 0, -1); /* array('A', 'B', 'C') */
$redis->lTrim('key1', 0, 1);
$redis->lRange('key1', 0, -1); /* array('A', 'B') */
</pre>

## lRem, lRemove
##### *Description*
Removes the first `count` occurences of the value element from the list. If count is zero, all the matching elements are removed. If count is negative, elements are removed from tail to head.
##### *Parameters*
*key*  
*value*  
*count*  

##### *Return value*
*LONG* the number of elements to remove  
*BOOL* `FALSE` if the value identified by key is not a list.
##### *Example*
<pre>
$redis->lPush('key1', 'A');
$redis->lPush('key1', 'B');
$redis->lPush('key1', 'C'); 
$redis->lPush('key1', 'A'); 
$redis->lPush('key1', 'A'); 

$redis->lRange('key1', 0, -1); /* array('A', 'A', 'C', 'B', 'A') */
$redis->lRem('key1', 'A', 2); /* 2 */
$redis->lRange('key1', 0, -1); /* array('C', 'B', 'A') */
</pre>

## lInsert
##### *Description*
Insert value in the list before or after the pivot value. the parameter options specify the position of the insert (before or after).
If the list didn't exists, or the pivot didn't exists, the value is not inserted.
##### *Parameters*
*key*
*position*  Redis::BEFORE | Redis::AFTER
*pivot*
*value*

##### *Return value*
The number of the elements in the list, -1 if the pivot didn't exists.

##### *Example*
<pre>
$redis->delete('key1');
$redis->lInsert('key1', Redis::AFTER, 'A', 'X'); /* 0 */

$redis->lPush('key1', 'A');
$redis->lPush('key1', 'B');
$redis->lPush('key1', 'C');

$redis->lInsert('key1', Redis::BEFORE, 'C', 'X'); /* 4 */
$redis->lRange('key1', 0, -1); /* array('A', 'B', 'X', 'C') */

$redis->lInsert('key1', Redis::AFTER, 'C', 'Y'); /* 5 */
$redis->lRange('key1', 0, -1); /* array('A', 'B', 'X', 'C', 'Y') */

$redis->lInsert('key1', Redis::AFTER, 'W', 'value'); /* -1 */

</pre>

## sAdd
##### *Description*
Adds a value to the set value stored at key. If this value is already in the set, `FALSE` is returned.  
##### *Parameters*
*key*
*value*

##### *Return value*
*BOOL* `TRUE` if value didn't exist and was added successfully, `FALSE` if the value is already present.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); /* TRUE, 'key1' => {'set1'} */
$redis->sAdd('key1' , 'set2'); /* TRUE, 'key1' => {'set1', 'set2'}*/
$redis->sAdd('key1' , 'set2'); /* FALSE, 'key1' => {'set1', 'set2'}*/
</pre>

## sRem, sRemove
##### *Description*
Removes the specified member from the set value stored at key.
##### *Parameters*
*key*
*member*
##### *Return value*
*BOOL* `TRUE` if the member was present in the set, `FALSE` if it didn't.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); 
$redis->sAdd('key1' , 'set2'); 
$redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/
$redis->sRem('key1', 'set2'); /* 'key1' => {'set1', 'set3'} */
</pre>

## sMove
##### *Description*
Moves the specified member from the set at srcKey to the set at dstKey.
##### *Parameters*
*srcKey*
*dstKey*
*member*
##### *Return value*
*BOOL* If the operation is successful, return `TRUE`. If the srcKey and/or dstKey didn't exist, and/or the member didn't exist in srcKey, `FALSE` is returned.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set11'); 
$redis->sAdd('key1' , 'set12'); 
$redis->sAdd('key1' , 'set13'); /* 'key1' => {'set11', 'set12', 'set13'}*/
$redis->sAdd('key2' , 'set21'); 
$redis->sAdd('key2' , 'set22'); /* 'key2' => {'set21', 'set22'}*/
$redis->sMove('key1', 'key2', 'set13'); /* 'key1' =>  {'set11', 'set12'} */
					/* 'key2' =>  {'set21', 'set22', 'set13'} */

</pre>

## sIsMember, sContains
##### *Description*
Checks if `value` is a member of the set stored at the key `key`.
##### *Parameters*
*key*
*value*

##### *Return value*
*BOOL* `TRUE` if `value` is a member of the set at key `key`, `FALSE` otherwise.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); 
$redis->sAdd('key1' , 'set2'); 
$redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/

$redis->sIsMember('key1', 'set1'); /* TRUE */
$redis->sIsMember('key1', 'setX'); /* FALSE */

</pre>

## sCard, sSize
##### *Description*
Returns the cardinality of the set identified by key.
##### *Parameters*
*key*
##### *Return value*
*LONG* the cardinality of the set identified by key, 0 if the set doesn't exist.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); 
$redis->sAdd('key1' , 'set2'); 
$redis->sAdd('key1' , 'set3'); /* 'key1' => {'set1', 'set2', 'set3'}*/
$redis->sCard('key1'); /* 3 */
$redis->sCard('keyX'); /* 0 */
</pre>

## sPop
##### *Description*
Removes and returns a random element from the set value at Key.
##### *Parameters*
*key*
##### *Return value*
*String* "popped" value  
*Bool* `FALSE` if set identified by key is empty or doesn't exist.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); 
$redis->sAdd('key1' , 'set2'); 
$redis->sAdd('key1' , 'set3'); /* 'key1' => {'set3', 'set1', 'set2'}*/
$redis->sPop('key1'); /* 'set1', 'key1' => {'set3', 'set2'} */
$redis->sPop('key1'); /* 'set3', 'key1' => {'set2'} */
</pre>

## sRandMember
##### *Description*
Returns a random element from the set value at Key, without removing it.
##### *Parameters*
*key*
##### *Return value*
*String* value from the set  
*Bool* `FALSE` if set identified by key is empty or doesn't exist.
##### *Example*
<pre>
$redis->sAdd('key1' , 'set1'); 
$redis->sAdd('key1' , 'set2'); 
$redis->sAdd('key1' , 'set3'); /* 'key1' => {'set3', 'set1', 'set2'}*/
$redis->sRandMember('key1'); /* 'set1', 'key1' => {'set3', 'set1', 'set2'} */
$redis->sRandMember('key1'); /* 'set3', 'key1' => {'set3', 'set1', 'set2'} */
</pre>

## sInter

##### *Description*

Returns the members of a set resulting from the intersection of all the sets held at the specified keys.
If just a single key is specified, then this command produces the members of this set. If one of the keys
is missing, `FALSE` is returned.

##### *Parameters*

key1, key2, keyN: keys identifying the different sets on which we will apply the intersection.
		
##### *Return value*

Array, contain the result of the intersection between those keys. If the intersection beteen the different sets is empty, the return value will be empty array.

##### *Examples*

<pre>
$redis->sAdd('key1', 'val1');
$redis->sAdd('key1', 'val2');
$redis->sAdd('key1', 'val3');
$redis->sAdd('key1', 'val4');

$redis->sAdd('key2', 'val3');
$redis->sAdd('key2', 'val4');

$redis->sAdd('key3', 'val3');
$redis->sAdd('key3', 'val4');

var_dump($redis->sInter('key1', 'key2', 'key3'));
</pre>

Output:

<pre>
array(2) {
  [0]=>
  string(4) "val4"
  [1]=>
  string(4) "val3"
}
</pre>

## sInterStore
##### *Description*
Performs a sInter command and stores the result in a new set.
##### *Parameters*
*Key*: dstkey, the key to store the diff into.

*Keys*: key1, key2... keyN. key1..keyN are intersected as in sInter.

##### *Return value*
*INTEGER*: The cardinality of the resulting set, or `FALSE` in case of a missing key.

##### *Example*
<pre>
$redis->sAdd('key1', 'val1');
$redis->sAdd('key1', 'val2');
$redis->sAdd('key1', 'val3');
$redis->sAdd('key1', 'val4');

$redis->sAdd('key2', 'val3');
$redis->sAdd('key2', 'val4');

$redis->sAdd('key3', 'val3');
$redis->sAdd('key3', 'val4');

var_dump($redis->sInterStore('output', 'key1', 'key2', 'key3'));
var_dump($redis->sMembers('output'));
</pre>

Output:

<pre>
int(2)

array(2) {
  [0]=>
  string(4) "val4"
  [1]=>
  string(4) "val3"
}
</pre>

## sUnion
##### *Description*
Performs the union between N sets and returns it.

##### *Parameters*
*Keys*: key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.

##### *Return value*
*Array of strings*: The union of all these sets.

##### *Example*
<pre>
$redis->delete('s0', 's1', 's2');

$redis->sAdd('s0', '1');
$redis->sAdd('s0', '2');
$redis->sAdd('s1', '3');
$redis->sAdd('s1', '1');
$redis->sAdd('s2', '3');
$redis->sAdd('s2', '4');

var_dump($redis->sUnion('s0', 's1', 's2'));
</pre>
Return value: all elements that are either in s0 or in s1 or in s2.
<pre>
array(4) {
  [0]=>
  string(1) "3"
  [1]=>
  string(1) "4"
  [2]=>
  string(1) "1"
  [3]=>
  string(1) "2"
}
</pre>

## sUnionStore
##### *Description*
Performs the same action as sUnion, but stores the result in the first key

##### *Parameters*
*Key*: dstkey, the key to store the diff into.

*Keys*: key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.

##### *Return value*
*INTEGER*: The cardinality of the resulting set, or `FALSE` in case of a missing key.

##### *Example*
<pre>
$redis->delete('s0', 's1', 's2');

$redis->sAdd('s0', '1');
$redis->sAdd('s0', '2');
$redis->sAdd('s1', '3');
$redis->sAdd('s1', '1');
$redis->sAdd('s2', '3');
$redis->sAdd('s2', '4');

var_dump($redis->sUnionStore('dst', 's0', 's1', 's2'));
var_dump($redis->sMembers('dst'));
</pre>
Return value: the number of elements that are either in s0 or in s1 or in s2.
<pre>
int(4)
array(4) {
  [0]=>
  string(1) "3"
  [1]=>
  string(1) "4"
  [2]=>
  string(1) "1"
  [3]=>
  string(1) "2"
}
</pre>

## sDiff
##### *Description*
Performs the difference between N sets and returns it.

##### *Parameters*
*Keys*: key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.

##### *Return value*
*Array of strings*: The difference of the first set will all the others.

##### *Example*
<pre>
$redis->delete('s0', 's1', 's2');

$redis->sAdd('s0', '1');
$redis->sAdd('s0', '2');
$redis->sAdd('s0', '3');
$redis->sAdd('s0', '4');

$redis->sAdd('s1', '1');
$redis->sAdd('s2', '3');

var_dump($redis->sDiff('s0', 's1', 's2'));
</pre>
Return value: all elements of s0 that are neither in s1 nor in s2.
<pre>
array(2) {
  [0]=>
  string(1) "4"
  [1]=>
  string(1) "2"
}
</pre>

## sDiffStore
##### *Description*
Performs the same action as sDiff, but stores the result in the first key
##### *Parameters*
*Key*: dstkey, the key to store the diff into.

*Keys*: key1, key2, ... , keyN: Any number of keys corresponding to sets in redis
##### *Return value*
*INTEGER*: The cardinality of the resulting set, or `FALSE` in case of a missing key.

##### *Example*
<pre>
$redis->delete('s0', 's1', 's2');

$redis->sAdd('s0', '1');
$redis->sAdd('s0', '2');
$redis->sAdd('s0', '3');
$redis->sAdd('s0', '4');

$redis->sAdd('s1', '1');
$redis->sAdd('s2', '3');

var_dump($redis->sDiffStore('dst', 's0', 's1', 's2'));
var_dump($redis->sMembers('dst'));
</pre>
Return value: the number of elements of s0 that are neither in s1 nor in s2.
<pre>
int(2)
array(2) {
  [0]=>
  string(1) "4"
  [1]=>
  string(1) "2"
}
</pre>

## sMembers, sGetMembers
##### *Description*
Returns the contents of a set.

##### *Parameters*
*Key*: key

##### *Return value*
An array of elements, the contents of the set.

##### *Example*
<pre>
$redis->delete('s');
$redis->sAdd('s', 'a');
$redis->sAdd('s', 'b');
$redis->sAdd('s', 'a');
$redis->sAdd('s', 'c');
var_dump($redis->sMembers('s'));
</pre>

Output:
<pre>
array(3) {
  [0]=>
  string(1) "c"
  [1]=>
  string(1) "a"
  [2]=>
  string(1) "b"
}
</pre>
The order is random and corresponds to redis' own internal representation of the set structure.

## getSet
##### *Description*
Sets a value and returns the previous entry at that key.
##### *Parameters*
*Key*: key

*STRING*: value

##### *Return value*
A string, the previous value located at this key.
##### *Example*
<pre>
$redis->set('x', '42');
$exValue = $redis->getSet('x', 'lol');	// return '42', replaces x by 'lol'
$newValue = $redis->get('x')'		// return 'lol'
</pre>

## randomKey
##### *Description*
Returns a random key.

##### *Parameters*
None.
##### *Return value*
*STRING*: an existing key in redis.

##### *Example*
<pre>
$key = $redis->randomKey();
$surprise = $redis->get($key);	// who knows what's in there.
</pre>

## select
##### *Description*
Switches to a given database.

##### *Parameters*
*INTEGER*: dbindex, the database number to switch to.

##### *Return value*
`TRUE` in case of success, `FALSE` in case of failure.
##### *Example*
(See following function)

## move
##### *Description*
Moves a key to a different database.

##### *Parameters*
*Key*: key, the key to move.

*INTEGER*: dbindex, the database number to move the key to.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.
##### *Example*

<pre>
$redis->select(0);	// switch to DB 0
$redis->set('x', '42');	// write 42 to x
$redis->move('x', 1);	// move to DB 1
$redis->select(1);	// switch to DB 1
$redis->get('x');	// will return 42
</pre>

## rename, renameKey
##### *Description*
Renames a key.
##### *Parameters*
*STRING*: srckey, the key to rename.

*STRING*: dstkey, the new name for the key.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.
##### *Example*
<pre>
$redis->set('x', '42');
$redis->rename('x', 'y');
$redis->get('y'); 	// → 42
$redis->get('x'); 	// → `FALSE`
</pre>

## renameNx
##### *Description*
Same as rename, but will not replace a key if the destination already exists. This is the same behaviour as setNx.

## setTimeout, expire
##### *Description*
Sets an expiration date (a timeout) on an item.

##### *Parameters*
*Key*: key. The key that will disappear.

*Integer*: ttl. The key's remaining Time To Live, in seconds.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.
##### *Example*
<pre>
$redis->set('x', '42');
$redis->setTimeout('x', 3);	// x will disappear in 3 seconds.
sleep(5);				// wait 5 seconds
$redis->get('x'); 		// will return `FALSE`, as 'x' has expired.
</pre>

## expireAt
##### *Description*
Sets an expiration date (a timestamp) on an item.

##### *Parameters*
*Key*: key. The key that will disappear.

*Integer*: Unix timestamp. The key's date of death, in seconds from Epoch time.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.
##### *Example*
<pre>
$redis->set('x', '42');
$now = time(NULL); // current timestamp
$redis->expireAt('x', $now + 3);	// x will disappear in 3 seconds.
sleep(5);				// wait 5 seconds
$redis->get('x'); 		// will return `FALSE`, as 'x' has expired.
</pre>

## keys, getKeys
##### *Description*
Returns the keys that match a certain pattern.
##### *Description*

##### *Parameters*
*STRING*: pattern, using '*' as a wildcard.

##### *Return value*
*Array of STRING*: The keys that match a certain pattern.

##### *Example*
<pre>
$allKeys = $redis->keys('*');	// all keys will match this.
$keyWithUserPrefix = $redis->keys('user*');
</pre>

## dbSize
##### *Description*
Returns the current database's size.

##### *Parameters*
None.

##### *Return value*
*INTEGER*: DB size, in number of keys.

##### *Example*
<pre>
$count = $redis->dbSize();
echo "Redis has $count keys\n";
</pre>

## auth
##### *Description*
Authenticate the connection using a password.
*Warning*: The password is sent in plain-text over the network.

##### *Parameters*
*STRING*: password

##### *Return value*
*BOOL*: `TRUE` if the connection is authenticated, `FALSE` otherwise.

##### *Example*
<pre>
$redis->auth('foobared');
</pre>

## bgrewriteaof
##### *Description*
Starts the background rewrite of AOF (Append-Only File)

##### *Parameters*
None.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.

##### *Example*
<pre>
$redis->bgrewriteaof();
</pre>

## slaveof
##### *Description*
Changes the slave status

##### *Parameters*
Either host (string) and port (int), or no parameter to stop being a slave.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure.

##### *Example*
<pre>
$redis->slaveof('10.0.1.7', 6379);
/* ... */
$redis->slaveof();
</pre>

## object
##### *Description*
Describes the object pointed to by a key.

##### *Parameters*
The information to retrieve (string) and the key (string). Info can be one of the following:

* "encoding"
* "refcount"
* "idletime"

##### *Return value*
*STRING* for "encoding", *LONG* for "refcount" and "idletime", `FALSE` if the key doesn't exist.

##### *Example*
<pre>
$redis->object("encoding", "l"); // → ziplist
$redis->object("refcount", "l"); // → 1
$redis->object("idletime", "l"); // → 400 (in seconds, with a precision of 10 seconds).
</pre>

## save
##### *Description*
Performs a synchronous save.

##### *Parameters*
None.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure. If a save is already running, this command will fail and return `FALSE`.

##### *Example*
<pre>
$redis->save();
</pre>

## bgsave

##### *Description*
Performs a background save.

##### *Parameters*
None.

##### *Return value*
*BOOL*: `TRUE` in case of success, `FALSE` in case of failure. If a save is already running, this command will fail and return `FALSE`.

##### *Example*
<pre>
$redis->bgSave();
</pre>
## lastSave

##### *Description*
Returns the timestamp of the last disk save.

##### *Parameters*
None.

##### *Return value*
*INT*: timestamp.

##### *Example*
<pre>
$redis->lastSave();
</pre>

## type

##### *Description*
Returns the type of data pointed by a given key.

##### *Parameters*
*Key*: key

##### *Return value*

Depending on the type of the data pointed by the key, this method will return the following value:  
string: Redis::REDIS_STRING  
set: Redis::REDIS_SET  
list: Redis::REDIS_LIST  
zset: Redis::REDIS_ZSET  
hash: Redis::REDIS_HASH  
other: Redis::REDIS_NOT_FOUND  

##### *Example*
<pre>
$redis->type('key');
</pre>

## append
##### *Description*
Append specified string to the string stored in specified key.

##### *Parameters*
*Key*
*Value*

##### *Return value*
*INTEGER*: Size of the value after the append

##### *Example*
<pre>
$redis->set('key', 'value1');
$redis->append('key', 'value2'); /* 12 */
$redis->get('key'); /* 'value1value2' */
</pre>

## getRange (substr also supported but deprecated in redis)
##### *Description*
Return a substring of a larger string 

##### *Parameters*
*key*
*start*
*end*

##### *Return value*
*STRING*: the substring 

##### *Example*
<pre>
$redis->set('key', 'string value');
$redis->getRange('key', 0, 5); /* 'string' */
$redis->getRange('key', -5, -1); /* 'value' */
</pre>

## setRange
##### *Description*
Changes a substring of a larger string.

##### *Parameters*
*key*  
*offset*  
*value*  

##### *Return value*
*STRING*: the length of the string after it was modified.

##### *Example*
<pre>
$redis->set('key', 'Hello world');
$redis->setRange('key', 6, "redis"); /* returns 11 */
$redis->get('key'); /* "Hello redis" */
</pre>

## strlen
##### *Description*
Get the length of a string value.

##### *Parameters*
*key*

##### *Return value*
*INTEGER*

##### *Example*
<pre>
$redis->set('key', 'value');
$redis->strlen('key'); /* 5 */
</pre>

## getBit
##### *Description*
Return a single bit out of a larger string

##### *Parameters*
*key*  
*offset*  

##### *Return value*
*LONG*: the bit value (0 or 1)

##### *Example*
<pre>
$redis->set('key', "\x7f"); // this is 0111 1111
$redis->getBit('key', 0); /* 0 */
$redis->getBit('key', 1); /* 1 */
</pre>

## setBit
##### *Description*
Changes a single bit of a string.

##### *Parameters*
*key*  
*offset*  
*value*: bool or int (1 or 0)  

##### *Return value*
*LONG*: 0 or 1, the value of the bit before it was set.

##### *Example*
<pre>
$redis->set('key', "*");	// ord("*") = 42 = 0x2f = "0010 1010"
$redis->setBit('key', 5, 1); /* returns 0 */
$redis->setBit('key', 7, 1); /* returns 0 */
$redis->get('key'); /* chr(0x2f) = "/" = b("0010 1111") */
</pre>

## flushDB

##### *Description*
Removes all entries from the current database.

##### *Parameters*
None.

##### *Return value*
*BOOL*: Always `TRUE`.

##### *Example*
<pre>
$redis->flushDB();
</pre>


## flushAll
##### *Description*
Removes all entries from all databases.

##### *Parameters*
None.

##### *Return value*
*BOOL*: Always `TRUE`.

##### *Example*
<pre>
$redis->flushAll();
</pre>

## sort
##### *Description*
##### *Parameters*
*Key*: key
*Options*: array(key => value, ...) - optional, with the following keys and values:
<pre>
    'by' => 'some_pattern_*',
    'limit' => array(0, 1),
    'get' => 'some_other_pattern_*' or an array of patterns,
    'sort' => 'asc' or 'desc',
    'alpha' => TRUE,
    'store' => 'external-key'
</pre>
##### *Return value*
An array of values, or a number corresponding to the number of elements stored if that was used.

##### *Example*
<pre>
$redis->delete('s');
$redis->sadd('s', 5);
$redis->sadd('s', 4);
$redis->sadd('s', 2);
$redis->sadd('s', 1);
$redis->sadd('s', 3);

var_dump($redis->sort('s')); // 1,2,3,4,5
var_dump($redis->sort('s', array('sort' => 'desc'))); // 5,4,3,2,1
var_dump($redis->sort('s', array('sort' => 'desc', 'store' => 'out'))); // (int)5
</pre>


## info
##### *Description*
Returns an associative array of strings and integers, with the following keys:

* redis_version
* arch_bits
* uptime_in_seconds
* uptime_in_days
* connected_clients
* connected_slaves
* used_memory
* changes_since_last_save
* bgsave_in_progress
* last_save_time
* total_connections_received
* total_commands_processed
* role


##### *Parameters*
None.

##### *Example*
<pre>
$redis->info();
</pre>

## ttl
##### *Description*
Returns the time to live left for a given key, in seconds. If the key doesn't exist, `FALSE` is returned.

##### *Parameters*
*Key*: key

##### *Return value*
Long, the time left to live in seconds.

##### *Example*
<pre>
$redis->ttl('key');
</pre>

## persist
##### *Description*
Remove the expiration timer from a key.

##### *Parameters*
*Key*: key

##### *Return value*
*BOOL*: `TRUE` if a timeout was removed, `FALSE` if the key didn’t exist or didn’t have an expiration timer.

##### *Example*
<pre>
$redis->persist('key');
</pre>

## mset, msetnx
##### *Description*
Sets multiple key-value pairs in one atomic command. MSETNX only returns TRUE if all the keys were set (see SETNX).

##### *Parameters*
*Pairs*: array(key => value, ...)

##### *Return value*
*Bool* `TRUE` in case of success, `FALSE` in case of failure.

##### *Example*
<pre>

$redis->mset(array('key0' => 'value0', 'key1' => 'value1'));
var_dump($redis->get('key0'));
var_dump($redis->get('key1'));

</pre>
Output:
<pre>
string(6) "value0"
string(6) "value1"
</pre>


## rpoplpush (redis >= 1.1)
##### *Description*
Pops a value from the tail of a list, and pushes it to the front of another list. Also return this value.

##### *Parameters*
*Key*: srckey  
*Key*: dstkey

##### *Return value*
*STRING* The element that was moved in case of success, `FALSE` in case of failure.

##### *Example*
<pre>
$redis->delete('x', 'y');

$redis->lPush('x', 'abc');
$redis->lPush('x', 'def');
$redis->lPush('y', '123');
$redis->lPush('y', '456');

// move the last of x to the front of y.
var_dump($redis->rpoplpush('x', 'y'));
var_dump($redis->lRange('x', 0, -1));
var_dump($redis->lRange('y', 0, -1));

</pre>
Output:
<pre>
string(3) "abc"
array(1) {
  [0]=>
  string(3) "def"
}
array(3) {
  [0]=>
  string(3) "abc"
  [1]=>
  string(3) "456"
  [2]=>
  string(3) "123"
}
</pre>

## brpoplpush
##### *Description*
A blocking version of `rpoplpush`, with an integral timeout in the third parameter.

##### *Parameters*
*Key*: srckey  
*Key*: dstkey  
*Long*: timeout

##### *Return value*
*STRING* The element that was moved in case of success, `FALSE` in case of timeout.


## zAdd
##### *Description*
Adds the specified member with a given score to the sorted set stored at key.
##### *Parameters*
*key*  
*score* : double  
*value*: string  

##### *Return value*
*Long* 1 if the element is added. 0 otherwise.
##### *Example*
<pre>
$redis->zAdd('key', 1, 'val1');
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 5, 'val5');
$redis->zRange('key', 0, -1); // array(val0, val1, val5)
</pre>

## zRange
##### *Description*
Returns a range of elements from the ordered set stored at the specified key, with values in the range [start, end]. start and stop are interpreted as zero-based indices:
0 the first element, 1 the second ...
-1 the last element, -2 the penultimate ...
##### *Parameters*
*key*  
*start*: long  
*end*: long  
*withscores*: bool = false  

##### *Return value*
*Array* containing the values in specified range. 
##### *Example*
<pre>
$redis->zAdd('key1', 0, 'val0');
$redis->zAdd('key1', 2, 'val2');
$redis->zAdd('key1', 10, 'val10');
$redis->zRange('key1', 0, -1); /* array('val0', 'val2', 'val10') */

// with scores
$redis->zRange('key1', 0, -1, true); /* array('val0' => 0, 'val2' => 2, 'val10' => 10) */
</pre>

## zDelete, zRem
##### *Description*
Deletes a specified member from the ordered set.
##### *Parameters*
*key*  
*member*  

##### *Return value*
*LONG* 1 on success, 0 on failure.
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zDelete('key', 'val2');
$redis->zRange('key', 0, -1); /* array('val0', 'val10') */
</pre>

## zRevRange
##### *Description*
Returns the elements of the sorted set stored at the specified key in the range [start, end] in reverse order. start and stop are interpretated as zero-based indices:
0 the first element, 1 the second ...
-1 the last element, -2 the penultimate ...

##### *Parameters*
*key*  
*start*: long  
*end*: long  
*withscores*: bool = false  

##### *Return value*
*Array* containing the values in specified range. 
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zRevRange('key', 0, -1); /* array('val10', 'val2', 'val0') */

// with scores
$redis->zRevRange('key', 0, -1, true); /* array('val10' => 10, 'val2' => 2, 'val0' => 0) */
</pre>

## zRangeByScore, zRevRangeByScore
##### *Description*
Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end]. Adding a parenthesis before `start` or `end` excludes it from the range. +inf and -inf are also valid limits. zRevRangeByScore returns the same items in reverse order, when the `start` and `end` parameters are swapped.
##### *Parameters*
*key*  
*start*: string  
*end*: string  
*options*: array  

Two options are available: `withscores => TRUE`, and `limit => array($offset, $count)`
##### *Return value*
*Array* containing the values in specified range. 
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zRangeByScore('key', 0, 3); /* array('val0', 'val2') */
$redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE); /* array('val0' => 0, 'val2' => 2) */
$redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1)); /* array('val2' => 2) */
$redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1)); /* array('val2') */
$redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1)); /* array('val2' => 2) */
</pre>

## zCount
##### *Description*
Returns the *number* of elements of the sorted set stored at the specified key which have scores in the range [start,end]. Adding a parenthesis before `start` or `end` excludes it from the range. +inf and -inf are also valid limits.
##### *Parameters*
*key*  
*start*: string  
*end*: string  

##### *Return value*
*LONG* the size of a corresponding zRangeByScore.  
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zCount('key', 0, 3); /* 2, corresponding to array('val0', 'val2') */
</pre>

## zRemRangeByScore, zDeleteRangeByScore
##### *Description*
Deletes the elements of the sorted set stored at the specified key which have scores in the range [start,end].
##### *Parameters*
*key*  
*start*: double or "+inf" or "-inf" string  
*end*: double or "+inf" or "-inf" string  

##### *Return value*
*LONG* The number of values deleted from the sorted set
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zRemRangeByScore('key', 0, 3); /* 2 */
</pre>

## zRemRangeByRank, zDeleteRangeByRank
##### *Description*
Deletes the elements of the sorted set stored at the specified key which have rank in the range [start,end].
##### *Parameters*
*key*  
*start*: LONG  
*end*: LONG  
##### *Return value*
*LONG* The number of values deleted from the sorted set
##### *Example*
<pre>
$redis->zAdd('key', 1, 'one');
$redis->zAdd('key', 2, 'two');
$redis->zAdd('key', 3, 'three');
$redis->zRemRangeByRank('key', 0, 1); /* 2 */
$redis->zRange('key', 0, -1, array('withscores' => TRUE)); /* array('three' => 3) */
</pre>

## zSize, zCard
##### *Description*
Returns the cardinality of an ordered set.
##### *Parameters*
*key*

##### *Return value*
*Long*, the set's cardinality
##### *Example*
<pre>
$redis->zAdd('key', 0, 'val0');
$redis->zAdd('key', 2, 'val2');
$redis->zAdd('key', 10, 'val10');
$redis->zSize('key'); /* 3 */
</pre>

## zScore
##### *Description*
Returns the score of a given member in the specified sorted set.
##### *Parameters*
*key*  
*member*  

##### *Return value*
*Double*
##### *Example*
<pre>
$redis->zAdd('key', 2.5, 'val2');
$redis->zScore('key', 'val2'); /* 2.5 */
</pre>

## zRank, zRevRank
##### *Description*
Returns the rank of a given member in the specified sorted set, starting at 0 for the item with the smallest score. zRevRank starts at 0 for the item with the *largest* score.
##### *Parameters*
*key*  
*member*  
##### *Return value*
*Long*, the item's score.
##### *Example*
<pre>
$redis->delete('z');
$redis->zAdd('key', 1, 'one');
$redis->zAdd('key', 2, 'two');
$redis->zRank('key', 'one'); /* 0 */
$redis->zRank('key', 'two'); /* 1 */
$redis->zRevRank('key', 'one'); /* 1 */
$redis->zRevRank('key', 'two'); /* 0 */
</pre>

## zIncrBy
##### Description
Increments the score of a member from a sorted set by a given amount.
##### Parameters
*key*  
*value*: (double) value that will be added to the member's score  
*member*  
##### Return value
*DOUBLE* the new value
##### Examples
<pre>
$redis->delete('key');
$redis->zIncrBy('key', 2.5, 'member1'); /* key or member1 didn't exist, so member1's score is to 0 before the increment */
					  /* and now has the value 2.5  */
$redis->zIncrBy('key', 1, 'member1'); /* 3.5 */
</pre>

## zUnion
##### *Description*
Creates an union of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument.
The third optionnel argument defines `weights` to apply to the sorted sets in input. In this case, the `weights` will be multiplied by the score of each element in the sorted set before applying the aggregation.
The forth argument defines the `AGGREGATE` option which specify how the results of the union are aggregated.
##### *Parameters*
*keyOutput*  
*arrayZSetKeys*  
*arrayWeights*  
*aggregateFunction* Either "SUM", "MIN", or "MAX": defines the behaviour to use on duplicate entries during the zUnion.  

##### *Return value*
*LONG* The number of values in the new sorted set.
##### *Example*
<pre>
$redis->delete('k1');
$redis->delete('k2');
$redis->delete('k3');
$redis->delete('ko1');
$redis->delete('ko2');
$redis->delete('ko3');

$redis->zAdd('k1', 0, 'val0');
$redis->zAdd('k1', 1, 'val1');

$redis->zAdd('k2', 2, 'val2');
$redis->zAdd('k2', 3, 'val3');

$redis->zUnion('ko1', array('k1', 'k2')); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */

/* Weighted zUnion */
$redis->zUnion('ko2', array('k1', 'k2'), array(1, 1)); /* 4, 'ko1' => array('val0', 'val1', 'val2', 'val3') */
$redis->zUnion('ko3', array('k1', 'k2'), array(5, 1)); /* 4, 'ko1' => array('val0', 'val2', 'val3', 'val1') */
</pre>

## zInter
##### *Description*
Creates an intersection of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument.
The third optionnel argument defines `weights` to apply to the sorted sets in input. In this case, the `weights` will be multiplied by the score of each element in the sorted set before applying the aggregation.
The forth argument defines the `AGGREGATE` option which specify how the results of the union are aggregated.
##### *Parameters*
*keyOutput*  
*arrayZSetKeys*  
*arrayWeights*  
*aggregateFunction* Either "SUM", "MIN", or "MAX": defines the behaviour to use on duplicate entries during the zInter.  

##### *Return value*
*LONG* The number of values in the new sorted set.
##### *Example*
<pre>
$redis->delete('k1');
$redis->delete('k2');
$redis->delete('k3');

$redis->delete('ko1');
$redis->delete('ko2');
$redis->delete('ko3');
$redis->delete('ko4');

$redis->zAdd('k1', 0, 'val0');
$redis->zAdd('k1', 1, 'val1');
$redis->zAdd('k1', 3, 'val3');

$redis->zAdd('k2', 2, 'val1');
$redis->zAdd('k2', 3, 'val3');

$redis->zInter('ko1', array('k1', 'k2')); 				/* 2, 'ko1' => array('val1', 'val3') */
$redis->zInter('ko2', array('k1', 'k2'), array(1, 1)); 	/* 2, 'ko2' => array('val1', 'val3') */

/* Weighted zInter */
$redis->zInter('ko3', array('k1', 'k2'), array(1, 5), 'min'); /* 2, 'ko3' => array('val1', 'val3') */
$redis->zInter('ko4', array('k1', 'k2'), array(1, 5), 'max'); /* 2, 'ko4' => array('val3', 'val1') */

</pre>

## hSet
##### *Description*
Adds a value to the hash stored at key. If this value is already in the hash, `FALSE` is returned.  
##### *Parameters*
*key*
*hashKey*
*value*

##### *Return value*
*LONG* `1` if value didn't exist and was added successfully, `0` if the value was already present and was replaced, `FALSE` if there was an error.
##### *Example*
<pre>
$redis->delete('h')
$redis->hSet('h', 'key1', 'hello'); /* 1, 'key1' => 'hello' in the hash at "h" */
$redis->hGet('h', 'key1'); /* returns "hello" */

$redis->hSet('h', 'key1', 'plop'); /* 0, value was replaced. */
$redis->hGet('h', 'key1'); /* returns "plop" */
</pre>

## hSetNx
##### *Description*
Adds a value to the hash stored at key only if this field isn't already in the hash.

##### *Return value*
*BOOL* `TRUE` if the field was set, `FALSE` if it was already present.

##### *Example*
<pre>
$redis->delete('h')
$redis->hSetNx('h', 'key1', 'hello'); /* TRUE, 'key1' => 'hello' in the hash at "h" */
$redis->hSetNx('h', 'key1', 'world'); /* FALSE, 'key1' => 'hello' in the hash at "h". No change since the field wasn't replaced. */
</pre>


## hGet
##### *Description*
Gets a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, `FALSE` is returned.  
##### *Parameters*
*key*
*hashKey*

##### *Return value*
*STRING* The value, if the command executed successfully
*BOOL* `FALSE` in case of failure


## hLen
##### *Description*
Returns the length of a hash, in number of items
##### *Parameters*
*key*

##### *Return value*
*LONG* the number of items in a hash, `FALSE` if the key doesn't exist or isn't a hash.
##### *Example*
<pre>
$redis->delete('h')
$redis->hSet('h', 'key1', 'hello');
$redis->hSet('h', 'key2', 'plop');
$redis->hLen('h'); /* returns 2 */
</pre>

## hDel
##### *Description*
Removes a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, `FALSE` is returned.  
##### *Parameters*
*key*
*hashKey*

##### *Return value*
*BOOL* `TRUE` in case of success, `FALSE` in case of failure


## hKeys
##### *Description*
Returns the keys in a hash, as an array of strings.

##### *Parameters*
*Key*: key

##### *Return value*
An array of elements, the keys of the hash. This works like PHP's array_keys().

##### *Example*
<pre>
$redis->delete('h');
$redis->hSet('h', 'a', 'x');
$redis->hSet('h', 'b', 'y');
$redis->hSet('h', 'c', 'z');
$redis->hSet('h', 'd', 't');
var_dump($redis->hKeys('h'));
</pre>

Output:
<pre>
array(4) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "c"
  [3]=>
  string(1) "d"
}
</pre>
The order is random and corresponds to redis' own internal representation of the set structure.

## hVals
##### *Description*
Returns the values in a hash, as an array of strings.

##### *Parameters*
*Key*: key

##### *Return value*
An array of elements, the values of the hash. This works like PHP's array_values().

##### *Example*
<pre>
$redis->delete('h');
$redis->hSet('h', 'a', 'x');
$redis->hSet('h', 'b', 'y');
$redis->hSet('h', 'c', 'z');
$redis->hSet('h', 'd', 't');
var_dump($redis->hVals('h'));
</pre>

Output:
<pre>
array(4) {
  [0]=>
  string(1) "x"
  [1]=>
  string(1) "y"
  [2]=>
  string(1) "z"
  [3]=>
  string(1) "t"
}
</pre>
The order is random and corresponds to redis' own internal representation of the set structure.

## hGetAll
##### *Description*
Returns the whole hash, as an array of strings indexed by strings.

##### *Parameters*
*Key*: key

##### *Return value*
An array of elements, the contents of the hash.

##### *Example*
<pre>
$redis->delete('h');
$redis->hSet('h', 'a', 'x');
$redis->hSet('h', 'b', 'y');
$redis->hSet('h', 'c', 'z');
$redis->hSet('h', 'd', 't');
var_dump($redis->hGetAll('h'));
</pre>

Output:
<pre>
array(4) {
  ["a"]=>
  string(1) "x"
  ["b"]=>
  string(1) "y"
  ["c"]=>
  string(1) "z"
  ["d"]=>
  string(1) "t"
}
</pre>
The order is random and corresponds to redis' own internal representation of the set structure.

## hExists
##### Description
Verify if the specified member exists in a key.
##### Parameters
*key*  
*memberKey*
##### Return value
*BOOL*: If the member exists in the hash table, return `TRUE`, otherwise return `FALSE`.
##### Examples
<pre>
$redis->hSet('h', 'a', 'x');
$redis->hExists('h', 'a'); /*  TRUE */
$redis->hExists('h', 'NonExistingKey'); /* FALSE */
</pre>

## hIncrBy
##### Description
Increments the value of a member from a hash by a given amount.
##### Parameters
*key*  
*member*  
*value*: (integer) value that will be added to the member's value  
##### Return value
*LONG* the new value
##### Examples
<pre>
$redis->delete('h');
$redis->hIncrBy('h', 'x', 2); /* returns 2: h[x] = 2 now. */
$redis->hIncrBy('h', 'x', 1); /* h[x] ← 2 + 1. Returns 3 */
</pre>

## hMset
##### Description
Fills in a whole hash. Non-string values are converted to string, using the standard `(string)` cast. NULL values are stored as empty strings.
##### Parameters
*key*  
*members*: key → value array  
##### Return value
*BOOL*  
##### Examples
<pre>
$redis->delete('user:1');
$redis->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
$redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
</pre>

## hMGet
##### Description
Retirieve the values associated to the specified fields in the hash.
##### Parameters
*key*  
*memberKeys* Array  
##### Return value
*Array* An array of elements, the values of the specified fields in the hash, with the hash keys as array keys.
##### Examples
<pre>
$redis->delete('h');
$redis->hSet('h', 'field1', 'value1');
$redis->hSet('h', 'field2', 'value2');
$redis->hmGet('h', array('field1', 'field2')); /* returns array('field1' => 'value1', 'field2' => 'value2') */
</pre>
