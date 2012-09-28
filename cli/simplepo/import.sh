#!/bin/sh
# use a parameter for define which language is imported

php ../../public/simplepo/simplepo.php -n "$1" -i "../../application/website/default/language/website-$1.po"