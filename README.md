# Grunt PHP Linter

This is a simple Grunt plugin to help lint PHP code and enforce consistent coding standards.

### Installation

Add the following to the Node `package.json`:
```
"devDependencies": {
        ...
        "grunt-blobfolio": "git+ssh://git@github.com:brightbrightgreat/grunt-php.git",
        ...
}
```

Install the Node modules the usual way:
```
npm i
```

Finally, add a Grunt task to the project (e.g. `grunt/blobphp.js`) containing the following:
```js
module.exports = {
    check: {
        src: process.cwd(),
        options: {
            colors: true,
            warnings: true
        }
    },
    fix: {
        src: process.cwd(),
        options: {
            fix: true
        },
    }
};
```

### Use

To run the linter from the command line, run:
```
grunt blobphp:check
```

To have the linter attempt to automatically fix any issues it can, run:
```
grunt blobphp:fix
```
