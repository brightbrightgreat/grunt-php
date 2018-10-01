'use strict';

/*
 * grunt-php
 *
 * This wraps (and includes) phpcs and phpcbf to verify
 * PHP code follows the BBG coding standards.
 *
 * Copyright (c) 2018 Bright Bright Great
 * https://brightbrightgreat.com
 * Licensed under the WTFPL license.
 */

module.exports = function(grunt) {

	var fs = require('fs'),
		path = require('path'),
		pluginPath = path.dirname(__dirname),
		nodePath = path.dirname(pluginPath),
		phpcsPath = path.resolve(pluginPath + '/lib/vendor/bin/phpcs'),
		phpcbfPath = path.resolve(pluginPath + '/lib/vendor/bin/phpcbf'),
		debugPath = path.resolve(process.cwd() + '/.blobphp.json'),
		exec = require('child_process').execSync,
		execute,
		defaults = {
			fix: false,
			warnings: true,
			colors: true
		};

	//faster phpcs runner
	grunt.task.registerMultiTask('blobphp', 'Blobfolio PHP coding standards: Review', function() {
		//sort out runtime options
		var files = [].concat.apply([], this.files.map(function(mapping) { return mapping.src; })).sort(),
			options = this.options(defaults),
			cmd = ' --standard=Blobfolio --encoding=utf8 --extensions=php %REPORT% ',
			fix = !!options.fix,
			warnings = !!options.warnings,
			colors = !!options.colors,
			x;

		//disable warnings?
		if(!warnings){
			cmd += '-n ';
		}

		//enable colored output?
		if(colors){
			cmd += '--colors ';
		}

		//remove duplicate files
		files = files.filter(function(file, position) {
			return typeof file === 'string' && (!position || file !== files[position - 1]);
		});

		//expand paths
		for(x=0; x<files.length; x++) {
			files[x] = path.resolve(files[x]);
		}

		if(!files.length)
			throw new Error("No sources were specified.");

		//add files to command
		cmd += '"' + files.join('" "') + '"';

		//review only
		try {
			exec(phpcsPath + cmd.replace('%REPORT%','--report=full'), {stdio: [1,2,3]});
			if(warnings){
				grunt.log.oklns('Congratulations! There are no errors or warnings.');
			}
			else {
				grunt.log.oklns("No coding standard errors were detected.\nEnable 'warnings' to ensure full standards compliance.");
			}
			return;
		} catch(ex){
			//if an error response is returned, recall the operation with JSON output
			//so it can be parsed, giving us a chance to make an intelligible error
			//message
			try {
				exec(phpcsPath + cmd.replace('%REPORT%','--report=json') + ' >"' + debugPath + '" 2>&1');
			} catch(Ex) {
				if(fs.existsSync(debugPath)){
					var json = JSON.parse(fs.readFileSync(debugPath, 'utf8')),
						debug = {
							'filesTotal' : Object.keys(json.files).length,
							'filesBad': 0,
							'errors' : json.totals.errors,
							'warnings' : json.totals.warnings,
							'fixable' : json.totals.fixable,
							'unique': []
						},
						msg = [];

					//dive a bit deeper
					for(x in json.files){
						if(json.files[x].warnings > 0 || json.files[x].errors > 0){
							debug.filesBad++;
							for(var y=0; y<json.files[x].messages.length; y++){
								if(debug.unique.indexOf(json.files[x].messages[y].message) === -1){
									debug.unique.push(json.files[x].messages[y].message);
								}
							}
						}
					}

					//build the message
					msg.push('Issue' + (debug.errors + debug.warnings > 1 ? 's' : '') + ' found in ' + debug.filesBad + '/' + debug.filesTotal + ' file' + (debug.filesTotal > 1 ? 's' : '') + '.');
					msg.push('---');
					if(debug.errors > 0){
						msg.push("Errors:\t" + debug.errors);
					}
					if(debug.warnings > 0){
						msg.push("Warnings:\t" + debug.warnings);
					}
					if(debug.unique.length){
						msg.push("Unique:\t" + debug.unique.length);
					}
					if(debug.fixable){
						msg.push("Fixable:\t" + debug.fixable);
					}

					msg.push('---');
					msg.push('Check the console for more details.');

					//print and cleanup
					grunt.log.errorlns(msg.join("\n"));
					fs.unlinkSync(debugPath);
				}
				//a generic error
				else {
					grunt.log.errorlns('Your PHP code contains one or more error(s)' + (warnings ? ' and/or warning(s)' : '') + ".\nCheck the console for more details.");
				}
			}
		}

		//fix errors
		if(fix){
			try {
				grunt.log.ok('Fixing fixable errors' + (warnings ? ' and warnings' : '') + ' now...');
				exec(phpcbfPath + cmd.replace('%REPORT%',''), {stdio: [1,2,3]});
				grunt.log.ok('All automatically fixable errors' + (warnings ? ' and warnings' : '') + ' have been corrected!');
			} catch(ex){}
		}
	});

};
