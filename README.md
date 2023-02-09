![logo-iot-logo](https://user-images.githubusercontent.com/58616684/217911987-aae92a7a-2e6c-4dd5-81b0-16decc543ad3.png)

# IoT for WP 

IOT for WP is a WordPress Plugin to connect IOT Objects with WordPress and communicate with them throw protocols like MQTT.

### Tech Stack

- [Docker](https://www.docker.com/)
- [WordPress](https://de.wordpress.org/download/)
- [NODE](https://www.npmjs.com/)
- [WEBPACK](https://webpack.js.org/)
- [php-mqtt](https://github.com/php-mqtt/client)

## Setup

start the docker environment

```bash
 docker compose up -d
```

#### Website Url: <a href="http://127.0.0.1">localhost</a> 
#### User: iot4wp
#### Password: iot4wp
More variables in ´.env´ file

### Phpmyadmin
<a href="http://127.0.0.1:8081/">Phpmyadmin</a>
#### Databasename: wordpress
#### DB_User: root
#### DB_PASSWORD = db

### Mailhog
<a href="http://127.0.0.1:8025/">Mailhog</a>

### working with composer
work inside folder ´iot-for-wp´

```bash
  composer install
```
### run static Tests phpcs, phpcpd and phpmd
```bash
  composer run-static-tests
```

To fix phpcs errors
```bash
  composer phpcbf
```

#### phpstan tests
```bash
  composer phpstan
```

#### phpunit tests
```bash
  composer phpunit
```

### working with npm

Work inside of directory `iot-for-wp/frontend`:

- Install dependencies: `npm install`
- Start development build and watcher: `npm run dev`
- Start production build: `npm run build`
