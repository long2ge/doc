
docker pull rabbitmq:3.7.7-management

docker run -d --name rabbitmq3.7.7 
-p 5672:5672 
-p 15672:15672 
-v `pwd`/data:/var/lib/rabbitmq 
--hostname myRabbit 
-e RABBITMQ_DEFAULT_VHOST=my_vhost  
-e RABBITMQ_DEFAULT_USER=admin 
-e RABBITMQ_DEFAULT_PASS=admin df80af9ca0c9






version: '3'
services:
  rabbitmq:
    image: rabbitmq:management
    container_name: myrabbitmq
    hostname: myrabbitmq
    restart: always
    ports:
      - 5672:5672
      - 15672:15672
    volumes:
      - /var/docker/rabbitmq/data:/var/lib/rabbitmq
    environment:
      - RABBITMQ_DEFAULT_USER=admin
      - RABBITMQ_DEFAULT_PASS=123456
说明:
rabbitmq:3.8.6-management:后面带management是带web管理界面的
RABBITMQ_DEFAULT_USER:默认账号和密码是:guest
RABBITMQ_DEFAULT_PASS:设置密码



