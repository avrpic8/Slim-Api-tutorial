<?php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "slimApi");


define("USER_CREATE",100);
define("USER_NOT_CREATE",101);
define("USER_EXIST",102);


define("USER_AUTHENTICATED", 201);
define("USER_NOT_FOUND", 202);
define("USER_PASSWORD_NOT_MATCH", 203);


define("PASSWORD_CHANGED", 301);
define("PASSWORD_NOT_MATCH", 302);
define("PASSWORD_NOT_CHANGED", 303);