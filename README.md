# Avantlink sample project - Tasks

This is a demo project written for Avantlink that will demonstrate simple CRUD operations for 
tasks.  Written using AngularJS and PHP mostly, but also leveraging some elements of the npm 
package manager for NodeJS.

## Prerequisites

- Apache2               >= 2.4.7
- PHP                   >= 5.3
- Node                  >= 0.10.42
- NPM                   >= 1.4.29


## Setup

A number of node.js tools are used to setup the environment, so you must have node.js and
its package manager (npm) installed.  You can get them from [http://nodejs.org/](http://nodejs.org/).

### Install Dependencies

We have two kinds of dependencies in this project: tools and angular framework code.  The tools help
us manage and test the application.

* We get the tools we depend upon via `npm`, the [node package manager][npm].
* We get the angular code via `bower`, a [client-side code package manager][bower].

I have preconfigured `npm` to automatically run `bower` so we can simply do:

    npm install

Behind the scenes this will also call `bower install`.  You should find that you have two new
folders in your project.

* `node_modules` - contains the npm packages for the tools we need
* `app/bower_components` - contains the angular framework files

*Note that the `bower_components` folder would normally be installed in the root folder but
angular-seed changes this location through the `.bowerrc` file.  Putting it in the app folder makes
it easier to serve the files by a webserver.*

### Database

To get the PostgreSQL database loaded and ready for use, it will require that a database be created
and the schema and data imported into it.  All of the SQL needed to create the database and user
that will be used to connect from the code base, is pre-written and available in the document root
as a file named 'schema.sql'.  This will need to be executed by a user that has rights within 
PostgreSQL to create databases and roles.  Here is one of many possible use cases:

    psql -U root -W < schema.sql

### Apache

You will also need to create a web server virtual host configuration to pull the site up with.
Following is an example configuration of the Apache config:

    <VirtualHost *:80>
        ServerName avantlink.php5
        ServerAdmin daveywalbeck@gmail.com
    
        <IfModule mod_php5.c>
            php_value   include_path            ".:/www/avantlink/app:/www/avantlink/api_service:/usr/share/php:/usr/share/php5"
            php_value   date.timezone           "America/Boise"
            php_flag    short_open_tag          On
            php_flag    display_error           On
            php_value   error_reporting         "E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED"
        </IfModule>
        SetEnv ENV development
    
        DocumentRoot /www/avantlink
        <Directory /www/avantlink>
            Options FollowSymLinks
            AllowOverride all
            DirectoryIndex index.php index.html
            Order allow,deny
            Allow from all
            Require all granted
        </Directory>
    
        ErrorLog /var/log/apache2/avantlink_error_log
        CustomLog /var/log/apache2/avantlink_access_log combined
    </VirtualHost>

If using a Debian based system, remember to enable the virtual host.  Also if using a fake domain
name, much like in the example configuration above, you'll also need to create an entry in your
    /etc/hosts      (on linux - windows is something like c:\windows\system32\drivers\etc\hosts)
file with and entry for the domain you choose to use.  The entry should look something like the
following example:

    127.0.0.1       avantlink.php5


## Testing

There are two kinds of tests in the angular-seed application: Unit tests and End to End tests.

### Running Unit Tests

The angular-seed app comes preconfigured with unit tests. These are written in
[Jasmine][jasmine], which we run with the [Karma Test Runner][karma]. We provide a Karma
configuration file to run them.

* the configuration is found at `karma.conf.js`
* the unit tests are found next to the code they are testing and are named as `..._test.js`.

The easiest way to run the unit tests is to use the supplied npm script:

```
npm test
```

This script will start the Karma test runner to execute the unit tests. Moreover, Karma will sit and
watch the source and test files for changes and then re-run the tests whenever any of them change.
This is the recommended strategy; if your unit tests are being run every time you save a file then
you receive instant feedback on any changes that break the expected code functionality.

You can also ask Karma to do a single run of the tests and then exit.  This is useful if you want to
check that a particular version of the code is operating as expected.  The project contains a
predefined script to do this:

```
npm run test-single-run
```


### End to end testing

The angular-seed app comes with end-to-end tests, again written in [Jasmine][jasmine]. These tests
are run with the [Protractor][protractor] End-to-End test runner.  It uses native events and has
special features for Angular applications.

* the configuration is found at `e2e-tests/protractor-conf.js`
* the end-to-end tests are found in `e2e-tests/scenarios.js`

Protractor simulates interaction with our web app and verifies that the application responds
correctly. Therefore, our web server needs to be serving up the application, so that Protractor
can interact with it.

```
npm start
```

In addition, since Protractor is built upon WebDriver we need to install this.  The angular-seed
project comes with a predefined script to do this:

```
npm run update-webdriver
```

This will download and install the latest version of the stand-alone WebDriver tool.

Once you have ensured that the development web server hosting our application is up and running
and WebDriver is updated, you can run the end-to-end tests using the supplied npm script:

```
npm run protractor
```

This script will execute the end-to-end tests against the application being hosted on the
development server.


## Updating Angular

Previously we recommended that you merge in changes to angular-seed into your own fork of the project.
Now that the angular framework library code and tools are acquired through package managers (npm and
bower) you can use these tools instead to update the dependencies.

You can update the tool dependencies by running:

```
npm update
```

This will find the latest versions that match the version ranges specified in the `package.json` file.

You can update the Angular dependencies by running:

```
bower update
```

This will find the latest versions that match the version ranges specified in the `bower.json` file.


## Loading Angular Asynchronously

The angular-seed project supports loading the framework and application scripts asynchronously.  The
special `index-async.html` is designed to support this style of loading.  For it to work you must
inject a piece of Angular JavaScript into the HTML page.  The project has a predefined script to help
do this.

```
npm run update-index-async
```

This will copy the contents of the `angular-loader.js` library file into the `index-async.html` page.
You can run this every time you update the version of Angular that you are using.


## Contact

For more information on AngularJS please check out http://angularjs.org/

[git]: http://git-scm.com/
[bower]: http://bower.io
[npm]: https://www.npmjs.org/
[node]: http://nodejs.org
[protractor]: https://github.com/angular/protractor
[jasmine]: http://jasmine.github.io
[karma]: http://karma-runner.github.io
[travis]: https://travis-ci.org/
[http-server]: https://github.com/nodeapps/http-server
