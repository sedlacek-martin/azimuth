#!/bin/sh

usermod -u ${HOST_UID} cocorico &> /dev/null
groupmod -g ${HOST_UID} cocorico &> /dev/null

chown cocorico:cocorico -R /cocorico &> /dev/null
chown cocorico:cocorico -R /home/cocorico &> /dev/null

mkdir /var/log/supervisor
touch /var/log/supervisor/bootstrap.log

supervisord -c /etc/supervisord.conf

# generate host keys if not present
# ssh-keygen -A

# do not detach (-D), log to stderr (-e), passthrough other arguments
# exec /usr/sbin/sshd -D -e "$@"

sleep 10

tail -f /var/log/supervisor/bootstrap.log
