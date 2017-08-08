---
title: SuperSQL

language_tabs:
  - php
  
toc_footers:
 - <a href='https://github.com/ThreeLetters/SuperSQL/archive/master.zip'>Download</a>
 - <a href='https://github.com/ThreeLetters/SuperSQL/'>Github</a>
 
search: true
---

# SuperSQL

```php
<?php
// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$SuperSQL = new SuperSQL($dsn,$user,$pass);
?>
```

SlickInject and Medoo on steroids - The most advanced and compact library available.

### Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

### Main Features

1. Very small - 24.2KB one file (Unminified, `dist/SuperSQL.php`. Minified version: 11.1KB)
2. Simple and easy - Very easy to lean. We also provide a simple and advanced API
3. Compatability - Supports major SQL databases
4. Customisability - We offer multiple files for your needs
5. Efficiency - This module was built with speed in mind.
6. Complexity - This module allows you to make all kinds of complex queries
7. Security - This module prevents SQL injections.
8. Availability - This module is FREE. Licensed under the MIT license.

### Usage

```php
<?php
new SuperSQL($dsn,$user,$pass);
?>
```

You may either

1. Use the built file (`/dist/SuperSQL*.php`)
2. Use the library (include index.php)

### Build
To build this library, do 

`node builder.js`

It will build to `/dist/SuperSQL.php`

# Documentation

> Conditionals (WHERE & JOIN)

```php
<?php
$where = array(
 "arg1" => "val1", // AND arg1 = val1
 "[>>]arg2" => "val2", // AND arg2 > val2
 "[<<]arg3" => "val3", // AND arg3 < val3
 "[>=]arg4" => "val4", // AND arg4 >= val4
 "[<=]arg5" => "val5", // AND arg5 <= val5
 "[!=]arg6" => "val6", // AND arg6 != val6
 "[||]" => [ // Bind ||.
     "arg7" => "val7"
 ],
 "[&&][>>]" => [ // Bind >.
     "arg8" => "val8"
 ],
 "arg9" => ["val9a","val9b"]
);
?>
```

> Multi-Querying

```php
<?php
// Way 1

array(
array( // NOTE: While the all the arrays dont have to be identical, the first one should have the most items
"arg1"=> "val1",
"arg2"=> "val2"
),
array(
"arg2"=> "val3"
}
); // -> [["val1","val2"],["val1","val3"]] - Two queries

// Way 2 (only works with the data argument in INSERT and UPDATE)

array(
"arg1" => "val1",
"arg2" => array("val2","val3")
); // -> [["val1","val2"],["val1","val3"]] - Two queries


// Binds
array(
    array(
        "hello" => "world"
        "[||]" => array(
            "foo" => "bar",
            "num" => 123
        )
    ),
    array( // this also works
    "hello" => "hi!",
    "foo" => "lol"
    )
);

array(
    array( // Uh-oh - collision
        "[>>]lol" => 3
        "[||]bind1" => array(
            "foo" => "bar",
            "lol" =>  5
        ),
        "[>>]bind2" => array(
            "lols" => 231
        )
    ),
    array( // args will be preserved
    "lol" => 2,
    "bind1" => array(
        "foo" => "lol"
    )
    )
);
?>
```

> Multi-Table queries

```php
<?php
$SuperSQL->SELECT(["table1","table2"],[],[]);
?>
```

> Simple queries

```php
<?php
$SuperSQL->sSELECT($table,$columns,$where);

$SuperSQL->sINSERT($table,$data);

$SuperSQL->sUPDATE($table,$data,$where);

$SuperSQL->sDELETE($table,$where);
?>
```

> Type Casting

```php
<?php
$SuperSQL->INSERT("sensitive_data",[ // NOTE: Also works with any other query. ALSO NOTE: Types are case-insensitive
   "nuclear_codes[int]" => 138148347734, // Integer (Use [int] or [integer]
   "in_state_of_emergency[bool]" => false, // Boolean (Use [bool] or [boolean]
   "secret_files[lob]" => $file // Large Objects/Resources (Use [lob] or [resource])
   "fake_data[null]" => null // Null values (use [null])
]);
?>
```

> SQL functions/Raw

```php
<?php
$SuperSQL->INSERT("times",[
    "#time" => "NOW()"
]);
?>
```

> Alias

```php
<?php
$SuperSQL->SELECT("users",["user_id[id]"]);
?>
```

### Conditionals

Conditional statements are extremly customisable. WHERE and JOIN clauses are conditional statements. 

<aside class="success">
Available operators: `==`, `>>`, `<<`, `>=`, `<=`, `!=`
</aside>

<aside class="success">
Available joins: `&&`(AND), `||`(OR)
</aside>

<aside class="notice">
To make duplicate keys work for binds, you can add a name to the bind. Ex: `[&&]name`
</aside>

<aside class="notice">
You can also bind operators. Not just `&&`(AND) or `||`(OR). However, the join (&& or ||), must come first
</aside>

### Multi-queries
Multiqueries can be done too. This allows for highly efficient repetative queries. Note: Only the values of WHERE and INSERT work with this. VALUES, not KEYS.

<aside class="success">
The keys for the second or later multi-query arrays do not have to be  exact. For example, the first array might have `[>>]test[int]` as a key, but the second one can have just `test`. Anything within brakets  in the key will be discarded
</aside>

<aside class="success">
The keys do not have to be in order - SuperSQL will do that for you.
</aside>

<aside class="success">
Binds dont have to be reproduced in the second or more arrays. You can put it in global scope if you like.
</aside>

<aside class="notice">
If there are key collisions with binds - no problem! You can reproduce the bind in the second or more arrays too.
</aside>

### Multi-Table support
If you want to query multiple tables at once, put the tables in as an array

### Type Casting
If you want to set the type of the input, you can set it by adding `[type] (replace type with type)`.

<aside class="success">
Available types: `int`, `bool`, `lob`, `null`
</aside>

### SQL Functions/raw
If you want to use SQL functions such as `NOW()` or want use insert raw, unescaped data, add `#` at the beginning of the key

<aside class="notice">
Programming guide: Please try to avoid SQL functions. If you can, use php. (EX:, replace `NOW()` with `date('Y-m-d H:i:s')`)
</aside>

### Alias
You can use an alias for columns and/or tables by adding `[aliasname]`.

<aside class="notice">
Aliases go after the key, not before ex: `[alias]key` is WRONG, `key[alias]` is RIGHT
</aside>

### Simple
If you are making simple queries, you may use simple functions to boost performance. Use simple functions by attatching an `s` in front of the function. The syntax is very similar to SlickInject.

<aside class="success">
Use simple API as much as you can! It is lightning fast!
</aside>

### Cache
Performance is boosted for a query if an identical query before it (with different values [EG where vals, join, insert]), is made right before. You can also clear the cache by doing: `$SuperSQL->clearCache()`

### Raw Queries
Raw queries can be made using `$SuperSQL->query($query)`.

<aside class="notice">
Programming guide: If you can, use raw queries. For example, theres nothing sensitive in `SELECT * FROM `table`, just do that instead of `$SuperSQL->SELECT("table");`
</aside>

## Responses

> Error handling

```php
<?php
$Response = $SuperSQL->select("test",[],[
    "#a" => "WHERE SELECT INSERT LOL" // raw
]); // SELECT * FROM `test` WHERE `a` = WHERE SELECT INSERT 

echo json_encode($Response->getData()); // NULL

echo json_encode($response->error()); // ["42000",1064,"You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'WHERE SELECT INSERT LOL' at line 1"]
?>
```

> Iterator usage

```php
<?php
$Response = $SuperSQL->select("test",[],[
    "a" => "WHERE SELECT INSERT LOL"
]); // SELECT * FROM `test` WHERE `a` = 'WHERE SELECT INSERT' 

echo $response->error(); // FALSE

while ($row = $response->next()) { // Use the iterator to iterate through rows easily

.... // Do some stuff

}

$response->reset(); // Reset iterator so you can do the above code again
?>
```

> Itit

When you make a query, SuperSQL will return a SQLResponse object.

### Response->getData()
Get all rows

### Response->error() 
Returns error object if there is one. False otherwise

### Response->getAffected()
Get number of rows affected by the query

### Response->next()
Get next row

### Response->reset()
Reset iterator.

<aside class="notice">
All rows are retrieved and stored at object initialisation. `Response->next()` or `Response->reset()` does not affect the database or connection
</aside>

## SELECT
```php
<?php
$SuperSQL->SELECT("horizon", [], [], array(
    // [>>] - Right join
    // [<<] - Left join
    // [><] - Inner join (Default)
    // [<>] - Full join
    "[><]meteors" => array("horizon.object" => "meteors.object"), // JOIN
),5); // only 5 rows
?>
```

**SuperSQL->SELECT($table, $columns, $where[,$join[, $limit);**

* `(String|Array)table` - Table(s) to query
* `(Array)columns` - Array of columns to return. `[]` will query using the `*` selector.
* `(Array)where` - Array of conditions for WHERE (See above for documentation on WHERE)
* `(Array|Null)join` - Array of conditions for JOIN. Usage below
* `(Int)limit` - Number of rows to retrieve. Usage below.

<aside class="notice">
You may also put `DISTINCT`, in the top of the columns array to use the DISTINCT keyword. Other available keywords include: `INSERT INTO table` and `INTO table` (replace table with table name)
</aside>

<aside class="notice">
You may also do `SuperSQL->SELECT($table, $columns, $where, $limit)`
</aside>

## INSERT
```php
<?php
$SuperSQL->INSERT("table",array(
"hello" => "world",
"SuperSQL" => "rocks"
));
?>
```

**SuperSQL->INSERT($table, $data);**

* `(String|Array)table` - Table(s) to insert to
* `(Array)data` - Data to insert

## UPDATE
```php
<?php
$SuperSQL->UPDATE("citizens",array(
"SuperSQL" => "To the rescue!"
),array(
"needs_help" => 1
));
?>
```

**SuperSQL->UPDATE($table, $data, $where);**

* `(String|Array)table` - Table(s) to insert to
* `(Array)data` - Data to update
* `(Array)where` - Conditional statements

<aside class="notice">
You can also use some special operators on the data argument. Example: `$SuperSQL->UPDATE("test", ["increment[+=]" => 1]);`. Available: `+=`, `-=`, `/=`, `*=`.
</aside>

## DELETE

```php
<?php
$SuperSQL->DELETE("persons",
"is_villain" => 1
));
?>
```

**SuperSQL->DELETE($table, $where);**

* `(String|Array)table` - Table(s) to insert to
* `(Array)where` - Conditional statements

## Transactions

```php
<?php
$SuperSQL->transact(function () {

$SuperSQL->DELETE("citizens",[
    "near_explosion" => 1
]);

return false; // SuperSQL to the rescue! He reversed time (the query)
});
?>
```

**SuperSQL->transact($call);**

* `(Callable)call` - Transaction Function. Return false to rollback

## Simple Documentation

```php
<?php
// Gets data
$SuperSQL->SELECT($table, $columns, $where);
// Inserts/adds data to database
$SuperSQL->sINSERT($table, $data);
// Updates data in database
$SuperSQL->sUPDATE($table, $data, $where);
// Deletes data in database
$SuperSQL->DELETE($table, $where);
?>
```

> sSELECT

```php
<?php
$SuperSQL->sSELECT("citizens",["name","age"],[ // SELECT `name`, `age` FROM `citizens` WHERE `in_trouble` = 1
    "in_trouble" => 1
]);
?>
```

> sINSERT

```php
<?php
$SuperSQL->sINSERT("message_board",array( // INSERT INTO `message_board` (`title`, `SuperSQL`) VALUES ('SuperSQL Saves The Day', 'SuperSQL rocks!')
    "title" => "SuperSQL Saves The Day",
    "message" => "SuperSQL rocks!"
));
?>
```

> sUPDATE

```php
<?php
$SuperSQL->sUPDATE("developers",[ // UPDATE `developers` SET `is_happy` = 1, `reason` = 'Becaz SuperSQL is awesome!' WHERE `is_happy` = 0
"is_happy" => 0,
],[
"is_happy" => 1,
"reason" => "Becaz SuperSQL is awesome!"
]);
?>
```

> sDELETE

```php
<?php
$SuperSQL->sDELETE("hackers",[ // DELETE FROM `hackers` WHERE `status` = 'Tried to SQL Inject attack a site' AND `encountered` = 'SuperSQL'
    "status" => "Tried to SQL Inject attack a site",
    "encountered" => "SuperSQL"
]);
?>
```

Simple API is for basic querying. It allows of lightning-fast, simple and easy querying. Unlike the advanced api, you cannot:

* You cannot use other opererators besides `=` (Equal to)
* No binds - Only `AND` is used
* No multi-querying
* No cache
* No type casting

### sSELECT
**SuperSQL->SELECT($table, $columns, $where[, $append);**

* `(String)table` - Table to query
* `(Array)columns` - Columns to return. `[]` is `*`
* `(Array)where` - Conditional statements
* `(String)append` - Append to sql (Optional)

### sINSERT
**SuperSQL->sINSERT($table, $data);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to insert

### sUPDATE

**SuperSQL->sUPDATE($table, $data, $where);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to update
* `(Array)where` - Conditional statements

### sDELETE

**SuperSQL->DELETE($table, $where);**

* `(String)table` - Table to insert to
* `(Array)where` - Conditional statements

## Helper Functions
SuperSQL provides some helper functions to allow for easier access.

<aside class="notice">
If your using the built/compiled file, you must include `dist/SuperSQL_helper.php` too
</aside>

### SQLHelper::connect
Connect easily with any database.

**connect($host,$user,$pass,$options)**

* `(String)host` - Host to connect to
* `(String)user` - Username
* `(String)pass` - Password
* `(Array)options` - Options (Optional)

**connect($host,$user,$pass,$dbtype)**

* `(String)host` - Host to connect to
* `(String)user` - Username
* `(String)pass` - Password
* `(String)dbtype` - Database type (`mysql`,`pgsql`,`sybase`,`oracle`)

**connect($host,$user,$pass,$dsn)**

* `(String)host` - Host to connect to
* `(String)user` - Username
* `(String)pass` - Password
* `(String)dbtype` - DSN string


### SQLHelper()
Initialise the helper

**new SQLHelper($SuperSQL)**

* `(SuperSQL)SuperSQL` - SuperSQL object

**new SQLHelper($host,$user,$pass,$options)**

* `(String)host` - Host to connect to
* `(String)user` - Username
* `(String)pass` - Password
* `(Array)options` - Options (Optional)

**new SQLHelper($connect)**

* `(Array)connect` - Array of connection data - Uses Helper::connect

#### Change
**$SQLHelper->change($id)**

Changes the selected connection

* `(Int)id` - Connection id

#### getCon
**$SQLHelper->getCon($all = false)**

* `(Bool)all` - if true, will return all connections. If not, then will only return the selected one

#### get
**$SQLHelper->get($table,$columns,$where,$join)**

Gets the first row

#### create
**$SQLHelper->create($table,$data)**

Creates a table

* `(String)table` - Table name to create
* `(Array)data` - Array of keys and types

#### drop
**$SQLHelper->drop($table)**

Removes a table

* `(String)table` - Table name to delete

## Super Advanced

> Logging

```php
<?php
$SuperSQL->dev(); // Turn on logging

... // do some queries

echo json_encode($a->getLog()); // Get some data
?>
```

### Logging
You find something isnt working for your website. You either:

1. Rage quit, break everything, scream "I #%@&$@! HATE SUPERSQL"
2. Use the log function to figure out whats wrong - LIKE A CIVILISED PERSON

To enable the logger, do `$SuperSQL->dev()`. Then make some queries.


Afterwords, do `$SuperSQL->getLog()` to get the log.

### Da log - What does it mean?

```php
<?php
$log = [
    [
    "fromCache" // If this is there, it means it used the cache.
    "SELECT * FROM `table` WHERE `test` = ?", // SQL base
    "s", // String of arg types
    [[24424,1]], // Array of initial values with types. In this case, the value is 24424 and the type is an INT (PDO::PARAM_INT)
    [["0":234]] // Multi-query array
    ]
]
?>
```

* fromCache - If there, it means it reused an old query for efficiency
* SQL - SQL base. `?` are replaced with values
* typeString - String of types, mysqli_bind_param style.
* Values - Initial values with types. NOTE: This is bound onto the SQL base string
* Insert - Multi-query array
