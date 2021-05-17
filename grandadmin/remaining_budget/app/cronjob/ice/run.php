<?php

if (PHP_SAPI !== "cli") {
  exit("\n -- Working on cli only -- \n");
}

function bar(): iterable {
  return [1,2,3,4,5];
}

print_r(bar());

?>