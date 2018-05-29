nginx 自签CA证书强制证书验证 配置流程 密码 game
	使用root 权限
1.1 openssl目录准备
	一般情况下openssl的配置文件都在这个目录/etc/ssl/，so：
	mkdir /etc/ssl/CA
	cd /etc/ssl/CA
	mkdir root server client newcerts
	echo 01 > serial
	echo 01 > crlnumber
	touch index.txt
1.2 openssl配置准备
	修改openssl配置
	vi /etc/ssl/tls/openssl.cnf
	找到这句注释掉，替换为下面那句
	#default_ca      = CA_default
	default_ca      = CA
	把[ CA_default ]整个部分拷贝一份，改成上面的名字[ CA ]
	修改里面的如下参数：
	dir = /etc/ssl/CA
	certificate = $dir/root/ca.crt
	private_key = $dir/root/ca.key
	保存退出
2 创建ssl根级证书
	生成key：openssl genrsa -out /etc/ssl/CA/root/ca.key
	生成csr：openssl req -new -key /etc/ssl/CA/root/ca.key -out /etc/ssl/CA/root/ca.csr
	生成crt：openssl x509 -req -days 3650 -in /etc/ssl/CA/root/ca.csr -signkey /etc/ssl/CA/root/ca.key -out /etc/ssl/CA/root/ca.crt
	生成crl：openssl ca -gencrl -out /etc/ssl/CA/root/ca.crl -crldays 365
3 创建server证书
	生成key：openssl genrsa -out /etc/ssl/CA/server/server.key
	生成csr：openssl req -new -key /etc/ssl/CA/server/server.key -out /etc/ssl/CA/server/server.csr
	生成crt：openssl ca -in /etc/ssl/CA/server/server.csr -cert /etc/ssl/CA/root/ca.crt -keyfile /etc/ssl/CA/root/ca.key -out /etc/ssl/CA/server/server.crt -days 3650
	说明：
	1、这里生成的crt是刚才ca根级证书下的级联证书，其实server证书主要用于配置正常单向的https，所以不使用级联模式也可以：
		openssl rsa -in /etc/ssl/CA/server/server.key -out /etc/ssl/CA/server/server.key
		openssl x509 -req -in /etc/ssl/CA/server/server.csr -signkey /etc/ssl/CA/server/server.key -out /etc/ssl/CA/server/server.crt -days 3650
	2、-days 参数可根据需要设置证书的有效期，例如默认365天
4 创建client证书
	生成key：openssl genrsa -des3 -out /etc/ssl/CA/client/client.key 1024
	生成csr：openssl req -new -key /etc/ssl/CA/client/client.key -out /etc/ssl/CA/client/client.csr
	生成crt：openssl ca -in /etc/ssl/CA/client/client.csr -cert /etc/ssl/CA/root/ca.crt -keyfile /etc/ssl/CA/root/ca.key -out /etc/ssl/CA/client/client.crt -days 3650
	说明：
	1、这里就必须使用级联证书，并且可以重复该步骤，创建多套client证书
	2、生成crt时可能会遇到如下报错：
		openssl TXT_DB error number 2 failed to update database
	可参照这里进行操作。
		我使用的是方法一，即将index.txt.attr中unique_subject = no
====================================================================================
5 配置nginx
	这里只列出server段的关键部分：
	ssl_certificate  /etc/ssl/CA/server/server.crt;#server公钥
	ssl_certificate_key  /etc/ssl/CA/server/server.key;#server私钥
	ssl_client_certificate   /etc/ssl/CA/root/ca.crt;#根级证书公钥，用于验证各个二级client
	ssl_verify_client on;
	重启Nginx
====================================================================================
6 将客户端证书文件client.crt和客户端证书密钥文件client.key合并成客户端证书安装包client.pfx：
	openssl pkcs12 -export -in client.crt -inkey client.key -out client.pfx
====================================================================================
7 若服务端要求客户端认证，需要将pfx证书转换成pem格式
	openssl pkcs12 -clcerts -nokeys -in client.pfx -out client.pem    #客户端个人证书的公钥  
	openssl pkcs12 -nocerts -nodes -in client.pfx -out key.pem #客户端个人证书的私钥
	也可以转换为公钥与私钥合二为一的文件
	openssl pkcs12 -in  client.pfx -out all.pem -nodes  #客户端公钥与私钥，一起存在all.pem中
====================================================================================
8 证书测试
	执行curl命令  
	使用-k，是不对服务器的证书进行检查，这样就不必关心服务器证书的导出问题了。
	１、使用client.pem+key.pem
		curl -k --cert client.pem --key key.pem https://www.xxxx.com
	2、使用all.pem
		curl -k --cert all.pem  https://www.xxxx.com
