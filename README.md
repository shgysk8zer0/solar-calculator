solar-calculator
================

*Solar Calculator for Ryan Tall @ vivint.solar*

[Solar Rates Calculator](<http://vivintsolar.chriszuber.com/> "Live Site")

## Contact
* [Email Developer](mailto:shgysk8zer0@gmail.com> "Email Developer")
* [Issues Page](<https://github.com/shgysk8zer0/solar-calculator/issues> "Report Bugs, request enhancements, etc")

## Forks
* [Main Repo](<https://github.com/shgysk8zer0/solar-calculator> "Main Repo")

## Install
**Database not included**

	git clone git://github.com/shgysk8zer0/solar-calculator.git

## Update

	git pull

## In case of update conflicts

	git mergetool

## Creating your Repo
**_Update addresses to SSH as needed and available_**

First, fork from the [Main Repository](<https://github.com/shgysk8zer0/solar-calculator> "Main Repo")

Copy your "clone URL"

*Install*

	git clone {clone URL}
	git remote add project_manager git://github.com/shgysk8zer0/solar-calculator.git

*Update*

	git pull project_manager master


## Other Info
### Tested Using:
* [Apache](<http://httpd.apache.org/download.cgi> "Download Apache") 2.4.7
* [PHP](<http://php.net/> "Download PHP") 5.5.9
* [MySQL](<http://dev.mysql.com/downloads/> "Download MySQL") 5.5.37
* [Ubuntu](<http://www.ubuntu.com/download> "Download Ubuntu") 13.10 64bit

### Required PHP Modules:
* [PDO](<http://php.net/manual/en/book.pdo.php>)

### Required Apache Modules:
* [mod_headers](<http://httpd.apache.org/docs/2.2/mod/mod_headers.html>)
* [mod_mime](<http://httpd.apache.org/docs/2.2/mod/mod_mime.html>)
* [mod_include](<http://httpd.apache.org/docs/2.2/mod/mod_include.html>)
* [mod_rewrite](<http://httpd.apache.org/docs/2.2/mod/mod_rewrite.html>)

### Recommended for CSS editing

Uses [CSS-variables](<https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_variables>) (currently Firefox 31+ only) in default stylesheet.
The [Node.js](<http://nodejs.org/> "Node.js Homepage") plugin [Myth](<http://www.myth.io> "Myth Homepage") creates fully vendor-prefixed CSS from the default CSS,
replaces variables with their values, as well as combining CSS files using @import
while still allowing the original to be used as CSS where supported

*Installation and configurations for Ubuntu*

	sudo apt-get install nodejs npm
	sudo ln -s /usr/bin/nodejs /usr/bin/node
	sudo npm install -g myth
Then to generate...

	myth stylesheets/style.css stylesheets/style.out.css
	myth -c stylesheets/combined.css stylesheets/combined.out.css

