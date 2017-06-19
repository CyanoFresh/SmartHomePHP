Solomaha Home
============================

This is core center for Solomaha Home system. Includes Web Panel and API.

Site built on Yii2 Framework, WebSocket server on Ratchet PHP

Main server built over WebSocket Server and references to [SmartHome Message Protocol](https://github.com/CyanoFresh/SHMP).

Author: Alex Solomaha <cyanofresh@gmail.com>

Feel free to contribute!

FEATURES
------------

- Real time updates via WebSocket protocol
- Universal Admin Panel for creating Items, managing Boards, configure application, etc
- Triggers (Events) with tasks (when triggered - doing tasks)
- API
- Web Panel


DEVICES & ROMS
------------

I've selected ESP8266 NodeMCU boards for low price and websocket module support. It uses lua firmware

- [NodeMCU #2](https://github.com/CyanoFresh/SmartHome-NodeMCU-2)
- [NodeMCU #3](https://github.com/CyanoFresh/SmartHome-NodeMCU-3)
- [WeMos D1 Mini #1](https://github.com/CyanoFresh/SmartHome-Wemos-1)
- ...

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
