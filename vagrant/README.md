# ZOOLU Vagrant Setup #

### Requirements ###

* VirtualBox >=4.1.x
* Vagrant >=1.0.3

### Setup Vagrant Virtual Environment ###

	vagrant box add zoolu-precise64 http://www.zoolucms.com/vagrant/zoolu-precise64.box
	vagrant up

* Point **zoolu.local** to config.vm.network IP
* Setup DB (phpmyadmin is already installed on the box)
