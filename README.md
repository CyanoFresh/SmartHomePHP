Smart Home
============================

This is core center for Solomaha Home system. Includes Web Panel and API.

Site built on Yii2 Framework, WebSocket server on Ratchet PHP

Main server built over WebSocket Server and references to [SmartHome Message Protocol](https://github.com/CyanoFresh/SHMP).

Author: Alex Solomaha <cyanofresh@gmail.com>

Feel free to contribute!

FEATURES
------------

- Real time via WebSocket protocol
- Universal Admin Panel for creating Items, managing Boards, configure application, etc
- Event system with multiple conditions and tasks
- HTTP REST API
- Web Panel for any device

REQUIREMENTS
------------

- PHP >= 5.4.0
- At least 1 open port for websockets (8081)


INSTALLATION
------------

Get project files:

~~~
git clone https://github.com/CyanoFresh/SmartHome
cd SmartHome
composer install
php init
~~~

Configure DB in config/db-local.php and run:

~~~
php yii migrate
~~~


To start the Main server:

~~~
php yii start-core-server
~~~

Enable WSS
----

Install and enable proxy module:

~~~
a2enmod proxy_wstunnel
~~~

Add this lines in the secured host conf body:

~~~
ProxyPreserveHost On
ProxyPass /wss ws://127.0.0.1:8081
ProxyPassReverse  /wss ws://127.0.0.1:8081
~~~

Where `8081` is your server's port
