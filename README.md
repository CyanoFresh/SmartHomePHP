Solomaha Home
============================

This is core center for Solomaha Home system. Includes Web Panel and API.
Main server built over WebSocket Server and references to [SmartHome Message Protocol](https://github.com/CyanoFresh/SHMP)


FEATURES
------------

- Real time updates via WebSocket protocol
- Universal Admin Panel for creating Items, managing Boards, configure application, etc
- Triggers (Events) with tasks (when triggered - doing tasks)
- API
- Web Panel


DEVICES & ROMS
------------

I've selected ESP8266 NodeMCU boards for low price and websocket module support. It uses lua firmaware

- [NodeMCU #2](https://github.com/CyanoFresh/SmartHome-NodeMCU-2)
- [NodeMCU #3](https://github.com/CyanoFresh/SmartHome-NodeMCU-3)
- [Wemos D1 Mini #1](https://github.com/CyanoFresh/SmartHome-Wemos-1)


REQUIREMENTS
------------

- PHP >= 5.4.0
- At lest 1 open port (8081)


INSTALLATION
------------

Get project files:

~~~
git clone https://github.com/CyanoFresh/SmartHome
cd SmartHome
composer install
php init
~~~

Then configure DB in config/db-local.php and run:

~~~
php yii migrate
~~~

**NOTES:**
- Check and edit the other files in the `config/` directory to customize your application as required.


To start the Main server:

~~~
php yii panel
~~~
