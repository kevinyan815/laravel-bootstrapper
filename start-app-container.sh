# set /etc/hosts in container
echo "10.120.17.57     cube.db.master\n
10.120.17.57     cube.db.slave\n
120.26.237.178   swoole.lenovo.com.cn\n
114.247.140.224 robotapi.lenovo.com\n
114.247.140.224 robotbd.lenovo.com.cn\n
219.142.122.168 sdiuattag.lenovo.com.cn\n
114.247.140.170 user.lenovo.com.cn\n
114.247.140.212 support.lenovo.com.cn\n
114.247.140.213 think.lenovo.com.cn\n
124.127.169.49 smg.lenovo.com.cn" >> /etc/hosts
# RUN php-fpm
php-fpm