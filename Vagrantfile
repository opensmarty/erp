# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu1404_64"
  config.vm.hostname = "dev"
  config.vm.network "forwarded_port", guest: 22, host: 2222 ,id: 'ssh'
  config.vm.network "forwarded_port", guest: 80, host: 8888 ,id: 'nginx'
  config.vm.network "forwarded_port", guest: 8080, host: 8889 ,id: 'apache'
  config.vm.network "forwarded_port", guest: 3306, host: 3307 ,id: 'mysql'
  
  config.vm.network "private_network", ip: "192.168.2.20"
  config.vm.synced_folder "D:/var/www", "/var/www/html", :ntf=>true 
 
  config.vm.provider "virtualbox" do |vb|
    #修改vb.name的值
    vb.customize ["modifyvm", :id, "--name", "ubuntu_dev"]
    #如修改显存，缺省为8M，如果启动桌面，至少需要10M，如下修改为16M：
    #vb.customize ["modifyvm", :id, "--vram", "16"]
    #调整虚拟机的内存
    vb.customize ["modifyvm", :id, "--memory", "512"]
    #指定虚拟CPU个数
    vb.customize ["modifyvm", :id, "--cpus", "2"]
  end
  
  # config.vm.provision "shell", inline: <<-SHELL
  #   sudo apt-get update
  #   sudo apt-get install -y git git-core openssh-server openssh-client 
  #   git config --global user.name "wangzq"   
  #   git config --global user.email "540079673@163.com"   
  #   sudo apt-get install -y apache2
  # SHELL
 
end
