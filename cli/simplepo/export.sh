#!/bin/sh
# use a parameter for define which language is exported
 
php ../../public/simplepo/simplepo.php -n "$1" -o "../../application/website/default/language/website-$1.po"