group { 'puppet':
    ensure => present,
}

exec { 'apt-get update':
    command => '/usr/bin/apt-get update',
}

class { 'apache::php': }

apache::vhost { 'zoolu.local':
    port                => '80',
    docroot             => '/zoolu.local/public/',
    directory           => '/zoolu.local/',
    serveradmin         => 'tsh@massiveart.com',
    configure_firewall  => false,
    template            => 'apache/vhost-zoolu-dev.conf.erb',
}