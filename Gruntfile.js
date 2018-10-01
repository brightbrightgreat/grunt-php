/*global module:false*/
module.exports = function(grunt) {

	//Project configuration.
	grunt.initConfig({
		//Metadata.
		pkg: grunt.file.readJSON('package.json'),

		//JS HINT
		jshint: {
			all: ['tasks/*.js']
		},

		//PHP REVIEW
		blobphp: {
			src: process.cwd(),
			options: {
				fix: false
			}
		},

		//WATCH
		watch: {
			scripts: {
				files: ['tasks/*.js'],
				tasks: ['javascript', 'notify:js'],
				options: {
					spawn: false
				},
			}
		},

		//NOTIFY
		notify: {
			js: {
				options: {
					title: "Javascript",
					message: "JSHint task complete"
				}
			}
		}
	});

	//This plugin's tasks ;)
	grunt.loadTasks('tasks');

	//These plugins provide necessary tasks.
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-notify');

	//tasks
	grunt.registerTask('default', ['javascript','blobphpr']);
	grunt.registerTask('javascript', ['jshint']);

	grunt.event.on('watch', function(action, filepath, target) {
		grunt.log.writeln(target + ': ' + filepath + ' has ' + action);
	});
};
