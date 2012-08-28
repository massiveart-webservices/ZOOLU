# Class: apache::php
#
# This class installs PHP for Apache
#
# Parameters:
# - $php_package
#
# Actions:
#   - Install Apache PHP package
#
# Requires:
#
# Sample Usage:
#
class apache::php {
    include apache::params

    package { 'apache_php_package':
        ensure => present,
        name   => $apache::params::php_package,
    }
  
    case $::operatingsystem {
        'ubuntu', 'debian': {
            package { ['php5-curl', 'php5-mysql', 'php5-tidy', 'php5-imagick', 'php-apc', 'php5-intl', 'phpmyadmin']:
                ensure => latest
            }
            a2mod { 'rewrite': ensure => present }
        }
    }
}
