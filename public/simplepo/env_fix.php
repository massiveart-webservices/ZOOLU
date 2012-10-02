<?php
if (get_magic_quotes_gpc()) {
        $in = array(&$_GET, &$_POST, &$_COOKIE);
        while (list($k,$v) = each($in)) {
                foreach ($v as $key => $val) {
                        if (!is_array($val)) {
                                $in[$k][$key] = stripslashes($val);
                                continue;
                        }
                        $in[] =& $in[$k][$key];
                }
        }
        unset($in);
}

