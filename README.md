**Moving this project from my ~/stuff, there is a lot to do here**

**See https://trello.com/b/EUUREZv4/release-that**

![Automated release system for PHP](.github/logo.svg)
# Release that :rocket: !

[list of features here]
[Screencast here]

## Table of contents
* [Introduction](#introduction)
* [Features](#features)
* [Getting started](#getting-started)
* [Configuration](#configuration)
* [Security](#security)
* [Credit](#credits)
* [License](#licensing)

## Introduction

// Why, how, when ?

## Getting started

Install `release-that` with 
```bash
composer require felixdorn/release-that
``` 
or globally with 
```bash
composer global require felixdorn/release-that
``` 

Next: [Configuration](#configuration)

## Configuration

### Supported filenames
* `.release.json`,
* `.release-that`,
* `.release-that.json`,
* `iMs0D4rK.release.json`

Each one will take precedence to the one before. So if a .release-that it will be used instead of .release-json or .release-json, etc.

.iMs0D4rk.release.json > .release-that.json > .release-that > .release.json

### Default config
```json
{
    "commit": {
        "message": "chore: release {version}",
        "empty": false,
        "stageAll": true
    },
    "push": false,
    "tag": {
        "name": "{version}",
        "message": "Release Tag {version}"
    },
    "tasks": {
        "beforeAll": "",
        "afterAll": "",
        "beforeRelease": "",
        "afterRelease": "",
        "beforeCommit": "",
        "afterCommit": "",
        "beforeTag": "",
        "afterTag": "",
        "beforePush": "",
        "afterPush": ""
    }
}
```

## Usage

Once you configured `release-that`.
Just run it to release your next version.

## Security

If you discover any security related issues, please email github@felixdorn.fr instead of using the issue tracker.

## Credits
* [Félix Dorn](https://felixdorn.fr)

## Licensing
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>
