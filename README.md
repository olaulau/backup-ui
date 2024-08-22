# description
display backup so that you can easilly check  your repo are running fine


# features
- 


# requirements
- borg (recent versions), on both client and server
- borgmatic on the client, if you use borg_client.sh script
- PHP (>= 7.4)


# install the borg client script (on the client)
cd bin/
rm borg_client.sh
wget https://raw.githubusercontent.com/olaulau/borg-ui/main/script/borg_client.sh
chmod u+x borg_client.sh
crontab -e
	0	*	*	*	*	bin/borg_client.sh <server> <user_name> <remo_name>


# create a user (on the server)
adduser <user>
adduser www-data <user>
systemctl restart apache2


# configure


# usage


# uses
gabrielelana/byte-units : size unit conversion & display


# screenshots
![repositories](doc/repositories.png)

![archives](doc/archives.png)
